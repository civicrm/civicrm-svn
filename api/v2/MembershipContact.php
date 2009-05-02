<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * Definition of CRM API for Membership<->Contact relationships.
 * More detailed documentation can be found 
 * {@link http://objectledge.org/confluence/display/CRM/CRM+v1.0+Public+APIs
 * here}
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

/**
 * Files required for this package
 */
require_once 'api/v2/utils.php';
require_once 'CRM/Utils/Rule.php';
require_once 'CRM/Utils/Array.php';

/**
 * Create a Contact Membership
 *  
 * This API is used for creating a Membership for a contact.
 * Required parameters : membership_type_id and status_id.
 * 
 * @param   array  $params     an associative array of name/value property values of civicrm_membership
 * 
 * @return array of newly created membership property values.
 * @access public
 */
function civicrm_membership_contact_create(&$params)
{
    _civicrm_initialize();
    if ( !is_array( $params ) ) {
        return civicrm_create_error( 'Params is not an array' );
    }
    
    if ( ! isset( $params['membership_type_id'] ) ||
         ! isset( $params['contact_id'] ) ||
         ( isset( $params['is_override'] ) &&
           ! $params['status_id'] )) {
        return civicrm_create_error( ts('Required parameter missing') );
    }
    
    $values  = array( );   
    $error = _civicrm_membership_format_params( $params, $values );
    if (is_a($error, 'CRM_Core_Error') ) {
        return civicrm_create_error( 'Membership is not created' );
    }

    $params = array_merge($values,$params);
    
    require_once 'CRM/Core/Action.php';
    $action = CRM_Core_Action::ADD;
    
    //for edit membership id should be present
    if ( CRM_Utils_Array::value( 'id', $params ) ) {
        $ids = array( 'membership' => $params['id'],
                      'user_id'    => $params['contact_id'] );
        $action = CRM_Core_Action::UPDATE;
    }
    
    //need to pass action to handle related memberships. 
    $params['action'] = $action;    
    
    require_once 'CRM/Member/BAO/Membership.php';
    $membershipBAO = CRM_Member_BAO_Membership::create($params, $ids, true);
    
    if ( array_key_exists( 'is_error', $membershipBAO ) ) {
        // In case of no valid status for given dates, $membershipBAO
        // is going to contain 'is_error' => "Error Message"
        return civicrm_create_error( ts( 'The membership can not be saved, no valid membership status for given dates' ) );
    }
    
    $membership = array();
    _civicrm_object_to_array($membershipBAO, $membership);
    $values = array( );
    $values['id'] = $membership['id'];
    $values['is_error']   = 0;
    
    return $values;
}

/**
 * Get contact membership record.
 * 
 * This api is used for finding an existing membership record.
 * This api will also return the mebership records for the contacts
 * having mebership based on the relationship with the direct members.
 * 
 * @params  Array $params key/value pairs for contact_id and some
 *          options affecting the desired results; has legacy support
 *          for just passing the contact_id itself as the argument
 *
 * @return  Array of all found membership property values.
 * @access public
 */
