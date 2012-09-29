<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * BAO object for civicrm_setting table. This table is used to store civicrm settings that are not used
 * very frequently (i.e. not on every page load)
 *
 * The group column is used for grouping together all settings that logically belong to the same set.
 * Thus all settings in the same group are retrieved with one DB call and then cached for future needs.
 *
 */
class CRM_Core_BAO_Setting extends CRM_Core_DAO_Setting {

  /**
   * various predefined settings that have been migrated to the setting table
   */
  CONST
    ADDRESS_STANDARDIZATION_PREFERENCES_NAME = 'Address Standardization Preferences',
    CAMPAIGN_PREFERENCES_NAME = 'Campaign Preferences',
    DIRECTORY_PREFERENCES_NAME = 'Directory Preferences',
    EVENT_PREFERENCES_NAME = 'Event Preferences',
    MAILING_PREFERENCES_NAME = 'Mailing Preferences',
    CONTRIBUTE_PREFERENCES_NAME = 'Contribute Preferences',
    MEMBER_PREFERENCES_NAME = 'Member Preferences',
    MULTISITE_PREFERENCES_NAME = 'Multi Site Preferences',
    NAVIGATION_NAME = 'Navigation Menu',
    SYSTEM_PREFERENCES_NAME = 'CiviCRM Preferences',
    URL_PREFERENCES_NAME = 'URL Preferences';
  static $_cache = NULL;

  /**
   * Checks whether an item is present in the in-memory cache table
   *
   * @param string $group (required) The group name of the item
   * @param string $name  (required) The name of the setting
   * @param int    $componentID The optional component ID (so componenets can share the same name space)
   * @param int    $contactID    If set, this is a contactID specific setting, else its a global setting
   * @param int    $load  if true, load from local cache (typically memcache)
   *
   * @return boolean true if item is already in cache
   * @static
   * @access public
   */
  static
  function inCache($group,
    $name,
    $componentID = NULL,
    $contactID   = NULL,
    $load        = FALSE,
    $domainID = NULL
  ) {
    if (!isset(self::$_cache)) {
      self::$_cache = array();
    }

    $cacheKey = "CRM_Setting_{$group}_{$componentID}_{$contactID}_{$domainID}";
    if ($load &&
      !isset(self::$_cache[$cacheKey])
    ) {
      // check in civi cache if present (typically memcache)
      $globalCache = CRM_Utils_Cache::singleton();
      $result = $globalCache->get($cacheKey);
      if ($result) {
        self::$_cache[$cacheKey] = $result;
      }
    }

    return isset(self::$_cache[$cacheKey]) ? $cacheKey : NULL;
  }

  static
  function setCache($values,
    $group,
    $componentID = NULL,
    $contactID = NULL,
    $domainID = NULL
  ) {
    if (!isset(self::$_cache)) {
      self::$_cache = array();
    }

    $cacheKey = "CRM_Setting_{$group}_{$componentID}_{$contactID}_{$domainID}";

    self::$_cache[$cacheKey] = $values;

    $globalCache = CRM_Utils_Cache::singleton();
    $result = $globalCache->set($cacheKey, $values);

    return $cacheKey;
  }

  static
  function dao($group,
    $name        = NULL,
    $componentID = NULL,
    $contactID   = NULL,
    $domainID = NULL
  ) {
    $dao = new CRM_Core_DAO_Setting();

    $dao->group_name   = $group;
    $dao->name         = $name;
    $dao->component_id = $componentID;
    if(empty($domainID)){
      $dao->domain_id    = CRM_Core_Config::domainID();
    }
    else{
      $dao->domain_id = $domainID;
    }

    if ($contactID) {
      $dao->contact_id = $contactID;
      $dao->is_domain = 0;
    }
    else {
      $dao->is_domain = 1;
    }

    return $dao;
  }

