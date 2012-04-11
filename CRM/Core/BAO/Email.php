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


/**
 * This class contains functions for email handling
 */
class CRM_Core_BAO_Email extends CRM_Core_DAO_Email 
{
  /*
   * Create email address - note that the create function calls 'add' but 
   * has more business logic & calls the hooks
   * 
   * @param array $params input parameters
   */
  static function create($params){
    if ( !empty($params['contact_id']) ) {
      CRM_Utils_Hook::pre( 'edit', 'email', $params['id'], $params );
    } else {
      CRM_Utils_Hook::pre( 'create', 'email', null, $params ); 
      $isEdit = false;
    }
    if(is_integer(CRM_Utils_Array::value('is_primary', $params)) ||
      empty($params['id'])){// if id is set & is_primary isn't we can assume no change
      self::handlePrimary($params);
    }
    return CRM_Core_BAO_Email::add($params);
    
    if ( CRM_Utils_Array::value( 'id', $params ) ) {
      CRM_Utils_Hook::post( 'edit', 'Group', $group->id, $group );
    } else {
      CRM_Utils_Hook::post( 'create', 'Group', $group->id, $group ); 
   }
  }
  
    /**
     * takes an associative array and adds email
     *
     * @param array  $params         (reference ) an assoc array of name/value pairs
     *
     * @return object       CRM_Core_BAO_Email object on success, null otherwise
     * @access public
     * @static
     */
    static function add( &$params ) 
    {
        $email = new CRM_Core_DAO_Email( );
        $email->copyValues($params);

        // lower case email field to optimize queries
        $strtolower = function_exists('mb_strtolower') ? 'mb_strtolower' : 'strtolower';
        $email->email = $strtolower( $email->email );

        // since we're setting bulkmail for 1 of this contact's emails, first reset all their emails to is_bulkmail false
        // (only 1 email address can have is_bulkmail = true)
        if ( $email->is_bulkmail != 'null' && 
             $params['contact_id']         &&
             ! self::isMultipleBulkMail( ) ) {
            $sql = "
UPDATE civicrm_email 
SET    is_bulkmail = 0
WHERE  contact_id = {$params['contact_id']}
";
            CRM_Core_DAO::executeQuery( $sql );
        }

        // handle if email is on hold
        self::holdEmail( $email );

        return $email->save( );
    }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array $entityBlock   input parameters to find object
     *
     * @return boolean
     * @access public
     * @static
     */
    static function &getValues( $entityBlock ) 
    {
        return CRM_Core_BAO_Block::getValues( 'email', $entityBlock );
    }
   
