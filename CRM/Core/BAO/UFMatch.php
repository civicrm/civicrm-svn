<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.9                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Core/Session.php';
require_once 'CRM/Core/DAO/UFMatch.php';

/**
 * The basic class that interfaces with the external user framework
 */
class CRM_Core_BAO_UFMatch extends CRM_Core_DAO_UFMatch {
    /**
     * Given a UF user object, make sure there is a contact
     * object for this user. If the user has new values, we need
     * to update the CRM DB with the new values
     *
     * @param Object  $user    the drupal user object
     * @param boolean $update  has the user object been edited
     * @param         $uf
     * 
     * @return void
     * @access public
     * @static
     */
    static function synchronize( &$user, $update, $uf, $ctype ) {
        $session =& CRM_Core_Session::singleton( );
        if ( ! is_object( $session ) ) {
            CRM_Core_Error::fatal( 'wow, session is not an object?' );
            return;
        }

        if ( $uf == 'Drupal' ) {
            $key  = 'uid';
            $mail = 'mail';
        } else if ( $uf == 'Joomla' ) {
            $key  = 'id';
            $mail = 'email';
        } else if ( $uf == 'Standalone' ) {
            // There is no CMS to synchronize with in the standalone version,
            //  so just return.
            return;
        } else {
            CRM_Core_Error::statusBounce(ts('Please set the user framework variable'));
        }

        // have we already processed this user, if so early
        // return.
        $userID = $session->get( 'userID' );
        $ufID   = $session->get( 'ufID'   );
        if ( ! $update && $ufID == $user->$key ) {
            return;
        }

        // reset the session if we are a different user
        if ( $ufID && $ufID != $user->$key ) {
            $session->reset( );
        }

        // make sure we load the joomla object to get valid information
        if ( $uf == 'Joomla' ) {
            if ( class_exists( 'JFactory' ) ) {
                $user =& JFactory::getUser( );
            } else {
                $user->load( );
            }
        }

        // if the id of the object is zero (true for anon users in drupal)
        // return early
        if ( $user->$key == 0 ) {
            return;
        }
        
        $ufmatch =& self::synchronizeUFMatch( $user, $user->$key, $user->$mail, $uf, null, $ctype );
        if ( ! $ufmatch ) {
            return;
        }

        $session->set( 'ufID'    , $ufmatch->uf_id       );
        $session->set( 'userID'  , $ufmatch->contact_id );
        $session->set( 'domainID', $ufmatch->domain_id  ); 
        $session->set( 'ufEmail' , $ufmatch->email      );

        if ( $update ) {
            // the only information we care about is email, so lets check that
            if ( $user->$mail != $ufmatch->email ) {
                // email has changed, so we need to change all our primary email also
                $ufmatch->email = $user->$mail;
                $ufmatch->save( );

                $query = "
UPDATE  civicrm_contact
LEFT JOIN civicrm_location ON ( civicrm_location.entity_table = 'civicrm_contact' AND
                                civicrm_contact.id  = civicrm_location.entity_id  AND
                                civicrm_location.is_primary = 1 )
LEFT JOIN civicrm_email    ON ( civicrm_location.id = civicrm_email.location_id   AND
                                civicrm_email.is_primary = 1    )
SET civicrm_email.email = %1 WHERE civicrm_contact.id = %2 ";

                $p = array( 1 => array( $user->$mail        , 'String'  ),
                            2 => array( $ufmatch->contact_id, 'Integer' ) );
                CRM_Core_DAO::executeQuery( $query, $p );
            }
        }
    }

