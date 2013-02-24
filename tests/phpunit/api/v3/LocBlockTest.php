<?php
// $Id$

require_once 'CiviTest/CiviUnitTestCase.php';
class api_v3_LocBlockTest extends CiviUnitTestCase {
  protected $_apiversion = 3;
  protected $_entity = 'loc_block';
  public $_eNoticeCompliant = TRUE;
  public function setUp() {
    parent::setUp();
  }

  function tearDown() {
  }

  public function testCreateLocBlock() {
    $email = civicrm_api('email', 'create', array(
      'version' => $this->_apiversion,
      'contact_id' => 'null',
      'location_type_id' => 1,
      'email' => 'test@loc.block',
    ));
    $phone = civicrm_api('phone', 'create', array(
      'version' => $this->_apiversion,
      'contact_id' => 'null',
      'location_type_id' => 1,
      'phone' => '1234567',
    ));
    $address = civicrm_api('address', 'create', array(
      'version' => $this->_apiversion,
      'contact_id' => 'null',
      'location_type_id' => 1,
      'street_address' => '1234567',
    ));
    $params = array(
      'version' => $this->_apiversion,
      'address_id' => $address['id'],
      'phone_id' => $phone['id'],
      'email_id' => $email['id'],
    );
    $result = civicrm_api($this->_entity, 'create', $params);print_r($result);
    $id = $result['id'];
    $this->documentMe($params, $result, __FUNCTION__, __FILE__);
    $this->assertAPISuccess($result, 'In line ' . __LINE__);
    $this->assertEquals(1, $result['count'], 'In line ' . __LINE__);
    $this->assertNotNull($result['values'][$id]['id'], 'In line ' . __LINE__);
    $this->getAndCheck($params, $id, $this->_entity);
    // Delete block
    $result = civicrm_api($this->_entity, 'create', array(
      'version' => $this->_apiversion,
      'id' => $id,
    ));
    $this->assertAPISuccess($result, 'In line ' . __LINE__);
  }

  public static function setUpBeforeClass() {
      // put stuff here that should happen before all tests in this unit
  }

  public static function tearDownAfterClass(){
  }
}

