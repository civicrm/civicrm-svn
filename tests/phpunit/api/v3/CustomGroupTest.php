<?php

/**
 *  Include class definitions
 */
require_once 'tests/phpunit/CiviTest/CiviUnitTestCase.php';
require_once 'api/v3/CustomGroup.php';

/**
 *  Test APIv3 civicrm_create_custom_group
 *
 *  @package   CiviCRM
 */
class api_v3_CustomGroupTest extends CiviUnitTestCase
{
    protected $_apiversion;
    protected $_entity;
        
    function get_info( )
    {
        return array(
                     'name'        => 'Custom Group Create',
                     'description' => 'Test all Custom Group Create API methods.',
                     'group'       => 'CiviCRM API Tests',
                     );
    }
    
    function setUp() 
    {   
        $this->_apiversion =3;
        $this->_entity = 'CustomGroup';
        parent::setUp();
    }
    
    function tearDown() 
    {
    }

///////////////// civicrm_custom_group_create methods   

    /**
     * check with empty array
     */    
    function testCustomGroupCreateNoParam()
    {
        $params = array( );
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['is_error'], 1); 
        $this->assertEquals($customGroup['error_message'],'Params must include either \'class_name\' (string) or \'extends\' (array).');
    }

    /**
     * check with empty array
     */
    function testCustomGroupCreateNoExtends()
    {
        $params = array( 'domain_id'        => 1,
                         'title'            => 'Test_Group_1',
                         'name'             => 'test_group_1',
                         'weight'           => 4,
                         'collapse_display' => 1,
                         'style'            => 'Tab',
                         'help_pre'         => 'This is Pre Help For Test Group 1',
                         'help_post'        => 'This is Post Help For Test Group 1',
                         'is_active'        => 1
                         );
        
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['error_message'],'Params must include either \'class_name\' (string) or \'extends\' (array).');
        $this->assertEquals($customGroup['is_error'],1);
    }

    /**
     * check with empty array
     */
    function testCustomGroupCreateInvalidExtends()
    {
        $params = array( 'domain_id'        => 1,
                         'title'            => 'Test_Group_1',
                         'name'             => 'test_group_1',
                         'weight'           => 4,
                         'collapse_display' => 1,
                         'style'            => 'Tab',
                         'help_pre'         => 'This is Pre Help For Test Group 1',
                         'help_post'        => 'This is Post Help For Test Group 1',
                         'is_active'        => 1,
                         'extends'          => array(),
                         );
        
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['error_message'], 'First item in params[\'extends\'] must be a class name (e.g. \'Contact\').');
        $this->assertEquals($customGroup['is_error'], 1);
    }

    
    /**
     * check with create fields
     */
    function testCustomGroupCreateWithFields()
    {
        $params = array( 'title'            => 'Test_Group_1',
                         'name'             => 'test_group_1',
                         'extends'          => array('Individual'),
                         'weight'           => 4,
                         'collapse_display' => 1,
                         'style'            => 'Inline',
                         'help_pre'         => 'This is Pre Help For Test Group 1',
                         'help_post'        => 'This is Post Help For Test Group 1',
                         'is_active'        => 1,
                         'html_type'       => 'Select',
                         'data_type'       => 'String',
                         'option_label'     => array('Label1', 'Label2'),
                         'option_value'     => array( 'value1', 'value2'),
                         'option_name'      => array( 'name_1', 'name_2'),
                         'option_weight'    => array(1, 2),
                         'label'            => 'Country'
                         );
        
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['is_error'], 0);
        $this->assertNotNull($customGroup['id']);
        $this->assertNotNull($customGroup['customFieldId']);
        $this->assertEquals($customGroup['extends'], 'Individual');
        $this->customGroupDelete($customGroup['id']);
    }

    /**
     * check with valid array
     */
    function testCustomGroupCreate()
    {
        $params = array( 'title'            => 'Test_Group_1',
                         'name'             => 'test_group_1',
                         'extends'          => array('Individual'),
                         'weight'           => 4,
                         'collapse_display' => 1,
                         'style'            => 'Inline',
                         'help_pre'         => 'This is Pre Help For Test Group 1',
                         'help_post'        => 'This is Post Help For Test Group 1',
                         'is_active'        => 1,
                         'version'					=>$this->_apiversion,
                         );
        
        $result =& civicrm_custom_group_create($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__);         $this->assertEquals($result['is_error'], 0);
        $this->assertNotNull($result['id']);
        $this->assertEquals($result['extends'], 'Individual');
        $this->customGroupDelete($customGroup['id']);

        unset( $params['style'] );
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['is_error'], 0);
        $this->assertNotNull($customGroup['id']);
        $this->assertEquals($customGroup['style'], 'Inline');
        $this->customGroupDelete($customGroup['id']);
    } 
    
    /**
     * check with not array
     */
    function testCustomGroupCreatNotArray()
    {
        $params = null;
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['is_error'], 1);
        $this->assertEquals($customGroup['error_message'],'Input variable `params` is not an array' );
    }

    /**
     * check without title
     */
    function testCustomGroupCreateNoTitle()
    {
        $params = array('extends'          => array('Contact'),
                        'weight'           => 5, 
                        'collapse_display' => 1,
                        'style'            => 'Tab',
                        'help_pre'         => 'This is Pre Help For Test Group 2',
                        'help_post'        => 'This is Post Help For Test Group 2',
                        'version'					 => $this->_apiversion,
                        );
        
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['error_message'],'Title parameter is required.');
        $this->assertEquals($customGroup['is_error'], 1);
	} 

    /**
     * check for household without weight
     */
    function testCustomGroupCreateHouseholdNoWeight()
    { 
        $params = array('title'            => 'Test_Group_3',
                        'name'             => 'test_group_3',
                        'extends'          => array('Household'),
                        'collapse_display' => 1,
                        'style'            => 'Tab',
                        'help_pre'         => 'This is Pre Help For Test Group 3',
                        'help_post'        => 'This is Post Help For Test Group 3',
                        'is_active'        => 1,
                        'version'					 => $this->_apiversion,
                        );
        
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['is_error'], 0);
        $this->assertNotNull($customGroup['id']);
        $this->assertEquals($customGroup['extends'], 'Household');
        $this->assertEquals($customGroup['style'], 'Tab');
        $this->customGroupDelete($customGroup['id']);
    }
    
    /**
     * check for Contribution Donation
     */
    function testCustomGroupCreateContributionDonation()
    {
        $params = array('title'            => 'Test_Group_6',
                        'name'             => 'test_group_6',
                        'extends'          => array( 'Contribution', array( 1 ) ),
                        'weight'           => 6,
                        'collapse_display' => 1,
                        'style'            => 'Inline',
                        'help_pre'         => 'This is Pre Help For Test Group 6',
                        'help_post'        => 'This is Post Help For Test Group 6',
                        'is_active'        => 1,
                        'version'					 => $this->_apiversion, 
                        );
        
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['is_error'], 0);
        $this->assertNotNull($customGroup['id']);
        $this->assertEquals($customGroup['extends'], 'Contribution');
        $this->customGroupDelete($customGroup['id']);
    }

    /**
     * check with valid array
     */ 
    function testCustomGroupCreateGroup()
    {
        $params = array('domain_id'        => 1,
                        'title'            => 'Test_Group_8',
                        'name'             => 'test_group_8',
                        'extends'          => array('Group'),
                        'weight'           => 7,
                        'collapse_display' => 1,
                        'is_active'        => 1,
                        'style'            => 'Inline',
                        'help_pre'         => 'This is Pre Help For Test Group 8',
                        'help_post'        => 'This is Post Help For Test Group 8'
                        );
        
        $customGroup =& civicrm_custom_group_create($params); 
        $this->assertEquals($customGroup['is_error'], 0);
        $this->assertNotNull($customGroup['id']);
        $this->assertEquals($customGroup['extends'], 'Group');
        $this->customGroupDelete($customGroup['id']);
    }
    
    /**
     * check with Activity - Meeting Type
     */
    function testCustomGroupCreateActivityMeeting()
    {
        $params = array(
                        'title'            => 'Test_Group_10',
                        'name'             => 'test_group_10',
                        'extends'          => array('Activity', array( 1 ) ),
                        'weight'           => 8,
                        'collapse_display' => 1,
                        'style'            => 'Inline',
                        'help_pre'         => 'This is Pre Help For Test Group 10',
                        'help_post'        => 'This is Post Help For Test Group 10'
                        );
        
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['is_error'], 0);
        $this->assertNotNull($customGroup['id']);
        $this->assertEquals($customGroup['extends'], 'Activity');
        $this->customGroupDelete($customGroup['id']);
    }

///////////////// civicrm_custom_group_delete methods   
    
    /**
     * check without GroupID
     */
    function testCustomGroupDeleteWithoutGroupID( )
    {
        $params = array( );
        $customGroup =& civicrm_custom_group_delete($params);
        $this->assertEquals($customGroup['is_error'], 1);
        $this->assertEquals($customGroup['error_message'],'Invalid or no value for Custom group ID');
    }    
   
    /**
     * check with no array
     */
    function testCustomGroupDeleteNoArray( )
    {
        $params = null;
        $customGroup =& civicrm_custom_group_delete($params);
        $this->assertEquals($customGroup['is_error'], 1);
        $this->assertEquals($customGroup['error_message'],'Input variable `params` is not an array' );
    }    
    /**
     * check with valid custom group id
     */    
    function testCustomGroupDelete( )
    {
        $customGroup = $this->customGroupCreate('Individual', 'test_group',3); 
        $params = array('id' => $customGroup['id']);                         
        $result =& civicrm_custom_group_delete($params);
        $this->documentMe($params,$result,__FUNCTION__,__FILE__); 
        $this->assertEquals($result ['is_error'], 0);
    } 
    
}