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

    function __construct( ) {
    }

    function run( $file ) {
        $json =  file_get_contents($file);
        $decodedContacts = json_decode($json);

        $contact = $decodedContacts->contact;
        $email   = $decodedContacts->email;
        $phone   = $decodedContacts->phone;
        $address = $decodedContacts->address;
        $note    = $decodedContacts->note;
        
        //migrate contact data
        $this->migrateContacts( $contact );
        $this->migrateEmails( $email );
        $this->migratePhones( $phone );
        $this->migrateAddresses( $address );
        $this->migrateNotes( $note );

        // clean up all caches etc
        CRM_Core_Config::clearDBCache( );
    }
    
    function migrateContacts( &$contact ) {
        $this->migrateDump( $contact , 'CRM_Contact_DAO_Contact', true );
    }

    function migrateEmails( &$email ) {
        $this->migrateDump( $emailDump , 
                            'CRM_Core_DAO_Email', 
                            true, 
                            array('contact_id' => 'civicrm_contact') );  
    }
    
    function migratePhones( &$phone ) {
        $this->migrateDump( $phone , 
                            'CRM_Core_DAO_Phone', 
                            true, 
                            array('contact_id' => 'civicrm_contact') );
    }

    function migrateAddresses( &$address ) {
        $this->migrateDump( $address ,
                            'CRM_Core_DAO_Address', 
                            true, 
                            array('contact_id' => 'civicrm_contact') );
    }
    
    function migrateNotes( &$note ) {
        $this->migrateDump( $note ,
                            'CRM_Core_DAO_Note',
                            true, 
                            array('contact_id' => 'civicrm_contact') );
    }

    function migrateDump( &$chunk, $daoName, $save = false, $lookUpMapping = false ) {
        if ( $lookUpMapping ) {
            $lookUp = array();
            foreach ($lookUpMapping  as $columnName => $tableName ) {
                $query = "SELECT master_id, slave_id
FROM civicrm_migration_mapping
WHERE entity_table = '{$tableName}'
";
                
                $dao = CRM_Core_DAO::executeQuery( $query );
                $lookUp[$columnName] = array();
                while ( $dao->fetch( ) ) {
                    $lookUp[$columnName][$dao->slave_id] = $dao->master_id;
                }
            }
        }
        
        $saveMapping = false;
        require_once(str_replace('_', DIRECTORY_SEPARATOR, $daoName) . ".php");
        eval( '$object   = new ' . $daoName . '( );' );
        $tableName = $object->__table;
        $columns = $chunk[0];
        foreach ( $chunk as $key => $value ) {
            if ( $key ) {
                eval( '$object   = new ' . $daoName . '( );' );
                foreach ( $columns as $k => $column) {
                    if ( $column == 'id') {
                        $childId = $value[$k];
                    } else {
                        if ( $lookUp ) {
                            if (array_key_exists( $column, $lookUp ) ) {
                                $object->$column = $lookUp[$column][$value[$k]];
                            } else {
                                $object->$column = $value[$k];
                            }
                            
                        } else {
                            $object->$column = $value[$k];
                        }
                    }
                }
                
                $object->save( );                                                                                                                                                                $masterId = $object->id;
                
                //dump into mapping DB 
                $mapValue[] = "( $masterId, $childId, '$tableName' )";
                $saveMapping = true;
            }
        }
        
        if ( $saveMapping ) { 
            $insert = "INSERT INTO civicrm_migration_mapping (master_id, slave_id, entity_table ) VALUES ";
            $mapValues = implode( ",\n",$mapValue );
            
            $sql = $insert . $mapValues;
            CRM_Core_DAO::executeQuery( $sql );
        }
    }
           
}
