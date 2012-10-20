<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_Extension_ManagerTest extends CiviUnitTestCase {
  function setUp() {
    parent::setUp();
    list ($this->basedir, $this->container) = $this->_createContainer();
    $this->mapper = new CRM_Extension_Mapper($this->container);
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
    $manager->install(array('test.foo.bar'));
  }

  /**
   * Install an extension with a valid type name
   *
   * Note: We initially install two extensions but then toggle only
   * the second. This controls for bad SQL queries which hit either
   * "the first row" or "all rows".
   */
  function testInstall_Disable_Uninstall() {
    $testingTypeManager = $this->getMock('CRM_Extension_Manager_Interface');
    $manager = $this->_createManager(array(
      'testing-type' => $testingTypeManager,
    ));
    $this->assertEquals('uninstalled', $manager->getStatus('test.foo.bar'));
    $this->assertEquals('uninstalled', $manager->getStatus('test.whiz.bang'));

    $testingTypeManager
      ->expects($this->exactly(2))
      ->method('onPreInstall');
    $testingTypeManager
      ->expects($this->exactly(2))
      ->method('onPostInstall');
    $manager->install(array('test.whiz.bang', 'test.foo.bar'));
    $this->assertEquals('installed', $manager->getStatus('test.foo.bar'));
    $this->assertEquals('installed', $manager->getStatus('test.whiz.bang'));

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreDisable');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostDisable');
    $manager->disable(array('test.foo.bar'));
    $this->assertEquals('disabled', $manager->getStatus('test.foo.bar'));
    $this->assertEquals('installed', $manager->getStatus('test.whiz.bang')); // no side-effect

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreUninstall');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostUninstall');
    $manager->uninstall(array('test.foo.bar'));
    $this->assertEquals('uninstalled', $manager->getStatus('test.foo.bar'));
    $this->assertEquals('installed', $manager->getStatus('test.whiz.bang')); // no side-effect
  }

  /**
   * Install an extension with a valid type name
   */
  function testInstall_Disable_Enable() {
    $testingTypeManager = $this->getMock('CRM_Extension_Manager_Interface');
    $manager = $this->_createManager(array(
      'testing-type' => $testingTypeManager,
    ));
    $this->assertEquals('uninstalled', $manager->getStatus('test.foo.bar'));
    $this->assertEquals('uninstalled', $manager->getStatus('test.whiz.bang'));

    $testingTypeManager
      ->expects($this->exactly(2))
      ->method('onPreInstall');
    $testingTypeManager
      ->expects($this->exactly(2))
      ->method('onPostInstall');
    $manager->install(array('test.whiz.bang', 'test.foo.bar'));
    $this->assertEquals('installed', $manager->getStatus('test.foo.bar'));
    $this->assertEquals('installed', $manager->getStatus('test.whiz.bang'));

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreDisable');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostDisable');
    $manager->disable(array('test.foo.bar'));
    $this->assertEquals('disabled', $manager->getStatus('test.foo.bar'));
    $this->assertEquals('installed', $manager->getStatus('test.whiz.bang'));

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreEnable');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostEnable');
    $manager->enable(array('test.foo.bar'));
    $this->assertEquals('installed', $manager->getStatus('test.foo.bar'));
    $this->assertEquals('installed', $manager->getStatus('test.whiz.bang'));
  }

  /**
   * Performing 'install' on a 'disabled' extension performs an 'enable'
   */
  function testInstall_Disable_Install() {
    $testingTypeManager = $this->getMock('CRM_Extension_Manager_Interface');
    $manager = $this->_createManager(array(
      'testing-type' => $testingTypeManager,
    ));
    $this->assertEquals('uninstalled', $manager->getStatus('test.foo.bar'));

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreInstall');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostInstall');
    $manager->install(array('test.foo.bar'));
    $this->assertEquals('installed', $manager->getStatus('test.foo.bar'));

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreDisable');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostDisable');
    $manager->disable(array('test.foo.bar'));
    $this->assertEquals('disabled', $manager->getStatus('test.foo.bar'));

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreEnable');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostEnable');
    $manager->install(array('test.foo.bar')); // install() instead of enable()
    $this->assertEquals('installed', $manager->getStatus('test.foo.bar'));
  }

  /**
   * Install an extension with a valid type name
   */
  function testEnableBare() {
    $testingTypeManager = $this->getMock('CRM_Extension_Manager_Interface');
    $manager = $this->_createManager(array(
      'testing-type' => $testingTypeManager,
    ));
    $this->assertEquals('uninstalled', $manager->getStatus('test.foo.bar'));

    $testingTypeManager
      ->expects($this->once())
      ->method('onPreInstall');
    $testingTypeManager
      ->expects($this->once())
      ->method('onPostInstall');
    $testingTypeManager
      ->expects($this->never())
      ->method('onPreEnable');
    $testingTypeManager
      ->expects($this->never())
      ->method('onPostEnable');
    $manager->enable(array('test.foo.bar')); // enable not install
    $this->assertEquals('installed', $manager->getStatus('test.foo.bar'));
  }

  /**
   * Get the status of an unknown extension
   */
  function testStatusUnknownKey() {
    $testingTypeManager = $this->getMock('CRM_Extension_Manager_Interface');
    $testingTypeManager->expects($this->never())
      ->method('onPreInstall');
    $manager = $this->_createManager(array(
      'testing-type' => $testingTypeManager,
    ));
    $this->assertEquals('unknown', $manager->getStatus('test.foo.bar.whiz.bang'));
  }

  function _createManager($typeManagers) {
    list ($basedir, $c) = $this->_createContainer();
    $mapper = new CRM_Extension_Mapper($c);
    return new CRM_Extension_Manager($c, $mapper, $typeManagers);
  }

  function _createContainer(CRM_Utils_Cache_Interface $cache = NULL, $cacheKey = NULL) {
    $basedir = $this->createTempDir('ext-');
    mkdir("$basedir/weird");
    mkdir("$basedir/weird/foobar");
    file_put_contents("$basedir/weird/foobar/info.xml", "<extension key='test.foo.bar' type='testing-type'><file>oddball</file></extension>");
    // not needed for now // file_put_contents("$basedir/weird/bar/oddball.php", "<?php\n");
    mkdir("$basedir/weird/whizbang");
    file_put_contents("$basedir/weird/whizbang/info.xml", "<extension key='test.whiz.bang' type='testing-type'><file>oddball</file></extension>");
    // not needed for now // file_put_contents("$basedir/weird/whizbang/oddball.php", "<?php\n");
    $c = new CRM_Extension_Container_Basic($basedir, 'http://example/basedir', $cache, $cacheKey);
    return array($basedir, $c);
  }
}
