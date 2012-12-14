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
class CRM_Financial_Form_Task_Close extends CRM_Financial_Form_Task {

  /**
   * Are we operating in "single mode", i.e. deleting one
   * specific contribution?
   *
   * @var boolean
   */
  protected $_single = false;

  /**
   * build all the data structures needed to build the form
   *
   * @return void
   * @access public
   */
  function preProcess() {
    //check for delete
    if ( !CRM_Core_Permission::checkActionPermission( 'CiviContribute', CRM_Core_Action::UPDATE ) ) {
      CRM_Core_Error::fatal( ts( 'You do not have permission to access this page' ) );  
    }
    parent::preProcess();
  }
  
  /**
   * Build the form
   *
   * @access public
   * @return void
   */
  function buildQuickForm() {
    $this->addDefaultButtons(ts('Close Batch(s)'), 'done');
    foreach ( $this->_financialBatchIds as $financialBatchId )
      $batchNames[] = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Batch', $financialBatchId, 'name' );
    $this->assign( 'batchNames', $batchNames );
    $this->assign( 'batchCount', count( $this->_financialBatchIds ) );
  }
  
  /**
   * process the form after the input has been submitted and validated
   *
   * @access public
   * @return None
   */
  public function postProcess( ) {
    $closedBatches = 0;
    $batchStatus = CRM_Core_PseudoConstant::accountOptionValues( 'batch_status' );
    
    $params['modified_date'] = date('YmdHis');
    $params['batch_status_id'] = CRM_Utils_Array::key( 'Closed', $batchStatus );
    foreach ( $this->_financialBatchIds as $financialBatchId ) {
      $ids = array( );
      if ( $financialBatchId ) {
        $ids['batchID'] = $financialBatchId;
      }
      if ( CRM_Batch_BAO_Batch::create( $params, $ids ) ) {
        $closedBatches++;
      }
    }
    
    $status = array(
      ts('Closed Batch(s): %1', array(1 => $closedBatches)),
      ts('Total Selected Batch(s): %1', array(1 => count($this->_financialBatchIds))),
     );
    CRM_Core_Session::setStatus($status);
  }
  
}


