<?php

require_once 'api/v2/Membership.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v2_MembershipStatusDeleteTest extends CiviUnitTestCase {
    function get_info( )
    {
        return array(
                     'name'        => 'MembershipStatus Delete',
                     'description' => 'Test all MembershipStatus Delete API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp( ) {
        parent::setUp();
    }

    function testMembershipStatusDeleteEmpty( ) {
        $params = array( );
        $result = civicrm_membership_status_delete( $params );
        $this->assertEquals( $result['is_error'], 1 );
    }

    function testMembershipStatusDeleteMissingRequired( ) {
        $params = array( 'title' => 'Does not make sense' );
        $result = civicrm_membership_status_delete( $params );
        $this->assertEquals( $result['is_error'], 1 );
    }

    function testMembershipStatusDelete( ) {
        $membershipID = $this->membershipStatusCreate( );
        $params = array( 'id' => $membershipID );
        $result = civicrm_membership_status_delete( $params );
        $this->assertEquals( $result['is_error'], 0 );
    }

    function tearDown( ) {
    }

}

