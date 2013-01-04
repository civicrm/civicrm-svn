<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

/**
 * This class provides the functionality to delete a group of
 * contributions. This class provides functionality for the actual
 * deletion.
 */
class CRM_Financial_Form_Export extends CRM_Core_Form {

  /**
   * The financial batch id, used when editing the field
   *
   * @var int
   * @access protected
   */
  protected $_id;

  /**
   * Financial batch ids
   */
  protected $_batchIds = array();

  /**
   * build all the data structures needed to build the form
   *
   * @return void
   * @access public
   */
  function preProcess() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);

    // this mean it's a batch action
    if (!$this->_id ) {
      if (!empty($_POST['batch_id'])) {
        $this->_batchIds = $_POST['batch_id'];
        $this->set('batchIds', $this->_batchIds);
      }
      else {
        $this->_batchIds = $this->get('batchIds');
      }
    }
    else {
      $this->_batchIds = $this->_id;
    }

    $status = CRM_Utils_Request::retrieve('status', 'Positive', $this);
    $path = '';
    if ($status) {
      $path = "&batchStatus={$status}";
    }
    $session = CRM_Core_Session::singleton();
    $session->replaceUserContext(CRM_Utils_System::url('civicrm/financial/financialbatches',
      "reset=1{$path}"));
  }
  
  /**
   * Build the form
   *
   * @access public
   * @return void
   */
  function buildQuickForm() {
    // this mean it's a batch action
    if (!empty($this->_batchIds)) {
      $batchNames = CRM_Batch_BAO_Batch::getBatchNames($this->_batchIds);
      $this->assign( 'batchNames', $batchNames );
    }

    $optionTypes = array(
      'IIF' => ts('Export to IIF'),
      'CSV' => ts('Export to CSV'),
    );

    $this->addRadio('export_format', NULL, $optionTypes, NULL, '<br/>', TRUE);

    $this->addButtons(
      array(
        array(
          'type' => 'next',
          'name' => ts('Export Batch'),
          'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
      )
    );
  }
  
  /**
   * process the form after the input has been submitted and validated
   *
   * @access public
   * @return None
   */
  public function postProcess( ) {
    $params = $this->exportValues();

    if ($this->_id) {
      $batchIds = array($this->_id);
    }
    else if (!empty($this->_batchIds)) {
      $batchIds = explode(',', $this->_batchIds);
    }

    // build batch params
    $session = CRM_Core_Session::singleton();
    $batchParams['modified_date'] = date('YmdHis');
    $batchParams['modified_id'] = $session->get('userID');

    $batchStatus = CRM_Core_PseudoConstant::accountOptionValues('batch_status');
    $batchParams['status_id'] = CRM_Utils_Array::key('Exported', $batchStatus);

    $ids = array();
    foreach($batchIds as $batchId) {
      $batchParams['id'] = $ids['batchID'] = $batchId;
      CRM_Batch_BAO_Batch::create($batchParams, $ids, 'financialBatch');
    }

    CRM_Batch_BAO_Batch::exportFinancialBatch($batchIds, $params['export_format']);
  }
}


