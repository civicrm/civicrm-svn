<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_Extension_Manager_ModuleTest extends CiviUnitTestCase {
  function setUp() {
    parent::setUp();
    global $_test_extension_manager_moduletest_counts;
    $_test_extension_manager_moduletest_counts = array();
  }

  function tearDown() {
    parent::tearDown();
  }

  /**
   * Install an extension with a valid type name
   */
  function testInstallDisableUninstall() {
    global $_test_extension_manager_moduletest_counts;
    $manager = CRM_Extension_System::singleton(TRUE)->getManager();
    $this->assertModuleActive(FALSE, 'moduletest');

    $manager->install('test.extension.manager.moduletest');
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 1,
      'disable' => 0,
      'uninstall' => 0,
    ));
    $this->assertModuleActive(TRUE, 'moduletest');

    $manager->disable('test.extension.manager.moduletest');
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 1,
      'disable' => 1,
      'uninstall' => 0,
    ));
    $this->assertModuleActive(FALSE, 'moduletest');

    $manager->uninstall('test.extension.manager.moduletest');
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 1,
      'disable' => 1,
      'uninstall' => 1,
    ));
    $this->assertModuleActive(FALSE, 'moduletest');
  }

  /**
   * Install an extension with a valid type name
   */
  function testInstallDisableEnable() {
    global $_test_extension_manager_moduletest_counts;
    $manager = CRM_Extension_System::singleton(TRUE)->getManager();
    $this->assertModuleActive(FALSE, 'moduletest');

    $manager->install('test.extension.manager.moduletest');
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 1,
      'disable' => 0,
      'uninstall' => 0,
    ));
    $this->assertModuleActive(TRUE, 'moduletest');

    $manager->disable('test.extension.manager.moduletest');
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 1,
      'disable' => 1,
      'uninstall' => 0,
    ));
    $this->assertModuleActive(FALSE, 'moduletest');

    $manager->enable('test.extension.manager.moduletest');
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 2,
      'disable' => 1,
      'uninstall' => 0,
    ));
    $this->assertModuleActive(TRUE, 'moduletest');
  }
  
  /**
   * @param array $counts expected hook invocation counts ($hookName => $count)
   */
  function assertHookCounts($counts) {
    global $_test_extension_manager_moduletest_counts;
    foreach ($counts as $key => $expected) {
      $actual = $_test_extension_manager_moduletest_counts[$key];
      $this->assertEquals($expected, $actual,
         sprintf('Expected %d calls to hook_civicrm_%s -- found %d', $expected, $key, $actual)
      );
    }
  }
  
  function assertModuleActive($expectedIsActive, $prefix) {
    $activeModules = CRM_Core_PseudoConstant::getModuleExtensions(TRUE); // FIXME
    foreach ($activeModules as $activeModule) {
      if ($activeModule['prefix'] == $prefix) {
        $this->assertEquals($expectedIsActive, TRUE);
        return;
      }
    }
    $this->assertEquals($expectedIsActive, FALSE);
  }
}
