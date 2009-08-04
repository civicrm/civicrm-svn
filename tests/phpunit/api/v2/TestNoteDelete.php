<?php
require_once 'api/v2/Note.php';

class api_v2_TestNoteDelete extends CiviUnitTestCase 
{
    protected $_contactID;
    protected $_noteID;
    protected $_note = array( );

    function get_info( )
    {
        return array(
                     'name'        => 'Note Delete',
                     'description' => 'Test all Note Delete API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }       
    
    function setUp( ) 
    {
        $this->_contactID = $this->organizationCreate( );
        $this->_note      = $this->noteCreate( $this->_contactID );
        $this->_noteID    = $this->_note['id'];
    }
    
    function testNoteDeleteWithEmptyParams( )
    {
        $params     = array();        
        $deleteNote = & civicrm_note_delete( $params );
               
        $this->assertEquals( $deleteNote['is_error'], 1 );
        $this->assertEquals( $deleteNote['error_message'], 'Invalid or no value for Note ID');
    }
    
    function testNoteDeleteWithWrongID( )
    {
        $params     = array( 'id' => 0 );        
        $deleteNote = & civicrm_note_delete( $params ); 
       
        $this->assertEquals( $deleteNote['is_error'], 1 );
        $this->assertEquals( $deleteNote['error_message'], 'Invalid or no value for Note ID');
    }
    
    function testNoteDelete( )
    {
        $params = array( 'id'        => $this->_noteID,
                         'entity_id' => $this->_note['entity_id']
                         );
                        
        $deleteNote  =& civicrm_note_delete( $params );
             
        $this->assertEquals( $deleteNote['is_error'], 0 );
        $this->assertEquals( $deleteNote['result'], 1 );
    }

    function tearDown( ) 
    { 
        if ( $this->_noteID ) {
            $params = array( 'id'        => $this->_noteID,
                             'entity_id' => $this->_note['entity_id']
                             );
            
            $deleteNote = civicrm_note_delete( $params );
        }
        $this->contactDelete( $this->_contactID );

    }
}

