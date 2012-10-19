<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_Extension_Manager_PaymentTest extends CiviUnitTestCase {
  function setUp() {
    parent::setUp();
    CRM_Core_DAO::executeQuery('DELETE FROM civicrm_extension WHERE full_name LIKE "test.%"');
  }

  function tearDown() {
    parent::tearDown();
  }

  /**
   * Install an extension with a valid type name
   */
  function testInstallDisableUninstall() {
    list ($container, $manager) = $this->_createContainerAndManager(
      $this->_files('test.install.disable.uninstall', 'test_install_disable_uninstall')
    );
    $this->assertDBQuery(0, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.install.disable.uninstall"');

    $manager->install('test.install.disable.uninstall');
    //FIXME: Hooks fail to run because CRM_Core_Payment::singleton depends on CRM_Extension_System::singleton -- which has diff ext metadata?
    //$this->assertEquals(1, test_install_disable_uninstall::$counts['install']);
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.install.disable.uninstall" AND is_active = 1');

    $manager->disable('test.install.disable.uninstall');
    //$this->assertEquals(1, test_install_disable_uninstall::$counts['disable']);
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.install.disable.uninstall"');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.install.disable.uninstall" AND is_active = 0');

    $manager->uninstall('test.install.disable.uninstall');
    //$this->assertEquals(1, test_install_disable_uninstall::$counts['uninstall']);
    $this->assertDBQuery(0, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.install.disable.uninstall"');
  }

  /**
   * Install an extension with a valid type name
   */
  function testInstallDisableEnable() {
    list ($container, $manager) = $this->_createContainerAndManager(
      $this->_files('test.install.disable.enable', 'test_install_disable_enable')
    );
    $this->assertDBQuery(0, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.install.disable.enable"');

    $manager->install('test.install.disable.enable');
    //FIXME: Hooks fail to run because CRM_Core_Payment::singleton depends on CRM_Extension_System::singleton -- which has diff ext metadata?
    //$this->assertEquals(1, test_install_disable_enable::$counts['install']);
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.install.disable.enable" AND is_active = 1');

    $manager->disable('test.install.disable.enable');
    // $this->assertEquals(1, test_install_disable_enable::$counts['disable']);
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.install.disable.enable"');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.install.disable.enable" AND is_active = 0');

    $manager->uninstall('test.install.disable.enable');
    // $this->assertEquals(1, test_install_disable_enable::$counts['enable']);
    $this->assertDBQuery(0, 'SELECT count(*) FROM civicrm_payment_processor_type WHERE class_name = "test.install.disable.enable"');
  }

  /**
   * Create some file content to describe a test payment extension
   */
  function _files($key, $class) {
    $mockExtension = $this->getMock("CRM_Core_Payment", array(), array(), "${class}_base");
    return array(
      "foobar/info.xml" => "
        <extension key='$key' type='payment'>
          <file>mainfile</file>
          <name>$key</name>
          <typeInfo>
            <userNameLabel>username</userNameLabel>
            <passwordLabel>password</passwordLabel>
            <signatureLabel></signatureLabel>
            <subjectLabel></subjectLabel>
            <urlSiteDefault>https://example.com/authorize</urlSiteDefault>
            <urlApiDefault></urlApiDefault>
            <urlRecurDefault></urlRecurDefault>
            <urlSiteTestDefault>https://example.com/authorize</urlSiteTestDefault>
            <urlApiTestDefault>/</urlApiTestDefault>
            <urlRecurTestDefault></urlRecurTestDefault>
            <urlButtonDefault></urlButtonDefault>
            <urlButtonTestDefault></urlButtonTestDefault>
            <billingMode>form</billingMode>
            <isRecur>0</isRecur>
            <paymentType>1</paymentType>
          </typeInfo>
        </extension>
      ",
      "foobar/mainfile.php" => "<?php
        class $class extends ${class}_base {
          static \$counts = array();
          function install() {
            \$counts['install'] = 1 + (int) self::\$counts['install'];
          }
          function uninstall() {
            \$counts['uninstall'] = 1 + (int) self::\$counts['uninstall'];
          }
          function disable() {
            \$counts['disable'] = 1 + (int) self::\$counts['disable'];
          }
          function enable() {
            \$counts['enable'] = 1 + (int) self::\$counts['enable'];
          }
        }
      ",
    );
  }

  function _createContainerAndManager($files) {
    list ($basedir, $c) = $this->_createContainer($files);
    $mapper = new CRM_Extension_Mapper($c);
    $typeManagers = array(
      'payment' => new CRM_Extension_Manager_Payment($mapper),
    );
    $manager = new CRM_Extension_Manager($mapper, $typeManagers);
    return array($c, $manager);
  }

  function _createContainer($files, CRM_Utils_Cache_Interface $cache = NULL, $cacheKey = NULL) {
    $basedir = $this->createTempDir('ext-');
    foreach ($files as $file => $content) {
      $parentPath = $basedir . '/' . dirname($file);
      if (!is_dir($parentPath)) {
        mkdir($parentPath);
      }
      $filePath = $basedir . '/' . $file;
      file_put_contents($filePath, $content);
    }
    $c = new CRM_Extension_Container_Basic($basedir, 'http://example/basedir', $cache, $cacheKey);
    return array($basedir, $c);
  }
}
