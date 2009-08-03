<?php

require_once 'api/v2/CustomGroup.php';

class api_v2_TestCustomGroupDelete extends CiviUnitTestCase 
{
    function get_info( )
    {
        return array(
                     'name'        => 'Custom Group Delete',
                     'description' => 'Test all Custom Group Delete API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp( ) 
    {
    }
    
    function tearDown( ) 
    {
    }
   
    function testCustomGroupDeleteWithoutGroupID( )
    {
        $params = array( );
        $customGroup =& civicrm_custom_group_delete($params);
        $this->assertEquals($customGroup['is_error'], 1);
        $this->assertEquals($customGroup['error_message'],'Invalid or no value for Custom group ID');
    }    
    
    function testCustomGroupDelete( )
    {
        $customGroup = $this->customGroupCreate('Individual', 'test_group'); 
        $params = array('id' => $customGroup['id']);                         
        $customGroup =& civicrm_custom_group_delete($params);  
        $this->assertEquals($customGroup['is_error'], 0);
    } 
}

 
