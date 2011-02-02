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

require_once 'api/v3/Tag.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_TagTest extends CiviUnitTestCase 
{
    protected $_apiversion;

    function setUp() 
    {
        $this->_apiversion = 3;
        parent::setUp();
    }

    function tearDown() 
    {  
    }

///////////////// civicrm_tag_get methods

    /**
     * Test civicrm_tag_get with wrong params type.
     */
    public function testGetWrongParamsType()
    {
        $params ='is_string';
        $result = civicrm_tag_get($params);
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 'Input parameters is not an array', $result['error_message'], 'In line ' . __LINE__ );
    }

    /**
     * Test civicrm_tag_get with empty params.
     */
    public function testGetEmptyParams()
    {
        $params = array( );
        $result = civicrm_tag_get( $params );
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 'Mandatory param missing - one required of: id, name', $result['error_message'], 'In line ' . __LINE__ );
    }

    /** 
     * Test civicrm_tag_get with wrong params.
     */
    public function testGetWrongParams()
    {
        $params = array( 'name' => 'Wrong Tag Name' , 'version' => $this->_apiversion );
        $result = civicrm_tag_get($params);
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 'Exact match not found.', $result['error_message'], 'In line ' . __LINE__ );
    }
    
    /**
     * Test civicrm_tag_get - success expected.
     */
    public function testGet()
    {   
        $tag = $this->tagCreate(null,$this->_apiversion);
        $this->assertEquals( 0, $tag['is_error'], 'In line ' . __LINE__ );
        
        $params = array( 'id' => $tag['id'],
                         'name' => $tag['name'],
                         'version' => $this->_apiversion  );
        $result = civicrm_tag_get($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( $tag['description'], $result['description'], 'In line ' . __LINE__ );
        $this->assertEquals( $tag['name'], $result['name'], 'In line ' . __LINE__ ); 
    }  
    

///////////////// civicrm_tag_create methods

    /**
      * Test civicrm_tag_create with wrong params type.
      */    
    function testCreateWrongParamsType()
    {
        $params = 'a string';
        $result = civicrm_tag_create( $params );
        $this->assertEquals( 1, $result['is_error'], "In line " . __LINE__ );
        $this->assertEquals( 'Input variable `params` is not an array', $result['error_message'], 'In line ' . __LINE__ );
    }

    /**
     * Test civicrm_tag_create with empty params.
     */    
    function testCreateEmptyParams()
    {
        $params = array( );
        $result = civicrm_tag_create( $params ); 
        $this->assertEquals( 1, $result['is_error'], "In line " . __LINE__ );
        $this->assertEquals( 'Mandatory key(s) missing from params array: name, version', $result['error_message'], 'In line ' . __LINE__ );
    }  

    /**
     * Test civicrm_tag_create
     */    
    function testCreatePasstagInParams()
    {
        $params = array( 'tag'         => 10,
                         'name'        => 'New Tag23',
                         'description' => 'This is description for New Tag 02' );
        $result = civicrm_tag_create($params);
        $this->assertEquals( 10, $result['tag_id'], 'In line ' . __LINE__ );
       
    }  

    /**
     * Test civicrm_tag_create - success expected.
     */    
    function testCreate()
    {
        $params = array( 'name'        => 'New Tag3',
                         'description' => 'This is description for New Tag 02' ,
                          'version'    => $this->_apiversion);
        
        $result = civicrm_tag_create($params); 
                $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__);
        $this->assertNotNull( $result['tag_id'], 'In line ' . __LINE__ );
    }

///////////////// civicrm_tag_delete methods

    /**
     * Test civicrm_tag_delete with wrong parameters type.
     */     
    function testDeleteWrongParamsType()
    {
        $tag = 'is string';
        $result = civicrm_tag_delete( $tag );
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__  );
        $this->assertEquals( 'Input variable `params` is not an array', $result['error_message'], 'In line ' . __LINE__ );
    }

    /**
     * Test civicrm_tag_delete with empty parameters.
     */
    function testDeleteEmptyParams()
    {
        $tag = array( );
        $result = civicrm_tag_delete( $tag );
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 'Mandatory key(s) missing from params array: tag_id, version', $result['error_message'], 'In line ' . __LINE__ );
    }

    /**
     * Test civicrm_tag_delete without tag id.
     */
    function testDeleteWithoutTagId()
    {
        $tag = array( 'version' => 3 );
        
        $result = civicrm_tag_delete($tag); 
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ ); 
        $this->assertEquals( 'Mandatory key(s) missing from params array: tag_id', $result['error_message'], 'In line ' . __LINE__ );            
    }

    /**
     * Test civicrm_tag_delete with wrong tag id type.
     */
    function testDeleteWrongParams()
    {
        $params = array( 'tag_id' => 'incorrect value');
        $result = civicrm_tag_delete( $tag );
        $this->assertEquals( 1, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 'Input variable `params` is not an array', $result['error_message'], 'In line ' . __LINE__ );
    }  

    /**
     * Test civicrm_tag_delete with wrong tag id type.
     */  
    function testTagDelete( )
    {
        $tagID = $this->tagCreate(null,$this->_apiversion); 
        $params = array('tag_id'=> $tagID,
                        'version' => $this->_apiversion);
        $result = civicrm_tag_delete($params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);  
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
    }
    

}

