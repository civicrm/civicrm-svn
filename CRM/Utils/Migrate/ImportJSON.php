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
        
        $this->mapContacts( $decodedContacts );  
        
        // clean up all caches etc
        CRM_Core_Config::clearDBCache( );
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
            $this->copyAddressData( &$contact, $dao, $save  ) ;
            
            //save note object
            $this->copyNoteData( &$contact, $dao, $save  ) ;
            
        }

        return true;
    }

    function copyAddressData( &$contact, $dao, $save = false ) {
        require_once 'CRM/Core/BAO/Address.php';
        $addressDAO = new CRM_Core_DAO_Address;
        $found = false;
        $fields =& $addressDAO->fields( );
        foreach ( $fields as $name => $dontCare ) {
            if ( isset( $contact->$name )  ) {
                if ( $name == 'street_address' ) {
                    $addressDAO->$name = (string ) $contact->$name;
                    $parsedAddress = CRM_Core_BAO_Address::parseStreetAddress( $addressDAO->street_address  );
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
        }
    }
    
    
}
