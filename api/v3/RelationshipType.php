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
 * new version of civicrm apis. See blog post at
 * http://civicrm.org/node/131
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Contact
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id: Contact.php 30415 2010-10-29 12:02:47Z shot $
 *
 */

/**
 * Include common API util functions
 */
require_once 'api/v3/utils.php';


/**
 * Function to create relationship type
 *
 * @param  array $params   Associative array of property name/value pairs to insert in new relationship type.
 *
 * @return Newly created Relationship_type object
 *
 * @access public
 * {@schema Contact/RelationshipType.xml}
 */
function civicrm_relationship_type_create( &$params ) {

  _civicrm_initialize(true);
  try{
     
    civicrm_verify_mandatory($params,'CRM_Contact_DAO_RelationshipType',array('contact_type_a','contact_type_b','name_a_b','name_b_a'));

    if (! isset( $params['label_a_b']) )
    $params['label_a_b'] = $params['name_a_b'];

    if (! isset( $params['label_b_a']) )
    $params['label_b_a'] = $params['name_b_a'];

    require_once 'CRM/Utils/Rule.php';

    $ids = array( );
    if( isset( $params['id'] ) && ! CRM_Utils_Rule::integer(  $params['id'] ) ) {
      return civicrm_create_error( 'Invalid value for relationship type ID' );
    } else {
      $ids['relationshipType'] = CRM_Utils_Array::value( 'id', $params );
    }

    require_once 'CRM/Contact/BAO/RelationshipType.php';
    $relationType = CRM_Contact_BAO_RelationshipType::add( $params, $ids );

    $relType = array( );
    _civicrm_object_to_array( $relationType, $relType );

    return civicrm_create_success($relType,$params);
  } catch (PEAR_Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  }
}

/**
 * Function to get all relationship type
 * retruns  An array of Relationship_type
 * * @access  public
 */
function civicrm_relationship_type_get( $params = null )
{
  _civicrm_initialize(true);
  try{

    require_once 'CRM/Contact/DAO/RelationshipType.php';
    $relationshipTypes = array();
    $relationshipType  = array();
    $relationType      = new CRM_Contact_DAO_RelationshipType();
    if ( !empty( $params ) && is_array( $params ) ) {
      $properties = array_keys( $relationType->fields() );
      foreach ($properties as $name) {
        if ( array_key_exists( $name, $params ) ) {
          $relationType->$name = $params[$name];
        }
      }
    }
    $relationType->find();
    while( $relationType->fetch() ) {
      _civicrm_object_to_array( clone($relationType), $relationshipType );
      $relationshipTypes[] = $relationshipType;
    }
    return civicrm_create_success($relationshipTypes,$params);

  } catch (PEAR_Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  }
}


/**
 * Delete a relationship type delete
 *
 * @param  id of relationship type  $id
 *
 * @return boolean  true if success, else false
 * @static void
 * @access public
 */
function civicrm_relationship_type_delete( &$params ) {

  _civicrm_initialize(true);
  try{

    civicrm_verify_mandatory($params,null,array('id'));
    require_once 'CRM/Utils/Rule.php';
    if( $params['id'] != null && ! CRM_Utils_Rule::integer( $params['id'] ) ) {
      return civicrm_create_error( 'Invalid value for relationship type ID' );
    }

    $relationTypeBAO = new CRM_Contact_BAO_RelationshipType( );
    $result = $relationTypeBAO->del( $params['id']);
    if (empty($result['id'])){
      return civicrm_create_error( 'Could not delete relationship type' ) ;
    }
    return  civicrm_create_success( $result, $params  );
  } catch (PEAR_Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_create_error( $e->getMessage() );
  }
}
