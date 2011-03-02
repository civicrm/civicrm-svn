<?php

/**
 *  Include class definitions
 */
require_once 'tests/phpunit/CiviTest/CiviUnitTestCase.php';
//require_once 'api/v3/CustomGroup.php';
require_once 'api/v3/CustomField.php';
/**
 *  Test APIv3 civicrm_create_custom_group
 *
 *  @package   CiviCRM
 */
class api_v3_CustomFieldTest extends CiviUnitTestCase
{
     protected $_apiversion;
    
    function get_info( )
    {
        return array(
                     'name'        => 'Custom Field Create',
                     'description' => 'Test all Custom Field Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp() 
    {
        $this->_apiversion = 3;
        parent::setUp();
    }
    
    function tearDown() 
    {
    }


   
    /**
     * check with no array
     */  
    function testCustomFieldCreateNoArray( )
    {
        $fieldParams = null;
               
        $customField =& civicrm_api3_custom_field_create($fieldParams);
        $this->assertEquals($customField['is_error'], 1);
        $this->assertEquals( $customField['error_message'],'Input variable `params` is not an array' );
    }    

    /**
     * check with no label
     */ 
    function testCustomFieldCreateWithoutLabel( )
    {
        $customGroup = $this->customGroupCreate('Individual','text_test_group',3);
        $params = array('custom_group_id' => $customGroup['id'],
                        'name'            => 'test_textfield2',
                        'html_type'       => 'Text',
                        'data_type'       => 'String',
                        'default_value'   => 'abc',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1
                        );
        
        $customField =& civicrm_api3_custom_field_create($params);
        $this->assertEquals($customField['is_error'],1);
        $this->assertEquals( $customField['error_message'],'Mandatory key(s) missing from params array: label, version' );

    } 

    /**
     * check with edit
     */ 
    function testCustomFieldCreateWithEdit( )
    {
        $customGroup = $this->customGroupCreate('Individual','text_test_group',3);
        $params = array('custom_group_id' => $customGroup['id'],
                        'name'            => 'test_textfield2',
                        'label'           => 'Name1',
                        'html_type'       => 'Text',
                        'data_type'       => 'String',
                        'default_value'   => 'abc',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1,
                        'version'					=>$this->_apiversion,
                        );
        
        $customField =& civicrm_api3_custom_field_create($params);
        $params['id'] = $customField['id'];
        $customField =& civicrm_api3_custom_field_create($params);

        $this->assertEquals($customField['is_error'],0,'in line ' . __LINE__);
        $this->assertNotNull($customField['id'],'in line ' . __LINE__);
        $this->customFieldDelete($customField['id'],$this->_apiversion); 
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 

    } 

    /**
     * check without groupId
     */  
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
               
        $customField =& civicrm_api3_custom_field_create($fieldParams);
        $this->assertEquals($customField['is_error'], 1);
        $this->assertEquals( $customField['error_message'],'Mandatory key(s) missing from params array: custom_group_id, version' );
    }    

    /**
     * check with data type - Text array
     */ 
    function testCustomTextFieldCreate( )
    {
        $customGroup = $this->customGroupCreate('Individual','text_test_group',3);
        $params = array('custom_group_id' => $customGroup['id'],
                        'name'            => 'test_textfield2',
                        'label'           => 'Name1',
                        'html_type'       => 'Text',
                        'data_type'       => 'String',
                        'default_value'   => 'abc',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1, 
                         'version'					=> $this->_apiversion,
                        );
        
        $customField =& civicrm_api3_custom_field_create($params);
        $this->assertEquals($customField['is_error'],0,'in line' . __LINE__);
        $this->assertNotNull($customField['id'],'in line' . __LINE__);
        $this->customFieldDelete($customField['id'],$this->_apiversion); 
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 
    } 
    
    /**
     * check with data type - Date array
     */
    function testCustomDateFieldCreate( )
    {
        $customGroup = $this->customGroupCreate('Individual','date_test_group',3);
        $params = array('custom_group_id' => $customGroup['id'],
                        'name'            => 'test_date',
                        'label'           => 'test_date',
                        'html_type'       => 'Select Date',
                        'data_type'       => 'Date',
                        'default_value'   => '20071212',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1,
                        'version'					=> $this->_apiversion,
                        );
        $result =& civicrm_api3_custom_field_create($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);        
        $this->assertEquals($result['is_error'],0);
        $this->assertNotNull($result['id']);
        $this->customFieldDelete($result['id'],$this->_apiversion);
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 

    } 
            /**
     *  Test  using example code
     */
    function testCustomFieldCreateExample( )
    {
     $customGroup = $this->customGroupCreate('Individual','date_test_group',3);
     require_once 'api/v3/examples/CustomFieldCreate.php';
     $result = custom_field_create_example();
     $expectedResult = custom_field_create_expectedresult();
     $this->assertEquals($result,$expectedResult);
    }
    /**
     * check with data type - Country array
     */
    function testCustomCountryFieldCreate( )
    {
        $customGroup = $this->customGroupCreate('Individual','Country_test_group',3);
        $params = array('custom_group_id' => $customGroup['id'],
                        'name'            => 'test_country',
                        'label'           => 'test_country',
                        'html_type'       => 'Select Country',
                        'data_type'       => 'Country',
                        'default_value'   => '1228',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1,
                         'version'					=> $this->_apiversion,
                        );
               
        $customField =& civicrm_api3_custom_field_create($params);
        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['id']);
        $this->customFieldDelete($customField['id'],$this->_apiversion);
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 
    }
    