    /**
     * Synchronize the object with the UF Match entry. Can be called stand-alone from
     * the drupalUsers script
     *
     * @param Object  $user    the drupal user object
     * @param string  $userKey the id of the user from the uf object
     * @param string  $mail    the email address of the user
     * @param string  $uf      the name of the user framework
     * @param integer $status  returns the status if user created or already exits (used for CMS sync)
     *
     * @return the ufmatch object that was found or created
     * @access public
     * @static
     */
    static function &synchronizeUFMatch( &$user, $userKey, $mail, $uf, $status = null, $ctype = null ) {
        // validate that mail is a valid email address. hopefully there is
        // not too many conflicting emails between the CMS and CiviCRM
        require_once 'CRM/Utils/Rule.php';
        if ( ! CRM_Utils_Rule::email( $mail ) ) {
            return $status ? null : false;
        }
        
        $newContact   = false;

        // make sure that a contact id exists for this user id
        $ufmatch =& new CRM_Core_DAO_UFMatch( );
        $ufmatch->uf_id = $userKey;
        $ufmatch->domain_id = CRM_Core_Config::domainID( );
        if ( ! $ufmatch->find( true ) ) {
            require_once 'CRM/Contact/BAO/Contact.php';
            $dao =& CRM_Contact_BAO_Contact::matchContactOnEmail( $mail, $ctype );
            if ( $dao ) {
                $ufmatch->contact_id = $dao->contact_id;
                $ufmatch->domain_id  = $dao->domain_id ;
                $ufmatch->email      = $mail;
            } else {
                require_once 'CRM/Core/BAO/LocationType.php';
                $locationType   =& CRM_Core_BAO_LocationType::getDefault( );  
                $params = array( 'email' => $mail, 'location_type' => $locationType->name );
                if ( $ctype == 'Organization' ) {
                    $params['organization_name'] = $mail;
                } else if ( $ctype == 'Household' ) {
                    $params['household_name'] = $mail;
                }
                if ( ! $ctype ) {
                    $ctype = "Individual";
                }
                $params['contact_type'] = $ctype;

                // extract first / middle / last name
                // for joomla
                if ( $uf == 'Joomla' && $user->name ) {
                    $name = trim( $user->name );
                    $names = explode( ' ', $user->name );
                    if ( count( $names ) == 1 ) {
                        $params['first_name'] = $names[0];
                    } else if ( count( $names ) == 2 ) {
                        $params['first_name'] = $names[0];
                        $params['last_name' ] = $names[1];
                    } else {
                        $params['first_name' ] = $names[0];
                        $params['middle_name'] = $names[1];
                        $params['last_name'  ] = $names[2];
                    }
                }
                
                require_once 'api/Contact.php';
                $contact =& crm_create_contact( $params, $ctype, false );
                
                if ( is_a( $contact, 'CRM_Core_Error' ) ) {
                    CRM_Core_Error::debug( 'error', $contact );
                    exit(1);
                }
                $ufmatch->contact_id = $contact->id;
                $ufmatch->domain_id  = $contact->domain_id ;
                $ufmatch->email      = $mail;
            }
            $ufmatch->save( );
            $newContact   = true;
        }

        if ( $status ) {
            return $newContact;
        } else {
            return $ufmatch;
        }
    }

    /**
     * update the email in the user object
     *
     * @param int    $contactId id of the contact to delete
     *
     * @return void
     * @access public
     * @static
     */
    static function updateUFEmail( $contactId ) {
        $email = CRM_Contact_BAO_Contact::getPrimaryEmail( $contactId );
        if ( ! $email ) {
            return;
        }

        $ufmatch =& new CRM_Core_DAO_UFMatch( );
        $ufmatch->contact_id = $contactId;
        if ( ! $ufmatch->find( true ) || $ufmatch->email == $email ) {
            // if object does not exist or the email has not changed
            return;
        }

        // save the updated ufmatch object
        $ufmatch->email = $email;
        $ufmatch->save( );
        $config =& CRM_Core_Config::singleton( ); 
        if ( $config->userFramework == 'Drupal' ) { 
            $user = user_load( array( 'uid' => $ufmatch->uf_id ) );
            user_save( $user, array( 'mail' => $email ) );
            $user = user_load( array( 'uid' => $ufmatch->uf_id ) );
        }
    }
    
