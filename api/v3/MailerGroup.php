<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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
 * APIv3 functions for registering/processing mailer group events.
 *
 * @package CiviCRM_APIv3
 * @subpackage API_MailerGroup
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

/**
 * Files required for this package
 */


require_once 'api/v3/utils.php';
require_once 'CRM/Contact/BAO/Group.php';
require_once 'CRM/Mailing/Event/BAO/Queue.php';
require_once 'CRM/Mailing/Event/BAO/Subscribe.php';
require_once 'CRM/Mailing/Event/BAO/Unsubscribe.php';
require_once 'CRM/Mailing/Event/BAO/Resubscribe.php';
require_once 'CRM/Mailing/Event/BAO/TrackableURLOpen.php';

/**
 * Handle an unsubscribe event
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_mailer_group_event_unsubscribe($params) 
{
    $errors = _civicrm_api3_mailer_group_check_params( $params, array('job_id', 'event_queue_id', 'hash') ) ;
  
    if ( !empty( $errors ) ) {
        return $errors;
    }
          
    $job   = $params['job_id']; 
    $queue = $params['event_queue_id']; 
    $hash  = $params['hash']; 

    $groups =& CRM_Mailing_Event_BAO_Unsubscribe::unsub_from_mailing($job, $queue, $hash); 

    if (count($groups)) {
        CRM_Mailing_Event_BAO_Unsubscribe::send_unsub_response($queue, $groups, false, $job);
        return civicrm_api3_create_success( );
    }

    return civicrm_api3_create_error( ts( 'Queue event could not be found' ) );
}

/**
 * Handle a site-level unsubscribe event
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_mailer_group_event_domain_unsubscribe($params) 
{
    $errors = _civicrm_api3_mailer_group_check_params( $params, array('job_id', 'event_queue_id', 'hash') ) ;
  
    if ( !empty( $errors ) ) {
        return $errors;
    }
          
    $job   = $params['job_id']; 
    $queue = $params['event_queue_id']; 
    $hash  = $params['hash']; 

    $unsubs = CRM_Mailing_Event_BAO_Unsubscribe::unsub_from_domain($job,$queue,$hash);

    if ( !$unsubs ) {
        return civicrm_api3_create_error( 'Queue event could not be found'  );
    }

    CRM_Mailing_Event_BAO_Unsubscribe::send_unsub_response($queue, null, true, $job);
    return civicrm_api3_create_success( );
}

/**
 * Handle a resubscription event
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_mailer_group_event_resubscribe($params) 
{
    $errors = _civicrm_api3_mailer_group_check_params( $params, array('job_id', 'event_queue_id', 'hash') ) ;
  
    if ( !empty( $errors ) ) {
        return $errors;
    }
          
    $job   = $params['job_id']; 
    $queue = $params['event_queue_id']; 
    $hash  = $params['hash']; 

    $groups =& CRM_Mailing_Event_BAO_Resubscribe::resub_to_mailing($job, $queue, $hash);
    
    if (count($groups)) {
        CRM_Mailing_Event_BAO_Resubscribe::send_resub_response($queue, $groups, false, $job);
        return civicrm_api3_create_success( );
    }

    return civicrm_api3_create_error(  'Queue event could not be found' ) ;
}

/**
 * Handle a subscription event
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_mailer_group_event_subscribe($params) 
{
    $errors = _civicrm_api3_mailer_group_check_params( $params, array('email', 'group_id') ) ;
    
    if ( !empty( $errors ) ) {
        return $errors;
    }
          
    $email      = $params['email']; 
    $group_id   = $params['group_id']; 
    $contact_id = CRM_Utils_Array::value('contact_id', $params);
    
    $group = new CRM_Contact_DAO_Group();
    $group->is_active = 1;
    $group->id = (int)$group_id;
    if ( !$group->find(true) ) {
        return civicrm_api3_create_error( 'Invalid Group id'  );
    }
        
    $subscribe =& CRM_Mailing_Event_BAO_Subscribe::subscribe($group_id, $email, $contact_id);

    if ($subscribe !== null) {
        /* Ask the contact for confirmation */
        $subscribe->send_confirm_request($email);
     
        $values = array( );
        $values['contact_id'] = $subscribe->contact_id;
        $values['subscribe_id'] = $subscribe->id;
        $values['hash'] = $subscribe->hash;
        $values['is_error'] = 0;
        
        return $values;
    }

    return civicrm_api3_create_error( 'Subscription failed'  );
}

/**
 * Helper function to check for required params
 *
 * @param array   $params       associated array of fields
 * @param array   $required     array of required fields
 *
 * @return array  $error        array with errors, null if none
 */
function _civicrm_api3_mailer_group_check_params ( $params, $required  ) 
{
    // return error if we do not get any params
    if ( empty( $params ) ) {
        return civicrm_api3_create_error( 'Input Parameters empty'  );
    }

    if ( ! is_array( $params ) ) {
        return civicrm_api3_create_error(  'Input parameter is not an array'  );
    }

    foreach ( $required as $name ) {
        if ( !array_key_exists($name, $params) || !$params[$name] ) {
            return civicrm_api3_create_error(  "Required parameter missing: $name"  );
        }
    }

    return null;
}
