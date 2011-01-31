<?php

require_once 'api/v3/Group.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_GroupTest extends CiviUnitTestCase 
{
    protected $_apiversion;
    protected $_groupID;
    
    
    function get_info( )
    {
        return array(
                     'name'        => 'Group Get',
                     'description' => 'Test all Group Get API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp() 
    {
        $this->_apiversion = 3;

        parent::setUp();
        $this->_groupID = $this->groupCreate(null,3);
    }
    
    function tearDown() 
    {
 
        $this-> groupDelete( $this->_groupID ,$this->_apiversion);
    }
    
    function testgroupCreateEmptyParams( )
    {
        $params = array();
        $group = & civicrm_group_create( $params );
        $this->assertEquals( $group['error_message'] , 'Mandatory key(s) missing from params array: title, version' );        
    }
    
    function testgroupAddNoTitle( )
    {
        $params = array(
                        'name'        => 'Test Group No title ',
                        'domain_id'   => 1,
                        'description' => 'New Test Group Created',
                        'is_active'   => 1,
                        'visibility'  => 'Public Pages',
                        'group_type'  => array( '1' => 1,
                                                '2' => 1 ) 
                        );
        
        $group = & civicrm_group_create( $params );
        $this->assertEquals( $group['error_message'] , 'Mandatory key(s) missing from params array: title, version' );        
    }
    
    
    function testGetGroupEmptyParams( )
    {
        $params = '';
        $group = civicrm_group_get( $params );
        
        $this->assertEquals( $group['error_message'], 'Input variable `params` is not an array' );        
    }
    
    function testGetGroupWithEmptyParams( ) 
    {
        $params = array('version' => $this->_apiversion );
        
        $group = civicrm_group_get( $params );
        
        $this->assertNotNull( count( $group ) );
        $this->assertEquals( $group[$this->_groupID]['name'], 'Test Group 1' );
        $this->assertEquals( $group[$this->_groupID]['is_active'], 1 );
        $this->assertEquals( $group[$this->_groupID]['visibility'], 'Public Pages' );
    }
    
    
    function testGetGroupParamsWithGroupId( ) 
    {
        $params = array('version' => $this->_apiversion );
        $params['id'] = $this->_groupID;
        $group =&civicrm_group_get( $params );
        
        foreach( $group as $v){
            $this->assertEquals( $v['name'],'Test Group 1' );
            $this->assertEquals( $v['title'],'New Test Group Created' );
            $this->assertEquals( $v['description'], 'New Test Group Created');
            $this->assertEquals( $v['is_active'], 1 );
            $this->assertEquals( $v['visibility'], 'Public Pages' );
        }
    }
    
    function testGetGroupParamsWithGroupName( ) 
    {    
        $params         = array('version' => $this->_apiversion  );
        $params['name'] = 'Test Group 1'; 
        $group =&civicrm_group_get( $params );
        $this->documentMe($params,$group,__FUNCTION__,__FILE__);         
        foreach( $group as $v){
            $this->assertEquals( $v['id'], $this->_groupID );
            $this->assertEquals( $v['title'],'New Test Group Created' );
            $this->assertEquals( $v['description'], 'New Test Group Created');
            $this->assertEquals( $v['is_active'], 1 );
            $this->assertEquals( $v['visibility'], 'Public Pages' );
        }
    }
    
    function testGetGroupParamsWithReturnName( ) 
    {    
        $params         = array( 'version' => $this->_apiversion );
        $params['id'] = $this->_groupID; 
        $params['return.name'] = 1;
        $group =&civicrm_group_get( $params );
        $this->assertEquals( $group[$this->_groupID]['name'],'Test Group 1' );
    }
    
    function testGetGroupParamsWithGroupTitle( ) 
    {  
        $params          = array('version' => $this->_apiversion  );
        $params['title'] = 'New Test Group Created'; 
        $group =&civicrm_group_get( $params );
        
        foreach( $group as $v){
            $this->assertEquals( $v['id'], $this->_groupID );
            $this->assertEquals( $v['name'],'Test Group 1' );
            $this->assertEquals( $v['description'], 'New Test Group Created' );
            $this->assertEquals( $v['is_active'], 1 );
            $this->assertEquals( $v['visibility'], 'Public Pages' );
        }
    }
    
    function testGetNonExistingGroup( )
    {
        $params          = array('version' => $this->_apiversion  );
        $params['title'] = 'No such group Exist'; 
        $group =&civicrm_group_get( $params );
        $this->assertEquals( $group['error_message'] , 'No such group exists' );        
    }
    
    function testgroupdeleteNonArrayParams( )
    {
        $params = 'TestNotArray';
        $group = & civicrm_group_delete( $params );
        $this->assertEquals( $group['error_message'] , 'Input variable `params` is not an array' );        
    }
    
    function testgroupdeleteParamsnoId( )
    {
        $params = array();
        $group = & civicrm_group_delete( $params );
        $this->assertEquals( $group['error_message'] , 'Mandatory key(s) missing from params array: id, version' );        
    }    
}