    /**
     * Get all the emails for a specified contact_id, with the primary email being first
     *
     * @param int $id the contact id
     *
     * @return array  the array of email id's
     * @access public
     * @static
     */
    static function allEmails( $id, $updateBlankLocInfo = false ) 
    {
        if ( ! $id ) {
            return null;
        }

        $query = "
SELECT email, civicrm_location_type.name as locationType, civicrm_email.is_primary as is_primary, civicrm_email.on_hold as on_hold,
civicrm_email.id as email_id, civicrm_email.location_type_id as locationTypeId
FROM      civicrm_contact
LEFT JOIN civicrm_email ON ( civicrm_email.contact_id = civicrm_contact.id )
LEFT JOIN civicrm_location_type ON ( civicrm_email.location_type_id = civicrm_location_type.id )
WHERE
  civicrm_contact.id = %1
ORDER BY
  civicrm_email.is_primary DESC, email_id ASC ";
        $params = array( 1 => array( $id, 'Integer' ) );

        $emails = $values = array( );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );
        $count = 1;
        while ( $dao->fetch( ) ) {
            $values = array( 'locationType'   => $dao->locationType,
                             'is_primary'     => $dao->is_primary,
                             'on_hold'        => $dao->on_hold,
                             'id'             => $dao->email_id,
                             'email'          => $dao->email,
                             'locationTypeId' => $dao->locationTypeId );
            
            if ( $updateBlankLocInfo ) {
                $emails[$count++] = $values; 
            } else {
                $emails[$dao->email_id] = $values;
            }
        }
        return $emails;
    }
    
    /**
     * Get all the emails for a specified location_block id, with the primary email being first
     *
     * @param array $entityElements the array containing entity_id and
     * entity_table name
     *
     * @return array  the array of email id's
     * @access public
     * @static
     */
    static function allEntityEmails( &$entityElements ) 
    {
        if ( empty($entityElements) ) {
            return null;
        }

        $entityId    = $entityElements['entity_id'];
        $entityTable = $entityElements['entity_table'];


        $sql = " SELECT email, ltype.name as locationType, e.is_primary as is_primary, e.on_hold as on_hold,e.id as email_id, e.location_type_id as locationTypeId 
FROM civicrm_loc_block loc, civicrm_email e, civicrm_location_type ltype, {$entityTable} ev
WHERE ev.id = %1
AND   loc.id = ev.loc_block_id
AND   e.id IN (loc.email_id, loc.email_2_id)
AND   ltype.id = e.location_type_id
ORDER BY e.is_primary DESC, email_id ASC ";
       
        $params = array( 1 => array( $entityId, 'Integer' ) );
            
        $emails = array( );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );
        while ( $dao->fetch( ) ) {
            $emails[$dao->email_id] = array( 'locationType'   => $dao->locationType,
                                             'is_primary'     => $dao->is_primary,
                                             'on_hold'        => $dao->on_hold,
                                             'id'             => $dao->email_id,
                                             'email'          => $dao->email,
                                             'locationTypeId' => $dao->locationTypeId );
        }
        
        return $emails;
    }

    /**
     * Function to set / reset hold status for an email
     *
     * @param object $email  email object
     *
     * @return void
     * @static
     */
    static function holdEmail ( &$email ) 
    {
        //check for update mode
        if ( $email->id ) {
            //get hold date
            $holdDate = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Email', $email->id, 'hold_date' );

            //get reset date
            $resetDate = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Email', $email->id, 'reset_date' );

            //set hold date only if it is not set and e
            if ( ($email->on_hold != 'null') && !$holdDate && $email->on_hold) {
                $email->hold_date  = date( 'YmdHis' );
                $email->reset_date = '';
            } else if ( $holdDate && ( $email->on_hold == 'null' ) && !$resetDate ) {
                //set reset date only if it is not set and if hold date is set
                $email->on_hold     = false;
                $email->hold_date   = '';
                $email->reset_date  = date( 'YmdHis' );
            }
        } else {
            if ( ($email->on_hold != 'null') && $email->on_hold ) {
                $email->hold_date   = date( 'YmdHis' );
            }
        }
    }

    /**
     * Build From Email as the combination of all the email ids of the logged in user and
     * the domain email id 
     * 
     * @return array         an array of email ids
     * @access public
     * @static
     */
    static function getFromEmail( )
    {
        $session   = CRM_Core_Session::singleton( );
        $contactID = $session->get( 'userID' );
        $fromEmailValues = array( ); 
        
        // add the domain email id
        $domainEmail = CRM_Core_BAO_Domain::getNameAndEmail( );
        $domainEmail = "$domainEmail[0] <$domainEmail[1]>";
        $fromEmailValues[$domainEmail] = htmlspecialchars( $domainEmail );
        
        // add logged in user's active email ids
        if ( $contactID ) {
            $contactEmails   = self::allEmails( $contactID );
            $fromDisplayName = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $contactID, 'display_name' );
            
            foreach( $contactEmails as $emailId => $emailVal ) {
                $email = trim( $emailVal['email'] );
                if ( !$email || $emailVal['on_hold'] ) {
                    continue;
                }
                $fromEmail      = "$fromDisplayName <$email>";
                $fromEmailHtml  =  htmlspecialchars( $fromEmail ) . ' ' . $emailVal['locationType'];
                                
                if ( CRM_Utils_Array::value( 'is_primary', $emailVal ) ) {
                    $fromEmailHtml .=  ' ' . ts('(preferred)');
                }
                $fromEmailValues[$fromEmail] = $fromEmailHtml;
            }
        }
        return $fromEmailValues;
    }

    static function isMultipleBulkMail( ) {
        return CRM_Core_BAO_Setting::getItem( CRM_Core_BAO_Setting::MAILING_PREFERENCES_NAME,
                                              'civimail_multiple_bulk_emails',
                                              null,
                                              false );
        
    }
    /*
     * Handling for is_primary. 
     * $params is_primary could be
     *  #  1 - find other entries with is_primary = 1 &  reset them to 0
     *  #  0 - make sure at least one entry is set to 1 
     *            - if no other entry is 1 change to 1
     *            - if one other entry exists change that to 1
     *            - if more than one other entry exists change first one to 1
     *              @fixme - perhaps should choose by location_type
     *  #  empty - same as 0 as one we have checked first step 
     *             we know if it should be 1 or 0
     *  
     *  if $params['id'] is set $params['contact_id'] may need to be retrieved
     *  
     *  @param array $params
     */
    public function handlePrimary(&$params){
      // if id is set & we don't have contact_id we need to retrieve it
      if(!empty($params['id']) && empty($params['contact_id']) ){
        $email = new CRM_Core_BAO_Email();
        $email->id = $params['id'];
        $email->find(true);
        $contactId = $email['contact_id'];
      }else{
        $contactId = $params['contact_id'];
      }
      // if params is_primary then set all others to not be primary & exit out
      if(CRM_Utils_Array::value('is_primary',$params)){
        $sql = 'UPDATE civicrm_email SET is_primary = 0 WHERE contact_id = %1';
        CRM_Core_DAO::executeQuery($sql,array(1 => array($contactId, 'Integer')));
        return;
      }
      
      //Check what other emails exist for the contact
      $existingEmails = new CRM_Core_BAO_Email();
      $existingEmails->contact_id = $contactId;
      $existingEmails->orderBy('is_primary DESC');
      if(!$existingEmails->find(true)){
        // ie. 0 / null is an invalid options as there are no others set to 1 so change
        $params['is_primary'] = 1; 
        return;
      }else{
      // so at this point we are only dealing with ones explicity setting is_primary to 0
      // since we have reverse sorted by email we can either set the first one to
      // primary or return if is already is
        $existingEmails->is_primary = 1;
        $existingEmails->save();
      }
      
    }
}

