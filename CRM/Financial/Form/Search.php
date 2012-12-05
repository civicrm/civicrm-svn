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
class CRM_Financial_Form_Search extends CRM_Core_Form {
  function setDefaultValues() {
    $defaults = array();

    $status = CRM_Utils_Request::retrieve('status', 'Positive', CRM_Core_DAO::$_nullObject, FALSE, 1);
    $batchStatus = CRM_Utils_Request::retrieve('batchStatus', 'Positive', CRM_Core_DAO::$_nullObject, FALSE, 1);
    $this->assign("batchStatus",$batchStatus);
    $defaults['batch_status'] = $status;
    return $defaults;
  }

  public function buildQuickForm() {
       
    $this->add('text', 'title', ts('Batch Name'),
      CRM_Core_DAO::getAttribute('CRM_Core_DAO_Batch', 'title')
    );
    $this->add( 'select', 
                'type_id', 
                ts( 'Batch Type' ), 
                array( ''=> ts( '- Select Batch Type -' ) ) + CRM_Contribute_PseudoConstant::accountOptionValues( 'batch_type' ) );
    $this->add( 'select', 
                'payment_instrument_id', 
                ts( 'Payment Instrument' ), 
                array( ''=> ts( '- Select Payment Instrument -' ) ) + CRM_Contribute_PseudoConstant::paymentInstrument( ),
                false );
    
    $this->add('text', 'manual_total', ts('Total Amount'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'manual_total' ) );
    
    $this->add('text', 'manual_number_trans', ts('Number of Transactions'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'manual_number_trans' ) );
    $this->add('text', 'sort_name', ts('Created By'), CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_Contact', 'sort_name' ) );
  
    $this->assign( 'elements', array( 'title', 'sort_name', 'type_id', 'close_date', 'open_date', 'payment_instrument_id', 'manual_number_trans', 'manual_total', ) );

    $this->addButtons(
      array(
        array(
          'type' => 'refresh',
          'name' => ts('Search'),
          'isDefault' => TRUE,
        ),
      )
    );

    parent::buildQuickForm();
    $this->assign('suppressForm', TRUE);
  }
}