    /**
     * Update the email value for the contact and user profile
     *  
     * @param  $contactId  Int     Contact ID of the user
     * @param  $email      String  email to be modified for the user
     *
     * @return void
     * @access public
     * @static
     */
    static function updateContactEmail($contactId, $emailAddress) 
    {
        $ufmatch =& new CRM_Core_DAO_UFMatch( );
        $ufmatch->contact_id = $contactId;
        if ( $ufmatch->find( true ) ) {
            // Save the email in UF Match table
            $ufmatch->email = $emailAddress;
            $ufmatch->save( );
            
            //check if the primary email for the contact exists 
            //$contactDetails[1] - email 
            //$contactDetails[3] - location id
            $contactDetails = CRM_Contact_BAO_Contact::getEmailDetails($contactId);

            if (trim($contactDetails[1])) {
                //update if record is found
                $query ="UPDATE  civicrm_contact, civicrm_location,civicrm_email
                     SET email = %1
                     WHERE civicrm_location.entity_table = 'civicrm_contact' 
                       AND civicrm_contact.id  = civicrm_location.entity_id 
                       AND civicrm_location.is_primary = 1 
                       AND civicrm_location.id = civicrm_email.location_id 
                       AND civicrm_email.is_primary = 1   
                       AND civicrm_contact.id =  %2";
                $p = array( 1 => array( $emailAddress, 'String'  ),
                            2 => array( $contactId   , 'Integer' ) );
                $dao =& CRM_Core_DAO::executeQuery( $query, $p );
            } else {
                //else insert a new email record
                $email =& new CRM_Core_DAO_Email();
                $email->location_id = $contactDetails[3];
                $email->is_primary  = 1;
                $email->email       = $emailAddress; 
                $email->save( );
                $emailID = $email->id;
            }
            require_once 'CRM/Core/BAO/Log.php';
            // we dont know the email id, so we use the location id
            CRM_Core_BAO_Log::register( $contactId,
                                        'civicrm_location',
                                        $contactDetails[3] );
        }
    }
    
    /**
     * Delete the object records that are associated with this contact
     *
     * @param  int  $contactID id of the contact to delete
     *
     * @return void
     * @access public
     * @static
     */
    static function deleteContact( $contactID ) {
        $ufmatch =& new CRM_Core_DAO_UFMatch( );

        $ufmatch->contact_id = $contactID;
        $ufmatch->delete( );
    }

    /**
     * Delete the object records that are associated with this cms user
     *
     * @param  int  $ufID id of the user to delete
     *
     * @return void
     * @access public
     * @static
     */
    static function deleteUser( $ufID ) {
        $ufmatch =& new CRM_Core_DAO_UFMatch( );

        $ufmatch->uf_id = $ufID;
        $ufmatch->delete( );
    }

    /**
     * get the contact_id given a uf_id
     *
     * @param int  $ufID  Id of UF for which related contact_id is required
     *
     * @return int    contact_id on success, null otherwise
     * @access public
     * @static
     */
    static function getContactId( $ufID ) {
        if (!isset($ufID)) {
            return null;
        }

        $ufmatch =& new CRM_Core_DAO_UFMatch( );

        $ufmatch->uf_id = $ufID;
        if ( $ufmatch->find( true ) ) {
            return $ufmatch->contact_id;
        }
        return null;
    }

    /** 
     * get the uf_id given a contact_id 
     * 
     * @param int  $contactID   ID of the contact for which related uf_id is required
     * 
     * @return int    uf_id of the given contact_id on success, null otherwise
     * @access public 
     * @static 
     */ 
    static function getUFId( $contactID ) { 
        if (!isset($contactID)) { 
            return null; 
        } 
        
        $ufmatch =& new CRM_Core_DAO_UFMatch( ); 
        
        $ufmatch->contact_id = $contactID;
        if ( $ufmatch->find( true ) ) {
            return $ufmatch->uf_id;
        }
        return null;
    }
    /**
     * get the list of contact_id
     *
     *
     * @return int    contact_id on success, null otherwise
     * @access public
     * @static
     */
    static function getContactIDs() {
        $id = array();
        $dao =& new CRM_Core_DAO_UFMatch();
        $dao->find();
        while ($dao->fetch()) {
            $id[] = $dao->contact_id;
        }
        return $id;
    }
}
?>
