<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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


require_once 'api/v3/MembershipPayment.php';
require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'api/v3/MembershipType.php';
require_once 'api/v3/MembershipStatus.php';
require_once 'CRM/Member/BAO/MembershipType.php';
require_once 'CRM/Member/BAO/Membership.php';


class api_v3_MembershipPaymentTest extends CiviUnitTestCase 
{
    protected $_apiversion;   
    function setUp() 
    {
        parent::setUp();
        $this->_apiversion = 3;
        $this->_contactID           = $this->organizationCreate( null) ;
        $this->_contributionTypeID  = $this->contributionTypeCreate();
        $this->_membershipTypeID    = $this->membershipTypeCreate( $this->_contactID );
        $this->_membershipStatusID  = $this->membershipStatusCreate( 'test status' );
        $activityTypes = CRM_Core_PseudoConstant::activityType( true,  true, true, 'name' );    
    }
    
    function tearDown() 
    {
        $this->contributionTypeDelete( );
        $params = array( 'id' => $this->_membershipTypeID );
        $this->membershipTypeDelete( $params );
        $this->membershipStatusDelete( $this->_membershipStatusID );
        civicrm_api('contact','delete', array('version' => API_LATEST_VERSION, 'id' => $this->_contactID));
    }
    
    ///////////////// civicrm_membership_payment_create methods
    

    
    /**
     * Test civicrm_membership_payment_create with empty params.
     */
    public function testCreateEmptyParams()
    {  
        $params = array('version' =>$this->_apiversion);
        $CreateEmptyParams = civicrm_api('membership_payment','create', $params);
        $this->assertEquals( $CreateEmptyParams['error_message'],'Mandatory key(s) missing from params array: membership_id, contribution_id');
    }
    
    /**
     * Test civicrm_membership_payment_create - success expected.
     */
    public function testCreate()
    {
        $contactId           = $this->individualCreate( null) ;
      

        $params = array (
                         'contact_id'             => $contactId,
                         'currency'               => 'USD',
                         'contribution_type_id'   => $this->_contributionTypeID,
                         'contribution_status_id' => 1,
                         'contribution_page_id'   => null, 
                         'payment_instrument_id'  => 1,
                         'source'                 => 'STUDENT',
                         'receive_date'           => '20080522000000',
                         'receipt_date'           => '20080522000000',
                         'id'                     => null,                         
                         'total_amount'           => 200.00,
                         'trxn_id'                => '22ereerwww322323',
                         'invoice_id'             => '22ed39c9e9ee6ef6031621ce0eafe6da70',
                         'thankyou_date'          => '20080522'
                         );
        
        require_once 'CRM/Contribute/BAO/Contribution.php';
        $contribution = CRM_Contribute_BAO_Contribution::create( $params ,$ids );
        $params = array(
                        'contact_id'         => $contactId,  
                        'membership_type_id' => $this->_membershipTypeID,
                        'join_date'          => '2006-01-21',
                        'start_date'         => '2006-01-21',
                        'end_date'           => '2006-12-21',
                        'source'             => 'Payment',
                        'is_override'        => 1,
                        'status_id'          => $this->_membershipStatusID,
                        'version'            => API_LATEST_VERSION,
                        );

        $membership = civicrm_api('membership', 'create',$params);
        $this->assertAPISuccess($membership, "membership created in line " . __LINE__);
        
        $params = array(
                        'contribution_id'    => $contribution->id,  
                        'membership_id'      => $membership['id'],
                        'version'						 => $this->_apiversion,
                        );
        $result = civicrm_api('membership_payment','create', $params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['values'][$result['id']]['membership_id'],$membership['id'] ,'Check Membership Id in line ' . __LINE__);
        $this->assertEquals( $result['values'][$result['id']]['contribution_id'],$contribution->id ,'Check Contribution Id in line ' . __LINE__);
        $this->contributionDelete( $contribution->id );
        $this->membershipDelete( $membership['id'] );
        
    }    
    

    ///////////////// civicrm_membershipPayment_get methods
    
    /**
     * Test civicrm_membershipPayment_get with wrong params type.
     */
    public function testGetWrongParamsType()
    { 
        $params = 'eeee';
        $GetWrongParamsType = civicrm_api('membership_payment','get',$params);
        $this->assertEquals( $GetWrongParamsType['error_message'],'Input variable `params` is not an array');
    }

    /**
     * Test civicrm_membershipPayment_get with empty params.
     */
    public function testGetEmptyParams()
    {
        $params = array();
        $GetEmptyParams = civicrm_api('membership_payment','get',$params);
        $this->assertEquals( $GetEmptyParams['error_message'],'Mandatory key(s) missing from params array: version');
        
    }
    
    /**
     * Test civicrm_membershipPayment_get - success expected.
     */
    public function testGet()
    {
        $contactId = $this->individualCreate(null );
        $params = array (
                         'contact_id'             => $contactId,
                         'currency'               => 'USD',
                         'contribution_type_id'   => $this->_contributionTypeID,
                         'contribution_status_id' => 1,
                         'contribution_page_id'   => null, 
                         'payment_instrument_id'  => 1,
                         'id'                     => null,                         
                         'total_amount'           => 200.00,
                         'version'								=> $this->_apiversion,
                         );
        
        require_once 'CRM/Contribute/BAO/Contribution.php';
        $contribution = CRM_Contribute_BAO_Contribution::create( $params ,$ids );
        $params = array(
                        'contact_id'         => $contactId,  
                        'membership_type_id' => $this->_membershipTypeID,
                        'source'             => 'Payment',
                        'is_override'        => 1,
                        'status_id'          => $this->_membershipStatusID,
                        'version'						 => $this->_apiversion,
                        );
        $ids = array();
        $membership = CRM_Member_BAO_Membership::create( $params, $ids );
        
        $params = array(
                        'contribution_id'    => $contribution->id,  
                        'membership_id'      => $membership->id,
                        'version'						 => $this->_apiversion,
                        );
        $Create = civicrm_api('membership_payment','create', $params);
     
        $result = civicrm_api('membership_payment','get',$params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);        
        $this->assertEquals( $result['values'][$result['id']]['membership_id'],$membership->id ,'Check Membership Id');
        $this->assertEquals( $result['values'][$result['id']]['contribution_id'],$contribution->id ,'Check Contribution Id');
        
        $this->contributionDelete( $contribution->id );
        $this->membershipDelete( $membership->id );
    }    
   
}

