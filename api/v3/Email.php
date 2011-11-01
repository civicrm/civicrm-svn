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
 * File for the CiviCRM APIv3 email functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Email
 * 
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Email.php 2011-02-16 ErikHommel $
 */

require_once 'CRM/Core/BAO/Email.php';

/**
 *  Add an Email for a contact
 * 
 * Allowed @params array keys are:
 * 
 * {@example EmailCreate.php}
 * @return array of newly created email property values.
 * {@getfields email_create}
 * @access public
 */
function civicrm_api3_email_create( $params ) 
{
	
    $emailBAO = CRM_Core_BAO_Email::add($params);
    
	 if ( is_a( $emailBAO, 'CRM_Core_Error' )) {
		 return civicrm_api3_create_error( "Email is not created or updated ");
	 } else {
		 $values = array( );
		 _civicrm_api3_object_to_array($emailBAO, $values[$emailBAO->id]);
		 return civicrm_api3_create_success($values, $params,'email','create',$emailBAO );
	 }

}
/*
 * Adjust Metadata for Create action
 * 
 * The metadata is used for setting defaults, documentation & validation
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_email_create_spec(&$params){
  $params['is_primary']['api.default'] = 0;// TODO a 'clever' default should be introduced
  $params['email']['api.required'] = 1;
  $params['contact_id']['api.required'] = 1;
}
/**
 * Deletes an existing Email
 *
 * @param  array  $params
 *
 * @example EmailDelete.php
 * {@example EmailDelete.php 0}
 * @return boolean | error  true if successfull, error otherwise
 * {@getfields email_delete}
 * @access public
 */
function civicrm_api3_email_delete( $params ) 
{

    civicrm_api3_verify_mandatory ($params,null,array ('id'));
    $emailID = CRM_Utils_Array::value( 'id', $params );

    require_once 'CRM/Core/DAO/Email.php';
    $emailDAO = new CRM_Core_DAO_Email();
    $emailDAO->id = $emailID;
    if ( $emailDAO->find( ) ) {
		while ( $emailDAO->fetch() ) {
			$emailDAO->delete();
			return civicrm_api3_create_success();
		}
	} else {
		return civicrm_api3_create_error( 'Could not delete email with id '.$emailID);
	}
    

}

/**
 * Retrieve one or more emails 
 *
 * @param  mixed[]  (reference ) input parameters
 * 
 * 
 * @example EmailGet.php
 * {@example EmailGet.php 0}
 * @param  array $params  an associative array of name/value pairs.
 *
 * @return  array api result
 * {@getfields email_get}
 * @access public
 */

function civicrm_api3_email_get($params) 
{   
	
    return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);

}
