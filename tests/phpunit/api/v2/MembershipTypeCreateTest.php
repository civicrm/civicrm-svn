<?php

require_once 'api/v2/Membership.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v2_MembershipTypeCreateTest extends CiviUnitTestCase 
{
    protected $_contactID;
    protected $_contributionTypeID;

    function get_info( )
    {
        return array(
                     'name'        => 'MembershipType Create',
                     'description' => 'Test all Membership Type Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    } 
    
    function setUp() 
    {
        parent::setUp();

        $this->_contactID           = $this->organizationCreate( ) ;
        $this->_contributionTypeID  = $this->contributionTypeCreate( );
              
    }
    
    function testMembershipTypeCreateEmpty()
    {
        $params = array();        
        $membershiptype = & civicrm_membership_type_create($params);
        $this->assertEquals( $membershiptype['is_error'], 1 );
    }
          
    function testMembershipTypeCreateWithoutMemberOfContactId()
    {
        $params = array(
                        'name'                 => '60+ Membership',
                        'description'          => 'people above 60 are given health instructions',                        'contribution_type_id' => $this->_contributionTypeID,
                        'minimum_fee'          => '200',
                        'duration_unit'        => 'month',
                        'duration_interval'    => '10',
                        'period_type'          => 'rolling',
                        'visibility'           => 'public'
                        );
        
        $membershiptype = & civicrm_membership_type_create($params);
        $this->assertEquals( $membershiptype['is_error'], 1 );
  
    }
    
    function testMembershipTypeCreateWithoutContributionTypeId()
    {
      $params = array(
                        'name'                 => '70+ Membership',
                        'description'          => 'people above 70 are given health instructions',                        'member_of_contact_id' => $this->_contactID,
                        'minimum_fee'          => '200',
                        'duration_unit'        => 'month',
                        'duration_interval'    => '10',
                        'period_type'          => 'rolling',
                        'visibility'           => 'public'
                        );
        $membershiptype = & civicrm_membership_type_create($params);
        $this->assertEquals( $membershiptype['is_error'], 1 );
    }   
         
    function testMembershipTypeCreateWithoutDurationUnit()
    {
        
        $params = array(
                        'name'                 => '80+ Membership',
                        'description'          => 'people above 80 are given health instructions',                        'member_of_contact_id' => $this->_contactID,
                        'contribution_type_id' => $this->_contributionTypeID,
                        'minimum_fee'          => '200',
                        'duration_unit'        => 'month',
                        'duration_interval'    => '10',                 
                        'visibility'           => 'public'
                        );
        
        $membershiptype = & civicrm_membership_type_create($params);
        $this->assertEquals( $membershiptype['is_error'], 0 );
        $this->assertNotNull( $membershiptype['id'] );   
        $this->membershipTypeDelete( $membershiptype['id'] );
        
    }
       
    function testMembershipTypeCreateWithoutName()
    {
        $params = array(
                        'name'                 => '50+ Membership',
                        'description'          => 'people above 50 are given health instructions',
                        'member_of_contact_id' => $this->_contactID,
                        'contribution_type_id' => $this->_contributionTypeID,
                        'minimum_fee'          => '200',
                        'duration_unit'        => 'month',
                        'duration_interval'    => '10',
                        'period_type'          => 'rolling',
                        'visibility'           => 'public'
                        );
        
        $membershiptype = & civicrm_membership_type_create($params);  
        $this->assertEquals( $membershiptype['is_error'], 0 );
        if ( ! $membershiptype['is_error'] ) {
            $this->assertNotNull( $membershiptype['id'] );   
            $this->membershipTypeDelete( $membershiptype['id'] );
        }
    }
    
    function testMembershipTypeCreate()
    {
        $params = array(
                        'name'                 => '40+ Membership',
                        'description'          => 'people above 40 are given health instructions', 
                        'member_of_contact_id' => $this->_contactID,
                        'contribution_type_id' => $this->_contributionTypeID,
                        'minimum_fee'          => '200',
                        'duration_unit'        => 'month',
                        'duration_interval'    => '10',
                        'period_type'          => 'rolling',
                        'visibility'           => 'public'
                        );
	
        $membershiptype = & civicrm_membership_type_create($params);  
        $this->assertEquals( $membershiptype['is_error'], 0 );
        if ( ! $membershiptype['is_error'] ) {
            $this->assertNotNull( $membershiptype['id'] );   
            $this->membershipTypeDelete( $membershiptype['id'] );
        }
    }
    
    function tearDown() 
    {
        $this->contactDelete( $this->_contactID ) ;
        $this->contributionTypeDelete($this->_contributionTypeID);
    }
}
 
?> 