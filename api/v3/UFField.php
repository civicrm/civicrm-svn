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
 * File for the CiviCRM APIv3 user framework group functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_UF
 * 
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: UFField.php 30171 2010-10-14 09:11:27Z mover $
 *
 */
/**
 * Defines 'uf field' within a group.
 *
 * @param $groupId int Valid uf_group id
 *
 * @param $params  array  Associative array of property name/value pairs to create new uf field.
 *
 * @return Newly created $ufFieldArray array
 *
 * @access public 
 *
 */
function civicrm_uf_field_create($groupId, $params)
{
    if (!is_array($params) or !isset($params['field_name']) or (int) $groupId < 1) {
        return civicrm_create_error('Params must be a field_name-carrying array and a positive integer.');
    }

    _civicrm_initialize( );
    
    $field_type       = CRM_Utils_Array::value ( 'field_type'       , $params );
    $field_name       = CRM_Utils_Array::value ( 'field_name'       , $params );
    $location_type_id = CRM_Utils_Array::value ( 'location_type_id' , $params );
    $phone_type       = CRM_Utils_Array::value ( 'phone_type'       , $params );
    
    $params['field_name'] =  array( $field_type, $field_name, $location_type_id, $phone_type);
    
    if ( !( CRM_Utils_Array::value('group_id', $params) ) ) {
        $params['group_id'] =  $groupId;
    }
    
    $ids = array();
    $ids['uf_group'] = $groupId;
    
    require_once 'CRM/Core/BAO/UFField.php';
    if (CRM_Core_BAO_UFField::duplicateField($params, $ids) ) {
        return civicrm_create_error("The field was not added. It already exists in this profile.");
    }
    
    $ufField = CRM_Core_BAO_UFField::add( $params,$ids );
    _civicrm_object_to_array( $ufField, $ufFieldArray);
    
    return $ufFieldArray;
} 

/**
 * Use this API to update uf field . See the CRM Data Model for uf_field property definitions
 *
 * @param $params  array   Associative array of property name/value pairs to update in field.
 *  
 * @param $fieldId int  A valid uf field id that to be updated.
 *  
 * @return  updated  $ufFieldArray array
 *
 * @access public 
 */
function civicrm_uf_field_update( $params , $fieldId ) {
    
    _civicrm_initialize( );
    
    if(! isset( $fieldId ) ) {
        return civicrm_create_error("parameter fieldId is not set");
    }
    
    if(! is_array( $params ) ) {
        return civicrm_create_error("params is not an array ");
    }   
    
    $field_type       = CRM_Utils_Array::value ( 'field_type'       , $params );
    $field_name       = CRM_Utils_Array::value ( 'field_name'       , $params );
    $location_type_id = CRM_Utils_Array::value ( 'location_type_id' , $params );
    $phone_type       = CRM_Utils_Array::value ( 'phone_type'       , $params );
    
    $params['field_name'] =  array( $field_type, $field_name, $location_type_id, $phone_type);
    
    require_once 'CRM/Core/BAO/UFField.php';
    $UFField = new CRM_core_BAO_UFField();
    $UFField->id = $fieldId;
    
    if ( !( CRM_Utils_Array::value('group_id', $params) ) && $UFField->find(true) ) {
        $params['group_id'] =  $UFField->uf_group_id;
    }

    $ids = array();

    if ( $UFField->find(true) ) { 
        $ids['uf_group'] =  $UFField->uf_group_id;
    } else {
        return civicrm_create_error("there is no field for this fieldId");
    }
    $ids['uf_field'] = $fieldId;
    
    if (CRM_Core_BAO_UFField::duplicateField($params, $ids) ) {
        return civicrm_create_error("The field was not added. It already exists in this profile.");
    }
    
    $ufField = CRM_Core_BAO_UFField::add( $params,$ids );
    _civicrm_object_to_array( $ufField, $ufFieldArray);
    
    return $ufFieldArray;
}

/**
 * Delete uf field
 *  
 * @param $fieldId int  Valid uf_field id that to be deleted
 *
 * @return true on successful delete or return error
 *
 * @access public
 *
 */
function civicrm_uf_field_delete( $fieldId ) {
    _civicrm_initialize( );
    
    if(! isset( $fieldId ) ) {
        return civicrm_create_error("provide a valid fieldId.");
    }
    
    require_once 'CRM/Core/BAO/UFField.php';
    return CRM_Core_BAO_UFField::del($fieldId);
    
}