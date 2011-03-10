<?php
require_once 'api/v3/OptionGroup.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_OptionGroupTest extends CiviUnitTestCase 
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

    public function testGetOptionGroupByID () {
        $result = civicrm_api3_option_group_get(array('id'=> 1, 'version' => 3));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['id'], 'In line ' . __LINE__ );
    }

    public function testGetOptionGroupByName () {
        $result = civicrm_api3_option_group_get(array('name'=> 'preferred_communication_method', 'version' => 3));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['id'], 'In line ' . __LINE__ );
    }

    public function testGetOptionGroup () {
        $result = civicrm_api3_option_group_get(array('version' => 3));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertGreaterThan( 1, $result['count'], 'In line ' . __LINE__ );
    }

    public function testGetOptionDoesNotExist () {
        $result = civicrm_api3_option_group_get(array('name'=> 'FSIGUBSFGOMUUBSFGMOOUUBSFGMOOBUFSGMOOIIB','version' => 3));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 0, $result['count'], 'In line ' . __LINE__ );
    }
}

