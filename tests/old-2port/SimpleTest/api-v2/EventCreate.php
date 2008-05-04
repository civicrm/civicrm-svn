<?php

require_once 'api/v2/Event.php';

class TestOfEventCreateAPIV2 extends CiviUnitTestCase 
{
    protected $_params;
    
    function setUp() 
    {
        $this->_params = array(
            'title'                   => 'Annual CiviCRM meet',
            'summary'                 => 'If you have any CiviCRM realted issues or want to track where CiviCRM is heading, Sign up now',
            'description'             => 'This event is intended to give brief idea about progess of CiviCRM and giving solutions to common user issues',
            'event_type_id'           => 1,
            'is_public'               => 1,
            'start_date'              => 20081021,
            'end_date'                => 20081023,
            'is_online_registration'  => 1,
            'registration_start_date' => 20080601,
            'registration_end_date'   => 20081015,
            'max_participants'        => 100,
            'event_full_text'         => 'Sorry! We are already full',
            'is_monetory'             => 0, 
            'is_active'               => 1,
            'is_show_location'        => 0,
        );
    }
    
    function testCreateEventParamsNotArray( )
    {
        $params = null;
        $result = civicrm_event_create( $params );
        $this->assertEqual( $result['is_error'], 1 );
        $this->assertNotEqual( $result['error_message'], 'Missing require fields ( title, event type id,start date)');
        $this->assertEqual( $result['error_message'], 'Params is not an array');
    }    
    
    function testCreateEventEmptyParams( )
    {
        $params = array( );
        $result = civicrm_event_create( $params );
        $this->assertEqual( $result['is_error'], 1 );
        $this->assertFalse( array_key_exists( 'event_id', $result ) );
        $this->assertEqual( $result['error_message'], 'Missing require fields ( title, event type id,start date)');
    }
    
    function testCreateEventParamsWithoutTitle( )
    {
        unset($this->_params['title']);
        $result = civicrm_event_create( $this->_params );
        $this->assertEqual( $result['is_error'], 1 );
        $this->assertEqual( $result['error_message'], 'Missing require fields ( title, event type id,start date)');
    }
    
    function testCreateEventParamsWithoutEventTypeId( )
    {
        unset($this->_params['event_type_id']);
        $result = civicrm_event_create( $this->_params );
        $this->assertEqual( $result['is_error'], 1 );
        $this->assertEqual( $result['error_message'], 'Missing require fields ( title, event type id,start date)');
    }
    
    function testCreateEventParamsWithoutStartDate( )
    {
        unset($this->_params['start_date']);
        $result = civicrm_event_create( $this->_params );
        $this->assertEqual( $result['is_error'], 1 );
        $this->assertEqual( $result['error_message'], 'Missing require fields ( title, event type id,start date)');
    }
    
    function testCreateEvent( )
    {
        $result = civicrm_event_create( $this->_params );
        
        $this->assertNotEqual( $result['is_error'], 1 );
        $this->assertTrue( array_key_exists( 'event_id', $result ) );
        
        civicrm_event_delete( $result );
    }
    
    function tearDown() 
    {
        
    }
}

