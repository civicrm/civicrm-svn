<?php

require_once 'api/v2/Note.php';

class api_v2_TestNoteCreate extends CiviUnitTestCase 
{
    protected $_contactID;
    protected $_params;

    function get_info( )
    {
        return array(
                     'name'        => 'Note Create',
                     'description' => 'Test all Note Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp() 
    {
        $this->_contactID = $this->organizationCreate( );
        
        $this->_params = array(
                               'entity_table'  => 'civicrm_contact',
                               'entity_id'     => $this->_contactID,
                               'note'          => 'Hello!!! m testing Note',
                               'contact_id'    => $this->_contactID,
                               'modified_date' => date('Ymd'),
                               'subject'       => 'Test Note', 
                               );
    }
    
    function testCreateNoteParamsNotArray( )
    {
        $params = null;
        $result = civicrm_note_create( $params );
        
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertNotEquals( $result['error_message'], 'Required parameter missing' );
        $this->assertEquals( $result['error_message'], 'Params is not an array' );
    }    
    
    function testCreateNoteEmptyParams( )
    {
        $params = array( );
        $result = civicrm_note_create( $params );
        
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertFalse( array_key_exists( 'entity_id', $result ) );
        $this->assertEquals( $result['error_message'], 'Required parameter missing' );
    }
    
    function testCreateNoteParamsWithoutEntityId( )
    {
        unset($this->_params['entity_id']);
        $result = civicrm_note_create( $this->_params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Required parameter missing' );
    }
    
    function testCreateNote( )
    {
        $result = civicrm_note_create( $this->_params );
        $this->assertEquals( $result['note'], 'Hello!!! m testing Note');
        $this->assertTrue( array_key_exists( 'entity_id', $result ) );
        $this->assertEquals( $result['is_error'], 0 );
        civicrm_note_delete( $result );
    }
    
    function tearDown( ) 
    {
        
        $this->contactDelete( $this->_contactID );
    }
}


