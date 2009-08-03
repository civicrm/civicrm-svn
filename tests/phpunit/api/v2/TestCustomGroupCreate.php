<?php

/**
 *  Include class definitions
 */
require_once 'api/v2/CustomGroup.php';

/**
 *  Test APIv2 civicrm_create_custom_group
 *
 *  @package   CiviCRM
 */
class api_v2_TestCustomGroupCreate extends CiviUnitTestCase
{
    
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
    }
    
    function tearDown() 
    {
    }
     
    function testCustomGroupCreateNoParam()
    {
        $params = array( );
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['is_error'], 1); 
        $this->assertEquals($customGroup['error_message'],'Params must include either \'class_name\' (string) or \'extends\' (array).');
    }
    
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
                         'is_active'        => 1
                         );
        
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['is_error'],0);
        $this->assertNotNull($customGroup['id']);
        $this->customGroupDelete($customGroup['id']);
    } 
    
    function testCustomGroupCreateNoTitle()
    {
        $params = array('extends'          => array('Contact'),
                        'weight'           => 5, 
                        'collapse_display' => 1,
                        'style'            => 'Tab',
                        'help_pre'         => 'This is Pre Help For Test Group 2',
                        'help_post'        => 'This is Post Help For Test Group 2'
                        );
        
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['error_message'],'Title parameter is required.');
        $this->assertEquals($customGroup['is_error'],1);
	} 
    
    function testCustomGroupCreateHouseholdNoWeight()
    { 
        $params = array('title'            => 'Test_Group_3',
                        'name'             => 'test_group_3',
                        'extends'          => array('Household'),
                        'collapse_display' => 1,
                        'style'            => 'Tab',
                        'help_pre'         => 'This is Pre Help For Test Group 3',
                        'help_post'        => 'This is Post Help For Test Group 3',
                        'is_active'        => 1
                        );
        
        $customGroup =& civicrm_custom_group_create($params);
        $this->assertEquals($customGroup['is_error'],0);
        $this->assertNotNull($customGroup['id']);
        $this->customGroupDelete($customGroup['id']);
    }
    
    function testCustomGroupCreateContributionDonation()
    {
        $params = array('title'            => 'Test_Group_6',
                        'name'             => 'test_group_6',
                        'extends'          => array( 'Contribution', 1 ),
                        'weight'           => 6,
                        'collapse_display' => 1,
                        'style'            => 'Inline',
                        'help_pre'         => 'This is Pre Help For Test Group 6',
                        'help_post'        => 'This is Post Help For Test Group 6',
                        'is_active'        => 1 
                        );
        
        $customGroup =& civicrm_custom_group_create($params); 
        $this->assertEquals($customGroup['is_error'], 0);
        $this->assertNotNull($customGroup['id']);
        $this->customGroupDelete($customGroup['id']);
    }
    
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
        $this->customGroupDelete($customGroup['id']);
    }
    
    function testCustomGroupCreateActivityMeeting()
    {
        $params = array(
                        'title'            => 'Test_Group_10',
                        'name'             => 'test_group_10',
                        'extends'          => array('Activity', 1),
                        'weight'           => 8,
                        'collapse_display' => 1,
                        'style'            => 'Inline',
                        'help_pre'         => 'This is Pre Help For Test Group 10',
                        'help_post'        => 'This is Post Help For Test Group 10'
                        );
        
        $customGroup =& civicrm_custom_group_create($params); 
        $this->assertEquals($customGroup['is_error'], 0);
        $this->assertNotNull($customGroup['id']);
        $this->customGroupDelete($customGroup['id']);
    }
    
}

