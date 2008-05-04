<?php

require_once 'api/v2/Activity.php';

/**
 * Class contains api test cases for "civicrm_activities_get_contact"
 *
 */
class TestOfActivityGetContactAPIV2 extends CiviUnitTestCase 
{

    protected $_individualSourceID;
    protected $_activityId;
    
    function setUp( ) 
    {
       $activity = $this->activityCreate( );
       
       $this->_activityId         = $activity['id'];
       $this->_individualSourceId = $activity['source_contact_id'];
       $this->_individualTargetId = $activity['target_contact_id'];
    }

    /**
     * check contact activity with empty params
     */
    function testGetContactForEmptyParams( )
    {
        $params = array( );
        $result =& civicrm_activities_get_contact( $params );
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check contact activity with incorrect required data
     */
    function testGetContactWithIncorrectData( )
    {
        $params = array('contact_id' => 'lets crash the system');
        $result =& civicrm_activities_get_contact( $params );
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check contact activity with invalid data
     */
    function testGetContactWithInvalidData( )
    {
        $params = array('contact_id' => 99999999 );
        $result =& civicrm_activities_get_contact( $params );
        $this->assertEqual( $result['is_error'], 1 );
    }
    
    /**
     * check contact activity with correct data
     */
    function testGetContactCorrectData( )
    {
        $params = array('contact_id' => $this->_individualTargetId  );
        $result =& civicrm_activities_get_contact( $params );
        $this->assertEqual( $result['is_error'], 0 );
    }
    
    function tearDown( ) 
    {
      $this->contactDelete( $this->_individualSourceId );
      $this->contactDelete( $this->_individualTargetId );
    }
}
