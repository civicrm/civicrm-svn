<?php

require_once 'api/v2/CustomGroup.php';

class api_v2_TestCustomFieldCreate extends CiviUnitTestCase 
{

    function get_info( )
    {
        return array(
                     'name'        => 'Custom Field Create',
                     'description' => 'Test aof Custom Field Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp() 
    {
    }
    
    function tearDown() 
    {
    }
    
    function testCustomFieldCreateNoParam()
    {
        $params = array();
        $customField =& civicrm_custom_field_create($params);
        $this->assertEquals($customField['is_error'], 1);
        $this->assertEquals( $customField['error_message'],'Missing Required field :custom_group_id' );
    }
    
    function testCustomFieldCreateWithoutGroupID( )
    {
        $fieldParams = array('name'           => 'test_textfield1',
                             'label'          => 'Name',
                             'html_type'      => 'Text',
                             'data_type'      => 'String',
                             'default_value'  => 'abc',
                             'weight'         => 4,
                             'is_required'    => 1,
                             'is_searchable'  => 0,
                             'is_active'      => 1
                             );
               
        $customField =& civicrm_custom_field_create($fieldParams);     
        $this->assertEquals($customField['is_error'], 1);
        $this->assertEquals( $customField['error_message'],'Missing Required field :custom_group_id' );
    }    
     
    function testCustomTextFieldCreate( )
    {
        $customGroup = $this->customGroupCreate('Individual','text_test_group');
        $params = array('custom_group_id' => $customGroup['id'],
                        'name'            => 'test_textfield2',
                        'label'           => 'Name1',
                        'html_type'       => 'Text',
                        'data_type'       => 'String',
                        'default_value'   => 'abc',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
        
        $customField =& civicrm_custom_field_create($params);
        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['result']['customFieldId']);
        $this->customFieldDelete($customField['result']['customFieldId']); 
        $this->customGroupDelete($customGroup['id']); 
    } 

    function testCustomDateFieldCreate( )
    {
        $customGroup = $this->customGroupCreate('Individual','date_test_group');
        $params = array('custom_group_id' => $customGroup['id'],
                        'name'            => 'test_date',
                        'label'           => 'test_date',
                        'html_type'       => 'Select Date',
                        'data_type'       => 'Date',
                        'default_value'   => '20071212',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
        $customField =& civicrm_custom_field_create($params); 
        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['result']['customFieldId']);
        $this->customFieldDelete($customField['result']['customFieldId']);
        $this->customGroupDelete($customGroup['id']); 
    } 
    
    function testCustomCountryFieldCreate( )
    {
        $customGroup = $this->customGroupCreate('Individual','Country_test_group');
        $params = array('custom_group_id' => $customGroup['id'],
                        'name'            => 'test_country',
                        'label'           => 'test_country',
                        'html_type'       => 'Select Country',
                        'data_type'       => 'Country',
                        'default_value'   => '1228',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
               
        $customField =& civicrm_custom_field_create($params);  
        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['result']['customFieldId']);
        $this->customFieldDelete($customField['result']['customFieldId']);
        $this->customGroupDelete($customGroup['id']); 
    }
    
    function testCustomNoteFieldCreate( )
    {
        $customGroup = $this->customGroupCreate('Individual','Country2_test_group');
        $params = array('custom_group_id' => $customGroup['id'],
                        'name'            => 'test_note',
                        'label'           => 'test_note',
                        'html_type'       => 'TextArea',
                        'data_type'       => 'Memo',
                        'default_value'   => 'Hello',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
        
        $customField =& civicrm_custom_field_create($params);  
        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['result']['customFieldId']);
        $this->customFieldDelete($customField['result']['customFieldId']);
        $this->customGroupDelete($customGroup['id']); 
    } 
    
    function testCustomFieldOptionValueCreate( )
    {
          $this->fail( 'Needs fixing!' );
        $customGroup = $this->customGroupCreate('Contact', 'select_test_group');
        $params = array ('custom_group_id' => 1,
                         'label'           => 'Country',
                         'html_type'       => 'Select',
                         'data_type'       => 'String',
                         'weight'          => 4,
                         'is_required'     => 1,
                         'is_searchable'   => 0,
                         'is_active'       => 1,
                         'option_label'    => array( 'Label1','Label2'),
                         'option_value'    => array( 'val1', 'val2' ),
                         'option_weight'   => array( 1, 2),
                         'option_status'   => array( 1, 1),
                         );
        $this->fail( 'Needs fixing!' );      
//        $customField =& civicrm_custom_field_create($params);  
       
        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['result']['customFieldId']);
        $this->customFieldDelete($customField['result']['customFieldId']);
        $this->customGroupDelete($customGroup['id']); 
    } 
    
    function testCustomFieldSelectOptionValueCreate( )
    {
        $customGroup = $this->customGroupCreate('Contact', 'select_test_group');
        $params = array ('custom_group_id' => 1,
                         'label'           => 'PriceSelect',
                         'html_type'       => 'Select',
                         'data_type'       => 'Int',
                         'weight'          => 4,
                         'is_required'     => 1,
                         'is_searchable'   => 0,
                         'is_active'       => 1,
                         'option_label'    => array( 'Label1','Label2'),
                         'option_value'    => array( '10', '20' ),
                         'option_weight'   => array( 1, 2),
                         'option_status'   => array( 1, 1),
                         );
        $this->fail( 'Needs fixing!' );                         
//        $customField =& civicrm_custom_field_create($params);    

        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['result']['customFieldId']);
        $this->customFieldDelete($customField['result']['customFieldId']);
        $this->customGroupDelete($customGroup['id']); 
    }
    
