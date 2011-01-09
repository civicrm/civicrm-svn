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
 * File for the CiviCRM APIv3 custom group functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_CustomGroup
 *
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: CustomGroup.php 30879 2010-11-22 15:45:55Z shot $
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
 * @todo missing get function
 *
 *
 */

/**
 * Use this API to create a new group. See the CRM Data Model for custom_group property definitions
 * $params['class_name'] is a required field, class being extended.
 *
 * @param $params     array   Associative array of property name/value pairs to insert in group.
 *
 *
 * @return   Newly create custom_group object
 *
 * @access public 
 */
function civicrm_custom_group_create( $params )
{
    _civicrm_initialize( );
    
    if(! is_array($params) ) { 
        return civicrm_create_error( "params is not an array");
    }   
    
    // Require either param['class_name'] (string) - for backwards compatibility - OR parm['extends'] (array)
    // If passing extends array - set class_name (e.g. 'Contact', 'Participant'...) as extends[0]. You may optionally
    // pass an extends_entity_column_value as extends[1] (e.g. an Activity Type ID).
    if( isset( $params['class_name'] ) && trim( $params['class_name'] ) ) {
        $params['extends'][0] = trim( $params['class_name'] );
    } else {
        if ( ! isset( $params['extends'] ) || ! is_array( $params['extends'] ) ) { 
            return civicrm_create_error( "Params must include either 'class_name' (string) or 'extends' (array)." );
        } else {
            if ( ! isset( $params['extends'][0] ) || ! trim( $params['extends'][0] ) ) {
                return civicrm_create_error( "First item in params['extends'] must be a class name (e.g. 'Contact')." );
            }
        }
    }

    $error = _civicrm_check_required_fields($params, 'CRM_Core_DAO_CustomGroup');
    
    require_once 'CRM/Utils/String.php';
    if (! isset( $params['title'] ) ||
        ! trim($params['title'] ) ) {
        return civicrm_create_error( "Title parameter is required." );
    } 

    if ( ! isset( $params['style'] ) || ! trim( $params['style'] ) ) {
        $params['style'] = 'Inline';
    }
        
    if (is_a($error, 'CRM_Core_Error')) {
        return civicrm_create_error( $error->_errors[0]['message'] );
    }
    
    require_once 'CRM/Core/BAO/CustomGroup.php';
    $customGroup = CRM_Core_BAO_CustomGroup::create($params);                             

    _civicrm_object_to_array( $customGroup, $values );
    
    if ( is_a( $customGroup, 'CRM_Core_Error' ) ) { 
        return civicrm_create_error( $customGroup->_errors[0]['message'] );
    } else {
        $values['is_error'] = 0;
    }
    if ( CRM_Utils_Array::value( 'html_type', $params ) ){
        $params['custom_group_id'] = $customGroup->id;
        require_once 'api/v3/CustomField.php';
        $fieldValues = civicrm_custom_field_create( $params );
        $values      = array_merge( $values, $fieldValues['result'] );
    }
    return $values;
}   


/**
 * Use this API to delete an existing group.
 *
 * @param array id of the group to be deleted
 *
 * @return Null if success
 * @access public
 **/
function civicrm_custom_group_delete($params)
{    
    _civicrm_initialize( );
          
    if ( !is_array( $params ) ) {
        return civicrm_create_error( 'Params is not an array' );
    }
    
    if ( ! CRM_Utils_Array::value( 'id', $params ) ) {
        return civicrm_create_error( 'Invalid or no value for Custom group ID' );
    }
    // convert params array into Object
    require_once 'CRM/Core/DAO/CustomGroup.php';
    $values = new CRM_Core_DAO_CustomGroup( );
    $values->id = $params['id'];
    $values->find(true);
    
    require_once 'CRM/Core/BAO/CustomGroup.php';
    $result = CRM_Core_BAO_CustomGroup::deleteGroup($values);  
    return $result ? civicrm_create_success( ): civicrm_error('Error while deleting custom group');
}

