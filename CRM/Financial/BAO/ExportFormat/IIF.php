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
class CRM_Financial_BAO_ExportFormat_IIF extends CRM_Financial_BAO_ExportFormat {

  // Tab character. Some people's editors replace tabs with spaces so I'm scared to use actual tabs.
  // Can't set it here using chr() because static. Same thing if a const. So it's set in constructor.
  static $SEPARATOR;

  // For this phase, we always output these records too so that there isn't data referenced in the journal entries that isn't defined anywhere.
  // Possibly in the future this could be selected by the user.
  public static $complementaryTables = array(
      'ACCNT',
      'CUST',
  );

  // This field is required. We use the grouping column in civicrm_option_value for the financial_account_type option group to map to the right code.
  // - So this variable below isn't actually used anywhere, but is good to keep here for reference.
  public static $accountTypes = array(
    'AP' => 'Accounts payable',
    'AR' => 'Accounts receivable',
    'BANK' => 'Checking or savings',
    'CCARD' => 'Credit card account',
    'COGS' => 'Cost of goods sold',
    'EQUITY' => 'Capital/Equity',
    'EXEXP' => 'Other expense',
    'EXINC' => 'Other income',
    'EXP' => 'Expense',
    'FIXASSET' => 'Fixed asset',
    'INC' => 'Income',
    'LTLIAB' => 'Long term liability',
    'NONPOSTING' => 'Non-posting account',
    'OASSET' => 'Other asset',
    'OCASSET' => 'Other current asset',
    'OCLIAB' => 'Other current liability',
  );

  /**
   * class constructor
   */
  function __construct() {
    parent::__construct();
    self::$SEPARATOR = chr(9);
  }

  function export( $exportParams ) {
    parent::export( $exportParams );

    foreach( self::$complementaryTables as $rct ) {
      $func = "export{$rct}";
      $this->$func();
    }

    // now do general journal entries
    $this->exportTRANS();

    $this->output();
  }

  function putFile($out) {
    $config = CRM_Core_Config::singleton();
    $fileName = $config->uploadDir.'Financial_Transactions_'.$this->_batchIds.'_'.date('YmdHis').'.'.$this->getFileExtension();
    $this->_downloadFile[] = $config->customFileUploadDir.CRM_Utils_File::cleanFileName(basename($fileName));
    $buffer = fopen($fileName, 'w');
    fwrite($buffer, $out);
    fclose($buffer);
    return $fileName;
  }

