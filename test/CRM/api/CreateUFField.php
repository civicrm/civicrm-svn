<?php

require_once 'api/crm.php';

class TestOfCreateUFFieldAPI extends UnitTestCase 
{
    protected $_UFGroup;
    
    function setUp() 
    {
    }
    
    function tearDown() 
    {
    }
    
    function testCreateUFGroup()
    {
        $params = array(
                        'title'     => 'New Profile Group F01',
                        'help_pre'  => 'Help For Profile Group F01',
                        'is_active' => 1
                        );
        $UFGroup = crm_create_uf_group($params);
        $this->assertIsA($UFGroup, 'CRM_Core_DAO_UFGroup');
        $this->_UFGroup = $UFGroup;
    }
    
    function testCreateUFFieldError()
    {
        $params = array();
        $UFField = crm_create_uf_field($this->_UFGroup, $params);
        $this->assertIsA($UFField , 'CRM_Core_Error');
    }
    
    function testCreateUFField()
    {
        $params = array(
                        'field_name' => 'first_name',
                        'visibility' => 'Public User Pages and Listings',
                        );
        $UFField = crm_create_uf_field($this->_UFGroup, $params);
        $this->_UFField =  $UFField;
        $this->assertIsA($UFField, 'CRM_Core_DAO_UFField');
    }

    function testDeleteUFField()
    {
        $UFField = crm_delete_uf_field($this->_UFField);
        $this->assertEqual($UFField,true);
    }
    
    function testDeleteUFGroup()
    {
        $UFGroup = crm_delete_uf_group($this->_UFGroup);
        $this->assertEqual($UFGroup,true);
    }
}
?>