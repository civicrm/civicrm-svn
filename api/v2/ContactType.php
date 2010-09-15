<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
 * File for the CiviCRM APIv2 contact functions
 *
 * @package CiviCRM_APIv2
 * @subpackage API_Contact
 * 
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Tag.php 28963 2010-08-02 08:16:58Z deepak $
 */

/**
 * Include utility functions
 */
require_once 'api/v2/utils.php';

/**
 *  Add a Contact Subtype
 *
 * @param   array   $params          an associative array used in
 *                                   construction / retrieval of the
 *                                   object
 * name & parent_id (1=Individual, 2 Household, 3 Organization)
 * 
 * @return array of newly created type property values.
 * @access public
 */
function civicrm_contacttype_create( &$params ) 
{
    
    if ( ! is_array( $params ) ) {
        return civicrm_create_error( ts( 'Input parameters is not an array' ) );
    }

    if ( empty( $params ) ) {
        return civicrm_create_error( ts( 'No input parameters present' ) );
    }
    
    if ( !array_key_exists ('name', $params)) {
        return civicrm_create_error( ts( 'param name missing' ) );
    }
    if ( !array_key_exists ('parent_id', $params)) {
        return civicrm_create_error( ts( 'param parent_id missing' ) );
    }
   
    require_once ('CRM/Contact/BAO/ContactType.php');
    $default =array();
    $p = array ("name" => $params['name'] );
    $r =CRM_Contact_BAO_ContactType::retrieve( &$p, &$defaults );
    if (!$r) {//we need to create it
      if (! array_key_exists ('label', $params))  $params['label'] = $params['name'];
      if (! array_key_exists ('is_active', $params))  $params['is_active'] = true;
      $r= CRM_Contact_BAO_ContactType::add($params);
    }
    if ( is_a( $r, 'CRM_Core_Error' ) ) {
        return civicrm_create_error( "Tag is not created" );
    } else {
        $values = array( );
        _civicrm_object_to_array($r, $values);
    }
    return $values;
}