  /**
   * Retrieve the value of a setting from the DB table
   *
   * @param string $group (required) The group name of the item
   * @param string $name  (required) The name under which this item is stored
   * @param int    $componentID The optional component ID (so componenets can share the same name space)
   * @param string $defaultValue The default value to return for this setting if not present in DB
   * @param int    $contactID    If set, this is a contactID specific setting, else its a global setting

   *
   * @return object The data if present in the setting table, else null
   * @static
   * @access public
   */
  static
  function getItem($group,
    $name         = NULL,
    $componentID  = NULL,
    $defaultValue = NULL,
    $contactID    = NULL,
    $domainID     = NULL
  ) {

    if (NULL !== ($override = self::getOverride($group, $name, NULL))) {
      return $override;
    }

    if(empty($domainID)){
      $domainID = CRM_Core_Config::domainID();
    }
    $cacheKey = self::inCache($group, $name, $componentID, $contactID, TRUE, $domainID);
    if (!$cacheKey) {
      $dao = self::dao($group, NULL, $componentID, $contactID, $domainID);
      $dao->find();

      $values = array();
      while ($dao->fetch()) {
        if (NULL !== ($override = self::getOverride($group, $dao->name, NULL))) {
          $values[$dao->name] = $override;
        }
        elseif ($dao->value) {
          $values[$dao->name] = unserialize($dao->value);
        }
        else {
          $values[$dao->name] = NULL;
        }
      }
      $dao->free();

      $cacheKey = self::setCache($values, $group, $componentID, $contactID, $domainID);
    }

    return $name ? CRM_Utils_Array::value($name, self::$_cache[$cacheKey], $defaultValue) : self::$_cache[$cacheKey];
  }
  /**
   * Store multiple items in the setting table
   *
   * @param array $params (required) An api formatted array of keys and values
   * @domains array an array of domains to get settings for. Default is the current domain
   * @return void
   * @static
   * @access public
   */
  static
  function getItems(&$params, $domains = null){
    if(empty($domains)){
      $domains[] = CRM_Core_Config::domainID();
    }
    $fields = array();
    $fieldsToGet = _civicrm_api3_setting_filterfields($params, $fields);
    foreach ($domains as $domain){
    foreach ($fieldsToGet as $name => $value){
      $setting =
      CRM_Core_BAO_Setting::getItem(
      $fields['values'][$name]['group_name'],
      $name,
      CRM_Utils_Array::value('component_id', $params),
      CRM_Utils_Array::value('default_value', $params),
      CRM_Utils_Array::value('contact_id', $params),
      $domain
      );
    $result[$domain][$name] = $setting;
    }
  }
    return $result;
  }
  /**
   * Store an item in the setting table
   *
   * @param object $value (required) The value that will be serialized and stored
   * @param string $group (required) The group name of the item
   * @param string $name  (required) The name of the setting
   * @param int    $componentID The optional component ID (so componenets can share the same name space)
   * @param int    $createdID   An optional ID to assign the creator to. If not set, retrieved from session
   *
   * @return void
   * @static
   * @access public
   */
  static
  function setItem($value,
    $group,
    $name,
    $componentID = NULL,
    $contactID   = NULL,
    $createdID   = NULL,
    $domainID    = NULL
  ) {

    $dao = self::dao($group, $name, $componentID, $contactID, $domainID);
    $dao->find(TRUE);

    if (CRM_Utils_System::isNull($value)) {
      $dao->value = 'null';
    }
    else {
      $dao->value = serialize($value);
    }

    $dao->created_date = date('Ymdhis');

    if ($createdID) {
      $dao->created_id = $createdID;
    }
    else {
      $session = CRM_Core_Session::singleton();
      $createdID = $session->get('userID');

      if ($createdID) {
        // ensure that this is a valid contact id (for session inconsistency rules)
        $cid = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact',
          $createdID,
          'id',
          'id'
        );
        if ($cid) {
          $dao->created_id = $session->get('userID');
        }
      }
    }

