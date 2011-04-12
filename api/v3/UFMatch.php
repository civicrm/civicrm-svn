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
 * File for the CiviCRM APIv3 user framework group functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_UF
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: UFGroup.php 30171 2010-10-14 09:11:27Z mover $
 *
 */


/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Core/BAO/UFMatch.php';

/**
 * get the contact_id given a uf_id or vice versa
 *
 * @param array $params
 *
 * @return array $result
 * @access public
 * @static
 * @example UFMatchGet.php
 * @todo this class is missing delete & create functions (do after exisitng functions upgraded to v3)
*/
function civicrm_api3_uf_match_get($params)
{
  _civicrm_api3_initialize( true );
  try{
    civicrm_api3_verify_one_mandatory($params,null,array('uf_id','contact_id'));
     require_once 'CRM/Core/BAO/UFMatch.php';
     $bao = new CRM_Core_BAO_UFMatch( );
      _civicrm_api3_dao_set_filter ( $bao, $params );
      return civicrm_api3_create_success(_civicrm_api3_dao_to_array ($bao,$params),$params,$bao);
  
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}




