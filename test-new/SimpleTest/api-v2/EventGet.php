<?php

require_once 'api/v2/Event.php';

class TestOfEventGetAPIV2 extends CiviUnitTestCase 
{
    private $_event;
    
    function setUp( )
    {
        $this->_event = $this->eventCreate( );
    }
    
    function testGetEventEmptyParams( )
    {
        $params = array( );
        
        $result = civicrm_event_get( $params );
        
        $this->assertEqual( $result['is_error'], 1 );
        $this->assertEqual( $result['error_message'], 'Params is not an array' );
    }
    
    function testGetEventById( )
    {
        $params = array( 'id' => $this->_event['event_id'] );
        
        $result = civicrm_event_get( $params );
        
        $this->assertNotEqual( $result['is_error'], 1 );
        $this->assertEqual( $result['event_title'], 'Annual CiviCRM meet' );
    }
    
    function testGetEventByEventTitle( )
    {
        $params = array( 'title' => 'Annual CiviCRM meet' );
        
        $result = civicrm_event_get( $params );
        
        $this->assertNotEqual( $result['is_error'], 1 );
        $this->assertEqual( $result['id'], $this->_event['event_id'] );
    }
    
    function tearDown( )
    {
        $this->eventDelete( $this->_event['event_id'] );
    }
}
