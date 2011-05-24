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

require_once 'api/v3/ContactActivity.php';
require_once 'CRM/Core/BAO/UFGroup.php';
require_once 'CRM/Core/BAO/UFField.php';
require_once 'CRM/Core/Permission.php';

function civicrm_api3_profile_get( $params ) {
    _civicrm_api3_initialize( true );
    try{
        civicrm_api3_verify_mandatory($params, null, array('profile_id', 'contact_id'));
        
        // FIX ME: check profile exists
        $isContactActivityProfile = CRM_Core_BAO_UFField::checkContactActivityProfileType( $params['profile_id'] );
                
        if ( CRM_Core_BAO_UFField::checkProfileType($params['profile_id']) && !$isContactActivityProfile ) {
            return civicrm_api3_create_error('Can not retrieve values for profiles include fields for more than one record type.' );
        }          
        
        // FIX ME: check for permission
        $profileFields = CRM_Core_BAO_UFGroup::getFields( $params['profile_id'], false, null, null, null, false, null, true, null, CRM_Core_Permission::EDIT );
        
        $values = array( );   
        if ( $isContactActivityProfile ) {
            civicrm_api3_verify_mandatory($params, null, array('activity_id'));
            $contactFields = $activityFields = array( );
            foreach ( $profileFields as $fieldName => $field ) {
                if ( CRM_Utils_Array::value('field_type', $field) == 'Activity' ) {
                    $activityFields[$fieldName] = $field;
                } else {
                    $contactFields[$fieldName]  = $field;
                }
            }
            
            CRM_Core_BAO_UFGroup::setProfileDefaults($params['contact_id'], $contactFields, $values, true );
            
            if ( $params['activity_id'] ) {
                CRM_Core_BAO_UFGroup::setComponentDefaults( $activityFields, $params['activity_id'], 'Activity', $values, true );
            }
        } else {
            CRM_Core_BAO_UFGroup::setProfileDefaults( $params['contact_id'], $profileFields, $values, true );
        }
        
        $result = civicrm_api3_create_success( );
        $result['values'] = $values;
        
        return $result;
    } catch (PEAR_Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    } catch (Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    }
}

function civicrm_api3_profile_set( $params ) {
    _civicrm_api3_initialize( true );
    try{
        civicrm_api3_verify_mandatory($params, null, array('profile_id', 'contact_id'));
        
        // FIX ME: check profile exists
        $isContactActivityProfile = CRM_Core_BAO_UFField::checkContactActivityProfileType( $params['profile_id'] );
                
        if ( CRM_Core_BAO_UFField::checkProfileType($params['profile_id']) && !$isContactActivityProfile ) {
            return civicrm_api3_create_error('Can not retrieve values for profiles include fields for more than one record type.' );
        }          

        $profileParams = $missingParams = array( );

        // FIX ME: check for permission?
        $profileFields = CRM_Core_BAO_UFGroup::getFields($params['profile_id'], false, null, null, null, false, null, true, null, CRM_Core_Permission::EDIT);

        $profileParams['contact_id'] = $params['contact_id'];
        $profileParams['version']    = 3;
        if ( $isContactActivityProfile ) {
            civicrm_api3_verify_mandatory($params, null, array('activity_id'));
            $profileParams['activity_id'] = $params['activity_id'];
        }        

        foreach ( $profileFields as $fieldName => $field ) {
            if ( CRM_Utils_Array::value('is_required', $field) && !CRM_Utils_Array::value($fieldName, $params) ) {
                $missingParams[] = $fieldName;
            }
            $profileParams[$fieldName] = isset($params[$fieldName]) ? $params[$fieldName] : '';
        }
        
        if ( !empty($missingParams) ) {
            return civicrm_api3_create_error("Missing required parameters for profile id {$params['profile_id']}: ". implode(', ', $missingParams) ); 
        }
        return civicrm_api3_contact_activity_set( $profileParams );
    } catch (PEAR_Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    } catch (Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    }
}