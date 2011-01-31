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
 * @version $Id: UFGroup.php 30171 2010-10-14 09:11:27Z mover $
 *
 */


/**
 * Files required for this package
 */
require_once 'api/v3/utils.php'; 
require_once 'CRM/Core/BAO/UFGroup.php';


/**
 * Use this API to create a new group. See the CRM Data Model for uf_group property definitions
 *
 * @param $params  array   Associative array of property name/value pairs to insert in group.
 *
 * @return   Newly create $ufGroupArray array
 *
 * @access public 
 */
function civicrm_uf_group_create($params)
{
    _civicrm_initialize(true );
    try{
    civicrm_verify_mandatory($params,'CRM_Core_DAO_UFGroup',array('id'));        
    
    $ids = array();
    $ids['ufgroup'] = $params['id'];
    
    require_once 'CRM/Core/BAO/UFGroup.php';
    
    $ufGroup = CRM_Core_BAO_UFGroup::add( $params,$ids );
    _civicrm_object_to_array( $ufGroup, $ufGroupArray[$params['id']]);
    
    return civicrm_create_success($ufGroupArray,$params);
    } catch (PEAR_Exception $e) {
       return civicrm_create_error( $e->getMessage() );
    } catch (Exception $e) {
        return civicrm_create_error( $e->getMessage() );
    }
}



/**
 * Delete uf group
 *  
 * @param $groupId int  Valid uf_group id that to be deleted
 *
 * @return true on successful delete or return error
 * @todo doesnt rtn success or error properly
 * @access public
 *
 */
function civicrm_uf_group_delete( $params) {
    _civicrm_initialize( true);
    try{
            
    require_once 'CRM/Core/BAO/UFGroup.php';
    return CRM_Core_BAO_UFGroup::del($params['id']);
    } catch (PEAR_Exception $e) {
        return civicrm_create_error( $e->getMessage() );
    } catch (Exception $e) {
        return civicrm_create_error( $e->getMessage() );
    }  

}