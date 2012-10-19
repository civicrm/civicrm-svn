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
   * @var CRM_Extension_Container_Interface, the interface
   */
  public $fullContainer;

  /**
   * @var CRM_Extension_Mapper
   */
  public $mapper;

  /**
   * @var array (typeName => CRM_Extension_Manager_Interface)
   */
  public $typeManagers;

  function __construct(CRM_Extension_Mapper $mapper, $typeManagers) {
   $this->mapper = $mapper;
   $this->typeManagers = $typeManagers;
  }

  /**
   * Add records of the extension to the database -- and enable it
   */
  public function install($key) {
    list ($info, $typeManager) = $this->_getInfoTypeHandler($key); // throws Exception
    $typeManager->onPreInstall($info);
    $this->_createExtensionEntry($info);
    $typeManager->onPostInstall($info);
    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);
  }

  public function enable($key) {
    list ($info, $typeManager) = $this->_getInfoTypeHandler($key); // throws Exception
    $typeManager->onPreEnable($info);
    CRM_Core_DAO::setFieldValue('CRM_Core_DAO_Extension', $this->id, 'is_active', 1);
    $typeManager->onPostEnable($info);
    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);
  }

  public function disable($key) {
    list ($info, $typeManager) = $this->_getInfoTypeHandler($key); // throws Exception
    $typeManager->onPreDisable($info);
    CRM_Core_DAO::setFieldValue('CRM_Core_DAO_Extension', $this->id, 'is_active', 0);
    $typeManager->onPostDisable($info);
    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);
  }

  /**
   * Remove all database references to an extension
   *
   * @param string $key extension key
   * @param bool $removeFiles whether to remove PHP source tree for the extension
   * @return void
   */
  public function uninstall($key) {
    list ($info, $typeManager) = $this->_getInfoTypeHandler($key); // throws Exception
    $typeManager->onPreUninstall($info);
    $this->_removeExtensionEntry($info);
    $typeManager->onPostUninstall($info);
    CRM_Core_Invoke::rebuildMenuAndCaches(TRUE);
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
    $dao->key = $info->key;
    if ($dao->find(TRUE)) {
      if (CRM_Core_BAO_Extension::del($dao->id)) {
        CRM_Core_Session::setStatus(ts('Selected option value has been deleted.'), ts('Deleted'), 'success');
      } else {
        throw new CRM_Extension_Exception("Failed to remove extension entry");
      }
    } // else: post-condition already satisified
  }
}