  function makeIIF($export) {
    // Keep running list of accounts and contacts used in this batch, since we need to
    // include those in the output. Only want to include ones used in the batch, not everything in the db,
    // since would increase the chance of messing up user's existing Quickbooks entries.
    foreach ($export as $batchId => $dao) {
      $accounts = $contacts = $journalEntries = $exportParams = array();
      $this->_batchIds = $batchId;
      while ($dao->fetch()) {
        // add to running list of accounts
        if (!empty($dao->from_account_id) && !isset($accounts[$dao->from_account_id])) {
          $accounts[$dao->from_account_id] = array(
            'name' => $this->format($dao->from_account_name),
            'account_code' => $this->format($dao->from_account_code),
            'description' => $this->format($dao->from_account_description),
            'type' => $this->format($dao->from_qb_account_type),
          );
        }
        if (!empty($dao->to_account_id) && !isset($accounts[$dao->to_account_id])) {
          $accounts[$dao->to_account_id] = array(
            'name' => $this->format($dao->to_account_name),
            'account_code' => $this->format($dao->to_account_code),
            'description' => $this->format($dao->to_account_description),
            'type' => $this->format($dao->to_qb_account_type),
          );
        }

        // add to running list of contacts
        if (!empty($dao->contact_from_id) && !isset($contacts[$dao->contact_from_id])) {
          $contacts[$dao->contact_from_id] = array(
            'name' => $this->format($dao->contact_from_name),
            'first_name' => $this->format($dao->contact_from_first_name),
            'last_name' => $this->format($dao->contact_from_last_name),
          );
        }

        if (!empty($dao->contact_to_id) && !isset($contacts[$dao->contact_to_id])) {
          $contacts[$dao->contact_to_id] = array(
            'name' => $this->format($dao->contact_to_name),
            'first_name' => $this->format($dao->contact_to_first_name),
            'last_name' => $this->format($dao->contact_to_last_name),
          );
        }

        // set up the journal entries for this financial trxn
        $journalEntries[$dao->financial_trxn_id] = array(
          'to_account' => array(
            'trxn_date' => $this->format( $dao->trxn_date, 'date' ),
            'trxn_id' =>  $this->format( $dao->trxn_id ),
            'account_name' => $this->format( $dao->to_account_name ),
            'amount' => $this->format( $dao->debit_total_amount ),
            'contact_name' => $this->format( $dao->contact_to_name ),
            'payment_instrument' => $this->format( $dao->payment_instrument ),
            'check_number' => $this->format( $dao->check_number ),
          ),
          'splits' => array(),
        );

        /*
         * splits has two possibilities depending on FROM account
         */
        if (empty($dao->from_account_id)) {
          // In this case, split records need to use the individual financial_item account for each item in the trxn
          $item_sql = "SELECT
            fa.id as account_id,
            fa.name as account_name,
            fa.accounting_code as account_code,
            fa.description as account_description,
            fi.id as financial_item_id,
            ft.currency as currency,
            cov.label as payment_instrument,
            ft.check_number as check_number,
            fi.transaction_date,
            fi.amount,
            ov.grouping as qb_account_type,
            contact.id as contact_id,
            contact.display_name as contact_name,
            contact.first_name as contact_first_name,
            contact.last_name as contact_last_name
            FROM civicrm_entity_financial_trxn eft
            LEFT JOIN civicrm_financial_item fi ON eft.entity_id = fi.id
            LEFT JOIN civicrm_financial_trxn ft ON ft.id = eft.financial_trxn_id
            LEFT JOIN civicrm_option_group cog ON cog.name = 'payment_instrument'
            LEFT JOIN civicrm_option_value cov ON (cov.value = ft.payment_instrument_id AND cov.option_group_id = cog.id)
            LEFT JOIN civicrm_financial_account fa ON fa.id = fi.financial_account_id
            LEFT JOIN civicrm_option_group og ON og.name = 'financial_account_type'
            LEFT JOIN civicrm_option_value ov ON (ov.option_group_id = og.id AND ov.value = fa.financial_account_type_id)
            LEFT JOIN civicrm_contact contact ON contact.id = fi.contact_id
            WHERE eft.entity_table = 'civicrm_financial_item'
            AND eft.financial_trxn_id = %1";

          $itemParams = array( 1 => array( $dao->financial_trxn_id, 'Integer' ) );

          $itemDAO = CRM_Core_DAO::executeQuery( $item_sql, $itemParams );
          while ($itemDAO->fetch()) {
            // add to running list of accounts
            if (!empty($itemDAO->account_id) && !isset($accounts[$itemDAO->account_id])) {
              $accounts[$itemDAO->account_id] = array(
                'name' => $this->format( $itemDAO->account_name ),
                'account_code' => $this->format( $itemDAO->account_code ),
                'description' => $this->format( $itemDAO->account_description ),
                'type' => $this->format( $itemDAO->qb_account_type ),
              );
            }

            if (!empty($itemDAO->contact_id) && !isset($contacts[$itemDAO->contact_id])) {
              $contacts[$itemDAO->contact_id] = array(
                'name' => $this->format( $itemDAO->contact_name ),
                'first_name' => $this->format( $itemDAO->contact_first_name ),
                'last_name' => $this->format( $itemDAO->contact_last_name ),
              );
            }

            // add split line for this item
            $journalEntries[$dao->financial_trxn_id]['splits'][$itemDAO->financial_item_id] = array(
              'trxn_date' => $this->format( $itemDAO->transaction_date, 'date' ),
              'spl_id' => $this->format( $itemDAO->financial_item_id ),
              'account_name' => $this->format( $itemDAO->account_name ),
              'amount' => $this->format( (-1) * $itemDAO->amount ),
              'contact_name' => $this->format( $itemDAO->contact_name ),
              'payment_instrument' => $this->format( $itemDAO->payment_instrument ),
              'check_number' => $this->format( $itemDAO->check_number ),
            );
          } // end items loop
          $itemDAO->free();
        }
        else {
          // In this case, split record just uses the FROM account from the trxn, and there's only one record here
          $journalEntries[$dao->financial_trxn_id]['splits'][] = array(
            'trxn_date' => $this->format( $dao->trxn_date, 'date' ),
            'account_name' => $this->format( $dao->from_account_name ),
            'amount' => $this->format( (-1) * $dao->total_amount ),
            'contact_name' => $this->format( $dao->contact_from_name ),
            'payment_instrument' => $this->format( $itemDAO->payment_instrument ),
            'check_number' => $this->format( $itemDAO->check_number ),
            'currency' => $this->format( $itemDAO->currency ),
          );
        }
      }
      $exportParams = array(
        'accounts' => $accounts,
        'contacts' => $contacts,
        'journalEntries' => $journalEntries,
      );
      self::export($exportParams);
    }
    parent::initiateDownload();
  }

  function exportACCNT() {
    self::assign( 'accounts', $this->_exportParams['accounts'] );
  }

  function exportCUST() {
    self::assign( 'contacts', $this->_exportParams['contacts'] );
  }

  function exportTRANS() {
    self::assign( 'journalEntries', $this->_exportParams['journalEntries'] );
  }

  function getMimeType() {
    return 'application/octet-stream';
  }

  function getFileExtension() {
    return 'iif';
  }

  function getTemplateFileName() {
    return 'CRM/Financial/ExportFormat/IIF.tpl';
  }

  /*
   * $s the input string
   * $type can be string, date, or notepad
   */
  static function format($s, $type = 'string') {
    // If I remember right there's a couple things:
    // NOTEPAD field needs to be surrounded by quotes and then get rid of double quotes inside, also newlines should be literal \n, and ditch any ascii 0x0d's.
    // Date handling has changed over the years. It used to only understand mm/dd/yy but I think now it might depend on your OS settings. Sometimes mm/dd/yyyy works but sometimes it wants yyyy/mm/dd, at least where I had used it.
    // In all cases need to do something with tabs in the input.

    $s1 = str_replace( self::$SEPARATOR, '\t', $s );
    switch( $type ) {
      case 'date':
        $sout = date( 'Y/m/d', strtotime( $s1 ) );
        break;
      case 'string':
      case 'notepad':
        $s2 = str_replace( "\n", '\n', $s1 );
        $s3 = str_replace( "\r", '', $s2 );
        $s4 = str_replace( '"', "'", $s3 );
        if ( $type == 'notepad' ) {
          $sout = '"' . $s4 . '"';
        } else {
          $sout = $s4;
        }
        break;
    }

    return $sout;
  }
}