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
 * File for the CiviCRM APIv3 contact activity functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_ContactActivity
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: ContactActivity.php 30486 2011-05-20 16:12:09Z rajan $
 *
 */

/**
 * Include common API util functions
 */
require_once 'api/v3/utils.php';

require_once 'api/v3/Activity.php';
require_once 'api/v3/Contact.php';
require_once 'api/v3/CustomField.php';

/**
 * Retrieve Contact and Activity.
 *
 * Return contact data provided with contact_id
 * Return activity data provided with activity_id
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to get contact and activity.
 *
 * {@schema Contact/Contact.xml}
 * {@schema Activity/Activity.xml}
 *
 * @return CRM_Contact + CRM_Activity|CRM_Error Contact and Activity object
 *
 * @todo add example
 * @todo add test cases
 *
 */
function civicrm_api3_contact_activity_get( $params ) {
    _civicrm_api3_initialize( true );
    try{
        civicrm_api3_verify_mandatory($params, null, array('contact_id'));
        
        $activityId = CRM_Utils_Array::value('activity_id', $params, null); 
        
        if ( $activityId ) {
            $updatedParams  = _civicrm_api3_contact_activity_resolve_params($params);
            $contactParams  = $updatedParams['contact'];
            $activityParams = $updatedParams['activity'];

            $activityParams['id']      =  $params['activity_id'];
            $activityParams['version'] = 3;
        } else {
            $contactParams = $params;
        }

        $contactParams['contact_id'] =  $params['contact_id'];
        $contactParams['version']    = 3; 

        $contact  = civicrm_api3_contact_get($contactParams);
        if ( CRM_Utils_Array::value('is_error', $contact) ) {
            return $contact;
        }
        
        if ( $activityId ) { 
            $activity = civicrm_api3_activity_get($activityParams);
            if ( CRM_Utils_Array::value('is_error', $activity) ) {
                return $activity;
            }
        }
        
        $result = civicrm_api3_create_success( );
        $result['values'] = array( );
        $result['values']['contact']  = CRM_Utils_Array::value('values', $contact);
        if ( $activityId ) {
            $result['values']['activity'] = CRM_Utils_Array::value('values', $activity);
        }

        return $result;
    } catch (PEAR_Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    } catch (Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    }
}

/**
 * Update Contact and Activity.
 *
 * Update a contact provided with contact_id and returns the modified
 * contact object.
 * Update a Activity provided with activity_id and returns the modified
 * activity object. 
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to update contact and activity.
 *
 * {@schema Contact/Contact.xml}
 * {@schema Activity/Activity.xml}
 *
 * @return CRM_Contact + CRM_Activity|CRM_Error Modified Contact and Activity object
 *
 * @todo add example
 * @todo add test cases
 *
 */
function civicrm_api3_contact_activity_set( $params ) {
    _civicrm_api3_initialize( true );
    try{
        civicrm_api3_verify_mandatory($params, null, array('contact_id'));
        
        $activityId = CRM_Utils_Array::value('activity_id', $params, null); 
            
        if ( $activityId ) { 
            $updatedParams  = _civicrm_api3_contact_activity_resolve_params($params);
            $contactParams  = $updatedParams['contact'];
            $activityParams = $updatedParams['activity'];

            $activityType = CRM_Core_DAO::getFieldValue('CRM_Activity_DAO_Activity', $params['activity_id'], 'activity_type_id' );
                    
            $activityParams['id'] = $params['activity_id'];
            $activityParams['activity_type_id'] = $activityType;
            $activityParams['version'] = 3;
        
        } else {
            $contactParams = $params; 
        }
        $contactType  = CRM_Contact_BAO_Contact::getContactType( $params['contact_id'] );
        
        if ( !$contactType ) {
            return civicrm_api3_create_error('Invalid value for the field contact_id'); 
        } else if ( $activityId && !$activityType ) {
            return civicrm_api3_create_error('Invalid value for the field activity_id'); 
        }
        
        $contactParams['contact_id']   = $params['contact_id'];
        $contactParams['contact_type'] = $contactType;
        $contactParams['version']      = 3;

        $contactCustomFields = CRM_Core_BAO_CustomField::getFields($contactType); 
        $errors = _civicrm_api3_custom_field_validate_fields($contactParams, $contactCustomFields);
        if ( !empty($errors) ) {
            return civicrm_api3_create_error( implode(', ', $errors) );
        }

        if ( $activityId ) {
            $activityCustomFields = CRM_Core_BAO_CustomField::getFields( 'Activity', false, false, $activityType);
            $errors               = _civicrm_api3_custom_field_validate_fields($activityParams, $activityCustomFields);
            if ( !empty($errors) ) {
                return civicrm_api3_create_error( implode(', ', $errors) );
            }       
        }
                     
        $contact  = civicrm_api3_contact_create($contactParams);
        if ( CRM_Utils_Array::value('is_error', $contact) ) {
            return $contact;
        }
        
        if ( $activityId ) {
            $activity = civicrm_api3_activity_create($activityParams);
            if ( CRM_Utils_Array::value('is_error', $activity) ) {
                return $activity;
            }
        }
        
        $result = civicrm_api3_create_success( );
        $result['values'] = array( );
        $result['values']['contact']  = CRM_Utils_Array::value('values', $contact);
        if ( $activityId ) {
            $result['values']['activity'] = CRM_Utils_Array::value('values', $activity);
        }

        return $result;
    } catch (PEAR_Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    } catch (Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    }    
}

/*
 * Helper function to differentiate contact and activity related parameters 
 * 
 * @param array $params fixed contact + activity parameters
 *
 * @return array seperate contact and activity parameters
 */
function _civicrm_api3_contact_activity_resolve_params( $params ) {    
    $contactParams  = $activityParams = array( );    
    $activityFields = CRM_Activity_BAO_Activity::getProfileFields( );

    foreach( $params as $n => $f ) {
        $fld = $n;
        if ( substr( $n, 0, 6 ) == 'return' ) {
            $fld = substr($n, 7);
        }
        
        if ( isset($activityFields[$n]) ) {
            $activityParams[$n] = $f;
        } else {
            $contactParams[$n]  = $f;
        }
    }
            
    return array( 'contact' => $contactParams, 'activity' => $activityParams );
}