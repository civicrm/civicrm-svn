<?php

require_once 'api/v2/Activity.php';

/**
 * Class contains api test cases for "civicrm_activity_delete"
 *
 */
class TestOfActivityDeleteAPIV2 extends CiviUnitTestCase 
{

    protected $_individualSourceID;
    protected $_individualTargetID;
    protected $_activityId;
    
    function setUp( ) 
    {
       $activity = $this->activityCreate( );
       
       $this->_activityId         = $activity['id'];
       $this->_individualSourceID = $activity['source_contact_id'];
       $this->_individualTargetID = $activity['target_entity_id'];
    }

    /**
     * check activity deletion with empty params
     */
    function testDeleteActivityForEmptyParams( )
    {
        $params = array( );
        $result =& civicrm_activity_delete($params);
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check activity deletion without activity id
     */
    function testDeleteActivityWithoutId( )
    {
        $params = array('activity_name' => 'Meeting');
        $result =& civicrm_activity_delete($params);
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check activity deletion without activity type
     */
    function testDeleteActivityWithoutActivityType( )
    {
        $params = array( 'id' => $this->_activityId );
        $result =& civicrm_activity_delete( $params );
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check activity deletion with incorrect data
     */
    function testDeleteActivityWithoutIncorrectActivityType( )
    {
        $params = array( 'id' => $this->_activityId,
                         'activity_name' => 'Phone Call'
                         );

        $result =& civicrm_activity_delete( $params );
        $this->assertEqual( $result['is_error'], 1 );
    }

    /**
     * check activity deletion with correct data
     */
    function testDeleteActivity( )
    {
        $params = array( 'id'            => $this->_activityId,
                         'activity_name' => 'Meeting'
                         );
        
        $result =& civicrm_activity_delete($params);
        $this->assertEqual( $result['is_error'], 0 );
    }

    function tearDown( ) 
    {
      $this->contactDelete( $this->_individualSourceID );
      $this->contactDelete( $this->_individualTargetID );
    }
}
?>