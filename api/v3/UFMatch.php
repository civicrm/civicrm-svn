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
 * Most API functions take in associative arrays ( name => value pairs
 * as parameters. Some of the most commonly used parameters are
 * described below
 *
 * @param array $params           an associative array used in construction
 *                                / retrieval of the object
 * @param array $returnProperties the limited set of object properties that
 *                                need to be returned to the caller
 *
 * @todo several functions here that don't take array. naming conventions don't match filename
 * 
 * @todo is this the right way to group these functions? UF_id (get user ID) seems more logically to be a return value on the contact api
 */



/** 
 * get the contact_id given a uf_id 
 * 
 * @param array $params
 * 
 * @return int contact_id 
 * @access public    
 * @static 
 */ 
function civicrm_uf_match_get($ufID)
{
    if ((int) $ufID > 0) {
        require_once 'CRM/Core/BAO/UFMatch.php';
        return CRM_Core_BAO_UFMatch::getContactId($ufID);
    } else {
        return civicrm_create_error('Param needs to be a positive integer.');
    }
}

/**  
 * get the uf_id given a contact_id  
 *  
 * @param int $contactID
 *  
 * @return int ufID
 * @access public
 * @todo function doesn't accept or return arrays
 * @todo does this function belong here? Would be useful as a return option on contact id     
 * @static  
 */  
function civicrm_uf_id_get($contactID)
{
    if ((int) $contactID > 0) {
        require_once 'CRM/Core/BAO/UFMatch.php';
        return CRM_Core_BAO_UFMatch::getUFId($contactID);
    } else {
        return civicrm_create_error('Param needs to be a positive integer.');
    }
} 




