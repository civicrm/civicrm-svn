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
 * File for the CiviCRM APIv3 group contact functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Group
 *
 * @copyright CiviCRM LLC (c) 2004-2011
 * @version $Id: GroupContact.php 21624 2009-06-04 22:02:55Z mover $
 *
 */

/**
 * Include utility functions
 */
require_once 'api/v3/utils.php';

/**
 * This API will give list of the groups for particular contact
 * Particualr status can be sent in params array
 * If no status mentioned in params, by default 'added' will be used
 * to fetch the records
 *
 * @param  array $params  name value pair of contact information
 *
 * @return  array  list of groups, given contact subsribed to
 */
function civicrm_api3_group_organization_get( $params )
{
  _civicrm_api3_initialize( true);
  try{

    civicrm_api3_verify_one_mandatory($params,null,array('organization_id','group_id'));

    require_once 'CRM/Contact/DAO/GroupOrganization.php';
    $dao = new CRM_Contact_DAO_GroupOrganization();
    if ( array_key_exists( 'organization_id', $params ) ) {
      $dao->organization_id = $params['organization_id'];
    }
    if ( array_key_exists( 'group_id', $params ) ) {
      $dao->group_id = $params['group_id'];
    }
    $dao->find();
    $values = array( );
    _civicrm_api3_object_to_array( $dao, $values );
    return civicrm_api3_create_success( $values );
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}

/**
 *
 * @param $params array
 * @return <type>
 */
function civicrm_api3_group_organization_create( $params )
{
  _civicrm_api3_initialize( true);

  try{

    civicrm_api3_verify_mandatory($params,null,array('organization_id','group_id'));  

    require_once 'CRM/Contact/BAO/GroupOrganization.php';
    $groupOrgBAO = CRM_Contact_BAO_GroupOrganization::add( $params );

    if (is_null($groupOrgBAO)){
      return civicrm_api3_create_error("group organization not created");     
    }

    _civicrm_api3_object_to_array( $groupOrgBAO, $values );
    return civicrm_api3_create_success( $values,$params, $groupOrgBAO);
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}


/**
 * Deletes an existing Group Organization
 *
 * This API is used for deleting a Group Organization
 *
 * @param  array  $params  ID of the Group Organization to be deleted
 *
 * @return null if successfull, array with is_error = 1 otherwise
 * @access public
 */

function civicrm_api3_group_organization_delete( $params )
{
  _civicrm_api3_initialize( true);
  try{

    civicrm_api3_verify_mandatory($params,null,array('id'));  
    require_once 'CRM/Contact/BAO/GroupOrganization.php';
    $result = CRM_Contact_BAO_GroupOrganization::delete( $params['id'] );
    return $result ? civicrm_api3_create_success(  'Deleted Group Organization successfully'  ):civicrm_api3_create_error(  'Could not delete Group Organization'  );
  } catch (PEAR_Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  } catch (Exception $e) {
    return civicrm_api3_create_error( $e->getMessage() );
  }
}