    $dao->save();
    $dao->free();

    // also save in cache if needed
    $cacheKey = self::inCache($group, $name, $componentID, $contactID, FALSE, $domainID);
    if ($cacheKey) {
      self::$_cache[$cacheKey][$name] = $value;
    }
  }
  /**
   * Store multiple items in the setting table
   *
   * @param array $params (required) An api formatted array of keys and values
   * @domains array an array of domains to get settings for. Default is the current domain
   * @return void
   * @static
   * @access public
   */
  static
  function setItems(&$params, $domains = null){
    if(empty($domains)){
      $domains[] = CRM_Core_Config::domainID();
    }
    $fields = array();
    $fieldsToSet = self::validateSettingsInput($params, $fields);
    foreach ($domains as $domain){
      foreach ($fieldsToSet as $name => $value){
        CRM_Core_BAO_Setting::setItem(
        $value,
        $fields['values'][$name]['group_name'],
        $name,
        CRM_Utils_Array::value('component_id', $params),
        CRM_Utils_Array::value('contact_id', $params),
        CRM_Utils_Array::value('created_id', $params),
        $domain
        );
        $result[$domain][$name] = $value;
      }
    }
    return $result;
  }

  /*
 * gets metadata about the settings fields (from getfields) based on the fields being passed in
 *
 * This function filters on the fields like 'version' & 'debug' that are not settings and ensures group
 * is set.
 * @param array $params Parameters as passed into API
 * @param array $fields empty array to be populated with fields metadata
 * @return array $fieldstoset name => value array of the fields to be set (with extraneous removed)
 */
  static function validateSettingsInput(&$params, &$fields){
    $group = CRM_Utils_Array::value('group', $params);

    $ignoredParams = array(
        'version' => 1,
        'id' => 1,
        'domain_id' => 1,
        'group' => 1,
        'debug' => 1,
        'created_id',
        'component_id',
        'contact_id'
    );
    $settingParams = array_diff_key($params, $ignoredParams);
    $getFieldsParams = array(
        'version' => 3,
        'group' => $group,
    );
    if(count($settingParams) ==1){
      // ie we are only setting one field - we'll pass it into getfields for efficiency
      list($name) = array_keys($settingParams);
      $getFieldsParams['name'] = $name;
    }
    $fields = civicrm_api('setting','getfields', $getFieldsParams);
    $invalidParams = (array_diff_key($settingParams,$fields['values']));
    if(!empty($invalidParams)){
      throw new api_Exception(implode(',', $invalidParams) . " are not valid settings");
    }
    $fieldsToSet = array_intersect_key($settingParams,$fields['values']);
    foreach ($fieldsToSet as $settingField => &$settingValue){
      self::validateSetting($settingValue, $fields['values'][$settingField]);
    }
    return $fieldsToSet;
  }
