<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

class CRM_Utils_Migrate_ExportJSON {

    const
        CHUNK_SIZE = 128;

    function __construct( ) {
    }


    /**
     * Split a large array of contactIDs into more manageable smaller chunks
     */
    function &splitContactIDs( &$contactIDs ) {
        // contactIDs could be a real large array, so we split it up into
        // smaller chunks and then general xml for each chunk
        $chunks           = array( );
        $current          = 0;
        $chunks[$current] = array( );
        $count            = 0;

        foreach ( $contactIDs as $k => $v ) {
            $chunks[$current][$k] = $v;
            $count++;

            if ( $count == self::CHUNK_SIZE ) {
                $current++;
                $chunks[$current] = array( );
                $count            = 0;
            }
        }
     
        if ( empty( $chunks[$current] ) ) {
            unset( $chunks[$current] );
        }

        return $chunks;
    }

    /**
     * Given a set of contact IDs get the values
     */
    function getValues( &$contactIDs, &$values, &$allContactIDs, &$addditionalContactIDs ) {
        $values = array( );
    
        $this->getContactInfo( $contactIDs, $values );
        $this->getAddressInfo( $contactIDs, $values );
        $this->getPhoneInfo  ( $contactIDs, $values );
        $this->getEmailInfo  ( $contactIDs, $values );
        $this->getNoteInfo   ( $contactIDs, $values );

        $this->getRelationshipInfo( $contactIDs, $values, $allContactIDs, $addditionalContactIDs );

        $this->getActivityInfo( $contactIDs, $values, $allContactIDs, $addditionalContactIDs );

        // got to do groups, tags

        // got to do meta data

        return $values;
    }

