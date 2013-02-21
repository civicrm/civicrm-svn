<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/


require_once 'CiviTest/CiviUnitTestCase.php';
class CRM_Core_BAO_SettingTest extends CiviUnitTestCase {
  function get_info() {
    return array(
      'name' => 'Setting BAO',
      'description' => 'Test set/get on setting variables.',
      'group' => 'CiviCRM BAO Tests',
    );
  }

  function setUp() {
    parent::setUp();
    global $civicrm_setting;
    $this->origSetting = $civicrm_setting;
    CRM_Utils_Cache::singleton()->flush();
  }

  function tearDown() {
    global $civicrm_setting;
    $civicrm_setting = $this->origSetting;
    CRM_Utils_Cache::singleton()->flush();
    parent::tearDown();
  }

  function testEnableComponentValid() {
    $config = CRM_Core_Config::singleton(TRUE, TRUE);

    $result = CRM_Core_BAO_ConfigSetting::enableComponent('CiviCampaign');

    $this->assertTrue($result);
  }

  function testEnableComponentAlreadyPresent() {
    $config = CRM_Core_Config::singleton(TRUE, TRUE);

    $result = CRM_Core_BAO_ConfigSetting::enableComponent('CiviCampaign');
    $result = CRM_Core_BAO_ConfigSetting::enableComponent('CiviCampaign');

    $this->assertTrue($result);
  }

  function testEnableComponentInvalid() {
    $config = CRM_Core_Config::singleton(TRUE, TRUE);

    $result = CRM_Core_BAO_ConfigSetting::enableComponent('CiviFake');

    $this->assertFalse($result);
  }

  /**
   * Ensure that overrides in $civicrm_setting apply when
   * using getItem($group,$name).
   */
  function testGetItem_Override() {
    global $civicrm_setting;
    $civicrm_setting[CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME]['imageUploadDir'] = '/test/override';
    $value = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME, 'imageUploadDir');
    $this->assertEquals('/test/override', $value);
  }

  /**
   * Ensure that overrides in $civicrm_setting apply when
   * using getItem($group).
   */
  function testGetItemGroup_Override() {
    global $civicrm_setting;
    $civicrm_setting[CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME]['imageUploadDir'] = '/test/override';
    $values = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME);
    $this->assertEquals('/test/override', $values['imageUploadDir']);
  }

  /**
   * Ensure that overrides in $civicrm_setting apply when
   * when using retrieveDirectoryAndURLPreferences().
   */
  function testRetrieveDirectoryAndURLPreferences_Override() {
    global $civicrm_setting;
    $civicrm_setting[CRM_Core_BAO_Setting::DIRECTORY_PREFERENCES_NAME]['imageUploadDir'] = '/test/override';

    $params = array();
    CRM_Core_BAO_Setting::retrieveDirectoryAndURLPreferences($params);
    $this->assertEquals('/test/override', $params['imageUploadDir']);
  }

  /**
   * This test checks that CRM_Core_BAO_Setting::updateSettingsFromMetaData();
   * 1) Removes 'maxAttachments' from config (because 'prefetch' is not set in the metadata it should
   * be removed
   *  2) for current domain setting max_attachments is set to the value that $config->maxAttachments
   *    had (6)
   *  3) for other domain (2) max_attachments is set to the configured default (3)
   *
   *
   **/
  function testConvertAndFillSettings() {
    $sql = " DELETE FROM civicrm_setting WHERE name = 'max_attachments'";
    CRM_Core_DAO::executeQuery($sql);

    $settings = array('maxAttachments' => 6);
    CRM_Core_BAO_ConfigSetting::add($settings);
    $config = CRM_Core_Config::singleton(TRUE, TRUE);
    $this->assertEquals(6, $config->maxAttachments);
    $checkSQL = "SELECT  count(*) FROM civicrm_domain WHERE config_backend LIKE '%Max%' AND id = 1
    ";
    $checkresult = CRM_Core_DAO::singleValueQuery($checkSQL);
    $this->assertEquals(1, $checkresult, "Check that maxAttachments has been saved to database not just stored in config");
    CRM_Core_BAO_Setting::updateSettingsFromMetaData();

    //check current domain
    $value = civicrm_api('setting', 'getvalue', array(
      'version' => 3,
      'name' => 'max_attachments',
      'group' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    ));

    $this->assertEquals(6, $value);
    // check alternate domain
    $value = civicrm_api('setting', 'getvalue', array(
      'version' => 3,
      'name' => 'max_attachments',
      'group' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
      'domain_id' => 2
    ));

    $this->assertEquals(3, $value);

    //some caching inconsistency here
    $config = CRM_Core_Config::singleton(TRUE, TRUE);
    $this->assertEmpty($config->maxAttachments, "Config item still Set to {$config->maxAttachments}
    . This works fine when test run alone");
  }

  /**
   * Ensure that overrides in $civicrm_setting apply when
   * when using retrieveDirectoryAndURLPreferences().
   */
  function testConvertConfigToSettingNoPrefetch() {
    $settings = array('maxAttachments' => 6);
    CRM_Core_BAO_ConfigSetting::add($settings);
    $config = CRM_Core_Config::singleton(TRUE, TRUE);
    $this->assertEquals(6, $config->maxAttachments);

    CRM_Core_BAO_Setting::convertConfigToSetting('max_attachments');
    $value = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME, 'max_attachments');
    $this->assertEquals(6, $value);

    civicrm_api('system', 'flush', array('version' => 3));
    $config = CRM_Core_Config::singleton(TRUE, TRUE);
    $this->assertEmpty($config->maxAttachments);
  }

  /*
   * Check that setting is converted without config value being removed
   *
  function testConvertConfigToSettingPrefetch() {
    $settings = array('debug' => 1);
    CRM_Core_BAO_ConfigSetting::add($settings);
    $config = CRM_Core_Config::singleton(true, true);
    $this->assertEquals(1, $config->debug);
    CRM_Core_BAO_Setting::convertConfigToSetting('debug_is_enabled');
    $value = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::DEBUG_PREFERENCES_NAME, 'debug_is_enabled');
    $this->assertEquals(1, $value);
    civicrm_api('system', 'flush', array('version' => 3));
    $config = CRM_Core_Config::singleton(true, true);
    $this->assertEmpty($config->debug);
  }
   */

}

