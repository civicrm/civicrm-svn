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
 * The extension manager handles installing, disabling enabling, and
 * uninstalling extensions.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class CRM_Extension_Manager {
  /**
   * The extension is fully installed and enabled
   */
  const STATUS_INSTALLED = 'installed';

  /**
   * The extension config has been applied to database but deactivated
   */
  const STATUS_DISABLED = 'disabled';

  /**
   * The extension code is visible, but nothing has been applied to DB
   */
  const STATUS_UNINSTALLED = 'uninstalled';

  /**
   * The extension code is not locally accessible
   */
  const STATUS_UNKNOWN = 'unknown';

  /**
   * @var CRM_Extension_Container_Interface, the interface
   *
   * Note: Treat as private. This is only public to facilitate debugging.
   */
  public $fullContainer;

  /**
   * @var CRM_Extension_Mapper
   *
   * Note: Treat as private. This is only public to facilitate debugging.
   */
  public $mapper;

  /**
   * @var array (typeName => CRM_Extension_Manager_Interface)
   *
   * Note: Treat as private. This is only public to facilitate debugging.
   */
  public $typeManagers;

  /**
   * @var array (extensionKey => statusConstant)
   *
   * Note: Treat as private. This is only public to facilitate debugging.
   */  
  public $statuses;

  function __construct(CRM_Extension_Container_Interface $fullContainer, CRM_Extension_Mapper $mapper, $typeManagers) {
    $this->fullContainer = $fullContainer;
    $this->mapper = $mapper;
    $this->typeManagers = $typeManagers;
  }

  /**
   * Add records of the extension to the database -- and enable it
   *
   * @param array $keys list of extension keys
   * @return void
   * @throws CRM_Extension_Exception
   */
  public function install($keys) {
    $origStatuses = $this->getStatuses();

    // TODO: to mitigate the risk of crashing during installation, scan
    // keys/statuses/types before doing anything
    
    foreach ($keys as $key) {
      list ($info, $typeManager) = $this->_getInfoTypeHandler($key); // throws Exception

      switch ($origStatuses[$key]) {
        case self::STATUS_INSTALLED:
          // ok, nothing to do
          break;
        case self::STATUS_DISABLED:
          // re-enable it
          $typeManager->onPreEnable($info);
          $this->_setExtensionActive($info, 1);
          $typeManager->onPostEnable($info);
          break;
        case self::STATUS_UNINSTALLED:
          // install anew
          $typeManager->onPreInstall($info);
          $this->_createExtensionEntry($info);
          $typeManager->onPostInstall($info);
          break;
        case self::STATUS_UNKNOWN:
        default:
          throw new CRM_Extension_Exception("Cannot install or enable extension: $key");
      }
    }

    $this->statuses = NULL;
    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);
  }

  /**
   * Add records of the extension to the database -- and enable it
   *
   * @param array $keys list of extension keys
   * @return void
   * @throws CRM_Extension_Exception
   */
  public function enable($keys) {
    $this->install($keys);
  }

  /**
   * Add records of the extension to the database -- and enable it
   *
   * @param array $keys list of extension keys
   * @return void
   * @throws CRM_Extension_Exception
   */
  public function disable($keys) {
    $origStatuses = $this->getStatuses();

    // TODO: to mitigate the risk of crashing during installation, scan
    // keys/statuses/types before doing anything

    foreach ($keys as $key) {
      list ($info, $typeManager) = $this->_getInfoTypeHandler($key); // throws Exception

      switch ($origStatuses[$key]) {
        case self::STATUS_INSTALLED:
          $typeManager->onPreDisable($info);
          $this->_setExtensionActive($info, 0);
          $typeManager->onPostDisable($info);
          break;
        case self::STATUS_DISABLED:
        case self::STATUS_UNINSTALLED:
          // ok, nothing to do
          break;
        case self::STATUS_UNKNOWN:
        default:
          throw new CRM_Extension_Exception("Cannot disable unknown extension: $key");
      }
    }

    $this->statuses = NULL;
    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);
  }

  /**
   * Remove all database references to an extension
   *
   * Add records of the extension to the database -- and enable it
   *
   * @param array $keys list of extension keys
   * @return void
   * @throws CRM_Extension_Exception
   */
  public function uninstall($keys) {
    $origStatuses = $this->getStatuses();

    // TODO: to mitigate the risk of crashing during installation, scan
    // keys/statuses/types before doing anything

   foreach ($keys as $key) {
      list ($info, $typeManager) = $this->_getInfoTypeHandler($key); // throws Exception

      switch ($origStatuses[$key]) {
        case self::STATUS_INSTALLED:
          throw new CRM_Extension_Exception("Cannot uninstall extension; disable it first: $key");
          break;
        case self::STATUS_DISABLED:
          $typeManager->onPreUninstall($info);
          $this->_removeExtensionEntry($info);
          $typeManager->onPostUninstall($info);
          break;
        case self::STATUS_UNINSTALLED:
          // ok, nothing to do
          break;
        case self::STATUS_UNKNOWN:
        default:
          throw new CRM_Extension_Exception("Cannot disable unknown extension: $key");
      }
    }

    $this->statuses = NULL;
    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);
  }

  /**
   * Determine the status of an extension
   *
   * @return string constant (STATUS_INSTALLED, STATUS_DISABLED, STATUS_UNINSTALLED, STATUS_UNKNOWN)
   */
  public function getStatus($key) {
    $statuses = $this->getStatuses();
    if (array_key_exists($key, $statuses)) {
      return $statuses[$key];
    } else {
      return self::STATUS_UNKNOWN;
    }
  }

  /**
   * Determine the status of all extensions
   *
   * @return array ($key => status_constant)
   */
  public function getStatuses() {
    if (!is_array($this->statuses)) {
      $this->statuses = array();

      foreach ($this->fullContainer->getKeys() as $key) {
        $this->statuses[$key] = self::STATUS_UNINSTALLED;
      }

      $sql = '
        SELECT full_name, is_active
        FROM civicrm_extension
      ';
      $dao = CRM_Core_DAO::executeQuery($sql);
      while ($dao->fetch()) {
        if ($dao->is_active) {
          $this->statuses[$dao->full_name] = self::STATUS_INSTALLED;
        } else {
          $this->statuses[$dao->full_name] = self::STATUS_DISABLED;
        }
      }
    }
    return $this->statuses;
  }

  // ----------------------

  /**
   * Find the $info and $typeManager for a $key
   *
   * @return array (0 => CRM_Extension_Info, 1 => CRM_Extension_Manager_Interface)
   * @throws CRM_Extension_Exception
   */
  private function _getInfoTypeHandler($key) {
    $info = $this->mapper->keyToInfo($key); // throws Exception
    if (array_key_exists($info->type, $this->typeManagers)) {
      return array($info, $this->typeManagers[$info->type]);
    } else {
      throw new CRM_Extension_Exception("Unrecognized extension type: " . $info->type);
    }
  }

  private function _createExtensionEntry(CRM_Extension_Info $info) {
    $dao = new CRM_Core_DAO_Extension();
    $dao->label = $info->label;
    $dao->name = $info->name;
    $dao->full_name = $info->key;
    $dao->type = $info->type;
    $dao->file = $info->file;
    $dao->is_active = 1;
    return (bool) ($dao->insert());
  }

  private function _removeExtensionEntry(CRM_Extension_Info $info) {
    $dao = new CRM_Core_DAO_Extension();
    $dao->full_name = $info->key;
    if ($dao->find(TRUE)) {
      if (CRM_Core_BAO_Extension::del($dao->id)) {
        CRM_Core_Session::setStatus(ts('Selected option value has been deleted.'), ts('Deleted'), 'success');
      } else {
        throw new CRM_Extension_Exception("Failed to remove extension entry");
      }
    } // else: post-condition already satisified
  }

  private function _setExtensionActive(CRM_Extension_Info $info, $isActive) {
    CRM_Core_DAO::executeQuery('UPDATE civicrm_extension SET is_active = %1 where full_name = %2', array(
      1 => array($isActive, 'Integer'),
      2 => array($info->key, 'String'),
    ));
  }
}
