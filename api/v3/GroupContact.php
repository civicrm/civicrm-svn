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
 * @version $Id: GroupContact.php 30171 2010-10-14 09:11:27Z mover $
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
function civicrm_api3_group_contact_get($params) {
	_civicrm_api3_initialize ( true );
	try {
		
		civicrm_api3_verify_mandatory ( $params, null, array ('contact_id' ) );
		$status = CRM_Utils_Array::value ( 'status', $params, 'Added' );
		require_once 'CRM/Contact/BAO/GroupContact.php';
		$values = & CRM_Contact_BAO_GroupContact::getContactGroup ( $params ['contact_id'], $status, null, false, true );
		return civicrm_api3_create_success ( $values, $params );
	} catch ( PEAR_Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	} catch ( Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	}
}

/**
 *
 * @param array $params
 * @return <type>
 */
function civicrm_api3_group_contact_create($params) {
	_civicrm_api3_initialize ( true );
	try {
		civicrm_api3_verify_mandatory ( $params, 'CRM_Contact_BAO_GroupContact' );
		
		return _civicrm_api3_group_contact_common ( $params, 'add' );
	} catch ( PEAR_Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	} catch ( Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	}
}

/**
 *
 * @param <type> $params
 * @return <type>
 */
function civicrm_api3_group_contact_delete($params) {
	_civicrm_api3_initialize ( true );
	try {
		civicrm_api3_verify_mandatory ( $params, null, array ('contact_id', 'group_id' ) );
		return _civicrm_api3_group_contact_common ( $params, 'remove' );
	} catch ( PEAR_Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	} catch ( Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	}

}

/**
 *
 * @param <type> $params
 * @return <type>
 */
function civicrm_api3_group_contact_pending($params) {
	_civicrm_api3_initialize ( true );
	try {
		civicrm_api3_verify_mandatory ( $params );
		return _civicrm_api3_group_contact_common ( $params, 'pending' );
	} catch ( PEAR_Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	} catch ( Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	}
}

/**
 *
 * @param <type> $params
 * @param <type> $op
 * @return <type>
 */
function _civicrm_api3_group_contact_common($params, $op = 'add') {
	
	$contactIDs = array ();
	$groupIDs = array ();
	foreach ( $params as $n => $v ) {
		if (substr ( $n, 0, 10 ) == 'contact_id') {
			$contactIDs [] = $v;
		} else if (substr ( $n, 0, 8 ) == 'group_id') {
			$groupIDs [] = $v;
		}
	}
	
	if (empty ( $contactIDs )) {
		return civicrm_api3_create_error ( 'contact_id is a required field' );
	}
	
	if (empty ( $groupIDs )) {
		return civicrm_api3_create_error ( 'group_id is a required field' );
	}
	
	$method = CRM_Utils_Array::value ( 'method', $params, 'API' );
	if ($op == 'add') {
		$status = CRM_Utils_Array::value ( 'status', $params, 'Added' );
	} elseif ($op == 'pending') {
		$status = CRM_Utils_Array::value ( 'status', $params, 'Pending' );
	} else {
		$status = CRM_Utils_Array::value ( 'status', $params, 'Removed' );
	}
	$tracking = CRM_Utils_Array::value ( 'tracking', $params );
	
	require_once 'CRM/Contact/BAO/GroupContact.php';
	$values = array ();
	if ($op == 'add' || $op == 'pending') {
		$values ['total_count'] = $values ['added'] = $values ['not_added'] = 0;
		foreach ( $groupIDs as $groupID ) {
			list ( $tc, $a, $na ) = CRM_Contact_BAO_GroupContact::addContactsToGroup ( $contactIDs, $groupID, $method, $status, $tracking );
			$values ['total_count'] += $tc;
			$values ['added'] += $a;
			$values ['not_added'] += $na;
		}
	} else {
		$values ['total_count'] = $values ['removed'] = $values ['not_removed'] = 0;
		foreach ( $groupIDs as $groupID ) {
			list ( $tc, $r, $nr ) = CRM_Contact_BAO_GroupContact::removeContactsFromGroup ( $contactIDs, $groupID, $method, $status, $tracking );
			$values ['total_count'] += $tc;
			$values ['removed'] += $r;
			$values ['not_removed'] += $nr;
		}
	}
	return civicrm_api3_create_success ( $values );
}

function civicrm_api3_group_contact_update_status($params) {
	_civicrm_api3_initialize ( true );
	try {
		civicrm_api3_verify_mandatory ( $params, null, array ('contact_id', 'group_id' ) );
		
		$method = CRM_Utils_Array::value ( 'method', $params, 'API' );
		$tracking = CRM_Utils_Array::value ( 'tracking', $params );
		
		require_once 'CRM/Contact/BAO/GroupContact.php';
		
		CRM_Contact_BAO_GroupContact::updateGroupMembershipStatus ( $params ['contact_id'], $params ['group_id'], $method, $tracking );
		
		return TRUE;
	} catch ( PEAR_Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	} catch ( Exception $e ) {
		return civicrm_api3_create_error ( $e->getMessage () );
	}
}
