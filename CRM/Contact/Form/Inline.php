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
 * Parent class for inline contact forms
 */
abstract class CRM_Contact_Form_Inline extends CRM_Core_Form {

  /**
   * Id of the contact that is being edited
   */
  public $_contactId;

  /**
   * Type of contact being edited
   */
  public $_contactType;

  /**
   * Common preprocess: fetch contact ID and contact type
   */
  public function preProcess() {
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE, NULL, $_REQUEST);
    $this->assign('contactId', $this->_contactId);

    // Get contact type
    if (empty($this->_contactType)) {
      $this->_contactType = CRM_Contact_BAO_Contact::getContactType($this->_contactId);
    }
    $this->assign('contactType', $this->_contactType);
  }

  /**
   * Common form elements
   *
   * @return void
   * @access public
   */
  public function buildQuickForm() {
    CRM_Contact_Form_Inline_Lock::buildQuickForm($this, $this->_contactId);

    $buttons = array(
      array(
        'type' => 'upload',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    );
    $this->addButtons($buttons);
  }

  /**
   * Override default cancel action
   *
   * @return void
   * @access public
   */
  public function cancelAction() {
    $response = array('status' => 'cancel');
    echo json_encode($response);
    CRM_Utils_System::civiExit();
  }

  /**
   * Set defaults for the form
   *
   * @return array
   * @access public
   */
  public function setDefaultValues() {
    $defaults = $params = array();
    $params['id'] = $this->_contactId;

    CRM_Contact_BAO_Contact::getValues($params, $defaults);

    return $defaults;
  }

  /**
   * Add entry to log table
   *
   * @return void
   * @protected
   */
  protected function log() {
    CRM_Core_BAO_Log::register($this->_contactId,
      'civicrm_contact',
      $this->_contactId
    );
  }

  /**
   * Final response from successful form submit
   *
   * @param response: array - data to send to the client
   *
   * @return void
   * @protected
   */
  protected function response($response = array()) {
    $response = array_merge(
      array('status' => 'save'),
      $response,
      CRM_Contact_Form_Inline_Lock::getResponse($this->_contactId)
    );
    echo json_encode($response);
    CRM_Utils_System::civiExit();
  }
}