    function testCustomFieldCheckBoxOptionValueCreate( )
    { 
        $customGroup = $this->customGroupCreate('Contact','CheckBox_test_group');
        $params = array ('custom_group_id' => $customGroup['id'],
                         'label'           => 'PriceChk',
                         'html_type'       => 'CheckBox',
                         'data_type'       => 'String',
                         'weight'          => 4,
                         'is_required'     => 1,
                         'is_searchable'   => 0,
                         'is_active'       => 1,
                         'option_label'    => array( 'Label1','Label2'),
                         'option_value'    => array( '10', '20' ),
                         'option_weight'   => array( 1, 2),
                         'option_status'   => array( 1, 1),
                         'default_checkbox_option' => array(1)
                         );
        
        $customField =& civicrm_custom_field_create($params); 

        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['result']['customFieldId']);
        $this->customFieldDelete($customField['result']['customFieldId']);
        $this->customGroupDelete($customGroup['id']); 
    }   
    
    function testCustomFieldRadioOptionValueCreate( )
    {
        $customGroup = $this->customGroupCreate('Contact', 'Radio_test_group');
        $params = array ('custom_group_id' => $customGroup['id'],
                         'label'           => 'PriceRadio',
                         'html_type'       => 'Radio',
                         'data_type'       => 'String',
                         'weight'          => 4,
                         'is_required'     => 1,
                         'is_searchable'   => 0,
                         'is_active'       => 1,
                         'option_label'    => array( 'radioLabel1','radioLabel2'),
                         'option_value'    => array( 10, 20 ),
                         'option_weight'   => array( 1, 2),
                         'option_status'   => array( 1, 1),
                         );
        
        $customField =& civicrm_custom_field_create($params); 

        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['result']['customFieldId']);
        $this->customFieldDelete($customField['result']['customFieldId']);
        $this->customGroupDelete($customGroup['id']); 
    } 
    
    function testCustomFieldMultiSelectOptionValueCreate( )
    {
        $customGroup = $this->customGroupCreate('Contact', 'MultiSelect_test_group');
        $params = array ('custom_group_id' => $customGroup['id'],
                         'label'           => 'PriceMufdlti',
                         'html_type'       => 'Multi-Select',
                         'data_type'       => 'String',
                         'weight'          => 4,
                         'is_required'     => 1,
                         'is_searchable'   => 0,
                         'is_active'       => 1,
                         'option_label'    => array( 'MultiLabel1','MultiLabel2'),
                         'option_value'    => array( 10, 20 ),
                         'option_weight'   => array( 1, 2),
                         'option_status'   => array( 1, 1),
                         );
              
        $customField =& civicrm_custom_field_create($params);    

        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['result']['customFieldId']);
        $this->customFieldDelete($customField['result']['customFieldId']);
        $this->customGroupDelete($customGroup['id']); 
    }     
    
}

 
