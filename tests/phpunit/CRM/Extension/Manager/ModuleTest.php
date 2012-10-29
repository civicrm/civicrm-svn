<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_Extension_Manager_ModuleTest extends CiviUnitTestCase {
  function setUp() {
    parent::setUp();
    global $_test_extension_manager_moduletest_counts;
    $_test_extension_manager_moduletest_counts = array();
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
    global $_test_extension_manager_moduletest_counts;
    $manager = $this->system->getManager();
    $this->assertModuleActiveByName(FALSE, 'moduletest');

    $manager->install(array('test.extension.manager.moduletest'));
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 1,
      'disable' => 0,
      'uninstall' => 0,
    ));
    $this->assertModuleActiveByName(TRUE, 'moduletest');
    $this->assertModuleActiveByKey(TRUE, 'test.extension.manager.moduletest');

    $manager->disable(array('test.extension.manager.moduletest'));
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 1,
      'disable' => 1,
      'uninstall' => 0,
    ));
    $this->assertModuleActiveByName(FALSE, 'moduletest');
    $this->assertModuleActiveByKey(FALSE, 'test.extension.manager.moduletest');

    $manager->uninstall(array('test.extension.manager.moduletest'));
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 1,
      'disable' => 1,
      'uninstall' => 1,
    ));
    $this->assertModuleActiveByName(FALSE, 'moduletest');
    $this->assertModuleActiveByKey(FALSE, 'test.extension.manager.moduletest');
  }

  /**
   * Install an extension with a valid type name
   */
  function testInstallDisableEnable() {
    global $_test_extension_manager_moduletest_counts;
    $manager = $this->system->getManager();
    $this->assertModuleActiveByName(FALSE, 'moduletest');
    $this->assertModuleActiveByKey(FALSE, 'test.extension.manager.moduletest');

    $manager->install(array('test.extension.manager.moduletest'));
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 1,
      'disable' => 0,
      'uninstall' => 0,
    ));
    $this->assertModuleActiveByName(TRUE, 'moduletest');
    $this->assertModuleActiveByKey(TRUE, 'test.extension.manager.moduletest');

    $manager->disable(array('test.extension.manager.moduletest'));
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 1,
      'disable' => 1,
      'uninstall' => 0,
    ));
    $this->assertModuleActiveByName(FALSE, 'moduletest');
    $this->assertModuleActiveByKey(FALSE, 'test.extension.manager.moduletest');

    $manager->enable(array('test.extension.manager.moduletest'));
    $this->assertHookCounts(array(
      'install' => 1,
      'enable' => 2,
      'disable' => 1,
      'uninstall' => 0,
    ));
    $this->assertModuleActiveByName(TRUE, 'moduletest');
    $this->assertModuleActiveByKey(TRUE, 'test.extension.manager.moduletest');
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

  function assertModuleActiveByName($expectedIsActive, $prefix) {
    $activeModules = CRM_Core_PseudoConstant::getModuleExtensions(TRUE); // FIXME
    foreach ($activeModules as $activeModule) {
      if ($activeModule['prefix'] == $prefix) {
        $this->assertEquals($expectedIsActive, TRUE);
        return;
      }
    }
    $this->assertEquals($expectedIsActive, FALSE);
  }

  function assertModuleActiveByKey($expectedIsActive, $key) {
    foreach (CRM_Core_Module::getAll() as $module) {
      if ($module->name == $key) {
        $this->assertEquals((bool)$expectedIsActive, (bool)$module->is_active);
        return;
      }
    }
    $this->assertEquals($expectedIsActive, FALSE);
  }
}
