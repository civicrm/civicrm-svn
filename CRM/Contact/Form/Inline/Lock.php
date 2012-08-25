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
 * Auxilary class to provide support for locking (and ignoring locks on)
 * contact records.
 */
class CRM_Contact_Form_Inline_Lock {

  /**
   * This function provides the HTML form elements
   *
   * @param object $form form object
   * @param int $inlineEditMode ( 1 for contact summary
   * top bar form and 2 for display name edit )
   *
   * @access public
   * @return void
   */
  public function buildQuickForm(&$form) {
    // oplock_ts will start out with blank value -- filled in via JS
    $form->addElement('hidden', 'oplock_ts', '', array('id' => 'oplock_ts'));
  }

  /**
   * Ensure that oplock_ts hasn't changed in the underlying DB
   *
   * @param array $fields  the input form values
   * @param array $files   the uploaded files if any
   * @param array $options additional user data
   *
   * @return true if no errors, else array of errors
   * @access public
   * @static
   */
  static function formRule($fields, $files, $contactID = NULL) {
    $errors = array();

    $timestamps = CRM_Contact_BAO_Contact::getTimestamps($contactID);
    if ($fields['oplock_ts'] != $timestamps['modified_date']) {
      // Inline buttons generated via JS
      $open = sprintf("<span class='update_oplock_ts' data:update_oplock_ts='%s'>", $timestamps['modified_date']);
      $close = "</span>";
      $errors['oplock_ts'] = $open . ts('This record was modified by another user!') . $close;
    } 

    return empty($errors) ? TRUE : $errors;
  }
  
  /**
   * Return any post-save data
   *
   * @return array extra options to return in JSON
   */
  static function getResponse($contactID) {
    $timestamps = CRM_Contact_BAO_Contact::getTimestamps($contactID);
    return array('oplock_ts' => $timestamps['modified_date']);
  }
}
