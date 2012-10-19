<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_Extension_ManagerTest extends CiviUnitTestCase {
  function setUp() {
    parent::setUp();
    list ($this->basedir, $this->container) = $this->_createContainer();
    $this->mapper = new CRM_Extension_Mapper($this->container);
    CRM_Core_DAO::executeQuery('DELETE FROM civicrm_extension WHERE full_name LIKE "test.%"');
  }

  function tearDown() {
    parent::tearDown();
  }

  /**
   * Install an extension with an invalid type name
   *
   * @expectedException CRM_Extension_Exception
   */
  function testInstallInvalidType() {
    $testingTypeManager = $this->getMock('CRM_Extension_Manager_Interface');
    $testingTypeManager->expects($this->never())
      ->method('onPreInstall');
    $manager = $this->_createManager(array(
      'other-testing-type' => $testingTypeManager,
    ));
    $manager->install('test.foo.bar');
  }

  /**
   * Install an extension with a valid type name
   */
  function testInstallDisableUninstall() {
    $testingTypeManager = $this->getMock('CRM_Extension_Manager_Interface');
    $manager = $this->_createManager(array(
      'testing-type' => $testingTypeManager,
    ));

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreInstall');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostInstall');
    $manager->install('test.foo.bar');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_extension WHERE full_name = "test.foo.bar"');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_extension WHERE full_name = "test.foo.bar" AND is_active = 1');

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreDisable');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostDisable');
    $manager->disable('test.foo.bar');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_extension WHERE full_name = "test.foo.bar"');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_extension WHERE full_name = "test.foo.bar" AND is_active = 0');

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreUninstall');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostUninstall');
    $manager->uninstall('test.foo.bar');
    $this->assertDBQuery(0, 'SELECT count(*) FROM civicrm_extension WHERE full_name = "test.foo.bar"');
  }

  /**
   * Install an extension with a valid type name
   */
  function testInstallDisableEnable() {
    $testingTypeManager = $this->getMock('CRM_Extension_Manager_Interface');
    $manager = $this->_createManager(array(
      'testing-type' => $testingTypeManager,
    ));

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreInstall');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostInstall');
    $manager->install('test.foo.bar');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_extension WHERE full_name = "test.foo.bar"');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_extension WHERE full_name = "test.foo.bar" AND is_active = 1');

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreDisable');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostDisable');
    $manager->disable('test.foo.bar');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_extension WHERE full_name = "test.foo.bar"');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_extension WHERE full_name = "test.foo.bar" AND is_active = 0');

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreEnable');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostEnable');
    $manager->enable('test.foo.bar');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_extension WHERE full_name = "test.foo.bar"');
    $this->assertDBQuery(1, 'SELECT count(*) FROM civicrm_extension WHERE full_name = "test.foo.bar" AND is_active = 1');
  }

  function _createManager($typeManagers) {
    list ($basedir, $c) = $this->_createContainer();
    $mapper = new CRM_Extension_Mapper($c);
    return new CRM_Extension_Manager($mapper, $typeManagers);
  }

  function _createContainer(CRM_Utils_Cache_Interface $cache = NULL, $cacheKey = NULL) {
    $basedir = $this->createTempDir('ext-');
    mkdir("$basedir/weird");
    mkdir("$basedir/weird/foobar");
    file_put_contents("$basedir/weird/foobar/info.xml", "<extension key='test.foo.bar' type='testing-type'><file>oddball</file></extension>");
    // not needed for now // file_put_contents("$basedir/weird/bar/oddball.php", "<?php\n");
    $c = new CRM_Extension_Container_Basic($basedir, 'http://example/basedir', $cache, $cacheKey);
    return array($basedir, $c);
  }
}