/*
 * Validate & convert settings input
 *
 * @value mixed value of the setting to be set
 * @fieldSpec array Metadata for given field (drawn from the xml)
 */
  static function validateSetting(&$value, $fieldSpec){
    if(empty($fieldSpec['validate_callback'])){
      return true;
    }
    else{
      list($class,$fn) = explode('::',$fieldSpec['validate_callback']);
      if(!$class::$fn($value,$fieldSpec)){
        throw new api_Exception("validation failed for {$fieldSpec['name']} = $value  based on callback {$fieldSpec['validate_callback']}");
      }
    }
  }

  /*
   * Validate & convert settings input - translate True False to 0 or 1
  *
  * @value mixed value of the setting to be set
  * @fieldSpec array Metadata for given field (drawn from the xml)
  */
  static function validateBoolSetting(&$value, $fieldSpec){
    if(!CRM_Utils_Rule::boolean($value)){
      throw new api_Exception("Boolean value required for {$fieldSpec['name']}");
    }
    if(!$value){
      $value = 0;
    }
    else{
      $value = 1;
    }
    return TRUE;
  }
  /**
   * Delete some or all of the items in the settings table
   *
   * @param string $group The group name of the entries to be deleted
   * @param string $name  The name of the setting to be deleted
   * @param int    $componentID The optional component ID (so componenets can share the same name space)
   *
   * @return void
   * @static
   * @access public
   */
  static
  function deleteItem($group, $name = NULL, $componentID = NULL, $contactID = NULL) {
    $dao = self::dao($group, $name, $componentID, $contactID);
    $dao->delete();

    // also reset memory cache if any
    CRM_Utils_System::flushCache();

    $cacheKey = self::inCache($group, $name, $componentID, $contactID, FALSE);
    if ($cacheKey) {
      if ($name) {
        unset(self::$_cache[$cacheKey][$name]);
      }
      else {
        unset(self::$_cache[$cacheKey]);
      }
    }
  }

  /*
  * This provides information about the setting - similar to the fields concept for DAO information.
  * As the setting is serialized code creating validation setting input needs to know the data type
  * This also helps move information out of the form layer into the data layer where people can interact with
  * it via the API or other mechanisms. In order to keep this consistent it is important the form layer
  * also leverages it.
  *
  * Function is intended for configuration rather than runtime access to settings
  *
  * The following params will filter the result. If none are passed all settings will be returns
  *
  * @params string $name Name of specific setting e.g customCSSURL
  * @params integer $componentID id of relevant component.
  *
  * @return array $result - the following information as appropriate for each setting
  * - name
  * - type
  * - default
  * - add (CiviCRM version added)
  * - is_domain
  * - is_contact
  * - description
  * - help_text
  */
  static
  function getSettingSpecification( $name = NULL, $componentID = NULL){
    $cacheString = 'settingsMetadata_';
    if($name){
      $cacheString .= 'name_' .$name;
    }

    $settingsMetadata = CRM_Core_BAO_Cache::getItem('CiviCRM setting Spec', $cacheString, $componentID);
    if ($settingsMetadata === NULL) {
      global $civicrm_root;
      $xmlPaths = array($civicrm_root. '/xml/settings/Settings.xml');
      CRM_Utils_Hook::alterXmlSettings($xmlPaths);
      foreach ($xmlPaths as $xmlFile) {
        $settingsMetadata = self::xmltoSettingsArray(self::parseSettingsXML($xmlFile));
      }

      CRM_Core_BAO_Cache::setItem($settingsMetadata,'CiviCRM setting Spec', $cacheString, $componentID);
    }
    return $settingsMetadata;

  }
/*
 * This is copied from GenCode  but since it's only 5 lines & that one isn't static it seemed ok to copy
 * perhaps
 */
  function parseSettingsXML($file) {
    $dom = new DomDocument();
    $dom->load($file);
    $dom->xinclude();
    $dbXML = simplexml_import_dom($dom);
    return $dbXML;
  }
