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
 * File for the CiviCRM APIv3 tag functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Tag
 * 
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Tag.php 30486 2010-11-02 16:12:09Z shot $
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';

function civicrm_tag_getfields( &$params ) {
    require_once 'CRM/Core/BAO/Tag.php';
    $bao = new CRM_Core_BAO_Tag();
    //function &exportableFields( $contactType = 'Individual', $status = false, $export = false, $search = false )
    return ($bao->fields());
    //return ($contact->fields());
}

/**
 *  Add a Tag. Tags are used to classify CRM entities (including Contacts, Groups and Actions).
 *
 * Allowed @params array keys are:
 * {@schema Core/Tag.xml}

 * @return array of newly created tag property values.
 * @access public
 */
function civicrm_tag_create( &$params ) 
{
  _civicrm_initialize( true );
  try {
    civicrm_verify_mandatory ($params,'CRM_Core_DAO_Tag',array ('name'));

    if ( !array_key_exists ('used_for', $params)) {
      $params ['used_for'] = "civicrm_contact";
    }
    
    require_once 'CRM/Core/BAO/Tag.php';
    $ids = array( 'tag' => CRM_Utils_Array::value( 'tag', $params ) );
    if ( CRM_Utils_Array::value( 'tag', $params ) ) {
        $ids['tag'] = $params['tag'];
    }

    $tagBAO = CRM_Core_BAO_Tag::add($params, $ids);

    if ( is_a( $tagBAO, 'CRM_Core_Error' ) ) {
        return civicrm_create_error( "Tag is not created" );
    } else {
        $values = array( );
        _civicrm_object_to_array($tagBAO, $values);
        return civicrm_create_success($values);
    }
  } catch (PEAR_Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  }
}

/**
 * Deletes an existing Tag
 *
 * @param  array  $params
 * 
 * @return boolean | error  true if successfull, error otherwise
 * @access public
 */
function civicrm_tag_delete( &$params ) 
{
  _civicrm_initialize( true );
  try {
    civicrm_verify_mandatory ($params,null,array ('tag_id'));
    $tagID = CRM_Utils_Array::value( 'tag_id', $params );

    require_once 'CRM/Core/BAO/Tag.php';
    return CRM_Core_BAO_Tag::del( $tagID ) ? civicrm_create_success( ) : civicrm_create_error(  ts( 'Could not delete tag' )  );
  } catch (Exception $e) {
    if (CRM_Core_Error::$modeException) throw $e;
    return civicrm_create_error( $e->getMessage() );
  }
}

/**
 * Get a Tag.
 * 
 * This api is used for finding an existing tag.
 * Either id or name of tag are required parameters for this api.
 * 
 * @param  array $params  an associative array of name/value pairs.
 *
 * @return  array details of found tags else error
 * @access public
 */

function civicrm_tag_get($params) 
{   
   try {
  _civicrm_initialize( true );

    require_once 'CRM/Core/BAO/Tag.php';
    $tagBAO = new CRM_Core_BAO_Tag();
    $fields = array_keys($tagBAO->fields());

    foreach ( $fields as $name) {
        if (array_key_exists($name, $params)) {
            $tagBAO->$name = $params[$name];
        }
    }
    
    if ( ! $tagBAO->find(true) ) {
        return civicrm_create_success(array());
    }
    _civicrm_object_to_array($tagBAO, $tag[]);
    while ($tagBAO->fetch()) {
      _civicrm_object_to_array($tagBAO, $tag[]);
    }
    return civicrm_create_success($tag);
  } catch (PEAR_Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  }
}