function civicrm_membership_contact_get(&$params)
{
    _civicrm_initialize();
    
    $activeOnly = false;
    if ( is_array($params) ) {
        $contactID = CRM_Utils_Array::value('contact_id', $params);
        $activeOnly = CRM_Utils_Array::value('active_only', $params, false);
        if ($activeOnly == 1) {
            $activeOnly = true;
        } else {
            $activeOnly = false;
        }
    } else {
        $contactID = $params;
    }
    
    if ( empty($contactID) ) {
        return civicrm_create_error( 'Invalid value for ContactID.' );
    }
    
    // get the membership for the given contact ID
    require_once 'CRM/Member/BAO/Membership.php';
    $membership       = array('contact_id' => $contactID);
    $membershipValues = array();
    CRM_Member_BAO_Membership::getValues($membership, $membershipValues, $activeOnly);
    
    if ( empty( $membershipValues ) ) {
        return civicrm_create_error('No memberships for this contact.');
    }
    
    $members[$contactID] = array( );
    $relationships       = array();;
    foreach ($membershipValues as $membershipId => $values) {
        // populate the membership type name for the membership type id
        require_once 'CRM/Member/BAO/MembershipType.php';
        $membershipType = CRM_Member_BAO_MembershipType::getMembershipTypeDetails($values['membership_type_id']);
        
        $membershipValues[$membershipId]['membership_name'] = $membershipType['name'];

        if ( CRM_Utils_Array::value( 'relationship_type_id', $membershipType ) ) {
            $relationships[$membershipType['relationship_type_id']] = $membershipId;
        }
        
        // populating relationship type name.
        require_once 'CRM/Contact/BAO/RelationshipType.php';
        $relationshipType = new CRM_Contact_BAO_RelationshipType();
        $relationshipType->id = CRM_Utils_Array::value( 'relationship_type_id', $membershipType );
        if ( $relationshipType->find(true) ) {
            $membershipValues[$membershipId]['relationship_name'] = $relationshipType->name_a_b;
        }
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $groupTree =& CRM_Core_BAO_CustomGroup::getTree( 'Membership', CRM_Core_DAO::$_nullObject, $membershipId, false,
                                                          $values['membership_type_id']);
        $groupTree = CRM_Core_BAO_CustomGroup::formatGroupTree( $groupTree, 1, CRM_Core_DAO::$_nullObject );

        $defaults  = array( );
        CRM_Core_BAO_CustomGroup::setDefaults( $groupTree, $defaults );  
        
        if ( !empty( $defaults ) ) {
            foreach ( $defaults as $key => $val ) {
                $membershipValues[$membershipId][$key] = $val;
            }
        }
    }
    
    $members[$contactID] = $membershipValues;
    
    // populating contacts in members array based on their relationship with direct members.
    require_once 'CRM/Contact/BAO/Relationship.php';
    if ( !empty( $relationships ) ) {
        foreach ($relationships as $relTypeId => $membershipId) {
            // As members are not direct members, there should not be
            // membership id in the result array.
            unset($membershipValues[$membershipId]['id']);
            $relationship = new CRM_Contact_BAO_Relationship();
            $relationship->contact_id_b            = $contactID;
            $relationship->relationship_type_id    = $relTypeId;
            if ($relationship->find()) {
                while ($relationship->fetch()) {
                    clone($relationship);
                    $membershipValues[$membershipId]['contact_id']    = $relationship->contact_id_a;
                    $members[$contactID][$relationship->contact_id_a] = $membershipValues[$membershipId];
                }
            }
        }
    }
    return $members;
}


/**
 * take the input parameter list as specified in the data model and 
 * convert it into the same format that we use in QF and BAO object
 *
 * @param array  $params       Associative array of property name/value
 *                             pairs to insert in new contact.
 * @param array  $values       The reformatted properties that we can use internally
 *
 * @param array  $create       Is the formatted Values array going to
 *                             be used for CRM_Member_BAO_Membership:create()
 *
 * @return array|error
 * @access public
 */
function _civicrm_membership_format_params( &$params, &$values, $create=false) 
{
    require_once "CRM/Member/DAO/Membership.php";
    $fields =& CRM_Member_DAO_Membership::fields( );
    _civicrm_store_values( $fields, $params, $values );
    
    foreach ($params as $key => $value) {
        // ignore empty values or empty arrays etc
        if ( CRM_Utils_System::isNull( $value ) ) {
            continue;
        }
               
        switch ($key) {
        case 'membership_contact_id':
            if (!CRM_Utils_Rule::integer($value)) {
                return civicrm_create_error("contact_id not valid: $value");
            }
            $dao =& new CRM_Core_DAO();
            $qParams = array();
            $svq = $dao->singleValueQuery("SELECT id FROM civicrm_contact WHERE id = $value",
                                          $qParams);
            if (!$svq) {
                return civicrm_create_error("Invalid Contact ID: There is no contact record with contact_id = $value.");
            }
            $values['contact_id'] = $values['membership_contact_id'];
            unset($values['membership_contact_id']);
            break;
        case 'join_date':
        case 'membership_start_date':
        case 'membership_end_date':
            if (!CRM_Utils_Rule::date($value)) {
                return civicrm_create_error("$key not a valid date: $value");
            }
            break;
        case 'membership_type_id':
            $id = CRM_Core_DAO::getFieldValue( "CRM_Member_DAO_MembershipType", $value, 'id', 'name' );
            $values[$key] = $id;
            break;
        case 'status_id':
            $id = CRM_Core_DAO::getFieldValue( "CRM_Member_DAO_MembershipStatus", $value, 'id', 'name' );
            $values[$key] = $id;
            break;
        default:
            break;
        }
    }

    _civicrm_custom_format_params( $params, $values, 'Membership' );
      
    
    if ( $create ) {
        // CRM_Member_BAO_Membership::create() handles membership_start_date,
        // membership_end_date and membership_source. So, if $values contains
        // membership_start_date, membership_end_date  or membership_source,
        // convert it to start_date, end_date or source
        $changes = array('membership_start_date' => 'start_date',
                         'membership_end_date'   => 'end_date',
                         'membership_source'     => 'source',
                         );
        
        foreach ($changes as $orgVal => $changeVal) {
            if ( isset($values[$orgVal]) ) {
                $values[$changeVal] = $values[$orgVal];
                unset($values[$orgVal]);
            }
        }
    }
    
    return null;
}