    /**
     * check with data type - Note array
     */
    function testCustomNoteFieldCreate( )
    {
        $customGroup = $this->customGroupCreate('Individual','Country2_test_group',3);
        $params = array('custom_group_id' => $customGroup['id'],
                        'name'            => 'test_note',
                        'label'           => 'test_note',
                        'html_type'       => 'TextArea',
                        'data_type'       => 'Memo',
                        'default_value'   => 'Hello',
                        'weight'          => 4,
                        'is_required'     => 1,
                        'is_searchable'   => 0,
                        'is_active'       => 1,
                         'version'					=> $this->_apiversion,
                        );
        
        $customField =& civicrm_api3_custom_field_create($params);
        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['id']);
        $this->customFieldDelete($customField['id'],$this->_apiversion);
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 
    } 
    
    /**
     * check with data type - Options array
     */
    function testCustomFieldOptionValueCreate( )
    {
        $customGroup = $this->customGroupCreate('Contact', 'select_test_group',3);
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
                         'version'				 =>$this->_apiversion,
                         );

        $customField =& civicrm_api3_custom_field_create($params);

        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['id']);
        $this->customFieldDelete($customField['id'],$this->_apiversion);
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 
    } 
     
    /**
     * check with data type - Options with option_values
     */
    function testCustomFieldCreateWithOptionValues()
    {
        $customGroup = $this->customGroupCreate('Contact', 'select_test_group',3);

        $option_values = array( array( 'weight'    => 1,
                                       'label'     => 'Label1',
                                       'value'     => 1,
                                       'is_active' => 1 ),
        
                                
                                array( 'weight'    => 2,
                                       'label'     => 'Label2',
                                       'value'     => 2,
                                       'is_active' => 1 ),
                                );

        $params = array ('custom_group_id' => 1,
                         'label'           => 'Country',
                         'html_type'       => 'Select',
                         'data_type'       => 'String',
                         'weight'          => 4,
                         'is_required'     => 1,
                         'is_searchable'   => 0,
                         'is_active'       => 1,
                         'option_values'   => $option_values,
                         'version'				 =>$this->_apiversion,
                         );

        $customField =& civicrm_api3_custom_field_create($params);

        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['id']);
        $this->customFieldDelete($customField['id'],$this->_apiversion);
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 
    }
    /**
     * check with data type - Select Option array
     */
    function testCustomFieldSelectOptionValueCreate( )
    {
        $customGroup = $this->customGroupCreate('Contact', 'select_test_group',3);
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
                         'version'				 =>$this->_apiversion,
                         );
        $customField =& civicrm_api3_custom_field_create($params);    

        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['id']);
        $this->customFieldDelete($customField['id'],$this->_apiversion);
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 
    }
    
    /**
     * check with data type - Checkbox Options array
     */
    function testCustomFieldCheckBoxOptionValueCreate( )
    { 
        $customGroup = $this->customGroupCreate('Contact','CheckBox_test_group',3);
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
                         'default_checkbox_option' => array(1),
                         'version'				 =>$this->_apiversion,
                         );
        
        $customField =& civicrm_api3_custom_field_create($params); 

        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['id']);
        $this->customFieldDelete($customField['id'],$this->_apiversion);
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 
    }   

    /**
     * check with data type - Radio Options array
     */
    function testCustomFieldRadioOptionValueCreate( )
    {
        $customGroup = $this->customGroupCreate('Contact', 'Radio_test_group',3);
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
                         'version'				 =>$this->_apiversion,
                         );
        
        $customField =& civicrm_api3_custom_field_create($params); 

        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['id']);
        $this->customFieldDelete($customField['id'],$this->_apiversion);
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 
    } 
    
    /**
     * check with data type - Multi-Select Options array
     */
    function testCustomFieldMultiSelectOptionValueCreate( )
    {
        $customGroup = $this->customGroupCreate('Contact', 'MultiSelect_test_group',3);
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
                         'version'				 =>$this->_apiversion,
                         );
              
        $customField =& civicrm_api3_custom_field_create($params);    

        $this->assertEquals($customField['is_error'],0);
        $this->assertNotNull($customField['id']);
        $this->customFieldDelete($customField['id'],$this->_apiversion);
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 
    }     

