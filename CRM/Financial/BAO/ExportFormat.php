<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                             |
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
 * Base class for Export Formats
 * Create a subclass for a specific format.
 * @see http://wiki.civicrm.org/confluence/display/CRM/CiviAccounts+Specifications+-++Batches#CiviAccountsSpecifications-Batches-%C2%A0Overviewofimplementation
 */

class CRM_Financial_BAO_ExportFormat {

  /*
   * Array of data which the individual export formats will output in the desired format
   */
  protected $_exportParams;

  /*
   * smarty template
   */
  static protected $_template;

  /**
   * class constructor
   */
  function __construct() {
    if ( !isset( self::$_template ) ) {
      self::$_template = CRM_Core_Smarty::singleton();
    }
  }

  // Override to assemble the appropriate subset of financial data for the specific export format
  function export( $exportParams ) {
    $this->_exportParams = $exportParams;
    $export = array();
    foreach ($this->_exportParams['batchIds'] as $batchId) {
      $export = self::createExport($batchId);
    }

    return $export;
  }

  function output($fileName) {
    $tplFile = $this->getTemplateFileName();
    $out = self::getTemplate()->fetch( $tplFile );
    self::createActivityExport($this->_exportParams['batchIds']['batchID'], $fileName);
    if ($this->getFileExtension() == 'csv') {
      self::createCSVDownload($fileName);
    }
    else {
      self::createIIFDownload($fileName);
    }
  }

  function getMimeType() {
    return 'text/plain';
  }

  function getFileExtension() {
    return 'txt';
  }

  // Override this if appropriate
  function getTemplateFileName() {
    return null;
  }

  static function &getTemplate() {
    return self::$_template;
  }

  function assign($var, $value = NULL) {
    self::$_template->assign($var, $value);
  }

  /*
   * This gets called for every item of data being compiled before being sent to the exporter for output.
   * 
   * Depending on the output format might want to override this, e.g. for IIF tabs need to be escaped etc,
   * but for CSV it doesn't make sense because php has built in csv output functions.
   */
  static function format( $s, $type = 'string' ) {
    return $s;
  }

  function createCSVDownload($fileName) {
    $config = CRM_Core_Config::singleton();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename='.CRM_Utils_File::cleanFileName(basename($fileName)));
    readfile($config->customFileUploadDir.CRM_Utils_File::cleanFileName(basename($fileName)));
    CRM_Utils_System::civiExit();
  }

  static function createActivityExport($batchIds, $fileName) {
    $session = CRM_Core_Session::singleton();
    $values = array();
    $params = array(
      'id' => $batchIds,
    );
    CRM_Batch_BAO_Batch::retrieve($params, $values);
    $createdBy = CRM_Contact_BAO_Contact::displayName($values['created_id']);
    $modifiedBy = CRM_Contact_BAO_Contact::displayName($values['modified_id']);
    $paymentInstrument = array_flip(CRM_Contribute_PseudoConstant::paymentInstrument('label'));

    $details = '<p>' . ts('Record: ') . $values['title'] . '</p><p>' . ts('Description: ') . $values['description'] . '</p><p>' . ts('Created By: ') . $createdBy . '</p><p>' . ts('Created Date: ') . $values['created_date'] . '</p><p>' . ts('Last Modified By: ') . $modifiedBy . '</p><p>' . ts('Payment Instrument: ') . array_search($values['payment_instrument_id'], $paymentInstrument) . '</p>';

    //create activity. 
    $activityTypes = CRM_Core_PseudoConstant::activityType(TRUE, FALSE, FALSE, 'name');
    $activityParams = array(
      'activity_type_id' => array_search('Export of Financial Transactions Batch', $activityTypes),
      'subject' => 'Total ['.$values['total'].'], Count ['.$values['item_count'].'], Batch ['.$values['title'].']',
      'status_id' => 2,
      'activity_date_time' => date('YmdHis'),
      'source_contact_id' => $session->get('userID'),
      'source_record_id' => $values['id'],
      'target_contact_id' => $session->get('userID'),
      'details' => $details,
      'attachFile_1' => array (
        'uri' => $fileName,
        'type' => 'text/csv',
        'location' => $fileName,
        'upload_date' => date('YmdHis')
      )
    );
    $activity = CRM_Activity_BAO_Activity::create($activityParams);
    return $activity;
  }

  function formatHeaders($values) {
    $arrayKeys = array_keys($values);
    foreach ($values[$arrayKeys[0]] as $title => $value) {
      $headers[] = $title;
    }
    return $headers;
  }

  function createExport($batchId) {

    $exportQuery = "SELECT 
      ft.id as id,
      ft.trxn_date as trxn_date, 
      fa.accounting_code AS debit_account, 
      fa.name AS debit_account_name, 
      ft.total_amount AS debit_account_amount, 
      ft.trxn_id AS trxn_id, 
      c.check_number AS check_number,
      c.source AS source,
      ft.currency AS currency, 
      eft.amount AS amount, 
      fac.accounting_code AS credit_account, 
      fac.name AS credit_account_name, 
      fi.description AS item_description
      FROM civicrm_financial_trxn ft 
      LEFT JOIN civicrm_entity_financial_trxn eft ON eft.financial_trxn_id  = ft.id AND eft.entity_table = 'civicrm_financial_item'
      LEFT JOIN civicrm_entity_financial_trxn eftc ON eftc.financial_trxn_id  = ft.id AND eftc.entity_table = 'civicrm_contribution'
      LEFT JOIN civicrm_contribution c ON c.id = eftc.entity_id
      LEFT JOIN civicrm_financial_account fa ON fa.id = ft.to_financial_account_id 
      LEFT JOIN civicrm_financial_item fi ON fi.id = eft.entity_id  
      LEFT JOIN civicrm_financial_account fac ON fac.id = fi.financial_account_id
      LEFT JOIN civicrm_entity_batch b ON b.entity_id = ft.id AND b.entity_table = 'civicrm_financial_trxn'
      WHERE b.batch_id = $batchId";

    $dao = CRM_Core_DAO::executeQuery($exportQuery);

    //TBD: perform check based on export format, IIF vs CSV.
    $financialItems = array();

    while ($dao->fetch()) {
      $financialItems[$dao->id]['Transaction Date'] = $dao->trxn_date;
      $financialItems[$dao->id]['Debit Account'] = $dao->debit_account;
      $financialItems[$dao->id]['Debit Account Name'] = $dao->debit_account_name;
      $financialItems[$dao->id]['Debit Account Amount (Unsplit)'] = $dao->debit_account_amount;
      $financialItems[$dao->id]['Transaction ID (Unsplit)'] = $dao->trxn_id;
      $financialItems[$dao->id]['Check Number'] = $dao->check_number;
      $financialItems[$dao->id]['Source'] = $dao->source;
      $financialItems[$dao->id]['Currency'] = $dao->currency;
      $financialItems[$dao->id]['Amount'] = $dao->amount;
      $financialItems[$dao->id]['Credit Account'] = $dao->credit_account;
      $financialItems[$dao->id]['Credit Account Name'] = $dao->credit_account_name;
      $financialItems[$dao->id]['Item Description'] = $dao->item_description;
    }

    $financialItems['headers'] = self::formatHeaders($financialItems);
    return $financialItems;
  }
}