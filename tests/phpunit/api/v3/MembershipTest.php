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

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'api/v3/Membership.php';
require_once 'api/v3/MembershipType.php';
require_once 'api/v3/MembershipStatus.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_MembershipTest extends CiviUnitTestCase
{
    protected $_apiversion;
    protected $_contactID;
    protected $_membershipTypeID;
    protected $_membershipStatusID ;
    protected $__membershipID;
    
    public function setUp()
    {
        //  Connect to the database
        parent::setUp();
        $this->_apiversion =3;
        $this->_contactID           = $this->individualCreate(null ) ;
 
        $this->_membershipTypeID    = $this->membershipTypeCreate( $this->_contactID);        
        $this->_membershipStatusID  = $this->membershipStatusCreate( 'test status' );                
 
        $params = array(
                        'contact_id'         => $this->_contactID,  
                        'membership_type_id' => $this->_membershipTypeID,
                        'join_date'          => '2009-01-21',
                        'start_date'         => '2009-01-21',
                        'end_date'           => '2009-12-21',
                        'source'             => 'Payment',
                        'is_override'        => 1,
                        'status_id'          => $this->_membershipStatusID
                        );
        
        $this->_membershipID = $this->contactMembershipCreate( $params );

    }

    /**
     *  Test civicrm_membership_delete()
     */
    function testMembershipDelete()
    {
        $params=array('id' 			 => $this->_membershipID,
                      'version'  => $this->_apiversion,);
        $result = civicrm_api3_membership_delete($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0,
                             "In line " . __LINE__ );      
  
    }

    
    function testMembershipDeleteEmpty( ) 
    {
        $params = array( );
        $result = civicrm_api3_membership_delete( $params );
        $this->assertEquals( $result['is_error'], 1 );
    }



    /**
     *  Test civicrm_membership_delete() with invalid Membership Id
     */
    function testMembershipDeleteWithInvalidMembershipId( )
    {
        $membershipId = 'membership';
        $result = civicrm_api3_membership_delete($membershipId);
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     *  All other methods calls MembershipType and MembershipContact
     *  api, but putting simple test methods to control existence of
     *  these methods for backwards compatibility, also verifying basic
     *  behaviour is the same as new methods.
     */
     
     function testContactMembershipsGet()
     {
         $this->assertTrue( function_exists(civicrm_api3_membership_get) );
         $params = array('version' => $this->_apiversion);
         $result = civicrm_api3_membership_get( $params );
         $this->assertEquals( 0, $result['is_error'],
                              "In line " . __LINE__ );
     }
     
     function testContactMembershipCreate()
     {
         $this->assertTrue( function_exists(civicrm_membership_create) );
         $params = array();
         $result = civicrm_api3_membership_create( $params );
         $this->assertEquals( 1, $result['is_error'],
                              "In line " . __LINE__ );
     }


        /**
     * Test civicrm_membership_get with empty params.
     * Error expected.
     */
    function testGetWithEmptyParams()
    {
        $params = array();
        $result = & civicrm_api3_membership_get( $params );
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    /**
     * Test civicrm_membership_get with params with wrong type.
     * Gets treated as contact_id, memberships expected.
     */
    function testGetWithWrongParamsType()
    {
        $params = 'a string';
        $result = & civicrm_api3_membership_get( $params );
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }


    /**
     * Test civicrm_membership_get with params not array.
     * Gets treated as contact_id, memberships expected.
     */
    function testGetWithParamsContactId()
    {
        $membership =& civicrm_api3_membership_get( $this->_contactID );

        $result = $membership[$this->_contactID][$this->_membershipID];

        $this->assertEquals($result['contact_id'],         $this->_contactID, "In line " . __LINE__);
        $this->assertEquals($result['membership_type_id'], $this->_membershipTypeID, "In line " . __LINE__);
        $this->assertEquals($result['status_id'],          $this->_membershipStatusID, "In line " . __LINE__);
        $this->assertEquals($result['join_date'],          '2009-01-21', "In line " . __LINE__);
        $this->assertEquals($result['start_date'],         '2009-01-21', "In line " . __LINE__);
        $this->assertEquals($result['end_date'],           '2009-12-21', "In line " . __LINE__);
        $this->assertEquals($result['source'],             'Payment', "In line " . __LINE__);
        $this->assertEquals($result['is_override'],         1, "In line " . __LINE__);        
    }
        
    /**
     * Test civicrm_membership_get with proper params.
     * Memberships expected.
     */
    function testGet()
    {
        $params = array ( 'contact_id' => $this->_contactID,
                          'version'		=> $this->_apiversion, );

        $membership =& civicrm_api3_membership_get( $params );

        $result = $membership[$this->_contactID][$this->_membershipID];
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals($result['contact_id'],         $this->_contactID, "In line " . __LINE__);
        $this->assertEquals($result['membership_type_id'], $this->_membershipTypeID, "In line " . __LINE__);
        $this->assertEquals($result['status_id'],          $this->_membershipStatusID, "In line " . __LINE__);
        $this->assertEquals($result['join_date'],          '2009-01-21', "In line " . __LINE__);
        $this->assertEquals($result['start_date'],         '2009-01-21', "In line " . __LINE__);
        $this->assertEquals($result['end_date'],           '2009-12-21', "In line " . __LINE__);
        $this->assertEquals($result['source'],             'Payment', "In line " . __LINE__);
        $this->assertEquals($result['is_override'],         1, "In line " . __LINE__);        
    }

    /**
     * Test civicrm_membership_get for only active.
     * Memberships expected.
     */
    function testGetOnlyActive()
    {
        $params = array ( 'contact_id'  => $this->_contactID,
                          'active_only' => 1);

        $membership =& civicrm_api3_membership_get( $params );
        $result = $membership[$this->_contactID][$this->_membershipID];

        $this->assertEquals($result['status_id'], $this->_membershipStatusID, "In line " . __LINE__);
        $this->assertEquals($result['contact_id'], $this->_contactID, "In line " . __LINE__);
    }

    /**
     * Test civicrm_membership_get for non exist contact.
     * empty Memberships.
     */
    function testGetNoContactExists()
    {
        $params = array ( 'contact_id'  => 'NoContact' );
                          
        $membership =& civicrm_api3_membership_get( $params );
        $this->assertEquals($membership['record_count'], 0, "In line " . __LINE__);
    }

    /**
     * Test civicrm_membership_get with relationship.
     * get Memberships.
     */
    function testGetWithRelationship()
    {

        $membershipOrgId = $this->organizationCreate(null  );
        $memberContactId = $this->individualCreate(null ) ;

        $relTypeParams = array(
                               'name_a_b'       => 'Relation 1',
                               'name_b_a'       => 'Relation 2',
                               'description'    => 'Testing relationship type',
                               'contact_type_a' => 'Organization',
                               'contact_type_b' => 'Individual',
                               'is_reserved'    => 1,
                               'is_active'      => 1,
                               'version'				=> $this->_apiversion,
                               );
        $relTypeID = $this->relationshipTypeCreate( $relTypeParams );

        $params = array( 'name'                   => 'test General',
                         'duration_unit'          => 'year',
                         'duration_interval'      => 1,
                         'period_type'            => 'rolling',
                         'member_of_contact_id'   => $membershipOrgId,
                         'domain_id'		  => 1,
                         'contribution_type_id'   => 1,
                         'relationship_type_id'   => $relTypeID,
                         'relationship_direction' => 'b_a',
                         'is_active'              => 1,
                         'version'				=> $this->_apiversion, );        
        $memType = civicrm_api3_membership_type_create( $params );
        // in order to reload static caching -
        CRM_Member_PseudoConstant::membershipType( null, true );

        $params = array(
                        'contact_id'         => $memberContactId,
                        'membership_type_id' => $memType['id'],
                        'join_date'          => '2009-01-21',
                        'start_date'         => '2009-01-21',
                        'end_date'           => '2009-12-21',
                        'source'             => 'Payment',
                        'is_override'        => 1,
                        'status_id'          => $this->_membershipStatusID,
                        'version'				=> $this->_apiversion,
                        );
        $membershipID = $this->contactMembershipCreate( $params );

        $params = array ( 'contact_id'  => $memberContactId ,
                          'membership_type_id' => $memType['id'],
                          'version'				=> $this->_apiversion, );
                          
        $result =& civicrm_api3_membership_get( $params );
        
        $this->assertArrayHasKey( $memberContactId, $result,
                                  "In line " . __LINE__ );

        // extra one for the record county key
        $this->assertEquals( 2, count( $result ),
                             "In line " . __LINE__ );

        $membership = $result[$memberContactId][$membershipID];
        $this->assertEquals( $this->_membershipStatusID, $membership['status_id'], 
                             "In line " . __LINE__);
    }

///////////////// civicrm_membership_create methods

    /**
     * Test civicrm_contact_memberships_create with empty params.
     * Error expected.
     */    
    function testCreateWithEmptyParams() 
    {
        $params = array();
        $result = civicrm_api3_membership_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
    }

    /**
     * Test civicrm_contact_memberships_create with params with wrong type.
     * Error expected.
     */
    function testCreateWithParamsString()
    {
        $params = 'a string';
        $result = & civicrm_api3_membership_create( $params );
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }

    function testMembershipCreateMissingRequired( ) 
    {
        $params = array(
                        'membership_type_id' => '1',
                        'join_date'          => '2006-01-21',
                        'start_date'         => '2006-01-21',
                        'end_date'           => '2006-12-21',
                        'source'             => 'Payment',
                        'status_id'          => '2' ,
                        'version'				=> $this->_apiversion,                      
                        );
        
        $result = civicrm_api3_membership_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
    }
    
    function testMembershipCreate( ) 
    {
        $params = array(
                        'contact_id'         => $this->_contactID,  
                        'membership_type_id' => $this->_membershipTypeID,
                        'join_date'          => '2006-01-21',
                        'start_date'         => '2006-01-21',
                        'end_date'           => '2006-12-21',
                        'source'             => 'Payment',
                        'is_override'        => 1,
                        'status_id'          => $this->_membershipStatusID ,                      
                        'version'				=> $this->_apiversion,                        );

        $result = civicrm_api3_membership_create( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0 );
        $this->assertNotNull( $result['id'] );
    }

    /**
     * Test civicrm_contact_memberships_create with membership id (edit
     * membership).
     * success expected.
     */
    function testMembershipCreateWithId( ) 
    {
        $params = array(
                        'id'                 => $this->_membershipID,
                        'contact_id'         => $this->_contactID,  
                        'membership_type_id' => $this->_membershipTypeID,
                        'join_date'          => '2006-01-21',
                        'start_date'         => '2006-01-21',
                        'end_date'           => '2006-12-21',
                        'source'             => 'Payment',
                        'is_override'        => 1,
                        'status_id'          => $this->_membershipStatusID,
                       'version'				=> $this->_apiversion,                       
                        );

        $result = civicrm_api3_membership_create( $params );
        $this->assertEquals( $result['is_error'], 0 );
        $this->assertEquals( $result['id'] , $this->_membershipID );
    }

    /**
     * Test civicrm_contact_memberships_create Invalid membership data
     * Error expected.
     */
    function testMembershipCreateInvalidMemData( ) 
    {
        //membership_contact_id as string
        $params = array(
                        'membership_contact_id' => 'Invalid',
                        'contact_id'         => $this->_contactID,  
                        'membership_type_id' => $this->_membershipTypeID,
                        'join_date'          => '2011-01-21',
                        'start_date'         => '2010-01-21',
                        'end_date'           => '2008-12-21',
                        'source'             => 'Payment',
                        'is_override'        => 1,
                        'status_id'          => $this->_membershipStatusID,
                        'version'				=> $this->_apiversion,                       
                        );

        $result = civicrm_api3_membership_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
        
        //membership_contact_id which is no in contact table
        $params['membership_contact_id'] = 999;
        $result = civicrm_api3_membership_create( $params );
        $this->assertEquals( $result['is_error'], 1 );

        //invalid join date
        unset( $params['membership_contact_id'] );
        $params['join_date'] = "invalid";
        $result = civicrm_api3_membership_create( $params );
        $this->assertEquals( $result['is_error'], 1 );
    }
    
    /**
     * Test civicrm_contact_memberships_create with membership_contact_id
     * membership).
     * Success expected.
     */
    function testMembershipCreateWithMemContact( ) 
    {
            
        $params = array(
                        'membership_contact_id' => $this->_contactID,
                        'contact_id'            => $this->_contactID,  
                        'membership_type_id'    => $this->_membershipTypeID,
                        'join_date'             => '2011-01-21',
                        'start_date'            => '2010-01-21',
                        'end_date'              => '2008-12-21',
                        'source'                => 'Payment',
                        'is_override'           => 1,
                        'status_id'             => $this->_membershipStatusID   ,                    
                        'version'				=> $this->_apiversion,                        );

        $result = civicrm_api3_membership_create( $params );

        $this->assertEquals( $result['is_error'], 0 );
        
    }

///////////////// civicrm_membership_delete methods

    /**
     * Test civicrm_contact_memberships_delete with params with wrong type.
     * Error expected.
     */
    function testDeleteWithParamsString()
    {
        $params = 'a string';
        $result = & civicrm_api3_membership_create( $params );
        $this->assertEquals( $result['is_error'], 1,
                             "In line " . __LINE__ );
    }
    




 ///////////////// _civicrm_membership_format_params with $create 
 
    function testMemebershipFormatParamsWithCreate( ) 
    {

        $params = array(
                        'contact_id'            => $this->_contactID,  
                        'membership_type_id'    => $this->_membershipTypeID,
                        'join_date'             => '2006-01-21',
                        'membership_start_date' => '2006-01-21',
                        'membership_end_date'   => '2006-12-21',
                        'source'                => 'Payment',
                        'is_override'           => 1,
                        'status_id'             => $this->_membershipStatusID ,
                        'version'				=> $this->_apiversion,                      
                        );

        $values = array( );
        _civicrm_api3_membership_format_params( $params , $values, true);
        
        $this->assertEquals( $values['start_date'], $params['membership_start_date'] );
        $this->assertEquals( $values['end_date'], $params['membership_end_date'] );
    }

}

     
