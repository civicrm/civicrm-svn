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
 * form helper class for contact name section 
 */
class CRM_Contact_Form_Inline_ContactName extends CRM_Core_Form {

  /**
   * contact id of the contact that is been viewed
   */
  public $_contactId;

  /**
   * contact type of the contact that is been viewed
   */
  private $_contactType;

  /**
   * call preprocess
   */
  public function preProcess() {
    //get all the existing email addresses
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE, NULL, $_REQUEST);
    $this->assign('contactId', $this->_contactId);
    
    $this->_contactType = CRM_Core_DAO::getFieldValue(
      'CRM_Contact_DAO_Contact',
      $this->_contactId, 'contact_type'
    );
    $this->assign('contactType', $this->_contactType);
  }

  /**
   * build the form elements for an email object
   *
   * @return void
   * @access public
   */
  public function buildQuickForm() {
    CRM_Contact_Form_Inline_Lock::buildQuickForm($this, $this->_contactId);

    //build contact type specific fields
    eval('CRM_Contact_Form_Edit_' . $this->_contactType . '::buildQuickForm( $this, 1 );');

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
   */
  function cancelAction() {
    $response = array('status' => 'cancel');
    echo json_encode($response);
    CRM_Utils_System::civiExit();
  }

  /**
   * set defaults for the form
   *
   * @return void
   * @access public
   */
  public function setDefaultValues() {
    $defaults = array();
    $params = array(
      'id' => $this->_contactId
    );

    $defaults = array();
    CRM_Contact_BAO_Contact::getValues( $params, $defaults );

    return $defaults;
  }

  /**
   * process the form
   *
   * @return void
   * @access public
   */
  public function postProcess() {
    $params = $this->exportValues();

    // need to process / save contact info 

    $params['contact_type'] = $this->_contactType;
    $params['contact_id']   = $this->_contactId;
    CRM_Contact_BAO_Contact::create( $params );

    $response = array('status' => 'save');
    $response = array_merge($response, CRM_Contact_Form_Inline_Lock::getResponse($this->_contactId));
    echo json_encode($response);
    CRM_Utils_System::civiExit();
  }
}

