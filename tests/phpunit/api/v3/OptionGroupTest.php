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
        $result = civicrm_api('option_group','get',array('id'=> 1, 'version' => 3));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['id'], 'In line ' . __LINE__ );
    }

    public function testGetOptionGroupByName () {
        $params = array('name'=> 'preferred_communication_method', 'version' => 3);
        $result = civicrm_api('option_group','get',$params);
        $this->documentMe($params,$result ,__FUNCTION__,__FILE__);
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['count'], 'In line ' . __LINE__ );
        $this->assertEquals( 1, $result['id'], 'In line ' . __LINE__ );
    }

    public function testGetOptionGroup () {
        $result = civicrm_api('option_group','get',array('version' => 3));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertGreaterThan( 1, $result['count'], 'In line ' . __LINE__ );
    }

    public function testGetOptionDoesNotExist () {
        $result = civicrm_api('option_group','get',array('name'=> 'FSIGUBSFGOMUUBSFGMOOUUBSFGMOOBUFSGMOOIIB','version' => 3));
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 0, $result['count'], 'In line ' . __LINE__ );
    }
    public function testGetOptionCreateSuccess () {
        $params =  array('version' => $this->_apiversion, 'sequential' => 1,'name' => 'civicrm_event.amount.560' ,'is_reserved' => 1, 'is_active' => 1, 'api.OptionValue.create' => array('label' => 'workshop', 'value'=> 35, 'is_default' => 1, 'is_active' => 1,'format.only_id' => 1));
        $result = civicrm_api('OptionGroup','create',$params);
        $this->documentMe($params,$result ,__FUNCTION__,__FILE__);
        $this->assertEquals( 0, $result['is_error'], 'In line ' . __LINE__ );
        $this->assertEquals( 'civicrm_event.amount.560', $result['values'][0]['name'], 'In line ' . __LINE__ );
        $this->assertTrue(is_integer($result['values'][0]['api.OptionValue.create']));
        $this->assertGreaterThan(0,$result['values'][0]['api.OptionValue.create']);

  
        
    }   

}

