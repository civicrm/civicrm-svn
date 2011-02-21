<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'api/v3/Note.php';
require_once 'tests/phpunit/CiviTest/CiviUnitTestCase.php';

/**
 * Class contains api test cases for "civicrm_note"
 *
 */

class api_v3_NoteTest extends CiviUnitTestCase 
{
  
    protected $_apiversion;
    protected $_contactID;
    protected $_params;
    protected $_noteID;

    function __construct( ) {
        parent::__construct( );
    }

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
       
        $this->_apiversion = 3;
        //  Connect to the database
        parent::setUp();

        $this->_contactID = $this->organizationCreate(null, $this->_apiversion );

        $this->_params = array(
                               'entity_table'  => 'civicrm_contact',
                               'entity_id'     => $this->_contactID,
                               'note'          => 'Hello!!! m testing Note',
                               'contact_id'    => $this->_contactID,
                               'modified_date' => date('Ymd'),
                               'subject'       => 'Test Note', 
                               'version'			 =>$this->_apiversion, 
                               );
        $this->_note      = $this->noteCreate( $this->_contactID, $this->_apiversion );
        $this->_noteID    = $this->_note['id'];
    }

    function tearDown( ) 
    {
    }

///////////////// civicrm_note_get methods

    /**
     * check retrieve note with wrong params type
     * Error Expected
     */
    function testGetWithWrongParamsType( )
    {
        $params = 'a string';
        $result =& civicrm_note_get( $params );
        $this->assertEquals( $result['is_error'], 1, 
                             "In line " . __LINE__ );
    } 

    /**
     * check retrieve note with empty parameter array
     * Error expected
     */
    function testGetWithEmptyParams( )
    {
        $params = array( );
        $note   =& civicrm_note_get( $params );
        $this->assertEquals( $note['is_error'], 1 );
        $this->assertEquals( $note['error_message'], 'Mandatory key(s) missing from params array: entity_id, version' );
    } 

    /**
     * check retrieve note with missing patrameters
     * Error expected
     */
    function testGetWithoutEntityId( )
    {   
        $params = array( 'entity_table' => 'civicrm_contact',
                         'version'			=>3 );
        $note   =& civicrm_note_get( $params );
        $this->assertEquals( $note['is_error'], 1 ); 
        $this->assertEquals( $note['error_message'], 'Mandatory key(s) missing from params array: entity_id' );
    }

    /**
     * check civicrm_note_get
     */
    function testGet( )
    { 
        $entityId = $this->_noteID;
        $params   = array(
                          'entity_table'  => 'civicrm_contact',
                          'entity_id'     => $entityId,
                          'version'			 =>$this->_apiversion,
                          ); 
        $result = civicrm_note_get($params);
        $this->documentMe( $this->_params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['is_error'], 0,'in line ' . __LINE__ );
    }


