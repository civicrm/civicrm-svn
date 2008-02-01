<?php

require_once 'api/v2/Activity.php';

/**
 * Class contains api test cases for "civicrm_activity_create"
 *
 */
class TestOfActivityCreateAPIV2 extends CiviUnitTestCase 
{
    protected $_individualSourceId;
    protected $_individualTargetId;
    
    function setUp() 
    {
        $this->_individualSourceId = $this->individualCreate( );
        
        $contactParams = array( 'first_name'       => 'Julia',
                                'Last_name'        => 'Anderson',
                                'prefix'           => 'Ms',
                                'email'            => 'julia_anderson@civicrm.org',
                                'contact_type'     => 'Individual' );
    }
    
    /**
     * check with empty array
     */
    function testActivityCreateEmpty( )
    {
        $params = array( );
        $result = & civicrm_activity_create($params);
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check if required fields are not passed
     */
    function testActivityCreateWithoutRequired( )
    {
        $params = array(
                        'subject'             => 'this case should fail',
                        'scheduled_date_time' => date('Ymd')
                        );
        
        $result = & civicrm_activity_create($params);
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check with incorrect required fields
     */
    function testActivityCreateWithIncorrectData( )
    {
        $params = array(
                        'activity_name'       => 'Breaking Activity',
                        'subject'             => 'this case should fail',
                        'scheduled_date_time' => date('Ymd')
                        );

        $result = & civicrm_activity_create($params);
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check with incorrect required fields
     */
    function testActivityCreateWithIncorrectContactId( )
    {
        $params = array(
                        'activity_name'       => 'Meeting',
                        'subject'             => 'this case should fail',
                        'scheduled_date_time' => date('Ymd')
                        );

        $result = & civicrm_activity_create($params);
        
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * this should create activity
     */
    function testActivityCreate( )
    {
        $params = array(
                        'source_contact_id'   => $this->_individualSourceId,
                        'subject'             => 'Discussion on Apis for v2',
                        'activity_date_time'  => date('Ymd'),
                        'duration_hours'      => 30,
                        'duration_minutes'    => 20,
                        'location'            => 'Pensulvania',
                        'details'             => 'a phonecall activity',
                        'status_id'           => 1,
                        'activity_name'       => 'Phone Call'
                        );
        
        $result = & civicrm_activity_create( $params );
        
        $this->assertEqual( $result['is_error'], 0 );
        $this->assertEqual( $result['source_contact_id'], $this->_individualSourceId );
        $this->assertEqual( $result['subject'], 'Discussion on Apis for v2' );
        $this->assertEqual( $result['activity_date_time'], date('Ymd') );
        $this->assertEqual( $result['location'], 'Pensulvania' );
        $this->assertEqual( $result['details'], 'a phonecall activity' );
        $this->assertEqual( $result['status_id'], 1 );
        
    }

    /**
     * check other activity creation
     */
    function testOtherActivityCreate( )
    {
        $params = array(
                        'source_contact_id'   => $this->_individualSourceId,
                        'subject'             => 'let test other activities',
                        'activity_date_time'  => date('Ymd'),
                        'location'            => 'Pensulvania',
                        'details'             => 'other activity details',
                        'status_id'           => 1,
                        'activity_name'       => 'Interview',
                        );
 
        $result = & civicrm_activity_create( $params );
        $this->assertEqual( $result['is_error'], 0 );
        $this->assertEqual( $result['source_contact_id'], $this->_individualSourceId );
        $this->assertEqual( $result['subject'], 'let test other activities' );
        $this->assertEqual( $result['activity_date_time'], date('Ymd') );
        $this->assertEqual( $result['location'], 'Pensulvania' );
        $this->assertEqual( $result['details'], 'other activity details' );
        $this->assertEqual( $result['status_id'], 1 );
    }

    /**
     * create activity with custom data 
     * ( fix this once custom * v2 api are ready  )
     */
    function testActivityCreateWithCustomData( )
    {
        
    }
    
    function tearDown() 
    {
      $this->contactDelete( $this->_individualSourceId );
    }
}
 
?> 