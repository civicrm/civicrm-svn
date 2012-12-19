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
 * This is base class for all ajax calls
 */
class CRM_Core_Page_AJAX {

  /**
   * function to call generic ajax forms
   *
   * @static
   * @access public
   */
  static function run() {
    $className = CRM_Utils_Type::escape($_REQUEST['class_name'], 'String');
    $type = '';
    if (!empty($_REQUEST['type'])) {
      $type = CRM_Utils_Type::escape($_REQUEST['type'], 'String');
    }

    if (!$className) {
      CRM_Core_Error::fatal(ts('Invalid className: %1', array(1 => $className)));
    }

    if (!self::checkAuthz($type, $className)) {
      CRM_Utils_System::civiExit();
    }

    if (!$type) {
      $wrapper = new CRM_Utils_Wrapper();
      $wrapper->run($className);
    }
    else {
      if ( $type == 'method' ) {
        $execute = "{$className}();";
        eval($execute);
      }
      else {
        $page = new $className();
        $page->run();
      }
    }
    CRM_Utils_System::civiExit();
  }

  /**
   * function to change is_quick_config priceSet to complex
   *
   * @static
   * @access public
   */
  static function setIsQuickConfig() {
    if (!$id = CRM_Utils_Array::value('id', $_GET)) {
        return false;
    }
    $priceSetId = CRM_Price_BAO_Set::getFor($_GET['context'], $id, NULL);
    if ($priceSetId) {
      $result = CRM_Price_BAO_Set::setIsQuickConfig($priceSetId,0);
    }
    if (!$result) {
      $priceSetId = null;
    }
    echo json_encode($priceSetId);

    CRM_Utils_System::civiExit();
  }

  /**
   * Determine whether the request is for a valid class/method name.
   *
   * @param string $type 'method'|'class'|''
   * @param string $className 'Class_Name' or 'Class_Name::method_name'
   */
  static function checkAuthz($type, $className) {
    switch ($type) {
      case 'method':
        return preg_match('/^CRM_[a-zA-Z0-9]+_Page_AJAX::[a-zA-Z0-9]+$/', $className);
      case 'page':
      case 'class':
      case '':
        return preg_match('/^CRM_[a-zA-Z0-9]+_(Page|Form)_Inline_[a-zA-Z0-9]+$/', $className);
      default:
        return FALSE;
}
  }
}

