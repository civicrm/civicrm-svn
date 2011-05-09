<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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
 * File for the CiviCRM APIv3 custom group functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_CustomField
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: CustomField.php 30879 2010-11-22 15:45:55Z shot $
 */

/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';

/**
 * Most API functions take in associative arrays ( name => value pairs
 * as parameters. Some of the most commonly used parameters are
 * described below
 *
 * @param array $params           an associative array used in construction
 * retrieval of the object
 *
 */


/**
 * Defines 'custom field' within a group.
 *
 *
 * @param $params       array  Associative array of property name/value pairs to create new custom field.
 *
 * @return Newly created custom_field id array
 *
 * @access public
 * 
 * @example CustomFieldCreate.php
 * 
 * {@example CustomFieldCreate.php 0}
 *
 */

function civicrm_api3_custom_field_create( $params )
{
  _civicrm_api3_initialize(true );
  try{
    civicrm_api3_verify_mandatory($params,null,array('custom_group_id','label'));

    if ( !( CRM_Utils_Array::value('option_type', $params ) ) ) {
      if( CRM_Utils_Array::value('id', $params ) ){
        $params['option_type'] = 2;
      } else {
        $params['option_type'] = 1;
      }
    }
     
    if (is_a($error, 'CRM_Core_Error')) {
      return civicrm_api3_create_error( $error->_errors[0]['message'] );
    }

    // Array created for passing options in params
    if ( isset( $params['option_values'] ) && is_array( $params['option_values'] ) ) {
      foreach ( $params['option_values'] as $key => $value ){
        $params['option_label'][$value['weight']]  = $value['label'];
        $params['option_value'][$value['weight']]  = $value['value'];
        $params['option_status'][$value['weight']] = $value['is_active'];
        $params['option_weight'][$value['weight']] = $value['weight'];
      }
    }
    require_once 'CRM/Core/BAO/CustomField.php';
    $customField = CRM_Core_BAO_CustomField::create($params);
    _civicrm_api3_object_to_array_unique_fields($customField , $values[$customField->id]);
    return civicrm_api3_create_success($values,$params, $customField);
    
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}

/**
 * Use this API to delete an existing custom group field.
 *
 * @param $params     Array id of the field to be deleted
 * @example CustomFieldDelete.php
 * 
 * {@example CustomFieldDelete.php 0}
 * @access public
 **/
function civicrm_api3_custom_field_delete( $params )
{
  _civicrm_api3_initialize( true);
  try{
    civicrm_api3_verify_mandatory($params,null,array('id'));

    require_once 'CRM/Core/DAO/CustomField.php';
    $field = new CRM_Core_DAO_CustomField( );
    $field->id = $params['id'];
    $field->find(true);

    require_once 'CRM/Core/BAO/CustomField.php';
    $customFieldDelete = CRM_Core_BAO_CustomField::deleteField( $field );
    return $customFieldDelete ?
    civicrm_api3_create_error('Error while deleting custom field') :
    civicrm_api3_create_success( );
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}

/**
 * Use this API to get existing custom fields.
 *
 * @param array $params Array to search on
 *
 * @todo copied from elsewhere but needs tidying up to use DAO->Find
 * @access public
 * 
 **/
function civicrm_api3_custom_field_get($params)
{
  try {
    _civicrm_api3_initialize(true );
    civicrm_api3_verify_mandatory($params);

    require_once 'CRM/Core/BAO/CustomField.php';
    $customfieldBAO = new CRM_Core_BAO_CustomField();
    $fields = ($customfieldBAO->getFields($params['entity']));

    foreach ($fields as $key=>$value){
      $result[$key] = $value['label'];

    }
    return civicrm_api3_create_success($result,$params,$customfieldBAO) ;

  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}
