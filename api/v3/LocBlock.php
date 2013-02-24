<?php
/*
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
 * File for the CiviCRM APIv3 loc_block functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_LocBlock
 * @copyright CiviCRM LLC (c) 20042012
 */

/**
 * Create or update a loc_block
 *
 * @param array $params  Associative array of property
 *                       name/value pairs to insert in new 'loc_block'
 * @example LocBlockCreate.php Std Create example
 *
 * @return array api result array
 * {@getfields loc_block_create}
 * @access public
 */
function civicrm_api3_loc_block_create($params) {
  $dao = new CRM_Core_DAO_LocBlock();
  $dao->copyValues($params);
  $dao->save();
  if (!empty($dao->id)) {
    $values = array();
    _civicrm_api3_object_to_array($dao, $values[$dao->id]);
    return civicrm_api3_create_success($values, $params, 'loc_block', 'create', $dao);
  }
  return civicrm_api3_create_error('Unable to create LocBlock. Please check your params.');
}

/**
 * Returns array of loc_blocks matching a set of one or more properties
 *
 * @param array $params Array of one or more valid property_name=>value pairs. If $params is set
 *  as null, all loc_blocks will be returned (default limit is 25)
 *
 * @return array  Array of matching loc_blocks
 * {@getfields loc_block_get}
 * @access public
 */
function civicrm_api3_loc_block_get($params) {
  return _civicrm_api3_basic_get('CRM_Core_DAO_LocBlock', $params);
}

/**
 * delete an existing loc_block
 *
 * This method is used to delete any existing loc_block.
 * id of the record to be deleted is required field in $params array
 *
 * @param array $params array containing id of the record to be deleted
 *
 * @return array  returns flag true if successfull, error message otherwise
 * {@getfields loc_block_delete}
 * @access public
 */
function civicrm_api3_loc_block_delete($params) {
  return _civicrm_api3_basic_delete('CRM_Core_DAO_LocBlock', $params);
}