    function getTableInfo( &$ids, &$values, $tableName, &$fields,
                           $whereField, $additionalWhereCond = null ) {
        $idString     = implode( ',', $ids );

        $sql = "
SELECT *
  FROM $tableName
 WHERE $whereField IN ( $idString )
";
    
        if ( $additionalWhereCond ) {
            $sql .= " AND $additionalWhereCond";
        }

        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch( ) ) {
            $value = array( );
            foreach ( $fields as $name ) {
                if ( empty( $dao->$name ) ) {
                    $value[$name] = null;
                } else {
                    $value[$name] = $dao->$name;
                }
            }
            $this->appendValue( $values, $dao->id, $tableName, $value );
        }
        $dao->free( );
    }

    function getContactInfo( &$contactIDs, &$values ) {
        $fields =& $this->getDBFields( 'CRM_Contact_DAO_Contact', true );
        $this->getTableInfo( $contactIDs, $values, 'civicrm_contact', $fields, 'id', null );
    }


    function getNoteInfo( &$contactIDs, &$values ) {
        $fields =& $this->getDBFields( 'CRM_Core_DAO_Note', true );
        $this->getTableInfo( $contactIDs, $values, 'civicrm_note', $fields, 'entity_id', "entity_table = 'civicrm_contact'" );
    }

    function getPhoneInfo( &$contactIDs, &$values ) {
        $fields =& $this->getDBFields( 'CRM_Core_DAO_Phone', true );
        $this->getTableInfo( $contactIDs, $values, 'civicrm_phone', $fields, 'contact_id', null );
    }

    function getEmailInfo( &$contactIDs, &$values ) {
        $fields =& $this->getDBFields( 'CRM_Core_DAO_Email', true );
        $this->getTableInfo( $contactIDs, $values, 'civicrm_email', $fields, 'contact_id', null );
    }

    function getAddressInfo( &$contactIDs, &$values ) {
        $fields =& $this->getDBFields( 'CRM_Core_DAO_Email', true );
        $this->getTableInfo( $contactIDs, $values, 'civicrm_address', $fields, 'contact_id', null );
    }

    function getRelationshipInfo( &$contactIDs, &$values, &$allContactIDs, &$additionalContacts ) {
        // handle relationships only once
        static $_relationshipsHandled = array( );

        $ids = implode( ',', $contactIDs );

        $sql = "(
  SELECT     r.*
  FROM       civicrm_relationship r
  WHERE      r.contact_id_a IN ( $ids )
) UNION (
  SELECT     r.*
  FROM       civicrm_relationship r
  WHERE      r.contact_id_b IN ( $ids )
)
";

        $fields = $this->getDBFields( 'CRM_Contact_DAO_Relationship', true );
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch( ) ) {
            if ( isset( $_relationshipsHandled[$dao->id] ) ) {
                continue;
            }
            $_relationshipsHandled[$dao->id] = $dao->id;

            $relationship = array( );
            foreach ( $fields as $fld ) {
                if ( empty( $dao->$fld ) ) {
                    $relationship[$fld] = null;
                } else {
                    $relationship[$fld] = $dao->$fld;
                }
            }
            $this->appendValue( $values, $dao->id, 'relationship', $relationship );

            $this->addAdditionalContacts( array( $dao->contact_id_a, 
                                          $dao->contact_id_b ),
                                   $allContactIDs, $additionalContacts );
        }
        $dao->free( );
    }

    function getActivityInfo( &$contactIDs, &$values, &$allContactIDs, &$additionalContacts ) {
        static $_activitiesHandled = array( );

        $ids = implode( ',', $contactIDs );

        $sql = "(
  SELECT     a.*
  FROM       civicrm_activity a
  INNER JOIN civicrm_activity_assignment aa ON aa.activity_id = a.id
  WHERE      aa.assignee_contact_id IN ( $ids )
    AND      ( a.activity_type_id != 3 AND a.activity_type_id != 20 )
) UNION (
  SELECT     a.*
  FROM       civicrm_activity a
  INNER JOIN civicrm_activity_target at ON at.activity_id = a.id
  WHERE      at.target_contact_id IN ( $ids )
    AND      ( a.activity_type_id != 3 AND a.activity_type_id != 20 )
)
";

        $fields =& $this->getDBFields( 'CRM_Activity_DAO_Activity', true );

        $activityIDs = array( );
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch( ) ) {
            if ( isset( $_activitiesHandled[$dao->id] ) ) {
                continue;
            }
            $_activitiesHandled[$dao->id] = $dao->id;
            $activityIDs[] = $dao->id;

            $activity = array( );
            foreach ( $fields as $fld ) {
                if ( empty( $dao->$fld ) ) {
                    $activity[$fld] = null;
                } else {
                    $activity[$fld] = $dao->$fld;
                }
            }

            $this->appendValue( $values, $dao->id, 'activity', $activity );
            $this->addAdditionalContacts( array( $dao->source_contact_id ),
                                   $allContactIDs, $additionalContacts );
        }
        $dao->free( );

        if ( empty( $activityIDs ) ) {
            return;
        }

        $activityIDString = implode( ",", $activityIDs );

        // now get all assignee contact ids and target contact ids for this activity
        $sql = "SELECT * FROM civicrm_activity_assignment WHERE activity_id IN ($activityIDString)";
        $aaDAO =& CRM_Core_DAO::executeQuery( $sql );
        $activityContacts = array( );
        while ( $aaDAO->fetch( ) ) {
            $activityAssignee = array( 'id'                  => $aaDAO->id,
                                       'assignee_contact_id' => $aaDAO->assignee_contact_id,
                                       'activity_id'         => $aaDAO->activity_id );
            $this->appendValue( $values, $aaDAO->id, 'activity_assignment', $activityAssignee );
            $activityContacts[] = $aaDAO->assignee_contact_id;
        }
        $aaDAO->free( );
    
        $sql = "SELECT * FROM civicrm_activity_target WHERE activity_id IN ($activityIDString)";
        $atDAO =& CRM_Core_DAO::executeQuery( $sql );
        while ( $atDAO->fetch( ) ) {
            $activityTarget = array( 'id'                => $atDAO->id,
                                     'target_contact_id' => $atDAO->target_contact_id,
                                     'activity_id'       => $atDAO->activity_id );
            $this->appendValue( $values, $atDAO->id, 'activity_target', $activityTarget );
            $activityContacts[] = $atDAO->target_contact_id;
        }
        $atDAO->free( );

        $this->addAdditionalContacts( $activityContacts, $allContactIDs, $additionalContacts );

    }

    function appendValue( &$values, $id, $name, $value ) {
        if ( empty( $value ) ) {
            return;
        }

        if ( ! isset( $values[$name] ) ) {
            $values[$name] = array( );
            $values[$name][] = array_keys( $value );
        }
        $values[$name][] = array_values( $value );
    }

    function getDBFields( $daoName, $onlyKeys = false ) {
        static $_fieldsRetrieved = array( );

        if ( ! isset( $_fieldsRetrieved[$daoName] ) ) {
            $_fieldsRetrieved[$daoName] = array( );
            $daoFile = str_replace( '_',
                                    DIRECTORY_SEPARATOR,
                                    $daoName ) . '.php';
            include_once( $daoFile );
        
            $daoFields =& $daoName::fields( );
            require_once 'CRM/Utils/Array.php';

            foreach ( $daoFields as $key =>& $value ) {
                $_fieldsRetrieved[$daoName][$value['name']] = array( 'uniqueName' => $key,
                                                                     'type'       => $value['type'],
                                                                     'title'      => CRM_Utils_Array::value( 'title',$value, null ) );
            }
        }

        if ( $onlyKeys ) {
            return array_keys( $_fieldsRetrieved[$daoName] );
        } else {
            return $_fieldsRetrieved[$daoName];
        }
    }

    function addAdditionalContacts( $contactIDs, &$allContactIDs, &$additionalContacts ) {
        foreach ( $contactIDs as $cid ) {
            if ( $cid &&
                 ! isset( $allContactIDs[$cid] ) &&
                 ! isset( $additionalContacts[$cid] ) ) {
                $additionalContacts[$cid] = $cid;
            }
        }
    }

    function export( &$values, &$contactIDs, &$allContactIDs ) {
        $chunks =& $this->splitContactIDs( $contactIDs );

        $additionalContactIDs = array( );

        foreach ( $chunks as $chunk ) {
            $this->getValues( $chunk, $values, $allContactIDs, $additionalContactIDs );
        }

        if ( ! empty( $additionalContactIDs ) ) {
            $allContactIDs = $allContactIDs + $additionalContactIDs;
            $this->export( $values, $additionalContactIDs, $allContactIDs );
        }

    }

    function run( $fileName, $sql = null ) {
        if ( ! $sql ) {
            $sql = "
SELECT id 
FROM civicrm_contact
LIMIT 10
";
        }

        $dao =& CRM_Core_DAO::executeQuery( $sql );
        
        $contactIDs = array( );
        while ( $dao->fetch( ) ) {
            $contactIDs[$dao->id] = $dao->id;
        }
        
        $values = array( );
        $this->export( $values, $contactIDs, $contactIDs );
        
        $json = json_encode( $values );
        file_put_contents( $fileName,
                           $json );
        
        print_r( json_decode( $json ) );
    }

}
