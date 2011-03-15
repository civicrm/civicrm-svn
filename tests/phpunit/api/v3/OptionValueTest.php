<?php
require_once 'api/v3/OptionValue.php';
require_once 'CiviTest/CiviUnitTestCase.php';

class api_v3_OptionValueTest extends CiviUnitTestCase 
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

    public function testGetOptionValueByID () {
        $result = civicrm_api3_option_value_get(array('id'=> 1, 'version' => 3));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['id'], 'In line ' . __LINE__ );
    }

    public function testGetOptionValueByValue () {
        $result = civicrm_api3_option_value_get(array('option_group_id'=> 1, 'value' => '1', 'version' => 3));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['id'], 'In line ' . __LINE__ );
    }

    public function testGetOptionGroup () {
        $params = array('option_group_id'=> 1, 'version' => 3);
        $result = civicrm_api3_option_value_get($params);
        $this->documentMe($params,$result ,__FUNCTION__,__FILE__);
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertGreaterThan( 1, $result['count'], 'In line ' . __LINE__ );
    }

    public function testGetOptionDoesNotExist () {
        $result = civicrm_api3_option_value_get(array('label'=> 'FSIGUBSFGOMUUBSFGMOOUUBSFGMOOBUFSGMOOIIB','version' => 3));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 0, $result['count'], 'In line ' . __LINE__ );
    }
}

