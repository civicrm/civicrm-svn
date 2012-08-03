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
 * form helper class for an Website object
 */
class CRM_Contact_Form_Inline_Website extends CRM_Core_Form {

  /**
   * contact id of the contact that is been viewed
   */
  private $_contactId;

  /**
   * websitess of the contact that is been viewed
   */
  private $_websites = array();

  /**
   * No of website blocks for inline edit
   */
  private $_blockCount = 6;

  /**
   * call preprocess
   */
  public function preProcess() {
    //get all the existing websites
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE, NULL, $_REQUEST);

    $this->assign('contactId', $this->_contactId);
    $params = array('contact_id' => $this->_contactId);
    $values = array();
    $this->_websites = CRM_Core_BAO_Website::getValues($params, $values);
  }

  /**
   * build the form elements for website object
   *
   * @return void
   * @access public
   */
  public function buildQuickForm() {
    $totalBlocks = $this->_blockCount;
    $actualBlockCount = 1;
    if (count($this->_websites) > 1) {
      $actualBlockCount = $totalBlocks = count($this->_websites);
      if ( $totalBlocks < $this->_blockCount ) {
        $additionalBlocks = $this->_blockCount - $totalBlocks;
        $totalBlocks += $additionalBlocks;
      }
      else {
        $actualBlockCount++;
        $totalBlocks++;
      }
    }

    $this->assign('actualBlockCount', $actualBlockCount);
    $this->assign('totalBlocks', $totalBlocks);

    $this->applyFilter('__ALL__', 'trim');

    for ($blockId = 1; $blockId < $totalBlocks; $blockId++) {
      CRM_Contact_Form_Edit_Website::buildQuickForm($this, $blockId, TRUE);
    }

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
    if (!empty($this->_websites)) {
      foreach ($this->_websites as $id => $value) {
        $defaults['website'][$id] = $value;
      }
    }
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

    // unset empty websites
    foreach( $params['website'] as $key => $values ) {
      if ( !CRM_Utils_Array::value('url', $values) ) {
        unset($params['website'][$key]);
      }
    }

    // need to process / save websites
    CRM_Core_BAO_Website::create($params['website'], $this->_contactId, true);

    // make entry in log table
    CRM_Core_BAO_Log::register( $this->_contactId,
      'civicrm_contact',
      $this->_contactId
    );

    $response = array('status' => 'save');
    echo json_encode($response);
    CRM_Utils_System::civiExit();
  }
}

