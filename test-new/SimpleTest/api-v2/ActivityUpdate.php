<?php

require_once 'api/v2/Activity.php';

/**
 * Class contains api test cases for "civicrm_activity_update"
 *
 */
class TestOfActivityUpdateAPIV2 extends CiviUnitTestCase 
{
    protected $_individualSourceId;
    protected $_individualTargetId;
    protected $_activityId;

    function setUp() 
    {
        $activity = $this->activityCreate( );

        $this->_activityId         = $activity['id'];
        $this->_individualSourceId = $activity['source_contact_id'];
    }
    
    /**
     * check with empty array
     */
    function testActivityUpdateEmpty( )
    {
        $params = array( );
        $result =& civicrm_activity_update($params);
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check if required fields are not passed
     */
    function testActivityUpdateWithoutRequired( )
    {
        $params = array(
                        'subject'             => 'this case should fail',
                        'scheduled_date_time' => date('Ymd')
                        );
        
        $result =& civicrm_activity_update($params);
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check with incorrect required fields
     */
    function testActivityUpdateWithIncorrectData( )
    {
        $params = array(
                        'activity_name'       => 'Meeting',
                        'subject'             => 'this case should fail',
                        'scheduled_date_time' => date('Ymd')
                        );

        $result =& civicrm_activity_update($params);
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check with incorrect required fields
     */
    function testActivityUpdateWithIncorrectId( )
    {
        $params = array( 'id'                  => 'lets break it',
                         'activity_name'       => 'Meeting',
                         'subject'             => 'this case should fail',
                         'scheduled_date_time' => date('Ymd')
                         );

        $result =& civicrm_activity_update($params);
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check with incorrect required fields
     */
    function testActivityUpdateWithIncorrectContactActivityType( )
    {
        $params = array(
                        'id'                  => $this->_activityId,
                        'activity_name'       => 'Test Activity',
                        'subject'             => 'this case should fail',
                        'scheduled_date_time' => date('Ymd')
                        );

        $result =& civicrm_activity_update($params);
        $this->assertEqual( $result['source_contact_id'], null );
    }
    
    /**
     * this should create activity
     */
    function testActivityUpdate( )
    {
        $params = array(
                        'id'                  => $this->_activityId,
                        'subject'             => 'Update Discussion on Apis for v2',
                        'activity_date_time'  => date('Ymd'),
                        'duration_hours'      => 15,
                        'duration_minutes'    => 20,
                        'location'            => '21, Park Avenue',
                        'details'             => 'Lets update Meeting',
                        'status_id'           => 1,
                        'activity_name'       => 'Meeting',
                        );

        $result =& civicrm_activity_update( $params );
        $this->assertEqual( $result['is_error'], 0 );
        $this->assertEqual( $result['id'] , $this->_activityId );
        $this->assertEqual( $result['activity_date_time'], date('Ymd') );
        $this->assertEqual( $result['subject'], 'Update Discussion on Apis for v2' );
        $this->assertEqual( $result['location'], '21, Park Avenue'); 
        $this->assertEqual( $result['details'], 'Lets update Meeting');
        $this->assertEqual( $result['status_id'], 1 );
        
    }
    
    /**
     * check activity update with status
     */
    function testActivityUpdateWithStatus( )
    {
        $params = array(
                        'id'                  => $this->_activityId,
                        'source_contact_id'   => $this->_individualSourceId,
                        'subject'             => 'Hurry update works', 
                        'status_id'           => 2,
                        'activity_name'       => 'Meeting',
                        );

        $result =& civicrm_activity_update( $params );
        $this->assertEqual( $result['is_error'], 0 );
        $this->assertEqual( $result['id'] , $this->_activityId );
        $this->assertEqual( $result['source_contact_id'] , $this->_individualSourceId );
        $this->assertEqual( $result['subject'], 'Hurry update works' );
        $this->assertEqual( $result['status_id'], 2 );
    }
    
    /**
     * create activity with custom data 
     * ( fix this once custom * v2 api are ready  )
     */
    function atestActivityUpdateWithCustomData( )
    {
        
    }
    
    function tearDown() 
    {
      $this->contactDelete( $this->_individualSourceId );
    }
}
 
?> 