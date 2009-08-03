<?php

require_once 'api/v2/CustomGroup.php';

class api_v2_TestCustomFieldDelete extends CiviUnitTestCase 
{
    function get_info( )
    {
        return array(
                     'name'        => 'Custom Field Delete',
                     'description' => 'Test all Custom Field Delete API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp() 
    {
    }
    
    function tearDown() 
    {
    }
   
    function testCustomFieldDeleteWithoutFieldID( )
    {
        $params = array( ); 
        $customField =& civicrm_custom_field_delete($params); 
        $this->assertEquals($customField['is_error'], 1);
        $this->assertEquals($customField['error_message'], 'Invalid or no value for Custom Field ID');
    }    
    
    function testCustomFieldDelete( )
    {
        $customGroup = $this->customGroupCreate('Individual','test_group');
        $customField = $this->customFieldCreate($customGroup['id'],'test_name'); 
        $this->assertNotNull($customField['result']['customFieldId']);
        $customField =& civicrm_custom_field_delete( $customField );
        $this->assertEquals($customField['is_error'], 0);
        $this->customGroupDelete($customGroup['id']);
    } 
    
    function testCustomFieldOptionValueDelete( )
    {
        $customGroup = $this->customGroupCreate('Contact','ABC' );  
        $customOptionValueFields = $this->customFieldOptionValueCreate($customGroup,'fieldABC' );
        $customField =& civicrm_custom_field_delete($customOptionValueFields);
        $this->assertEquals($customField['is_error'], 0);
        $this->customGroupDelete($customGroup['id']); 
    } 
    
}


