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

class CRM_Utils_Migrate_ImportJSON {

    protected $_lookupCache;

    protected $_saveMapping;

    function __construct( ) {
        $this->_lookupCache = array( );
        $this->_saveMapping = array( );
    }

    function run( $file ) {
        $json =  file_get_contents($file);

        $decodedContacts = json_decode($json);

        $contact = $decodedContacts->civicrm_contact;
        $email   = $decodedContacts->civicrm_email;
        $phone   = $decodedContacts->civicrm_phone;
        $address = $decodedContacts->civicrm_address;
        $note    = $decodedContacts->civicrm_note;
        $relationship = $decodedContacts->civicrm_relationship;
        $activity = $decodedContacts->civicrm_activity;
        $activityTarget = $decodedContacts->civicrm_activity_target;
        $activityAssignment = $decodedContacts->civicrm_activity_assignment;
        

        //migrate contact data
        $this->contacts( $contact );
        
        //migrate contact centric data
        $this->emails( $email );
        $this->phones( $phone );
        $this->addresses( $address );
        $this->notes( $note );
        $this->relationships( $relationship );
        $this->activities( $activity,  $activityTarget, $activityAssignment );
        
        // clean up all caches etc
        CRM_Core_Config::clearDBCache( );
    }
    
    function contacts( &$contact ) {
        $this->dump( $contact ,
                     'CRM_Contact_DAO_Contact',
                     true,
                     array( 'id' => 'civicrm_contact' ) );
    }

    function emails( &$email ) {
        $this->dump( $email, 
                     'CRM_Core_DAO_Email', 
                     true, 
                     array('contact_id' => 'civicrm_contact') );
    }

    function phones( &$phone ) {
        $this->dump( $phone,
                     'CRM_Core_DAO_Phone', 
                     true, 
                     array('contact_id' => 'civicrm_contact') );
    }
    
    function addresses( &$address ) {
        $this->dump( $address ,
                     'CRM_Core_DAO_Address', 
                     true, 
                     array('contact_id' => 'civicrm_contact') );
    }
    
    function notes( &$note ) {
        $this->dump( $note ,
                     'CRM_Core_DAO_Note',
                     true, 
                     array('contact_id' => 'civicrm_contact') );
    }
    
    function relationships( &$relationship ) {
        $this->dump( $relationship  ,
                     'CRM_Contact_DAO_Relationship',
                     true,
                     array('contact_id_a' => 'civicrm_contact',
                           'contact_id_b' => 'civicrm_contact') );
    }
    
    
    function activities( $activity,  $activityTarget, $activityAssignment ) {
        $this->dump( $activity ,
                     'CRM_Activity_DAO_Activity',
                     true,
                     array('source_contact_id' => 'civicrm_contact',
                           ) );
        
        $this->dump( $activityTarget ,
                     'CRM_Activity_DAO_ActivityTarget',
                     true,
                     array( 'target_contact_id' => 'civicrm_contact',
                            'activity_id'       => 'civicrm_activity'
                            ) );
        
        $this->dump( $activityAssignment ,
                     'CRM_Activity_DAO_ActivityAssignment',
                     true,
                     array( 'assignee_contact_id' => 'civicrm_contact',
                            'activity_id'         => 'civicrm_activity'
                            )
                     );
    }
    
    function dump( &$chunk, $daoName, $save = false, $lookUpMapping = null ) {
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $daoName) . ".php");
        eval( '$object   = new ' . $daoName . '( );' );
        $tableName = $object->__table;

        if ( is_array( $lookUpMapping ) ) {
            $lookUpMapping['id'] = $tableName;
        } else {
            $lookUpMapping = array( 'id' => $tableName );
        }

        foreach ($lookUpMapping  as $columnName => $tableName ) {
            $this->populateCache( $tableName );
        }

        $saveMapping = false;
        $columns = $chunk[0];
        foreach ( $chunk as $key => $value ) {
            if ( $key ) {
                eval( '$object   = new ' . $daoName . '( );' );
                foreach ( $columns as $k => $column) {
                    if ( $column == 'id') {
                        $childID  = $value[$k];
                        $masterID = CRM_Utils_Array::value( $value[$k],
                                                            $this->_lookupCache[$tableName],
                                                            null );
                        if ( $masterID ) {
                            $object->id = $masterID;
                        }
                    } else {
                        if (array_key_exists( $column, $lookUpMapping ) ) {
                            $object->$column = $this->_lookupCache[$lookUpMapping[$column]][$value[$k]];
                        } else {
                            $object->$column = $value[$k];
                        }
                    }
                }
                
                $object->save();
                if ( ! $masterID ) {
                    $this->_lookupCache[$tableName][$childID] = $object->id;
                    $this->_saveMapping[$tableName] = true;
                }
            }
        }
    }

    function saveCache( ) {
        $sql = "INSERT INTO civicrm_migration_mapping (master_id, slave_id, entity_table ) VALUES ";

        foreach ( $this->_lookupCache as $tableName =>& $values ) {
            if ( ! $this->_saveMapping[$tableName] ) {
                continue;
            }

            $mapValues = array( );
            CRM_Core_DAO::executeQuery( "DELETE FROM civicrm_migration_mapping where entity_table = '$tableName'" );
            foreach ( $values as $childID => $masterID ) {
                $mapValues[] = "($masterID,$childID,'$tableName')";
            }
            $insertSQL = $sql . implode( ",\n", $mapValues );
            CRM_Core_DAO::executeQuery( $insertSQL );
        }
    }

    function populateCache( $tableName ) {
        if ( isset( $this->_lookupCache[$tableName] ) ) {
            return;
        }

        $this->_lookupCache[$tableName] = array();
        $this->_saveMapping[$tableName] = false;

        $query = "SELECT master_id, slave_id
FROM civicrm_migration_mapping
WHERE entity_table = '{$tableName}'
";
        
        $dao = CRM_Core_DAO::executeQuery( $query );
        while ( $dao->fetch( ) ) {
            $this->_lookupCache[$dao->slave_id] = $dao->master_id;
        }
    }
}
