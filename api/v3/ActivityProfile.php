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
 * File for the CiviCRM APIv3 activity profile functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_ActivityProfile
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: ActivityProfile.php 30486 2011-05-20 16:12:09Z rajan $
 *
 */

/**
 * Include common API util functions
 */
require_once 'api/v3/utils.php';

require_once 'api/v3/Activity.php';
require_once 'api/v3/Contact.php';

function civicrm_api3_activity_profile_get( $params ) {
    _civicrm_api3_initialize( true );
    try{
        civicrm_api3_verify_mandatory($params, null, array('contact_id', 'activity_id'));
        
        list($contactParams, $activityParams) = _civicrm_api3_activity_profile_resolve_params($params);
            
        $contact  = civicrm_api3_contact_get($contactParams);
        if ( CRM_Utils_Array::value('is_error', $contact) ) {
            return $contact;
        }
        
        $activity = civicrm_api3_activity_get($activityParams);
        if ( CRM_Utils_Array::value('is_error', $activity) ) {
            return $activity;
        }

        $result = civicrm_api3_create_success( );
        $result['values']['contact']  = CRM_Utils_Array::value('values', $contact);
        $result['values']['activity'] = CRM_Utils_Array::value('values', $activity);
        
        return $result;
        
    } catch (PEAR_Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    } catch (Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    }
}

function civicrm_api3_activity_profile_create( $params ) {
    _civicrm_api3_initialize( true );
    try{
        civicrm_api3_verify_mandatory($params, null, array('contact_id', 'activity_id'));
        
        list($contactParams, $activityParams) = _civicrm_api3_activity_profile_resolve_params($params);
        
        $contact  = civicrm_api3_contact_create($contactParams);
        if ( CRM_Utils_Array::value('is_error', $contact) ) {
            return $contact;
        }
        
        $activity = civicrm_api3_activity_create($activityParams);
        if ( CRM_Utils_Array::value('is_error', $activity) ) {
            return $activity;
        }

        $result = civicrm_api3_create_success( );
        $result['values']['contact']  = CRM_Utils_Array::value('values', $contact);
        $result['values']['activity'] = CRM_Utils_Array::value('values', $activity);
        
        return $result;
        
    } catch (PEAR_Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    } catch (Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    }    
}

function _civicrm_api3_activity_profile_resolve_params( $params ) {
    
    $contactParams  = array( 'contact_id' => $params['contact_id'] );
    $activityParams = array( 'activity_id' => $params['activity_id'] );
    
    require_once 'CRM/Contact/BAO/Contact.php';
    require_once 'CRM/Activity/BAO/Activity.php';
    
    $contactType   = CRM_Contact_BAO_Contact::getContactType( $params['contact_id'] );
    //FIX ME: return error here if we do not get contact type
    $contactFields = CRM_Contact_BAO_Contact::exportableFields( $contactType );
    
    //FIX ME: return error if activity do not exist with given id
    $activityFields = CRM_Activity_BAO_Activity::getProfileFields( );
        
    // differenciate conact and activity fields
    foreach( $contactFields as $n => $f ) {
        if ( isset($params["return.{$n}"]) ) {
            $contactParams["return.{$n}"] = 1;
        } else if ( isset($params[$n]) ) {
            $contactParams[$n] = $params[$n];
        }
    }
    foreach( $activityFields as $n => $f ) {
        if ( isset($params["return.{$n}"]) ) {
            $activityParams["return.{$n}"] = 1;
        } else if ( isset($params[$n]) ) {
            $activityParams[$n] = $params[$n];
        }
    }
    
    return array( 'contact' => $contactParams, 'activity' => $activityParams );
}