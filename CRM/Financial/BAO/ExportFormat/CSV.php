<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
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

/*
 * @see http://wiki.civicrm.org/confluence/display/CRM/CiviAccounts+Specifications+-++Batches#CiviAccountsSpecifications-Batches-%C2%A0Overviewofimplementation
 */

class CRM_Financial_BAO_ExportFormat_CSV extends CRM_Financial_BAO_ExportFormat {

  // For this phase, we always output these records too so that there isn't data referenced in the journal entries that isn't defined anywhere.
  // Possibly in the future this could be selected by the user.
  public static $complementaryTables = array(
    'ACCNT',
    'CUST',
  );

  /**
   * class constructor
   */
  function __construct() {
    parent::__construct();
  }

  function export($exportParams) {
    $export = parent::export($exportParams);

    // Save the file in the public directory
    $fileName = self::putFile($export);

    foreach ( self::$complementaryTables as $rct ) {
      $func = "export{$rct}";
      $this->$func();
    }

    // now do general journal entries
    $this->exportTRANS();

    $this->output($fileName);
  }

  function putFile($export) {
    $config = CRM_Core_Config::singleton();
    $fileName = $config->uploadDir.'Financial_Transactions_'.$this->_batchIds.'_'.date('YmdHis').'.'.$this->getFileExtension();
    $this->_downloadFile[] = $config->customFileUploadDir.CRM_Utils_File::cleanFileName(basename($fileName));
    $out = fopen($fileName, 'w');
    fputcsv($out, $export['headers']);
    unset($export['headers']);
    if (!empty($export)) {
      foreach ($export as $fields) {
        fputcsv($out, $fields);
      }
      fclose($out);
    }
    return $fileName;
  }

  /**
   * Format table headers
   *
   * @param array $values
   * @return array
   */
  function formatHeaders($values) {
    $arrayKeys = array_keys($values);
    $headers = '';
    if (!empty($arrayKeys)) {
      foreach ($values[$arrayKeys[0]] as $title => $value) {
        $headers[] = $title;
      }
    }
    return $headers;
  }

  /**
   * Generate CSV array for export
   *
   * @param array $export
   *
   */
  function makeCSV($export) {
    foreach ($export as $batchId => $dao) {
      $financialItems = array();
      $this->_batchIds = $batchId;
      while ($dao->fetch()) {
        $financialItems[$dao->financial_trxn_id]['Transaction Date'] = $dao->trxn_date;
        $financialItems[$dao->financial_trxn_id]['Transaction Status'] = $dao->status;
        $financialItems[$dao->financial_trxn_id]['Debit Account'] = $dao->to_account_code;
        $financialItems[$dao->financial_trxn_id]['Debit Account Name'] = $dao->to_account_name;
        $financialItems[$dao->financial_trxn_id]['Debit Account Type'] = $dao->to_account_type_code;
        $financialItems[$dao->financial_trxn_id]['Debit Account Amount (Unsplit)'] = $dao->debit_total_amount;
        $financialItems[$dao->financial_trxn_id]['Transaction ID (Unsplit)'] = $dao->trxn_id;
        $financialItems[$dao->financial_trxn_id]['Payment Instrument'] = $dao->payment_instrument;
        $financialItems[$dao->financial_trxn_id]['Check Number'] = $dao->check_number;
        $financialItems[$dao->financial_trxn_id]['Source'] = $dao->source;
        $financialItems[$dao->financial_trxn_id]['Currency'] = $dao->currency;
        $financialItems[$dao->financial_trxn_id]['Amount'] = $dao->amount;
        $financialItems[$dao->financial_trxn_id]['Credit Account'] = $dao->credit_account;
        $financialItems[$dao->financial_trxn_id]['Credit Account Name'] = $dao->credit_account_name;
        $financialItems[$dao->financial_trxn_id]['Credit Account Type'] = $dao->credit_account_type_code;
        $financialItems[$dao->financial_trxn_id]['Item Description'] = $dao->item_description;
      }
      $financialItems['headers'] = self::formatHeaders($financialItems);
      self::export($financialItems);
    }
    parent::initiateDownload();
  }

  function getFileExtension() {
    return 'csv';
  }

  function exportACCNT() {
  }

  function exportCUST() {
  }

  function exportTRANS() {
  }
}