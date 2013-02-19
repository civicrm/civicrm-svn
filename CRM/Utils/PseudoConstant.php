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

/**
 * Utilities for manipulating/inspecting CRM_*_PseudoConstant classes
 */
class CRM_Utils_PseudoConstant {
  /*
   * CiviCRM pseudoconstant classes for wrapper functions
   */
  private static $constantClasses = array(
    'CRM_Core_PseudoConstant',
    'CRM_Event_PseudoConstant',
    'CRM_Contribute_PseudoConstant',
    'CRM_Member_PseudoConstant',
    'CRM_Grant_PseudoConstant',
  );

  /**
   * Get constant
   *
   * Wrapper for Pseudoconstant methods. We use this so the calling function
   * doesn't need to know which class the Pseudoconstant is on
   * (some are on the Contribute_Pseudoconsant Class etc
   *
   * @access public
   * @static
   *
   * @return array - array reference of all relevant constant
   */
  public static function getConstant($constant) {
    $classes = self::$constantClasses;
    foreach ($classes as $class) {
      if (method_exists($class, lcfirst($constant))) {
        return $class::$constant();
      }
    }
  }

  /**
   * Flush constant
   *
   * Wrapper for Pseudoconstant methods. We use this so the calling function
   * doesn't need to know which class the Pseudoconstant is on
   * (some are on the Contribute_Pseudoconsant Class etc
   *
   * @access public
   * @static
   *
   * @return array - array reference of all relevant constant
   */
  public static function flushConstant($constant) {
    $classes = self::$constantClasses;
    foreach ($classes as $class) {
      if (method_exists($class, lcfirst($constant))) {
        $class::flush(lcfirst($constant));
        //@todo the rule is api functions should only be called from within the api - we
        // should move this function to a Core class
        $name = _civicrm_api_get_entity_name_from_camel($constant);
        CRM_Core_OptionGroup::flush($name);
        return TRUE;
      }
    }
  }

}