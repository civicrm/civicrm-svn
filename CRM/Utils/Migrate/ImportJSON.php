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
        
        $contactDump = $decodedContacts->contact;
        $emailDump = $decodedContacts->email;
        $phoneDump = $decodedContacts->phone;
        $addressDump = $decodedContacts->address;
        $noteDump = $decodedContacts->note;
        
        //migrate contact data
        $this->migrateDump( $contactDump , 'CRM_Contact_DAO_Contact', true );
        $this->migrateDump( $emailDump , 'CRM_Core_DAO_Email', true, array('contact_id' => 'civicrm_contact') );  
        $this->migrateDump( $phoneDump , 'CRM_Core_DAO_Phone', true, array('contact_id' => 'civicrm_contact') );
        $this->migrateDump( $addressDump , 'CRM_Core_DAO_Address', true, array('contact_id' => 'civicrm_contact') );
        $this->migrateDump( $noteDump , 'CRM_Core_DAO_Note', true, array('contact_id' => 'civicrm_contact') );
        //migrate email data
        EXIT();

        // clean up all caches etc
        CRM_Core_Config::clearDBCache( );
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
    

    function copyContactData( &$contact, $save = false ) {
        require_once 'CRM/Contact/DAO/Contact.php';
        $dao = new CRM_Contact_DAO_Contact;
        $fields =& $dao->fields( );
        
        foreach ( $fields as $name => $dontCare ) {
            if ( isset( $contact->$name ) ) {
                $dao->$name = (string ) $contact->$name;
            }
        }
        if ( $save ) {
            $dao->save( );
            
            /*save contact centric data*/
            //save address
            //$this->copyAddressData( &$contact, $dao, $save ) ;
            
            //save phone
            $this->copyPhoneData( &$contact, $dao, $save ) ;
            
            //save email
            //$this->copyEmailData( &$contact, $dao, $save  ) ;
            
            //save note object
            //$this->copyNoteData( &$contact, $dao, $save ) ;
            
        }

        return true;
    }
    function copyPhoneData( &$contact, $dao, $save = false ) {
        require_once 'CRM/Core/DAO/Phone.php';
        require_once 'CRM/Utils/System.php';
        if ( !CRM_Utils_System::isNull( $contact->phone ) ) {
            foreach ( $contact->phone as $phoneString ) {
                list( $locationType, $phone, $phoneType ) = explode( ' ', $phoneString );
                $locationType = substr( $locationType, 0, -1 );
                $phoneType =substr( $phoneType, 1, -1 ); 
                
                //FIXME : need to look up location type & phone type
                $phoneDAO = new CRM_Core_DAO_Phone;
                $phoneDAO->phone = $phone;
                $phoneDAO->contact_id = $dao->id;
                if ( $save ) {
                    $phoneDAO->save( );
                }
            }
        }
    }

    function copyEmailData( &$contact, $dao, $save = false ) {
        require_once 'CRM/Core/DAO/Email.php';
        require_once 'CRM/Utils/System.php';
        if ( !CRM_Utils_System::isNull( $contact->email ) ) {
            crm_core_error::Debug('c', $contact->email);
            if ( is_array ( $contact->email ) ) {
                //multiple emails per contact
                foreach ( $contact->email as $emailString ) {
                    $emailDAO = new CRM_Core_DAO_Email;
                    list( $locationType, $email ) = explode(': ', $emailString);
                    
                    //FIXME : need to look up location type
                    $emailDAO->email = (string ) $email;
                    $emailDAO->contact_id = $dao->id;

                    if ( $save ) {
                        $emailDAO->save( );
                    }
                }
            } else {
                list( $locationType, $email ) = explode(': ', $contact->email);
                $emailDAO = new CRM_Core_DAO_Email;

                //FIXME : need to look up location type
                $emailDAO->email = (string ) $email;
                $emailDAO->contact_id = $dao->id;
                $emailDAO->is_primary = 1;
                if ( $save ) {
                    $emailDAO->save( );
                }
            }
        }

        return true;
    }

    
    function copyAddressData( &$contact, $dao, $save = false ) {
        require_once 'CRM/Core/BAO/Address.php';
        $addressDAO = new CRM_Core_DAO_Address;
        $found = false;
        $fields =& $addressDAO->fields( );
        foreach ( $fields as $name => $dontCare ) {
            if (  isset( $contact->$name )  ) {
                if ( $name == 'street_address' ) {
                    $addressDAO->$name = (string ) $contact->$name;
                    $parsedAddress = CRM_Core_BAO_Address::parseStreetAddress( $addressDAO->street_address  );
                    //crm_Core_error::debug('$parsedAddress',$parsedAddress);
                    foreach( $parsedAddress as $column => $value ) {
                        $addressDAO->$column = $parsedAddress[$column];
                    }
                    $addressDAO->contact_id = $dao->id;
                    $found = true;
                } elseif ( $name != 'contact_id' ) {
                    $addressDAO->$name = (string ) $contact->$name;
                    $addressDAO->contact_id = $dao->id;
                    $found = true;
                }

            }
        }
        if ( $save && $found ) {
            $addressDAO->save( );
        }
        return true;
    }

    function copyNoteData( &$contact, $dao, $save = false ) {
        require_once 'CRM/Core/DAO/Note.php';
        $noteDAO = new CRM_Core_DAO_Note;
        $fields =& $noteDAO->fields( );
        $found = false;
        
        if ( isset( $contact->note ) ) {
            $noteDAO->note = (string ) $contact->note;
            $noteDAO->entity_id = $dao->id;
            $noteDAO->entity_table = 'civicrm_contact';
            $found = true;
        }
        
        if ( $save && $found ) {
            $noteDAO->save( );
        }
        return true;
    }



    function mapContacts( &$contacts ) {
        
        foreach ( $contacts as $contact ) {
            $this->copyContactData( $contact, true );
            //crm_core_error::Debug('c', $contact);
        }
    }
    
    
}