///////////////// civicrm_custom_field_delete methods
    
    /**
     * check with no array
     */
    function testCustomFieldDeleteNoArray( )
    {
        $params = null; 
        $customField =& civicrm_api3_custom_field_delete($params); 
        $this->assertEquals($customField['is_error'], 1);
        $this->assertEquals($customField['error_message'], 'Input variable `params` is not an array');
    }    
    /**
     * check without Field ID
     */
    function testCustomFieldDeleteWithoutFieldID( )
    {
        $params = array( ); 
        $customField =& civicrm_api3_custom_field_delete($params); 
        $this->assertEquals($customField['is_error'], 1);
        $this->assertEquals($customField['error_message'], 'Mandatory key(s) missing from params array: id, version');
    }    

    /**
     * check without valid array
     */
    function testCustomFieldDelete( )
    {
        $customGroup = $this->customGroupCreate('Individual','test_group',$this->_apiversion);
        $customField = $this->customFieldCreate($customGroup['id'],'test_name', $this->_apiversion); 
        $this->assertNotNull($customField['id'],'in line ' .__LINE__);
        $params = array('version'					=> $this->_apiversion,
                         'id'							=> $customField );
        $result =& civicrm_api3_custom_field_delete( $params );
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);   

        $this->assertEquals($result['is_error'], 0,'in line ' .__LINE__);
        $this->customGroupDelete($customGroup['id'],$this->_apiversion);

    } 
      
    /**
     * check for Option Value
     */    
    function testCustomFieldOptionValueDelete( )
    {
        $customGroup = $this->customGroupCreate('Contact','ABC',$this->_apiversion );  
        $customOptionValueFields = $this->customFieldOptionValueCreate($customGroup,'fieldABC',$this->_apiversion );
        $customOptionValueFields['version'] = $this->_apiversion;
        $params = array('version'					=> $this->_apiversion,
                         'id'							=> $customOptionValueFields);
        
        $customField =& civicrm_api3_custom_field_delete($customOptionValueFields);
        $this->assertEquals($customField['is_error'], 0);
        $this->customGroupDelete($customGroup['id'],$this->_apiversion); 
    } 
 
}

