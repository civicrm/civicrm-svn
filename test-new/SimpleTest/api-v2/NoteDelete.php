<?php
require_once 'api/v2/Note.php';

class TestOfNoteDeleteAPIV2 extends CiviUnitTestCase 
{
    protected $_contactID;
    protected $_noteID;
    protected $_note = array( );
           
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
               
        $this->assertEqual( $deleteNote['is_error'], 1 );
        $this->assertEqual( $deleteNote['error_message'], 'Invalid or no value for Note ID');
    }
    
    function testNoteDeleteWithWrongID( )
    {
        $params     = array( 'id' => 0 );        
        $deleteNote = & civicrm_note_delete( $params ); 
       
        $this->assertEqual( $deleteNote['is_error'], 1 );
        $this->assertEqual( $deleteNote['error_message'], 'Invalid or no value for Note ID');
    }
    
    function testNoteDelete( )
    {
        $params = array( 'id'        => $this->_noteID,
                         'entity_id' => $this->_note['entity_id']
                         );
                        
        $deleteNote  =& civicrm_note_delete( $params );
             
        $this->assertEqual( $deleteNote['is_error'], 0 );
        $this->assertEqual( $deleteNote['result'], 1 );
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

