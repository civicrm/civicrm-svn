<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_Extension_Manager_PaymentTest extends CiviUnitTestCase {
  function setUp() {
    parent::setUp();
    if (class_exists('test_extension_manager_paymenttest')) {
      test_extension_manager_paymenttest::$counts = array();
    }
    $this->system = new CRM_Extension_System(array(
      'extensionsDir' => '',
      'extensionsURL' => '',
    ));
  }

  function tearDown() {
    parent::tearDown();
    $this->system = NULL;
  }

  /**
   * Install an extension with a valid type name
   */
  function testInstallDisableUninstall() {
    $manager = $this->system->getManager();
    $this->assertDBQuery(0, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.extension.manager.paymenttest"');

    $manager->install(array('test.extension.manager.paymenttest'));
    $this->assertEquals(1, test_extension_manager_paymenttest::$counts['install']);
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.extension.manager.paymenttest" AND is_active = 1');

    $manager->disable(array('test.extension.manager.paymenttest'));
    $this->assertEquals(1, test_extension_manager_paymenttest::$counts['disable']);
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.extension.manager.paymenttest"');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.extension.manager.paymenttest" AND is_active = 0');

    $manager->uninstall(array('test.extension.manager.paymenttest'));
    $this->assertEquals(1, test_extension_manager_paymenttest::$counts['uninstall']);
    $this->assertDBQuery(0, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.extension.manager.paymenttest"');
  }

  /**
   * Install an extension with a valid type name
   */
  function testInstallDisableEnable() {
    $manager = $this->system->getManager();
    $this->assertDBQuery(0, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.extension.manager.paymenttest"');

    $manager->install(array('test.extension.manager.paymenttest'));
    $this->assertEquals(1, test_extension_manager_paymenttest::$counts['install']);
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.extension.manager.paymenttest" AND is_active = 1');

    $manager->disable(array('test.extension.manager.paymenttest'));
    $this->assertEquals(1, test_extension_manager_paymenttest::$counts['disable']);
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.extension.manager.paymenttest"');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.extension.manager.paymenttest" AND is_active = 0');

    $manager->enable(array('test.extension.manager.paymenttest'));
    $this->assertEquals(1, test_extension_manager_paymenttest::$counts['enable']);
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.extension.manager.paymenttest"');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.extension.manager.paymenttest" AND is_active = 1');
  }
}
