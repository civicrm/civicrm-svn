<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
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
 * File for CiviCRM APIv3 settings
 *
 * @package CiviCRM_APIv3_Core
 * @subpackage API_Settings
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: Settings.php
 *
 */

function civicrm_api3_setting_getfields($params) {
  $result = CRM_Core_BAO_Setting::getSettingSpecification(
      CRM_Utils_Array::value('name',$params),
      CRM_Utils_Array::value('component_id',$params));
  // find any supplemental information
  if(CRM_Utils_Array::value('action',$params)){
    $specFunction = 'civicrm_api3_setting_' . $params['action'] . '_spec';
    if (function_exists($specFunction)) {
      // alter
      $specFunction($result);
    }
  }
  return civicrm_api3_create_success($result,$params,'setting','getfields');
}

function civicrm_api3_setting_getfields_spec(&$params) {
  $params['name'] = array('title' => 'Setting Name belongs to');
  $params['component_id'] = array('title' => 'id of relevant component');
}
/**
 * Create or update a setting
 *
 * @param array $params  Associative array of setting
 *                       name/value pairs + other vars as applicable - see getfields for more
 * @example SettingCreate.php Std Create example
 *
 * @return array api result array
 * {@getfields setting_create}
 * @access public
 */
function civicrm_api3_setting_create($params) {
  $domains = _civicrm_api3_setting_getDomainArray($params);
  $result = CRM_Core_BAO_Setting::setItems($params, $domains);
  return civicrm_api3_create_success($result,$params,'setting','create');
}
/*
 * Metadata for setting create function
 *
 * @param array $params parameters as passed to the API
 */
function civicrm_api3_setting_create_spec(&$params) {
  $params['domain_id'] = array(
    'api.default' => 'current_domain',
    'description' => 'if you do not pass in a domain id this will default to the current domain
      an array or "all" are acceptable values for multiple domains'
   );
   $params['group'] = array(
     'description' => 'if you know the group defining it will make the api more efficient'
   )
  ;
}

/**
 * Returns array of settings matching input parameters
 *
 * @param array $params  (referance) Array of one or more valid
 *                       property_name=>value pairs.
 *
 * @return array Array of matching settings
 * {@getfields setting_get}
 * @access public
 */
function civicrm_api3_setting_get($params) {
  $domains = _civicrm_api3_setting_getDomainArray($params);
  $result =   $result = CRM_Core_BAO_Setting::getItems($params, $domains);
  return civicrm_api3_create_success($result,$params,'setting','get');
}
/*
 * Metadata for setting create function
*
* @param array $params parameters as passed to the API
*/
function civicrm_api3_setting_get_spec(&$params) {
  $params['domain_id'] = array(
      'api.default' => 'current_domain',
      'description' => 'if you do not pass in a domain id this will default to the current domain'
  );
  $params['group'] = array(
      'description' => 'if you know the group defining it will make the api more efficient'
  )
  ;
}
/*
 * Converts domain input into an array. If an array is passed in this is used, if 'all' is passed
 * in this is converted to 'all arrays'
 */
function _civicrm_api3_setting_getDomainArray(&$params){
  if($params['domain_id'] == 'current_domain'){
    $params['domain_id']    = CRM_Core_Config::domainID();
  }

  if($params['domain_id'] == 'all'){
    $domainAPIResult = civicrm_api('domain','get',array('version' => 3, 'return' => 'id'));
    $params['domain_id'] = array_keys($domainAPIResult['values']);
  }
  if(is_array($params['domain_id'])){
    $domains = $params['domain_id'];
  }
  else{
    $domains = array($params['domain_id']);
  }
  return $domains;
}
/*
 * This function filters on the fields like 'version' & 'debug' that are not settings and ensures group
 * is set.
 *
 * The fields array is filled with metadata about the settings api fields (from getfields)
 *
 * @param array $params Parameters as passed into API
 * @param array $fields empty array to be populated with fields metadata
 * @return array $fieldstoset name => value array of the fields to be set (with extraneous removed)
 */
function _civicrm_api3_setting_filterfields(&$params, &$fields){
  $group = CRM_Utils_Array::value('group', $params);

  $ignoredParams = array(
      'version' => 1,
      'id' => 1,
      'domain_id' => 1,
      'group' => 1,
      'debug' => 1,
      'created_id',
      'component_id',
      'contact_id'
  );
  $settingParams = array_diff_key($params, $ignoredParams);
  $getFieldsParams = array(
      'version' => 3,
      'group' => $group,
  );
  if(count($settingParams) ==1){
    // ie we are only setting one field - we'll pass it into getfields for efficiency
    list($name) = array_keys($settingParams);
    $getFieldsParams['name'] = $name;
  }
  $fields = civicrm_api('setting','getfields', $getFieldsParams);
  return array_intersect_key($settingParams,$fields['values']);
}
