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
 *
 */
class CRM_Core_Permission_WordPress extends CRM_Core_Permission_Base {


  /**
   * given a permission string, check for access requirements
   *
   * @param string $str the permission to check
   *
   * @return boolean true if yes, else false
   * @access public
   */

  function check($str) {
    // for administrators give them all permissions
    if (!function_exists('current_user_can')) {
      return TRUE;
    }

    if (current_user_can('author')) {
      return FALSE;
    }

    if (current_user_can('super admin') ||
      current_user_can('administrator')
    ) {
      return TRUE;
    }

    static $otherPerms = NULL;
    if (!$otherPerms) {
      $otherPerms = array(
        'access CiviMail subscribe/unsubscribe pages' => 1,
        'access all custom data' => 1,
        'access uploaded files' => 1,
        'make online contributions' => 1,
        'profile create' => 1,
        'profile edit' => 1,
        'profile view' => 1,
        'register for events' => 1,
        'view event info' => 1,
        'access Contact Dashboard' => 1,
        'sign CiviCRM Petition' => 1,
        'view public CiviMail content' => 1,
      );
    }

    static $editPerms = NULL;
    if (!$editPerms) {
      $editPerms = array(
        'access CiviCRM' => 1,
      );
      $editPerms = array_merge($editPerms, $otherPerms);
    }

    $permissions = NULL;

    if (current_user_can('editor')) {
      //assign editor permissions
      $permissions = $editPerms;
    } else {
      // for everyone else, give them permission only for
      // some public pages
      $permissions = $otherPerms;
    }

    if (array_key_exists($str, $permissions)) {
      return TRUE;
    }

    return FALSE;
  }
}

