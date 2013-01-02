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
   * build all the data structures needed to build the form
   *
   * @return void
   * @access public
   */
  function preProcess() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    //check for delete
    if (!CRM_Core_Permission::checkActionPermission('CiviContribute', CRM_Core_Action::UPDATE)) {
      CRM_Core_Error::fatal(ts('You do not have permission to access this page'));
    }

    $session = CRM_Core_Session::singleton();
    $session->replaceUserContext(CRM_Utils_System::url('civicrm/financial/financialbatches',
      'reset=1&batchStatus=1'));
  }
  
  /**
   * Build the form
   *
   * @access public
   * @return void
   */
  function buildQuickForm() {
    /*
    $this->addDefaultButtons(ts('Export Batch(s)'), 'done');
    foreach ( $this->_financialBatchIds as $financialBatchId )
      $batchNames[] = CRM_Core_DAO::getFieldValue( 'CRM_Batch_DAO_Batch', $financialBatchId, 'name' );
    $this->assign( 'batchNames', $batchNames );
    $this->assign( 'batchCount', count( $this->_financialBatchIds ) );
    */

    $optionTypes = array(
      'IIF' => ts('Export to IIF'),
      'CSV' => ts('Export to CSV'),
    );

    $this->addRadio('export_format', NULL, $optionTypes, NULL, '<br/>', TRUE);
    $this->setdefaults(array('export_format' => 'IIF'));

    $this->addButtons(
      array(
        array(
          'type' => 'next',
          'name' => ts('Export Batch'),
          'spacing' => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
          'isDefault' => true,
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
      $ids['batchID'] = $this->_id;
      $params['id'] = $this->_id;
    }

    // store the submitted values in an array
    $session = CRM_Core_Session::singleton();
    $params['modified_date'] = date('YmdHis');
    $params['modified_id'] = $session->get('userID');
    if (CRM_Utils_Array::value('created_date', $params)) {
      $params['created_date'] = CRM_Utils_Date::processDate($params['created_date']);
    }
    $batchStatus = CRM_Core_PseudoConstant::accountOptionValues('batch_status');
    $params['status_id'] = CRM_Utils_Array::key('Exported', $batchStatus);
    CRM_Batch_BAO_Batch::exportFinancialBatch($ids, $params['export_format']);
  }
}


