<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * Definition of the ActivityType part of the CRM API. 
 * More detailed documentation can be found 
 * {@link http://objectledge.org/confluence/display/CRM/CRM+v1.0+Public+APIs
 * here}
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Activity
 *
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id: ActivityType.php 30171 2010-10-14 09:11:27Z mover $
 *
 */

/**
 * Include common API util functions
 */   
require_once 'api/v3/utils.php';

/**
 * Function to retrieve activity types
 * 
 * @return array $activityTypes activity types keyed by id
 * @access public
 *
 * @todo Erik Hommel 16 dec 2010 Check if get function returns all DB fields
 * @todo Erik Hommel 16 dec 2010 Check permission with utils function civicrm_api_permission_check
 * @todo Erik Hommel 16 dec 2010 Introduce version as param
 */
function civicrm_activity_type_get( ) {
  _civicrm_initialize(true);
    try{
    require_once 'CRM/Core/OptionGroup.php';
    $activityTypes = CRM_Core_OptionGroup::values( 'activity_type' );
    return $activityTypes;
        } catch (PEAR_Exception $e) {
      return civicrm_create_error( $e->getMessage() );
    } catch (Exception $e) {
      return civicrm_create_error( $e->getMessage() );
    }
}

/**
 * Function to create activity type
 * @param array   $params  associated array of fields
 *                 $params['option_value_id'] is required for updation of activity type
 * @return array $activityType created / updated activity type
 *
 * @access public
 *
 * @todo Erik Hommel 16 dec 2010 Check permission with utils function civicrm_api_permission_check
 * @todo Erik Hommel 16 dec 2010 Introduce version as param
 * @todo Erik Hommel 16 dec 2010 Check if function processes update according to standards
 * @todo Erik Hommel 16 dec 2010 Use utils function civicrm_verify_mandatory to check required fields
 */
 
function civicrm_activity_type_create( &$params ) {
    _civicrm_initialize(true);
    try{
    civicrm_verify_mandatory($params,null,array('label','weight'));
    require_once 'CRM/Core/OptionGroup.php';
   
    $action = 1;
    $groupParams = array ( 'name' => 'activity_type' );

    if ( $optionValueID = CRM_Utils_Array::value ( 'option_value_id', $params ) ){
        $action = 2;
    }

    require_once 'CRM/Core/OptionValue.php';  
    $activityObject = CRM_Core_OptionValue::addOptionValue( $params, $groupParams, $action, $optionValueID );
    $activityType = array();
    _civicrm_object_to_array( $activityObject, $activityType );
    return civicrm_create_success($activityType,$params);
    } catch (PEAR_Exception $e) {
      return civicrm_create_error( $e->getMessage() );
    } catch (Exception $e) {
      return civicrm_create_error( $e->getMessage() );
    }
}

/**
 * Function to delete activity type
 * @param activityTypeId int   activity type id to delete
 * @return boolen
 *
 * @access public
 *
 * @todo Erik Hommel 16 dec 2010 Check permission with utils function civicrm_api_permission_check
 * @todo Erik Hommel 16 dec 2010 Introduce version as param
 * @todo Erik Hommel 16 dec 2010 Use utils function civicrm_verify_mandatory to check required fields
 */
function civicrm_activity_type_delete( $params ) {
    _civicrm_initialize(true);
    try{
      civicrm_verify_mandatory($params,null,array('activity_type_id'));

    $activityTypeId = $params['activity_type_id'];
    require_once 'CRM/Core/BAO/OptionValue.php';

    return CRM_Core_BAO_OptionValue::del( $activityTypeId );
    } catch (PEAR_Exception $e) {
      return civicrm_create_error( $e->getMessage() );
    } catch (Exception $e) {
      return civicrm_create_error( $e->getMessage() );
    }
}
