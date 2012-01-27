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

    protected $_contactIDs;

    protected $_allContactIDs;

    protected $_values;

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
    function getValues( &$contactIDs, &$additionalContactIDs ) {

        $this->getContact     ( $contactIDs );
        $this->getAddress     ( $contactIDs );
        $this->getPhone       ( $contactIDs );
        $this->getEmail       ( $contactIDs );
        $this->getNote        ( $contactIDs );

        $this->getGroup       ( $contactIDs );
        $this->getGroupContact( $contactIDs );

        $this->getTag         ( $contactIDs );
        $this->getEntityTag   ( $contactIDs );

        $this->getRelationship( $contactIDs, $additionalContactIDs );
        $this->getActivity    ( $contactIDs, $additionalContactIDs );
    }

    function getMetaData( ) {
        $optionGroupVars = 
            array(
                  'prefix_id'       => 'individual_prefix',
                  'suffix_id'       => 'individual_suffix',
                  'gender_id'       => 'gender',
                  'mobile_provider' => 'mobile_provider',
                  'phone_type'      => 'phone_type',
                  'activity_type'   => 'activity_type',
                  'status_id'       => 'activity_status_id',
                  'priority_id'     => 'activity_priority_id',
                  'medium_id'       => 'encounter_medium',
                  'email_greeting'  => 'email_greeting',
                  'postal_greeting' => 'postal_greeting',
                  'addressee_id'    => 'addressee',
                  );
        $this->getOptionGroup( $optionGroupVars );

        $auxilaryTables = array( 'civicrm_location_type'     => 'CRM_Core_DAO_LocationType',
                                 'civicrm_relationship_type' => 'CRM_Contact_DAO_RelationshipType' );
        $this->getAuxTable( $auxilaryTables );
    }
    
    function getAuxTable( $tables ) {
        foreach ( $tables as $tableName => $daoName ) {
            $fields =& $this->getDBFields( $daoName, true );
            
            $sql = "SELECT * from $tableName";
            $this->getSQL( $sql, $tableName, $fields );
        }
    }

    function getOptionGroup( $optionGroupVars ) {
        $names = array_values( $optionGroupVars );
        $str = array( );
        foreach ( $names as $name ) {
            $str[] = "'$name'";
        }
        $nameString = implode( ",", $str );

        $sql = "
SELECT *
FROM   civicrm_option_group
WHERE  name IN ( $nameString )
";
        $fields =& $this->getDBFields( 'CRM_Core_DAO_OptionGroup', true );
        $this->getSQL( $sql, 'civicrm_option_group', $fields );

        $sql = "
SELECT     v.*
FROM       civicrm_option_value v
INNER JOIN civicrm_option_group g ON v.option_group_id = g.id
WHERE      g.name IN ( $nameString )
";
        $fields =& $this->getDBFields( 'CRM_Core_DAO_OptionValue', true );
        $this->getSQL( $sql, 'civicrm_option_value', $fields );
    }

    function getTable( &$ids,
                       $tableName,
                       &$fields,
                       $whereField,
                       $additionalWhereCond = null ) {
        if ( empty( $ids ) ) {
            return;
        }

        $idString     = implode( ',', $ids );

        $sql = "
SELECT *
  FROM $tableName
 WHERE $whereField IN ( $idString )
";
    
        if ( $additionalWhereCond ) {
            $sql .= " AND $additionalWhereCond";
        }

        $this->getSQL( $sql, $tableName, &$fields );
    }

    function getSQL( $sql, $tableName, &$fields ) {
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
            $this->appendValue( $dao->id, $tableName, $value );
        }
        $dao->free( );
    }

    function getContact( &$contactIDs ) {
        $fields =& $this->getDBFields( 'CRM_Contact_DAO_Contact', true );
        $this->getTable( $contactIDs, 'civicrm_contact', $fields, 'id', null );
    }


    function getNote( &$contactIDs ) {
        $fields =& $this->getDBFields( 'CRM_Core_DAO_Note', true );
        $this->getTable( $contactIDs, 'civicrm_note', $fields, 'entity_id', "entity_table = 'civicrm_contact'" );
    }

    function getPhone( &$contactIDs ) {
        $fields =& $this->getDBFields( 'CRM_Core_DAO_Phone', true );
        $this->getTable( $contactIDs, 'civicrm_phone', $fields, 'contact_id', null );
    }

    function getEmail( &$contactIDs ) {
        $fields =& $this->getDBFields( 'CRM_Core_DAO_Email', true );
        $this->getTable( $contactIDs, 'civicrm_email', $fields, 'contact_id', null );
    }

    function getAddress( &$contactIDs ) {
        $fields =& $this->getDBFields( 'CRM_Core_DAO_Email', true );
        $this->getTable( $contactIDs, 'civicrm_address', $fields, 'contact_id', null );
    }

    function getGroupContact( &$contactIDs ) {
        $fields =& $this->getDBFields( 'CRM_Contact_DAO_GroupContact', true );
        $this->getTable( $contactIDs, 'civicrm_group_contact', $fields, 'contact_id', null );
    }

    function getGroup( &$contactIDs ) {
        // handle groups only once
        static $_groupsHandled = array( );

        $ids = implode( ',', $contactIDs );

        $sql = "
SELECT DISTINCT group_id
FROM   civicrm_group_contact
WHERE  contact_id IN ( $ids )
";
        $dao = CRM_Core_DAO::executeQuery( $sql );
        $groupIDs = array( );
        while ( $dao->fetch( ) ) {
            if ( ! isset( $_groupsHandled[$dao->group_id] ) ) {
                $groupIDs[] = $dao->group_id;
                $_groupsHandled[$dao->group_id] = 1;
            }
        }

        $fields =& $this->getDBFields( 'CRM_Contact_DAO_Group', true );
        $this->getTable( $groupIDs, 'civicrm_group', $fields, 'id' );
    }

    function getEntityTag( &$contactIDs ) {
        $fields =& $this->getDBFields( 'CRM_Core_DAO_EntityTag', true );
        $this->getTable( $contactIDs, 'civicrm_entity_tag', $fields, 'entity_id', "entity_table = 'civicrm_contact'" );
    }

    function getTag( &$contactIDs ) {
        // handle tags only once
        static $_tagsHandled = array( );

        $ids = implode( ',', $contactIDs );

        $sql = "
SELECT DISTINCT tag_id
FROM   civicrm_entity_tag
WHERE  entity_id IN ( $ids )
AND    entity_table = 'civicrm_contact'
";
        $dao = CRM_Core_DAO::executeQuery( $sql );
        $tagIDs = array( );
        while ( $dao->fetch( ) ) {
            if ( ! isset( $_tagsHandled[$dao->tag_id] ) ) {
                $tagIDs[] = $dao->tag_id;
                $_tagsHandled[$dao->tag_id] = 1;
            }
        }

        $fields =& $this->getDBFields( 'CRM_Core_DAO_Tag', true );
        $this->getTable( $tagIDs, 'civicrm_tag', $fields, 'id' );
    }

    function getRelationship( &$contactIDs, &$additionalContacts ) {
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
            $this->appendValue( $dao->id, 'civicrm_relationship', $relationship );

            $this->addAdditionalContacts( array( $dao->contact_id_a, 
                                          $dao->contact_id_b ),
                                          $additionalContacts );
        }
        $dao->free( );
    }

    function getActivity( &$contactIDs, &$additionalContacts ) {
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

            $this->appendValue( $dao->id, 'civicrm_activity', $activity );
            $this->addAdditionalContacts( array( $dao->source_contact_id ),
                                          $additionalContacts );
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
            $this->appendValue( $aaDAO->id, 'civicrm_activity_assignment', $activityAssignee );
            $activityContacts[] = $aaDAO->assignee_contact_id;
        }
        $aaDAO->free( );
    
        $sql = "SELECT * FROM civicrm_activity_target WHERE activity_id IN ($activityIDString)";
        $atDAO =& CRM_Core_DAO::executeQuery( $sql );
        while ( $atDAO->fetch( ) ) {
            $activityTarget = array( 'id'                => $atDAO->id,
                                     'target_contact_id' => $atDAO->target_contact_id,
                                     'activity_id'       => $atDAO->activity_id );
            $this->appendValue( $atDAO->id, 'civicrm_activity_target', $activityTarget );
            $activityContacts[] = $atDAO->target_contact_id;
        }
        $atDAO->free( );

        $this->addAdditionalContacts( $activityContacts, $additionalContacts );

    }

    function appendValue( $id, $name, $value ) {
        if ( empty( $value ) ) {
            return;
        }

        if ( ! isset( $this->_values[$name] ) ) {
            $this->_values[$name] = array( );
            $this->_values[$name][] = array_keys( $value );
        }
        $this->_values[$name][] = array_values( $value );
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

    function addAdditionalContacts( $contactIDs, &$additionalContacts ) {
        foreach ( $contactIDs as $cid ) {
            if ( $cid &&
                 ! isset( $this->_allContactIDs[$cid] ) &&
                 ! isset( $additionalContacts[$cid] ) ) {
                $additionalContacts[$cid] = $cid;
            }
        }
    }

    function export( &$contactIDs ) {
        $chunks =& $this->splitContactIDs( $contactIDs );

        $additionalContactIDs = array( );

        foreach ( $chunks as $chunk ) {
            $this->getValues( $chunk, $additionalContactIDs );
        }

        if ( ! empty( $additionalContactIDs ) ) {
            $this->_allContactIDs = $this->_allContactIDs + $additionalContactIDs;
            $this->export( $additionalContactIDs );
        }

    }

    function run( $fileName, $sql = null ) {
        if ( ! $sql ) {
            $sql = "
SELECT id 
FROM civicrm_contact
";
        }

        $dao =& CRM_Core_DAO::executeQuery( $sql );
        
        $contactIDs = array( );
        while ( $dao->fetch( ) ) {
            $contactIDs[$dao->id] = $dao->id;
        }

        $this->_allContactIDs = $contactIDs;
        $this->_values = array( );

        $this->getMetaData( );

        $this->export( $contactIDs );
        
        $json = json_encode( $this->_values );
        file_put_contents( $fileName,
                           $json );
        
        // print_r( json_decode( $json ) );
    }

}
