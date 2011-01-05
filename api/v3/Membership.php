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
 * File for the CiviCRM APIv3 membership functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Membership
 *
 * @copyright CiviCRM LLC (c) 2004-2010
 * @version $Id: Membership.php 30590 2010-11-08 10:58:25Z shot $
 *
 */

/**
 * Files required for this package
 */
require_once 'api/v3/utils.php';
require_once 'CRM/Utils/Rule.php';
require_once 'api/v3/MembershipContact.php';
require_once 'api/v3/MembershipType.php';
require_once 'api/v3/MembershipStatus.php';

/**
 * Deletes an existing contact membership
 * 
 * This API is used for deleting a contact membership
 * 
 * @param  $params array  array holding membership_id - Id of the contact membership to be deleted
 * @todo should this really return null if successful - should be array
 * @todo should function be in here or membershp contact? This whole file is probably deprecated
 * @return null if successfull, object of CRM_Core_Error otherwise
 * @access public
 */
function civicrm_membership_delete($params)
{
    _civicrm_initialize();
    
    if (empty($params['membership_id'])) {
        return civicrm_create_error('Membership ID cannot be empty.');
    }
    
    // membershipID should be numeric
    if ( ! is_numeric( $params['membership_id']) ) {
        return civicrm_create_error( 'Input parameter should be numeric' );
    }    
    
    require_once 'CRM/Member/BAO/Membership.php';
    CRM_Member_BAO_Membership::deleteRelatedMemberships( $params['membership_id'] );
    
    $membership = new CRM_Member_BAO_Membership();
    $result = $membership->deleteMembership($params['membership_id']);
    
    return $result ? civicrm_create_success( ) : civicrm_create_error('Error while deleting Membership');
}


/**
 *
 * @param <type> $params
 * @return <type>
 * @todo wrapper fuunction - delete?
 */
function civicrm_contact_membership_create(&$params)
{
    return civicrm_membership_contact_create($params);
}

/**
 *
 * @param  $params array
 * @return <type>
 * @todo  wrapper fuunction - delete?
 */
function civicrm_membership_types_get(&$params) {
    return civicrm_membership_type_get($params);
}

/**
 *
 * @param  $params array
 * @return <type> 
 * @todo  wrapper fuunction - delete?
 */
function civicrm_membership_statuses_get(&$params) {
    return civicrm_membership_status_get($params);
}

