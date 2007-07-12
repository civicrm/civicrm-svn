<?php

 /*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.8                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Member/DAO/Membership.php';
require_once 'CRM/Member/DAO/MembershipType.php';

require_once 'CRM/Core/BAO/CustomField.php';
require_once 'CRM/Core/BAO/CustomValue.php';

class CRM_Member_BAO_Membership extends CRM_Member_DAO_Membership
{
    /**
     * static field for all the membership information that we can potentially import
     *
     * @var array
     * @static
     */
    static $_importableFields = null;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * takes an associative array and creates a membership object
     *
     * the function extract all the params it needs to initialize the create a
     * membership object. the params array could contain additional unused name/value
     * pairs
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     * @param array $ids    the array that holds all the db ids
     *
     * @return object CRM_Member_BAO_Membership object
     * @access public
     * @static
     */
    static function &add(&$params, &$ids) 
    {
        require_once 'CRM/Utils/Hook.php';

        if ( CRM_Utils_Array::value( 'membership', $ids ) ) {
            CRM_Utils_Hook::pre( 'edit', 'Membership', $ids['membership'], $params );
        } else {
            CRM_Utils_Hook::pre( 'create', 'Membership', null, $params ); 
        }

        // converting dates to mysql format
        $params['join_date']  = CRM_Utils_Date::isoToMysql($params['join_date']);
        $params['start_date'] = CRM_Utils_Date::isoToMysql($params['start_date']);
        $params['end_date']   = CRM_Utils_Date::isoToMysql($params['end_date']);

        $membership =& new CRM_Member_BAO_Membership();
        $membership->copyValues($params);
        $membership->id = CRM_Utils_Array::value( 'membership', $ids );
        
        $membership->save();
        $membership->free( );

        $session = & CRM_Core_Session::singleton();
        
        $membershipLog = array('membership_id' => $membership->id,
                               'status_id'     => $membership->status_id,
                               'start_date'    => $membership->start_date,
                               'end_date'      => $membership->end_date,
                               'modified_id'   => CRM_Utils_Array::value( 'userId', $ids ),
                               'modified_date' => date('Ymd')
                               );
        require_once 'CRM/Member/BAO/MembershipLog.php';
        $temp = array();
        CRM_Member_BAO_MembershipLog::add($membershipLog, $temp);
        
        if ( CRM_Utils_Array::value( 'membership', $ids ) ) {
            CRM_Utils_Hook::post( 'edit', 'Membership', $membership->id, $membership );
        } else {
            CRM_Utils_Hook::post( 'create', 'Membership', $membership->id, $membership );
        }
        
        return $membership;
    }

    /**
     * Given the list of params in the params array, fetch the object
     * and store the values in the values array
     *
     * @param array   $params input parameters to find object
     * @param array   $values output values of the object
     * @param array   $ids    the array that holds all the db ids
     * @param boolean $active do you want only active memberships to
     *                        be returned
     * 
     * @return CRM_Member_BAO_Membership|null the found object or null
     * @access public
     * @static
     */
    static function &getValues( &$params, &$values, &$ids, $active=false ) 
    {
        $membership =& new CRM_Member_BAO_Membership( );
        
        $membership->copyValues( $params );
        $membership->find();
        $memberships = array();
        while ( $membership->fetch() ) {
            if ( $active && 
                 ( ! CRM_Core_DAO::getFieldValue('CRM_Member_DAO_MembershipStatus',
                                                 $membership->status_id,
                                                 'is_current_member') ) ) {
                continue;
            }
            
            $ids['membership'] = $membership->id;
            CRM_Core_DAO::storeValues( $membership, $values[$membership->id] );
            $memberships[$membership->id] = $membership;
        }
        
        return $memberships;
    }

    /**
     * takes an associative array and creates a membership object
     *
     * @param array    $params      (reference ) an assoc array of name/value pairs
     * @param array    $ids         the array that holds all the db ids
     * @param boolean  $callFromAPI Is this function called from API?
     * 
     * @return object CRM_Member_BAO_Membership object 
     * @access public
     * @static
     */
    static function &create(&$params, &$ids, $callFromAPI = false ) 
    {
        require_once 'CRM/Utils/Date.php';
        
        if ( ! isset( $params['is_override'] ) ) {
            $startDate  = CRM_Utils_Date::customFormat($params['start_date'],'%Y-%m-%d');
            $endDate    = CRM_Utils_Date::customFormat($params['end_date'],'%Y-%m-%d');
            $joinDate   = CRM_Utils_Date::customFormat($params['join_date'],'%Y-%m-%d');
            
            require_once 'CRM/Member/BAO/MembershipStatus.php';
            $calcStatus = CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate( $startDate, $endDate, $joinDate );
            
            if ( empty( $calcStatus ) ) {
                if ( ! $callFromAPI ) {
                    // Redirect the form in case of error
                    CRM_Core_Session::setStatus( ts('The membership can not be saved.<br/> No valid membership status for given dates.') );
                    return CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/contact/view', "reset=1&force=1&cid={$this->_contactID}&selectedChild=member"));
                }
                // Return the error message to the api
                $error = array( );
                $error['is_error'] = ts( 'The membership can not be saved. No valid membership status for given dates' );
                return $error;
            }
            $params['status_id'] = $calcStatus['id'];
        }
        
        CRM_Core_DAO::transaction('BEGIN');
        
        $membership =& self::add($params, $ids);
        
        if ( is_a( $membership, 'CRM_Core_Error') ) {
            CRM_Core_DAO::transaction( 'ROLLBACK' );
            return $membership;
        }
        
        // add custom field values
        if (CRM_Utils_Array::value('custom', $params)) {
            foreach ($params['custom'] as $customValue) {
                $cvParams = array(
                                  'entity_table'    => 'civicrm_membership',
                                  'entity_id'       => $membership->id,
                                  'value'           => $customValue['value'],
                                  'type'            => $customValue['type'],
                                  'custom_field_id' => $customValue['custom_field_id'],
                                  'file_id'         => $customValue['file_id'],
                                  );
                
                if ($customValue['id']) {
                    $cvParams['id'] = $customValue['id'];
                }
                CRM_Core_BAO_CustomValue::create($cvParams);
            }
        }
        
        $params['membership_id'] = $membership->id;
                
        // Create activity history record.
        require_once "CRM/Member/PseudoConstant.php";
        $membershipType = CRM_Member_PseudoConstant::membershipType( $membership->membership_type_id );
        
        if ( ! $membershipType ) {
            $membershipType = ts('Membership');
        }
        
        $activitySummary = "{$membershipType}";
        
        if ( $membership->source != 'null' ) {
            $activitySummary .= " - {$membership->source}";
        }
        
        if ( $membership->owner_membership_id ) {
            $cid         = CRM_Core_DAO::getFieldValue( 
                                                       'CRM_Member_DAO_Membership', 
                                                       $membership->owner_membership_id,
                                                       'contact_id' );
            $displayName = CRM_Core_DAO::getFieldValue( 
                                                       'CRM_Contact_DAO_Contact',
                                                       $cid, 'display_name' );
            
            $activitySummary .= " (by {$displayName})";
                                                       
        }        
        
        require_once 'CRM/Member/DAO/MembershipStatus.php';
        $activityType = "Membership - " . CRM_Core_DAO::getFieldValue( 
                                                                      'CRM_Member_DAO_MembershipStatus', 
                                                                      $membership->status_id
                                                                      );
        
        $historyParams = array(
                               'entity_table'     => 'civicrm_contact',
                               'entity_id'        => $membership->contact_id,
                               'activity_type'    => $activityType,
                               'module'           => 'CiviMember',
                               'callback'         => 'CRM_Member_Page_Membership::details',
                               'activity_id'      => $membership->id,
                               'activity_summary' => $activitySummary,
                               'activity_date'    => $membership->start_date
                               );
        
        require_once "api/History.php";
        if ( is_a( crm_create_activity_history( $historyParams ), 'CRM_Core_Error' ) ) {
            CRM_Core_Error::fatal("Failed creating Activity History for membership of id {$membership->id}");
        }
        
        CRM_Core_DAO::transaction('COMMIT');

        return $membership;
    }
    
    /**
     * Function to check the membership extended through relationship
     * 
     * @param int $membershipId membership id
     * @param int $contactId    contact id
     *
     * @return Array    array of contact_id of all related contacts.
     * @static
     */
    static function checkMembershipRelationship( $membershipId, $contactId, $action = CRM_Core_Action::ADD ) 
    {
        $contacts = array( );

        $params   = array( 'id' => $membershipId );
        $defaults = array( );
        $membership = self::retrieve( $params, $defaults );

        require_once 'CRM/Member/BAO/MembershipType.php';
        $membershipType   = CRM_Member_BAO_MembershipType::getMembershipTypeDetails( $membership->membership_type_id ); 
        
        $relationships = array( );
        if ( isset( $membershipType['relationship_type_id'] ) ) {
            $relationships =
                CRM_Contact_BAO_Relationship::getRelationship( $contactId,
                                                               CRM_Contact_BAO_Relationship::CURRENT
                                                               );
            if ( $action & CRM_Core_Action::UPDATE ) {
                $pastRelationships =
                    CRM_Contact_BAO_Relationship::getRelationship( $contactId,
                                                                   CRM_Contact_BAO_Relationship::PAST
                                                                   );
                $relationships = array_merge( $relationships, $pastRelationships );
            }
        }
        
        if ( ! empty($relationships) ) {
            require_once "CRM/Contact/BAO/RelationshipType.php";
            // check for each contact relationships
            foreach ( $relationships as $values) {
                //get details of the relationship type
                $relType   = array( 'id' => $values['civicrm_relationship_type_id'] );
                $relValues = array( );
                CRM_Contact_BAO_RelationshipType::retrieve( $relType, $relValues);
                
                // 1. Check if contact and membership type relationship type are same
                // 2. Check if relationship direction is same or name_a_b = name_b_a
                if ( ( $values['civicrm_relationship_type_id'] == $membershipType['relationship_type_id'] )
                     && ( ( $values['rtype'] == $membershipType['relationship_direction'] ) ||
                          ( $relValues['name_a_b'] == $relValues['name_b_a'] ) ) ) {
                    // $values['status'] is going to have value for
                    // current or past relationships.
                    $contacts[$values['cid']] = $values['status'];
                }
            }
        }
        
        return $contacts;
    }
    
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. We'll tweak this function to be more
     * full featured over a period of time. This is the inverse function of
     * create.  It also stores all the retrieved values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the name / value pairs
     *                        in a hierarchical manner
     * @param array $ids      (reference) the array that holds all the db ids
     *
     * @return object CRM_Member_BAO_Membership object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $membership =& new CRM_Member_DAO_Membership( );
        $membership->copyValues( $params );
        $idList = array('membership_type' => 'MembershipType',
                        'status'          => 'MembershipStatus',
                        );
        if ( $membership->find( true ) ) {
            CRM_Core_DAO::storeValues( $membership, $defaults );
            foreach ( $idList as $name => $file ) {
                if ( $defaults[$name .'_id'] ) {
                    $defaults[$name] = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_' . $file, 
                                                                    $defaults[$name .'_id'] );
                }
            }

            if ( $membership->status_id ) {
                $active = CRM_Core_DAO::getFieldValue('CRM_Member_DAO_MembershipStatus',
                                                      $membership->status_id,
                                                      'is_current_member');
                if ( $active ) {
                    $defaults['active'] = $active;
                }
            }

            $membership->free( );
            return $membership;
        }
        return null;
    }

    /** 
     * Function to delete membership.
     * 
     * @param int $membershipId membership id that needs to be deleted 
     *
     * @static
     * @access public
     */
    static function deleteMembership( $membershipId ) 
    {
        require_once 'CRM/Member/BAO/MembershipLog.php';
        CRM_Member_BAO_MembershipLog::del( $membershipId );

        //delete Custom Data, if any
        require_once 'CRM/Core/BAO/CustomValue.php';
        $customDAO = & new CRM_Core_DAO_CustomValue();
        $customDAO->entity_id = $membershipId;
        $customDAO->entity_table = 'civicrm_membership';
        $customDAO->find( );
        while( $customDAO->fetch( )) {
            $customDAO->delete();
        }

        require_once 'CRM/Member/DAO/MembershipPayment.php';
        $membershipPayment = & new CRM_Member_DAO_MembershipPayment( );
        $membershipPayment->membership_id = $membershipId;
        $membershipPayment->delete();

        require_once 'CRM/Member/DAO/Membership.php';
        $membership = & new CRM_Member_DAO_Membership( );
        $membership->id = $membershipId;
        return $membership->delete( );
    }

    /**                                                           
     * Delete the object records that are associated with this contact 
     *                    
     * @param  int  $contactId id of the contact to delete 
     * @userId int  $userID of the Logged-in id of the Civicrm.
     * 
     * @return boolean  true if deleted, false otherwise
     * @access public 
     * @static 
     */ 
    static function deleteContact( $contactID, $userID = null ) 
    {
        $membership =& new CRM_Member_DAO_Membership( );
        $membership->contact_id = $contactID;
        $membership->find( );
        
        while ( $membership->fetch( ) ) {
            self::deleteMembership( $membership->id );
        }
        
        // also we need to fix any membership types which point to this contact
        // for now lets just make this point to the current userID

        if ( !$userID ) {
            $session =& CRM_Core_Session::singleton( );
            $userID  = $session->get( 'userID' );
            if ( ! $userID ) {
                return;
            }
        }

        $query = "
UPDATE civicrm_membership_type
   SET member_of_contact_id = %1
 WHERE member_of_contact_id = %2
";
        $params = array( 1 => array( $userID, 'Integer' ), 2 => array( $contactID, 'Integer' ) );
        CRM_Core_DAO::executeQuery( $query, $params );

        // also reset any entries from membership log which this contact has an FK
        require_once 'CRM/Member/BAO/MembershipLog.php';
        CRM_Member_BAO_MembershipLog::resetModifiedID( $contactID );
    }

    /** 
     * Function to obtain active/inactive memberships from the list of memberships passed to it.
     * 
     * @param int    $contactId   contact id
     * @param array  $memberships membership records
     * @param string $status      active or inactive
     *
     * @return array $actives array of memberships based on status
     * @static
     * @access public
     */
    static function activeMembers( $contactId, $memberships, $status = 'active' ) 
    {
        $actives = array();
        if ( $status == 'active' ) {
            foreach ($memberships as $f => $v) {
                if ( CRM_Utils_Array::value( 'active', $v ) ) {
                    $actives[$f] = $v;
                }
            }
            return $actives;
        } elseif ( $status == 'inactive' ) {
            foreach ($memberships as $f => $v) {
                if ( ! CRM_Utils_Array::value('active',$v) ) {
                    $actives[$f] = $v;
                }
            }
            return $actives;
        }
        return null;
    }

    /**
     * Function to build Membership  Block in Contribution Pages 
     * 
     * @param object  $form                  form object
     * @param int     $pageId                contribution page id
     * @param boolean $formItems
     * @param int     $selectedMembershipID  selected membership id
     * @param boolean $thankPage             thank you page
     *
     * @static
     */
    function buildMembershipBlock( &$form , $pageID , $formItems = false,
                                   $selectedMembershipID = null ,$thankPage = false,
                                   $isTest = null )
    {
        require_once 'CRM/Member/DAO/MembershipBlock.php';

        $dao = & new CRM_Member_DAO_MembershipBlock();
        $dao->entity_table = 'civicrm_contribution_page';
        $dao->entity_id = $pageID; 
        $dao->is_active = 1;

        $separateMembershipPayment = false;
        if ( $dao->find(true) ) {
            require_once 'CRM/Member/DAO/MembershipType.php';
            require_once 'CRM/Member/DAO/Membership.php';

            $session = & CRM_Core_Session::singleton();
            $cid = $session->get('userID');    

            $membershipBlock   = array( ); 
            $membershipTypeIds = array( );
            $membershipTypes   = array( ); 
            $radio             = array( ); 

            $form->assign( "is_separate_payment", $dao->is_separate_payment );
            $separateMembershipPayment = $dao->is_separate_payment;
            CRM_Core_DAO::storeValues($dao, $membershipBlock );
            if( $dao->membership_types ) {
                $membershipTypeIds = explode( ',' , $dao->membership_types);
            }
            if(! empty( $membershipTypeIds ) ) {
                foreach ( $membershipTypeIds as $value ) {
                    $memType = & new CRM_Member_DAO_MembershipType(); 
                    $memType->id = $value;
                    if ( $memType->find(true) ) {
                        if ($selectedMembershipID  != null ) {
                            if ( $memType->id == $selectedMembershipID ) {
                                CRM_Core_DAO::storeValues($memType,$mem);
                                $form->assign( 'minimum_fee', CRM_Utils_Array::value('minimum_fee',$mem) );
                                $form->assign( 'membership_name', $mem['name'] );
                                if ( !$thankPage && $cid ) {
                                    $membership = &new CRM_Member_DAO_Membership();
                                    $membership->contact_id         = $cid;
                                    $membership->membership_type_id = $memType->id;
                                    if ( $membership->find(true) ) {
                                        $form->assign("renewal_mode", true );
                                        $mem['current_membership'] =  $membership->end_date;
                                    }
                                }
                                $membershipTypes[] = $mem;
                            }
                        } else {
                            $mem = array();
                            CRM_Core_DAO::storeValues($memType,$mem);
                            $radio[$memType->id] = $form->createElement('radio',null, null, null, $memType->id , null);
                            if ( $cid ) {
                                $membership = &new CRM_Member_DAO_Membership();
                                $membership->contact_id         = $cid;
                                $membership->membership_type_id = $memType->id;
                                
                                if ( ! is_null( $isTest ) ) {
                                    $membership->is_test        = $isTest;
                                }
                                
                                if ( $membership->find(true) ) {
                                    $form->assign("renewal_mode", true );
                                    $mem['current_membership'] =  $membership->end_date;
                                }
                            }
                            $membershipTypes[] = $mem;
                        }
                    }
                }
            }
            
            $form->assign( 'showRadio',$formItems );
            if ( $formItems ) {
                if ( ! $dao->is_required ) {
                    $form->assign( 'showRadioNoThanks', true );
                    $radio[''] = $form->createElement('radio',null,null,null,'no_thanks', null);
                    $form->addGroup($radio,'selectMembership',null);
                } else if( $dao->is_required  && count( $radio ) == 1 ) {
                    $temp = array_keys( $radio ) ;
                    $form->addElement('hidden', "selectMembership", $temp[0]  );
                    $form->assign('singleMembership' , true );
                    $form->assign( 'showRadio', false );
                } else {
                    $form->addGroup($radio,'selectMembership',null);
                }
                $form->addRule('selectMembership',ts("Please select one of the memberships"),'required');
            }
            
            $form->assign( 'membershipBlock' , $membershipBlock );
            $form->assign( 'membershipTypes' ,$membershipTypes );
        
        }

        return $separateMembershipPayment;
    }
    
    /**
     * Function to return Membership  Block info in Contribution Pages 
     * 
     * @param int $pageId contribution page id
     *
     * @static
     */
    static function getMembershipBlock( $pageID ) 
    {
        $membershipBlock = array();
        require_once 'CRM/Member/DAO/MembershipBlock.php';
        $dao = & new CRM_Member_DAO_MembershipBlock();
        $dao->entity_table = 'civicrm_contribution_page';
        
        $dao->entity_id = $pageID; 
        $dao->is_active = 1;
        if ( $dao->find(true) ) {
            CRM_Core_DAO::storeValues($dao, $membershipBlock );
        } else {
            return null;
        } 
        
        return $membershipBlock;
    }

    /**
     * Function to return current membership of given contacts 
     * 
     * @param int $contactID  contact id
     * @static
     */
    static function getContactMembership( $contactID , $memType, $isTest ) 
    {
        $dao = &new CRM_Member_DAO_Membership( );
        $dao->contact_id         = $contactID;
        $dao->membership_type_id = $memType;
        $dao->is_test            = $isTest;
        if ( $dao->find( true ) ) {
            $membership = array( );
            CRM_Core_DAO::storeValues( $dao, $membership );
            
            $membership['is_current_member'] = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipStatus', 
                                                                            $membership['status_id'],
                                                                            'is_current_member', 'id' );
            return $membership;
        }
        return false;
    }
    
    /**
     * Combine all the importable fields from the lower levels object
     *
     * @param string  $contactType contact type
     * @param boolean $status      
     *
     * @return array array of importable Fields
     * @access public
     */
    function &importableFields( $contactType = 'Individual', $status = true ) 
    {
        if ( ! self::$_importableFields ) {
            if ( ! self::$_importableFields ) {
                self::$_importableFields = array();
            }

            if (!$status) {
                $fields = array( '' => array( 'title' => ts('- do not import -') ) );
            } else {
                $fields = array( '' => array( 'title' => ts('- Membership Fields -') ) );
            }
            
            $tmpFields     = CRM_Member_DAO_Membership::import( );
            $contactFields = CRM_Contact_BAO_Contact::importableFields( $contactType, null );
            if ($contactType == 'Individual') {
                require_once 'CRM/Core/DAO/DupeMatch.php';
                $dao = & new CRM_Core_DAO_DupeMatch();
                $dao->find(true);
                $fieldsArray = explode('AND',$dao->rule);
            } elseif ($contactType == 'Household') {
                $fieldsArray = array('household_name', 'email');
            } elseif ($contactType == 'Organization') {
                $fieldsArray = array('organization_name', 'email');
            }
            $tmpConatctField = array();
            if( is_array($fieldsArray) ) {
                foreach ( $fieldsArray as $value) {
                    $tmpConatctField[trim($value)] = CRM_Utils_Array::value(trim($value),$contactFields);
                    if (!$status) {
                        $title = $tmpConatctField[trim($value)]['title']." (match to contact)" ;
                    } else {
                        $title = $tmpConatctField[trim($value)]['title'];
                    }
                    $tmpConatctField[trim($value)]['title'] = $title;
                }
            }
            $tmpConatctField['external_identifier'] = $contactFields['external_identifier'];
            $tmpConatctField['external_identifier']['title'] = $contactFields['external_identifier']['title'] . " (match to contact)";
            
            $fields = array_merge($fields, $tmpConatctField);
            $fields = array_merge($fields, $tmpFields);
            $fields = array_merge($fields, CRM_Core_BAO_CustomField::getFieldsForImport('Membership'));
            self::$_importableFields = $fields;
        }
        return self::$_importableFields;
    }

    /**
     * function to get all exportable fields
     *
     * @retun array return array of all exportable fields
     */
    function &exportableFields( ) 
    { 
        require_once 'CRM/Member/DAO/MembershipType.php';
        $expFieldMembership = CRM_Member_DAO_Membership::export( );
        $expFieldsMemType   = CRM_Member_DAO_MembershipType::export( );
        $fields = array_merge($expFieldMembership, $expFieldsMemType);
        $fields = array_merge($fields, $expFieldMembership );
        return $fields;
    }

    /**
     * Function to get membership summary
     * 
     * @param int    $membershipTypeId   membership type id
     * @param string $membershipTypeName membership type name
     *
     * @return returns memberhsip summary
     */
    function getMembershipSummary( $membershipTypeId ,$membershipTypeName = null) 
    {
        $membershipSummary = array();
        $queryString =  "SELECT  count( id ) as total_count
FROM   civicrm_membership
WHERE ";
        
        //calculate member count for current month 
        $currentMonth    = date("Y-m-01");
        $currentMonthEnd = date("Y-m-31");
        $whereCond =  "membership_type_id = $membershipTypeId AND start_date >= '".$currentMonth ."' AND start_date <= ' ".$currentMonthEnd."'" ;
        
        $query = $queryString . $whereCond;
        
        $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        if ( $dao->fetch( ) ) {
            $membershipSummary['month'] = array( "count" => $dao->total_count ,"name" => $membershipTypeName);
        }

        //calculate member count for current year 
        $currentYear    = date("Y-01-01");
        $currentYearEnd = date("Y-12-31");
        $whereCond =  "membership_type_id = $membershipTypeId AND start_date >= '".$currentYear ."' AND start_date <= '".$currentYearEnd."'";

        $query = $queryString . $whereCond;
        
        $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        if ( $dao->fetch( ) ) {
            $membershipSummary['year'] = array ( "count" => $dao->total_count ,"name" => $membershipTypeName) ;
        }

        // calculate total count for current membership
        $query = "SELECT  count(civicrm_membership.id ) as total_count
FROM   civicrm_membership left join civicrm_membership_status on ( civicrm_membership.status_id = civicrm_membership_status.id  ) WHERE civicrm_membership.membership_type_id = $membershipTypeId AND 
civicrm_membership_status.is_current_member =1";

        $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        if ( $dao->fetch( ) ) {
            $membershipSummary['current'] = array( "count" => $dao->total_count ,"name" => $membershipTypeName) ;
        }

        return $membershipSummary;
    }
    
    /** 
     * Function check the status of the membership before adding membership for a contact
     *
     * @param int $contactId contact id
     *
     * @return 
     */
    function statusAvilability( $contactId ) 
    {
        require_once 'CRM/Member/DAO/MembershipStatus.php';
        $membership =& new CRM_Member_DAO_MembershipStatus( );
        $membership->whereAdd('1');
        $count = $membership->count();
        
        if(!$count){
            $session =& CRM_Core_Session::singleton( );
            CRM_Core_Session::setStatus(ts('There are no status present, You can not add membership.'));
            return CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/contact/view', "reset=1&force=1&cid={$contactId}&selectedChild=member"));
        }
    }

    /**
     * Process the Memberships
     *
     * @param array  $membershipParams array of membership fields
     * @param int    $contactID        contact id 
     * @param object $form             form object  
     *
     * @return void
     * @access public
     */                                   
    public function postProcessMembership( $membershipParams, $contactID ,&$form, &$premiumParams)
    {
        $tempParams = $membershipParams;
        $paymentDone = false;
        $form->assign('membership_assign' , true );
        // WE NEED TO FIX THIS BAD NAMING
        $form->set('membershipID' , $membershipParams['selectMembership']);
        
        require_once 'CRM/Member/BAO/MembershipType.php';
        require_once 'CRM/Member/BAO/Membership.php';
        $membershipTypeID = $membershipParams['selectMembership'];
        $membershipDetails = CRM_Member_BAO_MembershipType::getMembershipTypeDetails( $membershipTypeID );
        $form->assign( 'membership_name', $membershipDetails['name'] );
        
        $minimumFee = CRM_Utils_Array::value('minimum_fee',$membershipDetails);
        
        $contributionTypeId = null;
        
        if ( $form->_values['amount_block_is_active']) {
            $contributionTypeId = $form->_values['contribution_type_id'];
        } else {
            $paymentDone  = true ;
            $params['amount'] = $minimumFee;
            $contributionTypeId = $membershipDetails['contribution_type_id']; 
        }
        
        $result = CRM_Contribute_BAO_Contribution::processConfirm( $form, $membershipParams, 
                                                                   $premiumParams, $contactID,
                                                                   $contributionTypeId, 
                                                                   'membership' );
        
        $errors = array();
        if ( is_a( $result[1], 'CRM_Core_Error' ) ) {
            $errors[1]       = $result[1];
        } else {
            $contribution[1] = $result[1];
        }
        
        $memBlockDetails    = CRM_Member_BAO_Membership::getMemberShipBlock( $form->_id );
        if ( $memBlockDetails['is_separate_payment']  && ! $paymentDone ) {
            $contributionType =& new CRM_Contribute_DAO_ContributionType( );
            $contributionType->id = $membershipDetails['contribution_type_id']; 
            if ( ! $contributionType->find( true ) ) {
                CRM_Core_Error::fatal( "Could not find a system table" );
            }
            $tempParams['amount'] = $minimumFee;
            $invoiceID = md5(uniqid(rand(), true));
            $tempParams['invoiceID'] = $invoiceID;
            if ($form->_values['is_monetary']) {
                if ( $form->_contributeMode == 'express' ) {
                    $result =& $payment->doExpressCheckout( $tempParams );
                } else {
                    $result =& $payment->doDirectPayment( $tempParams );
                }
            }
            if ( is_a( $result, 'CRM_Core_Error' ) ) {
                $errors[2] = $result;
            } else {
                $form->set('membership_trx_id' , $result['trxn_id']);
                $form->set('membership_amount'  , $minimumFee);
                
                $form->assign('membership_trx_id' , $result['trxn_id']);
                $form->assign('membership_amount'  , $minimumFee);
                $contribution[2] =
                    CRM_Contribute_Form_Contribution_Confirm::processContribution( $form,
                                                                                   $tempParams,
                                                                                   $result,
                                                                                   $contactID,
                                                                                   $contributionType,
                                                                                   false );
            }
        }
        
        $index = $memBlockDetails['is_separate_payment'] ? 2 : 1;

        if ( ! $errors[$index] ){
            $membership = self::renewMembership( $contactID, $membershipTypeID, $membershipParams['is_test'], $form);
            //insert payment record
            require_once 'CRM/Member/DAO/MembershipPayment.php';
            $dao =& new CRM_Member_DAO_MembershipPayment();    
            $dao->membership_id         = $membership->id;
            $dao->payment_entity_table  = 'civicrm_contribute';
            $dao->payment_entity_id     = $contribution[$index]->id;
            $dao->save();
        }
        
        foreach($errors as $error ) {
            CRM_Core_Error::displaySessionError( $error );
            CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/contribute/transact', '_qf_Main_display=true' ) );
        }
        
        //finally send an email receipt
        if ( !$errors[1]  &&  !$errors[2] ) {
            require_once "CRM/Contribute/BAO/ContributionPage.php";
            CRM_Contribute_BAO_ContributionPage::sendMail( $contactID,
                                                           $form->_values,
                                                           $contribution[$index]->id );
        }
    }
    
    /**
     * Renew the membership
     * 
     * This method will renew the membership and will add the modified
     * dates for mebership and in the log table.
     * 
     * @param int     $contactID           id of the contact 
     * @param int     $membershipTypeID    id of the membership type
     * @param boolean $is_test             if this is test contribution or live contribution
     * @param object  $form                form object  
     * @param array   $ipnParams           array of name value pairs, to be used (for e.g source) when $form not present
     *
     * @return object $membership          object of membership
     * 
     * @static
     * @access public
     * 
     **/
    static function renewMembership( $contactID, $membershipTypeID, $is_test,
                                     &$form, $changeToday = null, $ipnParams = null )
    {
        require_once 'CRM/Utils/Hook.php';
        
        $statusFormat = '%Y-%m-%d';
        $format       = '%Y%m%d';
        
        if ( $ipnParams ) {
            $template =& CRM_Core_Smarty::singleton( );
        }
        
        if ( $currentMembership = 
             CRM_Member_BAO_Membership::getContactMembership( $contactID, $membershipTypeID, $is_test ) ) {
            
            if ( $form ) {
                $form->set("renewal_mode", true );
            }
            
            // Check and fix the membership if it is STALE
            self::fixMembershipStatusBeforeRenew( $currentMembership, $changeToday );
            
            // Now Renew the membership
            if ( ! $currentMembership['is_current_member'] ) {
                // membership is not CURRENT
                require_once 'CRM/Member/BAO/MembershipStatus.php';
                                
                $dates =
                    CRM_Member_BAO_MembershipType::getRenewalDatesForMembershipType( $currentMembership['id'],
                                                                                     $changeToday );
                
                $currentMembership['join_date']     = 
                    CRM_Utils_Date::customFormat($currentMembership['join_date'], $format );
                $currentMembership['start_date']    = 
                    CRM_Utils_Date::customFormat($dates['start_date'],    $format );
                $currentMembership['end_date']      = 
                    CRM_Utils_Date::customFormat($dates['end_date'],      $format );
                $currentMembership['reminder_date'] = 
                    CRM_Utils_Date::customFormat($dates['reminder_date'], $format );
                $currentMembership['is_test']       = $is_test;
                
                if ( $form ) {
                    $currentMembership['source']    = ts( 'Online Contribution:' ) . ' ' . $form->_values['title'];
                }
                if ( is_array($ipnParams) ) {
                    $currentMembership['source']    = $ipnParams['source'];
                }

                if ( CRM_Utils_Array::value( 'id', $currentMembership ) ) {
                    CRM_Utils_Hook::pre('edit', 'Membership', $currentMembership['id'], $currentMembership);
                } else {
                    CRM_Utils_Hook::pre('create', 'Membership', null, $currentMembership);
                }
                
                $membership =& new CRM_Member_DAO_Membership();
                $membership->copyValues($currentMembership);
                $membership->save( );
                
                if ( CRM_Utils_Array::value( 'id', $currentMembership ) ) {
                    CRM_Utils_Hook::post( 'edit', 'Membership', $membership->id, $membership );
                } else {
                    CRM_Utils_Hook::post( 'create', 'Membership', $membership->id, $membership );
                }
                
                // membership status will change as per the new start
                // and end dates.
                $membershipStatus = 
                    CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate( 
                                                                               CRM_Utils_Date::customFormat( $membership->start_date,
                                                                                                             $statusFormat ),
                                                                               CRM_Utils_Date::customFormat( $membership->end_date,
                                                                                                             $statusFormat ),
                                                                               CRM_Utils_Date::customFormat( $membership->join_date,
                                                                                                             $statusFormat )
                                                                               );
                $membership->status_id = $membershipStatus['id'];
                $membership->save();
                
                //insert log here 
                $logParams = array( 
                                   'membership_id'         => $membership->id,
                                   'status_id'             => $membership->status_id,
                                   'start_date'            => (CRM_Utils_Date::customFormat($dates['log_start_date'],
                                                                                            $format) ),
                                   'end_date'              => (CRM_Utils_Date::customFormat($dates['end_date'],
                                                                                            $format) ), 
                                   'renewal_reminder_date' => (CRM_Utils_Date::customFormat($dates['reminder_date'],
                                                                                            $format) ), 
                                   'modified_id'           => $contactID,
                                   'modified_date'         => (CRM_Utils_Date::customFormat($currentMembership['today_date'],
                                                                                            $format) ) );
                $dontCare = null;
                
                require_once 'CRM/Member/BAO/MembershipLog.php';
                CRM_Member_BAO_MembershipLog::add( $logParams, $dontCare );
                
                if ( $form ) {
                    $form->assign('mem_start_date',  
                                  CRM_Utils_Date::customFormat($dates['start_date'], $format) );
                    $form->assign('mem_end_date', 
                                  CRM_Utils_Date::customFormat($dates['end_date'],   $format) );
                }
                if ( $ipnParams ) {
                    $template->assign('mem_start_date',  
                                      CRM_Utils_Date::customFormat($dates['start_date'], $format) );
                    $template->assign('mem_end_date', 
                                      CRM_Utils_Date::customFormat($dates['end_date'],   $format) );
                }

            } else {
                // CURRENT Membership
                require_once 'CRM/Member/BAO/MembershipStatus.php';
                $membership =& new CRM_Member_DAO_Membership();
                $membership->id = $currentMembership['id'];
                $membership->find( true ); 
                                
                $dates = CRM_Member_BAO_MembershipType::getRenewalDatesForMembershipType( $membership->id , 
                                                                                          $changeToday );
                
                // Insert renewed dates for CURRENT membership
                $params               = array( );
                $params['join_date']  = CRM_Utils_Date::isoToMysql( $membership->join_date );
                $params['start_date'] = CRM_Utils_Date::isoToMysql( $membership->start_date );
                $params['end_date']   = CRM_Utils_Date::customFormat( $dates['end_date'], $format );

                CRM_Utils_Hook::pre('edit', 'Membership', $currentMembership['id'], $params );
                $membership->copyValues( $params );
                $membership->save( );
                CRM_Utils_Hook::post( 'edit', 'Membership', $membership->id, $membership );
                
                //Now insert the log for renewal 
                $logParams = array( 
                                   'membership_id'         => $membership->id,
                                   'status_id'             => $membership->status_id,
                                   'start_date'            => ( CRM_Utils_Date::customFormat($dates['log_start_date'],
                                                                                             $format) ),
                                   'end_date'              => ( CRM_Utils_Date::customFormat($dates['end_date'],
                                                                                             $format) ),
                                   'renewal_reminder_date' => ( CRM_Utils_Date::customFormat($dates['reminder_date'],
                                                                                             $format) ),
                                   'modified_id'           => $contactID,
                                   'modified_date'         => (CRM_Utils_Date::customFormat($currentMembership['today_date'],
                                                                                            $format) ) );
                $dontCare = null;
                
                require_once 'CRM/Member/BAO/MembershipLog.php';
                CRM_Member_BAO_MembershipLog::add( $logParams, $dontCare );
                
                if ( $form ) {
                    $form->assign('mem_start_date',  
                                  CRM_Utils_Date::customFormat($dates['start_date'], $format));
                    $form->assign('mem_end_date', 
                                  CRM_Utils_Date::customFormat($dates['end_date'],   $format));
                }
                if ( $ipnParams ) {
                    $template->assign('mem_start_date',  
                                      CRM_Utils_Date::customFormat($dates['start_date'], $format) );
                    $template->assign('mem_end_date', 
                                      CRM_Utils_Date::customFormat($dates['end_date'],   $format) );
                }
            }

        } else {
            require_once 'CRM/Member/BAO/MembershipStatus.php';
            
            $memParams                       = array( );
            $memParams['contact_id']         = $contactID;
            $memParams['membership_type_id'] = $membershipTypeID;

            $dates = CRM_Member_BAO_MembershipType::getDatesForMembershipType($membershipTypeID);

            $memParams['join_date']     = 
                CRM_Utils_Date::customFormat( $dates['join_date'],     $format );
            $memParams['start_date']    = 
                CRM_Utils_Date::customFormat( $dates['start_date'],    $format );
            $memParams['end_date']      = 
                CRM_Utils_Date::customFormat( $dates['end_date'],      $format );
            $memParams['reminder_date'] = 
                CRM_Utils_Date::customFormat( $dates['reminder_date'], $format );
            $memParams['is_test']       = $is_test;

            if ( $form ) {
                $memParams['source'  ]  = ts( 'Online Contribution:' ) . ' ' . $form->_values['title'];
            }
            if ( is_array($ipnParams) ) {
                $memParams['source']    = $ipnParams['source'];
            }
            
            $status =
                CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate( 
                                                                           CRM_Utils_Date::customFormat( $dates['start_date'],
                                                                                                         $statusFormat ),
                                                                           CRM_Utils_Date::customFormat( $dates['end_date'],
                                                                                                         $statusFormat ),
                                                                           CRM_Utils_Date::customFormat( $dates['join_date'],
                                                                                                         $statusFormat )
                                                                           );  
            $memParams['status_id']     = $status['id'];
            $memParams['is_override']   = false;
            
            CRM_Utils_Hook::pre('create', 'Membership', null, $memParams );
            
            $membership = &new CRM_Member_DAO_Membership();
            $membership->copyValues($memParams);
            $membership->save();
            
            CRM_Utils_Hook::post( 'create', 'Membership', $membership->id, $membership );
            
            //Now insert the log  
            $logParams = array( 
                               'membership_id' => $membership->id,
                               'status_id'     => $membership->status_id,
                               'start_date'    => ( CRM_Utils_Date::customFormat($dates['start_date'],'%Y%m%d') ),
                               'end_date'      => ( CRM_Utils_Date::customFormat($dates['end_date'],'%Y%m%d') ),
                               'modified_id'   => $contactID,
                               'modified_date' => date('Ymd') );
            
            $dontCare = null;
            
            require_once 'CRM/Member/BAO/MembershipLog.php';
            CRM_Member_BAO_MembershipLog::add( $logParams, $dontCare );
            
            if ( $form ) {
                $form->assign( 'mem_start_date',
                               CRM_Utils_Date::customFormat($dates['start_date'], $format) );
                $form->assign( 'mem_end_date'  ,
                               CRM_Utils_Date::customFormat($dates['end_date'],   $format) );
            }
            if ( $ipnParams ) {
                $template->assign('mem_start_date',  
                                  CRM_Utils_Date::customFormat($dates['start_date'], $format) );
                $template->assign('mem_end_date', 
                                  CRM_Utils_Date::customFormat($dates['end_date'],   $format) );
            }
        }
        
        return $membership;
    }
    
    /**
     * Method to fix membership status of stale membership
     * 
     * This method first checks if the membership is stale. If it is,
     * then status will be updated based on existing start and end
     * dates and log will be added for the status change.
     * 
     * @param  array  $currentMembership   referance to the array
     *                                     containing all values of
     *                                     the current membership
     * @param  array  $changeToday         array of month, day, year
     *                                     values in case today needs
     *                                     to be customised, null otherwise
     * 
     * @return void
     * @static
     */
    static function fixMembershipStatusBeforeRenew( &$currentMembership, $changeToday )
    {
        $today = CRM_Utils_Date::getToday( $changeToday );
        require_once 'CRM/Member/BAO/MembershipStatus.php';
        $status = CRM_Member_BAO_MembershipStatus::getMembershipStatusByDate( 
                               $currentMembership['start_date'],
                               $currentMembership['end_date'],
                               $currentMembership['join_date'],
                               $today
                               );
        
        $currentMembership['today_date'] = $today;
        
        if ( $status['id'] !== $currentMembership['status_id'] ) {
            $memberDAO = new CRM_Member_BAO_Membership( );
            $memberDAO->id = $currentMembership['id'];
            $memberDAO->find(true);
                        
            $memberDAO->status_id  = $status['id'];
            $memberDAO->join_date  = CRM_Utils_Date::isoToMysql( $memberDAO->join_date );
            $memberDAO->start_date = CRM_Utils_Date::isoToMysql( $memberDAO->start_date );
            $memberDAO->end_date   = CRM_Utils_Date::isoToMysql( $memberDAO->end_date );
            $memberDAO->save( );
            
            CRM_Core_DAO::storeValues( $memberDAO , $currentMembership );
            
            $memberDAO->free( );
            
            $currentMembership['is_current_member'] = CRM_Core_DAO::getFieldValue( 
                                                      'CRM_Member_DAO_MembershipStatus',
                                                      $currentMembership['status_id'],
                                                      'is_current_member' );
            $format = '%Y%m%d';
            $logParams = array( 'membership_id'         => $currentMembership['id'],
                                'status_id'             => $status['id'],
                                'start_date'            => CRM_Utils_Date::customFormat( 
                                                                        $currentMembership['start_date'],
                                                                        $format ),
                                'end_date'              => CRM_Utils_Date::customFormat(
                                                                        $currentMembership['end_date'],
                                                                        $format ),
                                'modified_id'           => $currentMembership['contact_id'],
                                'modified_date'         => CRM_Utils_Date::customFormat( 
                                                                        $currentMembership['today_date'],
                                                                        $format ),
                                'renewal_reminder_date' => CRM_Utils_Date::customFormat(
                                                                        $currentMembership['reminder_date'],
                                                                        $format )
                                );
            $dontCare = null;
            require_once 'CRM/Member/BAO/MembershipLog.php';
            CRM_Member_BAO_MembershipLog::add( $logParams, $dontCare );
        }
    }
    
    /**
     * Function to get the contribution page id from the membership record
     *
     * @param int membershipId membership id
     *
     * @return int $contributionPageId contribution page id
     * @access public
     * @static
     */
    static function getContributionPageId( $membershipId )
    {
        $query = "
SELECT c.contribution_page_id pageId
FROM civicrm_membership_payment mp, civicrm_contribution c
WHERE mp.payment_entity_table ='civicrm_contribute'
  AND mp.payment_entity_id = c.id
  AND mp.membership_id = " . CRM_Utils_Type::escape( $membershipId, 'Integer' ) ;

        $contributionPageID = null;

        $dao =& new CRM_Core_DAO( );
        $dao->query( $query );
        while ( $dao->fetch( ) ) {
            $contributionPageID = $dao->pageId;
        }
        
        return $contributionPageID;
    }

    /**
     * Function to delete related memberships
     *
     * @param int $ownerMembershipId
     * @param int $contactId
     *
     * @return null
     * @static
     */
    static function deleteRelatedMemberships( $ownerMembershipId, $contactId = null ) 
    {
        $membership = & new CRM_Member_DAO_Membership( );
        $membership->owner_membership_id = $ownerMembershipId;
        
        if ( $contactId ) {
            $membership->contact_id      = $contactId;
        }
        
        $membership->find( );
        while ( $membership->fetch( ) ) {
            self::deleteMembership( $membership->id ) ;
        }
        $membership->free( );
    }
    
    /**
     * Function to get list of membership fields for profile
     * For now we only allow custom membership fields to be in
     * profile
     *
     * @return return the list of membership fields
     * @static
     * @access public
     */
    static function getMembershipFields( ) 
    {
        $membershipFields =& CRM_Member_DAO_Membership::export( );
        
        foreach ($membershipFields as $key => $var) {
            if ($key == 'membership_contact_id') {
                continue;
            }
            $fields[$key] = $var;
        }
        
        $fields = array_merge($fields, CRM_Core_BAO_CustomField::getFieldsForImport('Membership'));
        
        return $fields;
    }

    /**
     * 
     * This method will process (add/edit) membership for IPN files (e.g PaypalIPN, GoogleIPN ..).
     *
     * @param int     $contactID           id of the contact 
     * @param object  $contribution        contribution object
     * @param int     $membershipTypeID    id of the membership type
     * @param int     $contribAmount       contribution amount
     *
     * @return void
     * 
     * @static
     * @access public
     * 
     **/
    static function processIPNMembership( $contactID, &$contribution, $membershipTypeID, &$contribAmount ) 
    {
        $template =& CRM_Core_Smarty::singleton( );
        $template->assign('membership_assign' , true );
        
        require_once 'CRM/Member/BAO/MembershipType.php';
        $membershipDetails = CRM_Member_BAO_MembershipType::getMembershipTypeDetails( $membershipTypeID );
        $template->assign('membership_name',$membershipDetails['name']);
        
        $minimumFee = $membershipDetails['minimum_fee'];
        $template->assign('membership_amount'  , $minimumFee);
        $contribAmount -= $minimumFee;
        if ( $contribAmount < 0 ) {
            $contribAmount = 0;
        }
        
        $ipnParams = array( 'source' => $contribution->source );
        self::renewMembership( $contactID, $membershipTypeID, $contribution->is_test, $form, null, $ipnParams );
        
        require_once 'CRM/Member/DAO/MembershipBlock.php';
        $dao = & new CRM_Member_DAO_MembershipBlock();
        $dao->entity_table = 'civicrm_contribution_page';
        $dao->entity_id = $contribution->contribution_page_id; 
        $dao->is_active = 1;
        if ( $dao->find(true) ) {
            $membershipBlock   = array(); 
            CRM_Core_DAO::storeValues($dao, $membershipBlock );
            $template->assign( 'membershipBlock' , $membershipBlock );
        }
    }
}
?>