/*
 * Convert Settings xml to settings array. We are going to return it as a list of settings rather than expect
 * people to always know the group which can seem a bit arbitrary. But if a group was passed in than only
 * that xml should have been loaded
 * @param xml $xml object loaded from file
 */
  function xmltoSettingsArray($xml) {
    $settingSpecs = array();
    $array = json_decode(json_encode($xml), TRUE);
    if(empty($array['settings'])){
      $array = array('settings' => array($array));
    }
    foreach ($array['settings'] as $settingsGroup => $group){
       if(!CRM_Utils_Array::value(1,$group['setting'])){
         // ie the has been flattened as only one child
         $group['setting'] = array($group['setting']['name'] = $group['setting']);
       }
        foreach ($group['setting'] as $setting => $values){
          $settingSpecs[$values['name']] = $values;
          foreach ($settingSpecs[$values['name']] as $key => $value){
            if(is_array($value) && empty( $value)){
              unset($settingSpecs[$values['name']][$key]);
            }
          }
        }
    }
    return $settingSpecs;
  }


  static
  function valueOptions($group,
    $name,
    $system              = TRUE,
    $userID              = NULL,
    $localize            = FALSE,
    $returnField         = 'name',
    $returnNameANDLabels = FALSE,
    $condition           = NULL
  ) {
    $optionValue = self::getItem($group, $name);

    $groupValues = CRM_Core_OptionGroup::values($name, FALSE, FALSE, $localize, $condition, $returnField);

    //enabled name => label require for new contact edit form, CRM-4605
    if ($returnNameANDLabels) {
      $names = $labels = $nameAndLabels = array();
      if ($returnField == 'name') {
        $names = $groupValues;
        $labels = CRM_Core_OptionGroup::values($name, FALSE, FALSE, $localize, $condition, 'label');
      }
      else {
        $labels = $groupValues;
        $names = CRM_Core_OptionGroup::values($name, FALSE, FALSE, $localize, $condition, 'name');
      }
    }

    $returnValues = array();
    foreach ($groupValues as $gn => $gv) {
      $returnValues[$gv] = 0;
    }

    if ($optionValue && !empty($groupValues)) {
      $dbValues = explode(CRM_Core_DAO::VALUE_SEPARATOR,
        substr($optionValue, 1, -1)
      );

      if (!empty($dbValues)) {
        foreach ($groupValues as $key => $val) {
          if (in_array($key, $dbValues)) {
            $returnValues[$val] = 1;
            if ($returnNameANDLabels) {
              $nameAndLabels[$names[$key]] = $labels[$key];
            }
          }
        }
      }
    }

    return ($returnNameANDLabels) ? $nameAndLabels : $returnValues;
  }

  static
  function setValueOption($group,
    $name,
    $value,
    $system   = TRUE,
    $userID   = NULL,
    $keyField = 'name'
  ) {
    if (empty($value)) {
      $optionValue = NULL;
    }
    elseif (is_array($value)) {
      $groupValues = CRM_Core_OptionGroup::values($name, FALSE, FALSE, FALSE, NULL, $keyField);

      $cbValues = array();
      foreach ($groupValues as $key => $val) {
        if (CRM_Utils_Array::value($val, $value)) {
          $cbValues[$key] = 1;
        }
      }

      if (!empty($cbValues)) {
        $optionValue = CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR,
          array_keys($cbValues)
        ) . CRM_Core_DAO::VALUE_SEPARATOR;
      }
      else {
        $optionValue = NULL;
      }
    }
    else {
      $optionValue = $value;
    }

    self::setItem($optionValue,
      $group,
      $name
    );
  }

  static
  function fixAndStoreDirAndURL(&$params) {
    $sql = "
 SELECT name, group_name
 FROM   civicrm_setting
 WHERE  ( group_name = %1
 OR       group_name = %2 )
";
    $sqlParams = array(1 => array(self::DIRECTORY_PREFERENCES_NAME, 'String'),
      2 => array(self::URL_PREFERENCES_NAME, 'String'),
    );

    $dirParams = array();
    $urlParams = array();

    $dao = CRM_Core_DAO::executeQuery($sql,
      $sqlParams,
      TRUE,
      NULL,
      FALSE,
      TRUE,
      // trap exceptions as error
      TRUE
    );

    if (is_a($dao, 'DB_Error')) {
      if (CRM_Core_Config::isUpgradeMode()) {
        // seems like this is a 4.0 -> 4.1 upgrade, so we suppress this error and continue
        return;
      }
      else {
        echo "Fatal DB error, exiting, seems like your schema does not have civicrm_setting table\n";
        exit();
      }
    }

    while ($dao->fetch()) {
      if (!isset($params[$dao->name])) {
        continue;
      }
      if ($dao->group_name == self::DIRECTORY_PREFERENCES_NAME) {
        $dirParams[$dao->name] = CRM_Utils_Array::value($dao->name, $params, '');
      }
      else {
        $urlParams[$dao->name] = CRM_Utils_Array::value($dao->name, $params, '');
      }
      unset($params[$dao->name]);
    }

    if (!empty($dirParams)) {
      self::storeDirectoryOrURLPreferences($dirParams,
        self::DIRECTORY_PREFERENCES_NAME
      );
    }

    if (!empty($urlParams)) {
      self::storeDirectoryOrURLPreferences($urlParams,
        self::URL_PREFERENCES_NAME
      );
    }
  }

  static
  function storeDirectoryOrURLPreferences(&$params, $group) {
    foreach ($params as $name => $value) {
      // always try to store relative directory or url from CMS root
      $value = ($group == self::DIRECTORY_PREFERENCES_NAME) ? CRM_Utils_File::relativeDirectory($value) : CRM_Utils_System::relativeURL($value);

      self::setItem($value,
        $group,
        $name
      );
    }
  }

  static
  function retrieveDirectoryAndURLPreferences(&$params, $setInConfig = FALSE) {
    if ($setInConfig) {
      $config = CRM_Core_Config::singleton();
    }

    $isJoomla = (defined('CIVICRM_UF') && CIVICRM_UF == 'Joomla') ? TRUE : FALSE;

    if (CRM_Core_Config::isUpgradeMode() && !$isJoomla) {
      $currentVer = CRM_Core_BAO_Domain::version();
      if (version_compare($currentVer, '4.1.alpha1') < 0) {
        return;
      }
    }
    $sql = "
SELECT name, group_name, value
FROM   civicrm_setting
WHERE  ( group_name = %1
OR       group_name = %2 )
AND domain_id = %3
";
    $sqlParams = array(1 => array(self::DIRECTORY_PREFERENCES_NAME, 'String'),
      2 => array(self::URL_PREFERENCES_NAME, 'String'),
      3 => array(CRM_Core_Config::domainID(), 'Integer'),
    );

    $dao = CRM_Core_DAO::executeQuery($sql,
      $sqlParams,
      TRUE,
      NULL,
      FALSE,
      TRUE,
      // trap exceptions as error
      TRUE
    );

    if (is_a($dao, 'DB_Error')) {
      if (CRM_Core_Config::isUpgradeMode()) {
        // seems like this is a 4.0 -> 4.1 upgrade, so we suppress this error and continue
        // hack to set the resource base url so that js/ css etc is loaded correctly
        if ($isJoomla) {
          $params['userFrameworkResourceURL'] = CRM_Utils_File::addTrailingSlash(CIVICRM_UF_BASEURL, '/') . str_replace('administrator', '', CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionValue', 'userFrameworkResourceURL', 'value', 'name'));
        }
        return;
      }
      else {
        echo "Fatal DB error, exiting, seems like your schema does not have civicrm_setting table\n";
        exit();
      }
    }

    while ($dao->fetch()) {
      $value = self::getOverride($dao->group_name, $dao->name, NULL);
      if ($value === NULL && $dao->value) {
        $value = unserialize($dao->value);
        if ($dao->group_name == self::DIRECTORY_PREFERENCES_NAME) {
          $value = CRM_Utils_File::absoluteDirectory($value);
        }
        else {
          // CRM-7622: we need to remove the language part
          $value = CRM_Utils_System::absoluteURL($value, TRUE);
        }
      }
      $params[$dao->name] = $value;

      if ($setInConfig) {
        $config->{$dao->name} = $value;
      }
    }
  }

  /**
   * Determine what, if any, overrides have been provided
   * for a setting.
   *
   * @return mixed, NULL or an overriden value
   */
  protected static function getOverride($group, $name, $default) {
    global $civicrm_setting;
    if ($group && $name && isset($civicrm_setting[$group][$name])) {
      return $civicrm_setting[$group][$name];
    } else {
      return $default;
}
  }
}

