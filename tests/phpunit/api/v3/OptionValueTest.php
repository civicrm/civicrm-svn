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
        $result = civicrm_api3_option_value_get(array('id'=> 1, 'version' => $this->_apiversion));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['id'], 'In line ' . __LINE__ );
    }

    public function testGetOptionValueByValue () {
        $result = civicrm_api3_option_value_get(array('option_group_id'=> 1, 'value' => '1', 'version' => $this->_apiversion));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['id'], 'In line ' . __LINE__ );
    }

    public function testGetOptionGroup () {
        $params = array('option_group_id'=> 1, 'version' => $this->_apiversion);
        $result = civicrm_api3_option_value_get($params);
        $this->documentMe($params,$result ,__FUNCTION__,__FILE__);
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertGreaterThan( 1, $result['count'], 'In line ' . __LINE__ );
    }
    /*
     * test that using option_group_name returns more than 1 & less than all
     */
    public function testGetOptionGroupByName () {
        $activityTypesParams = array('option_group_name'=> 'activity_type', 'version' => $this->_apiversion);
        $params= array( 'version' => $this->_apiversion);
        $activityTypes = civicrm_api3_option_value_get($activityTypesParams);
        $result = civicrm_api3_option_value_get($params);
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertGreaterThan( 1, $activityTypes['count'], 'In line ' . __LINE__ );
        $this->assertGreaterThan( $activityTypes['count'], $result['count'], 'In line ' . __LINE__ );
    }
    public function testGetOptionDoesNotExist () {
        $result = civicrm_api3_option_value_get(array('label'=> 'FSIGUBSFGOMUUBSFGMOOUUBSFGMOOBUFSGMOOIIB','version' => $this->_apiversion));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 0, $result['count'], 'In line ' . __LINE__ );
    }
}