///////////////// civicrm_note_create methods
    
    /**
     * Check create with wrong parameter
     * Error expected
     */
    function testCreateWithWrongParamsType( )
    {
        $params = 'a string';
        $result =& civicrm_note_create( $params );
        $this->assertEquals( $result['is_error'], 1, 
                             "In line " . __LINE__ );
        $this->assertEquals( $result['error_message'], 'Input variable `params` is not an array' );                             
    } 
    
    /**
     * Check create with empty parameter array
     * Error Expected
     */    
    function testCreateWithEmptyParams( )
    {
        $params = array( );
        $result = civicrm_note_create( $params );     
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: entity_id, note, version' );
    }

    /**
     * Check create with partial params
     * Error expected
     */    
    function testCreateWithoutEntityId( )
    {
        unset($this->_params['entity_id']);
        $result = civicrm_note_create( $this->_params );
        $this->assertEquals( $result['is_error'], 1 );
        $this->assertEquals( $result['error_message'], 'Mandatory key(s) missing from params array: entity_id' );
    }

    /**
     * Check civicrm_note_create
     */
    function testCreate( )
    {
 
        $result = civicrm_note_create( $this->_params );
        $this->documentMe( $this->_params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( $result['values'][$result['id']]['note'], 'Hello!!! m testing Note','in line ' . __LINE__);
        $this->assertArrayHasKey( 'id', $result,'in line ' . __LINE__ ); 
        $this->assertEquals( $result['is_error'], 0,'in line ' . __LINE__ );
        $note = array('id' => $result['id'],
                      'version' => $this->_apiversion );
        $this->noteDelete( $note,$this->_apiversion );
    }

///////////////// civicrm_note_update methods


    /**
     * Check update note with wrong params type
     * Error expected
     */
    function testUpdateWithWrongParamsType( )
    {
        $params = 'a string';
        $result =& civicrm_note_create( $params );
        $this->assertEquals( $result['is_error'], 1, 
                             "In line " . __LINE__ );
    } 

    /**
     * Check update with empty parameter array
     * Error expected
     */
    function testUpdateWithEmptyParams( )
    {
        $params = array();        
        $note   =& civicrm_note_create( $params );
        $this->assertEquals( $note['is_error'], 1 );
        $this->assertEquals( $note['error_message'], 'Mandatory key(s) missing from params array: entity_id, note, version' );
    }

    /**
     * Check update with missing parameter (contact id)
     * Error expected
     */
    function testUpdateWithoutContactId( )
    {
        $params = array(
                        'entity_id'    => $this->_contactID,
                        'entity_table' => 'civicrm_contact',
                        'version'			 => $this->_apiversion,                
                        );        
        $note   =& civicrm_note_create( $params );
        $this->assertEquals( $note['is_error'], 1 );
        $this->assertEquals( $note['error_message'], 'Mandatory key(s) missing from params array: note' );
    }

    /**
     * Check civicrm_note_update
     */    
    function testUpdate( )
    {
       $params = array(
                        'id'           => $this->_noteID,
                        'contact_id'   => $this->_contactID,
                        'entity_id'    => $this->_contactID,
                        'entity_table' => 'civicrm_contribution',
                        'note'         => 'Note1',
                        'subject'      => 'Hello World',
                        'version'			=> $this->_apiversion,
                        );
        
        //Update Note
        $note =& civicrm_note_create( $params );
        $this->assertEquals( $note['id'],$this->_noteID,'in line ' . __LINE__ );
        $this->assertEquals( $note['is_error'],0,'in line ' . __LINE__ );       
        $this->assertEquals( $note['values'][$this->_contactID]['entity_id'],$this->_contactID,'in line ' . __LINE__ );
        $this->assertEquals( $note['values'][$this->_contactID]['entity_table'],'civicrm_contribution','in line ' . __LINE__ );
    }

///////////////// civicrm_note_delete methods

    /**
     * Check delete note with wrong params type
     * Error expected
     */
    function testDeleteWithWrongParamsType( )
    {
        $params = 'a string';
        $result =& civicrm_note_delete( $params );
        $this->assertEquals( $result['is_error'], 1, 
                             "In line " . __LINE__ );
    } 

    /**
     * Check delete with empty parametes array
     * Error expected
     */
    function testDeleteWithEmptyParams( )
    {
        $params     = array();        
        $deleteNote = & civicrm_note_delete( $params );           
        $this->assertEquals( $deleteNote['is_error'], 1 );
        $this->assertEquals( $deleteNote['error_message'], 'Mandatory key(s) missing from params array: id, version');
    }

    /**
     * Check delete with wrong id
     * Error expected
     */    
    function testDeleteWithWrongID( )
    {
        $params     = array( 'id' => 0,
                             'version' => $this->_apiversion, );        
        $deleteNote = & civicrm_note_delete( $params ); 
        $this->assertEquals( $deleteNote['is_error'], 1 );
        $this->assertEquals( $deleteNote['error_message'], 'Error while deleting Note');
    }

    /**
     * Check civicrm_note_delete
     */        
    function testDelete( )
    {
        $params = array( 'id'        => $this->_noteID,
                         'version'   => $this->_apiversion,
                         ); 
       
        $result  =& civicrm_note_delete( $params );  
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);        
        $this->assertEquals( $result['is_error'], 0,'in line ' . __LINE__ );
    }
    

}

     /**
     *  Test civicrm_activity_create() using example code
     */
    function testNoteCreateExample( )
    {
      require_once 'api/v3/examples/NoteCreate.php';
      $result = UF_match_get_example();
      $expectedResult = UF_match_get_expectedresult();
      $this->assertEquals($result,$expectedResult);
    }
