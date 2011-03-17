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
 * APIv3 functions for registering/processing mailing events.
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Mailing
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

/**
 * Files required for this package
 */


require_once 'api/v3/utils.php';

require_once 'CRM/Contact/BAO/Group.php';

require_once 'CRM/Mailing/BAO/BouncePattern.php';
require_once 'CRM/Mailing/Event/BAO/Bounce.php';
require_once 'CRM/Mailing/Event/BAO/Confirm.php';
require_once 'CRM/Mailing/Event/BAO/Opened.php';
require_once 'CRM/Mailing/Event/BAO/Queue.php';
require_once 'CRM/Mailing/Event/BAO/Reply.php';
require_once 'CRM/Mailing/Event/BAO/Forward.php';
require_once 'CRM/Mailing/Event/BAO/TrackableURLOpen.php';


/**
 * Process a bounce event by passing through to the BAOs.
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_mailing_event_bounce($params)
{   
    _civicrm_api3_initialize();
    $errors = _civicrm_api3_mailing_check_params( $params, array('job_id', 'event_queue_id', 'hash', 'body') ) ;
  
    if ( !empty( $errors ) ) {
        return $errors;
    }
          
    $body = $params['body']; 
    unset ( $params['body'] );

    $params += CRM_Mailing_BAO_BouncePattern::match($body);
    
    if (CRM_Mailing_Event_BAO_Bounce::create($params)) {
        return civicrm_api3_create_success( );
    }

    return civicrm_api3_create_error(  'Queue event could not be found'  );
}

/**
 * Handle a confirm event
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_mailing_event_confirm($params) 
{
    $errors = _civicrm_api3_mailing_check_params( $params, array('contact_id', 'subscribe_id', 'hash') ) ;
  
    if ( !empty( $errors ) ) {
        return $errors;
    }
          
    $contact_id   = $params['contact_id']; 
    $subscribe_id = $params['subscribe_id']; 
    $hash         = $params['hash']; 
    
    $confirm = CRM_Mailing_Event_BAO_Confirm::confirm($contact_id, $subscribe_id, $hash) !== false;
    
    if ( !$confirm ) {
        return civicrm_api3_create_error( 'Confirmation failed'  );
    }
    
    return civicrm_api3_create_success( );
}

/**
 * Handle a reply event
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_mailing_event_reply($params)
{
    $errors = _civicrm_api3_mailing_check_params( $params, array('job_id', 'event_queue_id', 'hash', 'bodyTxt', 'replyTo') ) ;
  
    if ( !empty( $errors ) ) {
        return $errors;
    }
          
    $job       = $params['job_id']; 
    $queue     = $params['event_queue_id']; 
    $hash      = $params['hash']; 
    $bodyTxt   = $params['bodyTxt']; 
    $replyto   = $params['replyTo']; 
    $bodyHTML  = CRM_Utils_Array::value('bodyHTML', $params);
    $fullEmail = CRM_Utils_Array::value('fullEmail', $params);

    $mailing =& CRM_Mailing_Event_BAO_Reply::reply($job, $queue, $hash, $replyto);

    if (empty($mailing)) {
        return civicrm_api3_create_error( 'Queue event could not be found'  );
    }

    CRM_Mailing_Event_BAO_Reply::send($queue, $mailing, $bodyTxt, $replyto, $bodyHTML, $fullEmail);

    return civicrm_api3_create_success( );
}

/**
 * Handle a forward event
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_mailing_event_forward($params) 
{
    $errors = _civicrm_api3_mailing_check_params( $params, array('job_id', 'event_queue_id', 'hash', 'email') ) ;
  
    if ( !empty( $errors ) ) {
        return $errors;
    }
          
    $job       = $params['job_id']; 
    $queue     = $params['event_queue_id']; 
    $hash      = $params['hash']; 
    $email     = $params['email']; 
    $fromEmail = CRM_Utils_Array::value('fromEmail', $params);
    $params    = CRM_Utils_Array::value('params', $params);

    $forward   = CRM_Mailing_Event_BAO_Forward::forward($job, $queue, $hash, $email, $fromEmail, $params );
    
    if ( $forward ) {
        return civicrm_api3_create_success( );
    }
    
    return civicrm_api3_create_error( 'Queue event could not be found'  );
}


/**
 * Handle a click event
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_mailing_event_click($params) 
{
    $errors = _civicrm_api3_mailing_check_params( $params, array('event_queue_id', 'url_id') ) ;
  
    if ( !empty( $errors ) ) {
        return $errors;
    }
          
    $url_id = $params['url_id']; 
    $queue = $params['event_queue_id']; 

    $url = CRM_Mailing_Event_BAO_TrackableURLOpen::track( $queue, $url_id );

    $values = array( );
    $values['url'] = $url;
    $values['is_error'] = 0;
        
    return $values;
}

/**
 * Handle an open event
 *
 * @param array $params
 * @return array
 */
function civicrm_api3_mailing_event_open($params) 
{
    $errors = _civicrm_api3_mailing_check_params( $params, array('event_queue_id') ) ;
  
    if ( !empty( $errors ) ) {
        return $errors;
    }
          
    $queue = $params['event_queue_id']; 

    $success = CRM_Mailing_Event_BAO_Opened::open( $queue );

    if ( !$success ) {
        return civicrm_api3_create_error( 'mailing open event failed'  );
    }

    return civicrm_api3_create_success( );
}


/**
 * Helper function to check for required params
 *
 * @param array   $params       associated array of fields
 * @param array   $required     array of required fields
 *
 * @return array  $error        array with errors, null if none
 */
function _civicrm_api3_mailing_check_params ( $params, $required  ) 
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
