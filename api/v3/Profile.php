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

/**
 * Retrieve Profile field values.
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to get profile field values
 *
 * @return Profile field values|CRM_Error
 *
 * @todo add example
 * @todo add test cases
 *
 */
function civicrm_api3_profile_get( $params ) {
    _civicrm_api3_initialize( true );
    try{
        civicrm_api3_verify_mandatory($params, null, array('profile_id', 'contact_id'));
        
        // FIX ME: check profile exists
        $isContactActivityProfile = CRM_Core_BAO_UFField::checkContactActivityProfileType( $params['profile_id'] );
                
        if ( CRM_Core_BAO_UFField::checkProfileType($params['profile_id']) && !$isContactActivityProfile ) {
            return civicrm_api3_create_error('Can not retrieve values for profiles include fields for more than one record type.' );
        }          
        
        $profileFields = CRM_Core_BAO_UFGroup::getFields( $params['profile_id'], false, null, null, null, false, null, true, null, CRM_Core_Permission::EDIT );
        
        $values = array( );   
        if ( $isContactActivityProfile ) {
            civicrm_api3_verify_mandatory($params, null, array('activity_id'));
            
            require_once 'CRM/Profile/Form.php';
            $errors = CRM_Profile_Form::validateContactActivityProfile($params['activity_id'], $params['profile_id']);
            if ( !empty($errors) ) {
                return civicrm_api3_create_error(array_pop($errors));
            }
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

/**
 * Update Profile field values.
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to update profile field values
 *
 * @return Updated Contact/ Activity object|CRM_Error
 *
 * @todo add example
 * @todo add test cases
 *
 */
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

        $profileFields = CRM_Core_BAO_UFGroup::getFields($params['profile_id'], false, null, null, null, false, null, true, null, CRM_Core_Permission::EDIT);

        $profileParams['version']    = 3;
        $profileParams['contact_id'] = $params['contact_id'];
        $profileParams['profile_id'] = $params['profile_id'];
        if ( $isContactActivityProfile ) {
            civicrm_api3_verify_mandatory($params, null, array('activity_id'));
            $profileParams['activity_id'] = $params['activity_id'];

            require_once 'CRM/Profile/Form.php';
            $errors = CRM_Profile_Form::validateContactActivityProfile($params['activity_id'], $params['profile_id']);
            if ( !empty($errors) ) {
                return civicrm_api3_create_error(array_pop($errors));
            }
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

        $profileParams['skip_custom'] = 1;
        $updatedParams = civicrm_api3_profile_apply( $profileParams );
        if ( CRM_Utils_Array::value('is_error',$updatedParams ) ) {
            return $updatedParams;
        } 
        
        return civicrm_api3_contact_activity_set( $updatedParams['values'] );
    } catch (PEAR_Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    } catch (Exception $e) {
        return civicrm_api3_create_error( $e->getMessage() );
    }
}

/**
 * Provide formatted values for profile fields.
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to profile field values
 *
 * @return formatted profile field values|CRM_Error
 *
 * @todo add example
 * @todo add test cases
 *
 */
function civicrm_api3_profile_apply( $params ) {
  _civicrm_api3_initialize( true );
  try{  
      civicrm_api3_verify_mandatory($params, null, array('profile_id', 'contact_id'));
      require_once 'CRM/Contact/BAO/Contact.php';
      
      $profileFields = CRM_Core_BAO_UFGroup::getFields($params['profile_id'], false, null, null, null, false, null, true, null, CRM_Core_Permission::EDIT);
      list($data, $contactDetails) =  CRM_Contact_BAO_Contact::formatProfileContactParams($params, $profileFields, $params['contact_id'], $params['profile_id'], CRM_Utils_Array::value('contact_type', $params), CRM_Utils_Array::value('skip_custom', $params, false) );

      if ( empty($data) ) {
          return civicrm_api3_create_error('Enable to format profile parameters.');
      }
      
      return civicrm_api3_create_success( $data );
  } catch (PEAR_Exception $e) {
      return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
      return civicrm_api3_create_error( $e->getMessage() );
  }
}