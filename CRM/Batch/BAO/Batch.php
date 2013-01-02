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
 *
 */
class CRM_Batch_BAO_Batch extends CRM_Batch_DAO_Batch {

  /**
   * Cache for the current batch object
   */
  static $_batch = NULL;

  /**
   * Not sure this is the best way to do this. Depends on how exportFinancialBatch() below gets called.
   * Maybe a parameter to that function is better.
   */
  static $_exportFormat = NULL;

  /**
   * Create a new batch
   *
   * @return batch array
   * @access public
   */
  static function create(&$params, $ids = NULL, $context = NULL) {
    if (!CRM_Utils_Array::value('id', $params)) {
      $params['name'] = CRM_Utils_String::titleToVar($params['title']);
    }

    $batch = new CRM_Batch_DAO_Batch();
    $batch->copyValues($params);
    if ($context == 'financialBatch' && CRM_Utils_Array::value('batchID', $ids)) {
      $batch->id = $ids['batchID'];
    }
    $batch->save();

    return $batch;
  }

  /**
   * Retrieve the information about the batch
   *
   * @param array $params   (reference ) an assoc array of name/value pairs
   * @param array $defaults (reference ) an assoc array to hold the flattened values
   *
   * @return array CRM_Batch_BAO_Batch object on success, null otherwise
   * @access public
   * @static
   */
  static function retrieve(&$params, &$defaults) {
    $batch = new CRM_Batch_DAO_Batch();
    $batch->copyValues($params);
    if ($batch->find(TRUE)) {
      CRM_Core_DAO::storeValues($batch, $defaults);
      return $batch;
    }
    return NULL;
  }

  /**
   * Get profile id associated with the batch type
   *
   * @param int   $batchTypeId batch type id
   *
   * @return int  $profileId   profile id
   * @static
   */
  static function getProfileId($batchTypeId) {
    //retrieve the profile specific to batch type
    switch ($batchTypeId) {
      case 1:
        //batch profile used for contribution
        $profileName = "contribution_batch_entry";
        break;

      case 2:
        //batch profile used for memberships
        $profileName = "membership_batch_entry";
    }

    // get and return the profile id
    return CRM_Core_DAO::getFieldValue('CRM_Core_BAO_UFGroup', $profileName, 'id', 'name');
  }

  /**
   * generate batch name
   *
   * @return batch name
   * @static
   */
  static function generateBatchName() {
    $sql = "SELECT max(id) FROM civicrm_batch";
    $batchNo = CRM_Core_DAO::singleValueQuery($sql) + 1;
    return ts('Batch %1', array(1 => $batchNo)) . ': ' . date('Y-m-d');
  }

  /**
   * create entity batch entry
   * @param array $params associated array
   * @return batch array
   * @access public
   */
  static function addBatchEntity(&$params) {
    $entityBatch = new CRM_Batch_DAO_EntityBatch();
    $entityBatch->copyValues($params);
    $entityBatch->save();
    return $entityBatch;
  }

  /**
   * Remove entries from entity batch
   * @param array $params associated array
   * @return object CRM_Batch_DAO_EntityBatch
   */
  static function removeBatchEntity($params) {
    $entityBatch = new CRM_Batch_DAO_EntityBatch();
    $entityBatch->copyValues($params);
    $entityBatch->delete();
    return $entityBatch;
  }

  /**
   * function to delete batch entry
   *
   * @param int $batchId batch id
   *
   * @return void
   * @access public
   */
  static function deleteBatch($batchId) {
    //delete batch entries from cache
    $cacheKeyString = CRM_Batch_BAO_Batch::getCacheKeyForBatch($batchId);
    CRM_Core_BAO_Cache::deleteGroup('batch entry', $cacheKeyString, FALSE);

    // delete entry from batch table
    $batch = new CRM_Batch_DAO_Batch();
    $batch->id = $batchId;
    $batch->delete();
    return true;
  }

  /**
   * function to get cachekey for batch
   *
   * @param int $batchId batch id
   *
   * @retun string $cacheString
   * @static
   * @access public
   */
  static function getCacheKeyForBatch($batchId) {
    return "batch-entry-{$batchId}";
  }

  /**
   * This function is a wrapper for ajax batch selector
   *
   * @param  array   $params associated array for params record id.
   *
   * @return array   $batchList associated array of batch list
   * @access public
   */
  public function getBatchListSelector(&$params) {
    // format the params
    $params['offset'] = ($params['page'] - 1) * $params['rp'];
    $params['rowCount'] = $params['rp'];
    $params['sort'] = CRM_Utils_Array::value('sortBy', $params);

    // get batches
    $batches = self::getBatchList($params);

    // get batch totals for open batches
    $fetchTotals = array();
    if ($params['context'] == 'financialBatch') {
      foreach ($batches as $id => $batch) {
        if ($batch['status_id'] == 1) {
          $fetchTotals[] = $id;
        }
      }
    }
    $totals = self::batchTotals($fetchTotals);

    // add count
    $params['total'] = self::getBatchCount($params);

    // format params and add links
    $batchList = array();

    foreach ($batches as $id => $value) {
      $batch = array();
      if (($params['context'] == 'financialBatch')) {
        $batch['check'] = $value['check'];
      }
      $batch['batch_name'] = $value['title'];
      $batch['payment_instrument'] = $value['payment_instrument'];
      $batch['item_count'] = $value['item_count'];
      $batch['total'] = $value['total'] ? CRM_Utils_Money::format($value['total']) : '';
      // Compare totals with actuals
      if (isset($totals[$id])) {
        $batch['item_count'] = self::displayTotals($totals[$id]['item_count'], $value['item_count']);
        $batch['total'] = self::displayTotals(CRM_Utils_Money::format($totals[$id]['total']), $batch['total']);
      }
      $batch['status'] = $value['batch_status'];
      $batch['created_by'] = $value['created_by'];
      $batch['links'] = $value['action'];
      $batchList[$id] = $batch;
    }
    return $batchList;
  }

  /**
   * Get list of batches
   *
   * @param  array   $params associated array for params
   * @access public
   */
  static function getBatchList(&$params) {
    $whereClause = self::whereClause($params);

    if (!empty($params['rowCount']) && is_numeric($params['rowCount'])
      && is_numeric($params['offset']) && $params['rowCount'] > 0
    ) {
      $limit = " LIMIT {$params['offset']}, {$params['rowCount']} ";
    }

    $orderBy = ' ORDER BY batch.id desc';
    if (!empty($params['sort'])) {
      $orderBy = ' ORDER BY ' . $params['sort'];
    }

    $query = "
      SELECT batch.*, c.sort_name created_by
      FROM  civicrm_batch batch
      INNER JOIN civicrm_contact c ON batch.created_id = c.id
    WHERE {$whereClause}
    {$orderBy}
    {$limit}";

    $object = CRM_Core_DAO::executeQuery($query, $params, TRUE, 'CRM_Batch_DAO_Batch');

    $links = isset($params['context']) ? self::links($params['context']) : self::links();

    $batchTypes = CRM_Core_PseudoConstant::getBatchType();
    $batchStatus = CRM_Core_PseudoConstant::getBatchStatus();
    $paymentInstrument = CRM_Contribute_PseudoConstant::paymentInstrument();

    $results = array();
    while ($object->fetch()) {
      $values = array();
      $newLinks = $links;
      CRM_Core_DAO::storeValues($object, $values);
      $action = array_sum(array_keys($newLinks));

      if ($values['status_id'] == 2 && $params['context'] != 'financialBatch') {
        $newLinks = array();
      }
      elseif ($params['context'] == 'financialBatch') {
        $values['check'] = "<input type='checkbox' id='check_".$object->id."' name='check_".$object->id."' value='1'  data-status_id='".$values['status_id']."' class='crm-batch-select'></input>";

        switch ($values['status_id']) {
          case '1':
            CRM_Utils_Array::remove($newLinks, 'reopen', 'download');
            break;
          case '2':
            CRM_Utils_Array::remove($newLinks, 'close', 'edit', 'download');
            break;
          case '5':
            CRM_Utils_Array::remove($newLinks, 'close', 'edit', 'reopen');
        }
      }

      $values['batch_type'] = $batchTypes[$values['type_id']];
      $values['batch_status'] = $batchStatus[$values['status_id']];
      $values['created_by'] = $object->created_by;
      $values['payment_instrument'] = $object->payment_instrument_id ? $paymentInstrument[$object->payment_instrument_id] : '';

      $values['action'] = CRM_Core_Action::formLink(
        $newLinks,
        $action,
        array('id' => $object->id, 'status' => $values['status_id'])
      );
      $results[$object->id] = $values;
    }

    return $results;
  }

  /**
   * Get count of batches
   *
   * @param  array   $params associated array for params
   * @access public
   */
  static function getBatchCount(&$params) {
    $args = array();
    $whereClause = self::whereClause($params, $args);
    $query = " SELECT COUNT(*) FROM civicrm_batch batch
      INNER JOIN civicrm_contact c ON batch.created_id = c.id
      WHERE {$whereClause}";
    return CRM_Core_DAO::singleValueQuery($query);
  }

  /**
   * Format where clause for getting lists of batches
   *
   * @param  array   $params associated array for params
   * @access public
   */
  function whereClause($params) {
    $clauses = array();
    // Exclude data-entry batches
    if (empty($params['status_id'])) {
      $clauses[] = 'batch.status_id <> 3';
    }

    $fields = array(
      'title' => 'String',
      'sort_name' => 'String',
      'status_id' => 'Integer',
      'payment_instrument_id' => 'Integer',
      'item_count' => 'Integer',
      'total' => 'Float',
    );

    foreach ($fields as $field => $type) {
      $table = $field == 'sort_name' ? 'c' : 'batch';
      if (isset($params[$field])) {
        $value = CRM_Utils_Type::escape($params[$field], $type, FALSE);
        if ($value && $type == 'String') {
          $clauses[] = "$table.$field LIKE '%$value%'";
        }
        elseif ($value && $type == 'Float') {
          $clauses[] = "$table.$field = '$value'";
        }
        elseif ($value) {
          $clauses[] = "$table.$field = $value";
        }
      }
    }
    return $clauses ? implode(' AND ', $clauses) : '1';
  }

  /**
   * Function to define action links
   *
   * @return array $links array of action links
   * @access public
   */
  function links($context = NULL) {
    if ($context == 'financialBatch') {
      $links = array(
        'transaction' =>  array(
          'name'  => ts('Transactions'),
          'url'   => 'civicrm/batchtransaction',
          'qs'    => 'reset=1&bid=%%id%%',
          'title' => ts('View/Add Transactions to Batch'),
        ),
        'edit' =>    array(
          'name'  => ts('Edit'),
          'url'   => 'civicrm/financial/batch',
          'qs'    => 'reset=1&action=update&id=%%id%%',
          'title' => ts('Edit Batch'),
        ),
        'close' =>   array(
          'name'  => ts('Close'),
          'title' => ts('Close Batch'),
          'url'   => '#',
          'extra' => 'rel="close"',
        ),
        'export' =>  array(
          'name'  => ts('Export'),
          'url'   => 'civicrm/financial/batch/export',
          'qs'    => 'reset=1&id=%%id%%',
          'title' => ts('Export Batch'),
        ),
        'reopen' =>  array(
          'name'  => ts('ReOpen'),
          'title' => ts('ReOpen Batch'),
          'url'   => '#',
          'extra' => 'rel="reopen"',
        ),
        'delete' =>  array(
          'name'  => ts('Delete'),
          'title' => ts('Delete Batch'),
          'url'   => '#',
          'extra' => 'rel="delete"',
        ),
        'download' => array(
          'name'  => ts('Download'),
          'url'   => 'civicrm/file',
          'qs'    => 'reset=1&id=%%fid%%&eid=%%id%%',
          'title' => ts('Download Batch'),
        )
      );
    }
    else {
      $links = array(
        CRM_Core_Action::COPY => array(
          'name' => ts('Enter records'),
          'url' => 'civicrm/batch/entry',
          'qs' => 'id=%%id%%&reset=1',
          'title' => ts('Batch Data Entry'),
        ),
        CRM_Core_Action::UPDATE => array(
          'name' => ts('Edit'),
          'url' => 'civicrm/batch',
          'qs' => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Batch'),
        ),
        CRM_Core_Action::DELETE => array(
          'name' => ts('Delete'),
          'url' => 'civicrm/batch',
          'qs' => 'action=delete&id=%%id%%',
          'title' => ts('Delete Batch'),
        )
      );
    }
    return $links;
  }

  /**
   * function to get batch list
   *
   * @return array array of batches
   */
  static function getBatches() {
    $query = 'SELECT id, title
      FROM civicrm_batch
      WHERE type_id IN (1,2)
      AND status_id = 2
      ORDER BY id DESC';

    $batches = array();
    $dao = CRM_Core_DAO::executeQuery($query);
    while ( $dao->fetch( ) ) {
      $batches[$dao->id] = $dao->title;
    }
    return $batches;
  }

  /**
   * Format table headers
   *
   * @param array $values
   * @return array
   */
  function formatHeaders($values) {
    $arrayKeys = array_keys($values);
    foreach ($values[$arrayKeys[0]] as $title => $value) {
      $headers[] = $title;
    }
    return $headers;
  }

  /**
   * Calculate sum of all entries in a batch
   * Used to validate and update item_count and total when closing an accounting batch
   *
   * @param array $batchIds
   * @return array
   */
  static function batchTotals($batchIds) {
    $totals = array_fill_keys($batchIds, array('item_count' => 0, 'total' => 0));
    if ($batchIds) {
      $sql = "SELECT eb.batch_id, COUNT(tx.id) AS item_count, SUM(tx.total_amount) AS total
      FROM civicrm_entity_batch eb
      INNER JOIN civicrm_financial_trxn tx ON tx.id = eb.entity_id AND eb.entity_table = 'civicrm_financial_trxn'
      WHERE eb.batch_id IN (" . implode(',', $batchIds) . ")
      GROUP BY eb.batch_id";
      $dao = CRM_Core_DAO::executeQuery($sql);
      while ($dao->fetch()) {
        $totals[$dao->batch_id] = (array) $dao;
      }
      $dao->free();
    }
    return $totals;
  }

  /**
   * Format markup for comparing two totals
   *
   * @param $actual: calculated total
   * @param $expected: user-entered total
   * @return array
   */
  static function displayTotals($actual, $expected) {
    $class = 'actual-value';
    if ($expected && $expected != $actual) {
      $class .= ' crm-error';
    }
    $actualTitle = ts('Current Total');
    $output = "<span class='$class' title='$actualTitle'>$actual</span>";
    if ($expected) {
      $expectedTitle = ts('Expected Total');
      $output .= " / <span class='expected-value' title='$expectedTitle'>$expected</span>";
    }
    return $output;
  }

  /*
   * @see http://wiki.civicrm.org/confluence/display/CRM/CiviAccounts+Specifications+-++Batches#CiviAccountsSpecifications-Batches-%C2%A0Overviewofimplementation
   */
  static function exportFinancialBatch($batchIds, $exportFormat) {
    if (empty($batchIds)) {
      CRM_Core_Error::fatal(ts('No batches were selected.'));
      return;
    }

    self::$_exportFormat = $exportFormat;

    // Instantiate appropriate exporter based on user-selected format.
    $exporterClass = "CRM_Financial_BAO_ExportFormat_" . self::$_exportFormat;
    if ( class_exists( $exporterClass ) ) {
      $exporter = new $exporterClass();
    }
    else {
      CRM_Core_Error::fatal("Could not locate exporter: $exporterClass");
    }

    $batchIds = implode(',', $batchIds);

    $sql = "SELECT
      ft.id as financial_trxn_id,
      ft.trxn_date,
      ft.total_amount as debit_total_amount,
      eft.amount as amount,
      ft.currency as currency,
      ft.trxn_id AS trxn_id,
      c.source as source,
      cov.label as payment_instrument,
      ft.check_number,
      fa_from.id as from_account_id,
      fa_from.name as from_account_name,
      fa_from.accounting_code as from_account_code,
      fa_from.financial_account_type_id as from_account_type_id,
      fa_from.description as from_account_description,
      ov_from.grouping as from_qb_account_type,
      fa_to.id as to_account_id,
      fa_to.name as to_account_name,
      fac.accounting_code AS credit_account,
      fac.name AS credit_account_name,
      fi.description AS item_description,
      fa_to.accounting_code as to_account_code,
      fa_to.financial_account_type_id as to_account_type_id,
      fa_to.description as to_account_description,
      ov_to.grouping as to_qb_account_type,
      contact_from.id as contact_from_id,
      contact_from.display_name as contact_from_name,
      contact_from.first_name as contact_from_first_name,
      contact_from.last_name as contact_from_last_name,
      contact_to.id as contact_to_id,
      contact_to.display_name as contact_to_name,
      contact_to.first_name as contact_to_first_name,
      contact_to.last_name as contact_to_last_name
      FROM civicrm_entity_batch eb
      LEFT JOIN civicrm_financial_trxn ft ON (eb.entity_id = ft.id AND eb.entity_table = 'civicrm_financial_trxn')
      LEFT JOIN civicrm_financial_account fa_from ON fa_from.id = ft.from_financial_account_id
      LEFT JOIN civicrm_option_group cog ON cog.name = 'payment_instrument'
      LEFT JOIN civicrm_entity_financial_trxn eft ON eft.financial_trxn_id  = ft.id AND eft.entity_table = 'civicrm_financial_item'
      LEFT JOIN civicrm_entity_financial_trxn eftc ON eftc.financial_trxn_id  = ft.id AND eftc.entity_table = 'civicrm_contribution'
      LEFT JOIN civicrm_contribution c ON c.id = eftc.entity_id
      LEFT JOIN civicrm_financial_item fi ON fi.id = eft.entity_id
      LEFT JOIN civicrm_financial_account fac ON fac.id = fi.financial_account_id
      LEFT JOIN civicrm_option_value cov ON (cov.value = ft.payment_instrument_id AND cov.option_group_id = cog.id)
      LEFT JOIN civicrm_financial_account fa_to ON fa_to.id = ft.to_financial_account_id
      LEFT JOIN civicrm_option_group og_from ON og_from.name = 'financial_account_type'
      LEFT JOIN civicrm_option_value ov_from ON (ov_from.option_group_id = og_from.id AND ov_from.value = fa_from.financial_account_type_id)
      LEFT JOIN civicrm_option_group og_to ON og_to.name = 'financial_account_type'
      LEFT JOIN civicrm_option_value ov_to ON (ov_to.option_group_id = og_to.id AND ov_to.value = fa_to.financial_account_type_id)
      LEFT JOIN civicrm_contact contact_from ON contact_from.id = fa_from.contact_id
      LEFT JOIN civicrm_contact contact_to ON contact_to.id = fa_to.contact_id
      WHERE eb.batch_id IN ( %1 )";

    $params = array(1 => array($batchIds, 'String'));

    // Keep running list of accounts and contacts used in this batch, since we need to
    // include those in the output. Only want to include ones used in the batch, not everything in the db,
    // since would increase the chance of messing up user's existing Quickbooks entries.
    $accounts = array();
    $contacts = array();

    $journalEntries = array();

    $dao = CRM_Core_DAO::executeQuery( $sql, $params );

    if (self::$_exportFormat == "CSV") {
      while ($dao->fetch()) {
        $financialItems[$dao->financial_trxn_id]['Transaction Date'] = $dao->trxn_date;
        $financialItems[$dao->financial_trxn_id]['Debit Account'] = $dao->to_account_code;
        $financialItems[$dao->financial_trxn_id]['Debit Account Name'] = $dao->to_account_name;
        $financialItems[$dao->financial_trxn_id]['Debit Account Amount (Unsplit)'] = $dao->debit_total_amount;
        $financialItems[$dao->financial_trxn_id]['Transaction ID (Unsplit)'] = $dao->trxn_id;
        $financialItems[$dao->financial_trxn_id]['Payment Instrument'] = $dao->payment_instrument;
        $financialItems[$dao->financial_trxn_id]['Check Number'] = $dao->check_number;
        $financialItems[$dao->financial_trxn_id]['Source'] = $dao->source;
        $financialItems[$dao->financial_trxn_id]['Currency'] = $dao->currency;
        $financialItems[$dao->financial_trxn_id]['Amount'] = $dao->amount;
        $financialItems[$dao->financial_trxn_id]['Credit Account'] = $dao->credit_account;
        $financialItems[$dao->financial_trxn_id]['Credit Account Name'] = $dao->credit_account_name;
        $financialItems[$dao->financial_trxn_id]['Item Description'] = $dao->item_description;
      }
      $financialItems['headers'] = self::formatHeaders($financialItems);
    }
    else {
      while ( $dao->fetch() ) {
        // add to running list of accounts
        if ( !empty( $dao->from_account_id ) && !isset( $accounts[$dao->from_account_id] ) ) {
          $accounts[$dao->from_account_id] = array(
            'name' => $exporter->format( $dao->from_account_name ),
            'account_code' => $exporter->format( $dao->from_account_code ),
            'description' => $exporter->format( $dao->from_account_description ),
            'type' => $exporter->format( $dao->from_qb_account_type ),
           );
        }
        if ( !empty( $dao->to_account_id ) && !isset( $accounts[$dao->to_account_id] ) ) {
          $accounts[$dao->to_account_id] = array(
            'name' => $exporter->format( $dao->to_account_name ),
            'account_code' => $exporter->format( $dao->to_account_code ),
            'description' => $exporter->format( $dao->to_account_description ),
            'type' => $exporter->format( $dao->to_qb_account_type ),
          );
        }

        // add to running list of contacts
        if ( !empty( $dao->contact_from_id ) && !isset( $contacts[$dao->contact_from_id] ) ) {
          $contacts[$dao->contact_from_id] = array(
            'name' => $exporter->format( $dao->contact_from_name ),
            'first_name' => $exporter->format( $dao->contact_from_first_name ),
            'last_name' => $exporter->format( $dao->contact_from_last_name ),
          );
        }

        if ( !empty( $dao->contact_to_id ) && !isset( $contacts[$dao->contact_to_id] ) ) {
          $contacts[$dao->contact_to_id] = array(
            'name' => $exporter->format( $dao->contact_to_name ),
            'first_name' => $exporter->format( $dao->contact_to_first_name ),
            'last_name' => $exporter->format( $dao->contact_to_last_name ),
          );
        }

        // set up the journal entries for this financial trxn
        $journalEntries[$dao->financial_trxn_id] = array(
          'to_account' => array(
            'trxn_date' => $exporter->format( $dao->trxn_date, 'date' ),
            'account_name' => $exporter->format( $dao->to_account_name ),
            'amount' => $exporter->format( $dao->debit_total_amount ),
            'contact_name' => $exporter->format( $dao->contact_to_name ),
            'payment_instrument' => $exporter->format( $dao->payment_instrument ),
            'check_number' => $exporter->format( $dao->check_number ),
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

          $item_params = array( 1 => array( $dao->financial_trxn_id, 'Integer' ) );

          $item_dao = CRM_Core_DAO::executeQuery( $item_sql, $item_params );
          while ($item_dao->fetch()) {
            // add to running list of accounts
            if (!empty($item_dao->account_id) && !isset($accounts[$item_dao->account_id])) {
              $accounts[$item_dao->account_id] = array(
                'name' => $exporter->format( $item_dao->account_name ),
                'account_code' => $exporter->format( $item_dao->account_code ),
                'description' => $exporter->format( $item_dao->account_description ),
                'type' => $exporter->format( $item_dao->qb_account_type ),
              );
            }

            if (!empty($item_dao->contact_id) && !isset($contacts[$item_dao->contact_id])) {
              $contacts[$item_dao->contact_id] = array(
                'name' => $exporter->format( $item_dao->contact_name ),
                'first_name' => $exporter->format( $item_dao->contact_first_name ),
                'last_name' => $exporter->format( $item_dao->contact_last_name ),
              );
            }

            // add split line for this item
            $journalEntries[$dao->financial_trxn_id]['splits'][$item_dao->financial_item_id] = array(
              'trxn_date' => $exporter->format( $item_dao->transaction_date, 'date' ),
              'account_name' => $exporter->format( $item_dao->account_name ),
              'amount' => $exporter->format( (-1) * $item_dao->amount ),
              'contact_name' => $exporter->format( $item_dao->contact_name ),
              'payment_instrument' => $exporter->format( $item_dao->payment_instrument ),
              'check_number' => $exporter->format( $item_dao->check_number ),
            );
          } // end items loop
          $item_dao->free();
        }
        else {
          // In this case, split record just uses the FROM account from the trxn, and there's only one record here
          $journalEntries[$dao->financial_trxn_id]['splits'][] = array(
            'trxn_date' => $exporter->format( $dao->trxn_date, 'date' ),
            'account_name' => $exporter->format( $dao->from_account_name ),
            'amount' => $exporter->format( (-1) * $dao->total_amount ),
            'contact_name' => $exporter->format( $dao->contact_from_name ),
            'payment_instrument' => $exporter->format( $item_dao->payment_instrument ),
            'check_number' => $exporter->format( $item_dao->check_number ),
            'currency' => $exporter->format( $item_dao->currency ),
          );
        }
      }
      $dao->free();
    }

    $exportParams = array(
      'accounts' => $accounts,
      'contacts' => $contacts,
      'journalEntries' => $journalEntries,
      'batchIds' => $batchIds,
      'csvExport' => isset($financialItems) ? $financialItems : NULL,
    );

    $exporter->export( $exportParams );
  }

  static function closeReOpen($batchIds = array(), $status) {
    $batchStatus = CRM_Core_PseudoConstant::accountOptionValues( 'batch_status' );
    $params['status_id'] = CRM_Utils_Array::key( $status, $batchStatus );
    $session = CRM_Core_Session::singleton( );
    $params['modified_date'] = date('YmdHis');
    $params['modified_id'] = $session->get( 'userID' );
    foreach ($batchIds as $key => $value) {
      $params['id'] = $ids['batchID'] = $value;
      self::create($params, $ids);
    }
    $url = CRM_Utils_System::url('civicrm/financial/financialbatches',"reset=1&batchStatus={$params['status_id']}");
    CRM_Utils_System::redirect($url);
  }

  /**
   * Function to retrieve financial items assigned for a batch
   *
   * @param int $entityID
   * @param array $returnValues
   * @param null $notPresent
   * @param null $params
   * @return Object
   */
  static function getBatchFinancialItems($entityID, $returnValues, $notPresent = NULL, $params = NULL, $getCount = FALSE) {
    if (!$getCount) {
      if (!empty($params['rowCount']) &&
        $params['rowCount'] > 0
      ) {
        $limit = " LIMIT {$params['offset']}, {$params['rowCount']} ";
      }
    }
    // action is taken depending upon the mode
    $select = 'civicrm_financial_trxn.id ';
    if (!empty( $returnValues)) {
      $select .= " , ".implode(' , ', $returnValues);
    }

    $orderBy = " ORDER BY civicrm_financial_trxn.id";
    if (CRM_Utils_Array::value('sort', $params)) {
      $orderBy = ' ORDER BY ' . CRM_Utils_Array::value('sort', $params);
    }

    $from = "civicrm_financial_trxn
LEFT JOIN civicrm_entity_financial_trxn ON civicrm_entity_financial_trxn.financial_trxn_id = civicrm_financial_trxn.id
LEFT JOIN civicrm_entity_batch ON civicrm_entity_batch.entity_id = civicrm_financial_trxn.id
LEFT JOIN civicrm_financial_item ON civicrm_entity_financial_trxn.entity_id = civicrm_financial_item.id
LEFT JOIN civicrm_financial_account ON civicrm_financial_account.id = civicrm_financial_item.financial_account_id
LEFT JOIN civicrm_contribution ON civicrm_contribution.id = civicrm_entity_financial_trxn.entity_id
LEFT JOIN civicrm_contact contact_a ON contact_a.id = civicrm_contribution.contact_id
LEFT JOIN civicrm_contribution_soft ON civicrm_contribution_soft.contribution_id = civicrm_contribution.id
";

    $searchFields =
      array(
        'sort_name',
        'financial_type_id',
        'contribution_page_id',
        'contribution_payment_instrument_id',
        'contribution_transaction_id',
        'contribution_source',
        'contribution_currency_type',
        'contribution_pay_later',
        'contribution_recurring',
        'contribution_test',
        'contribution_thankyou_date_is_not_null',
        'contribution_receipt_date_is_not_null',
        'contribution_pcp_made_through_id',
        'contribution_pcp_display_in_roll',
        'contribution_date_relative',
        'contribution_amount_low',
        'contribution_amount_high',
        'contribution_in_honor_of',
        'contact_tags',
        'group',
        'contribution_date_relative',
        'contribution_date_high',
        'contribution_date_low',
      );
    $values = array();
    foreach ($searchFields as $field) {
      if (isset($params[$field])) {
        $values[$field] = $params[$field];
        if ($field == 'sort_name') {
          $from .= " LEFT JOIN civicrm_contact contact_b ON contact_b.id = civicrm_contribution.contact_id
          LEFT JOIN civicrm_email ON contact_b.id = civicrm_email.contact_id";
        }
        if ($field == 'contribution_in_honor_of') {
          $from .= " LEFT JOIN civicrm_contact contact_b ON contact_b.id = civicrm_contribution.contact_id";
        }
        if ($field == 'contact_tags') {
          $from .= " LEFT JOIN civicrm_entity_tag `civicrm_entity_tag-{$params[$field]}` ON `civicrm_entity_tag-{$params[$field]}`.entity_id = contact_a.id";
        }
        if ($field == 'group') {
          $from .= " LEFT JOIN civicrm_group_contact `civicrm_group_contact-{$params[$field]}` ON contact_a.id = `civicrm_group_contact-{$params[$field]}`.contact_id ";
        }
        if ($field == 'contribution_date_relative') {
          $relativeDate = explode('.', $params[$field]);
          $date = CRM_Utils_Date::relativeToAbsolute($relativeDate[0], $relativeDate[1]);
          $values['contribution_date_low'] = $date['from'];
          $values['contribution_date_high'] = $date['to'];
        }
        $searchParams = CRM_Contact_BAO_Query::convertFormValues($values);
        $query = new CRM_Contact_BAO_Query($searchParams,
          CRM_Contribute_BAO_Query::defaultReturnProperties(CRM_Contact_BAO_Query::MODE_CONTRIBUTE,
            FALSE
          ),NULL, FALSE, FALSE,CRM_Contact_BAO_Query::MODE_CONTRIBUTE
        );
        if ($field == 'contribution_date_high' || $field == 'contribution_date_low') {
          $query->dateQueryBuilder($params[$field], 'civicrm_contribution', 'contribution_date', 'receive_date', 'Contribution Date');
        }
      }
    }
    if (!empty($query->_where[0])) {
      $where = implode(' AND ', $query->_where[0])." AND civicrm_financial_trxn.status_id = 1
      AND civicrm_entity_batch.batch_id IS NULL
      AND civicrm_entity_financial_trxn.entity_table = 'civicrm_contribution'";
      $searchValue = TRUE;
    }
    else {
      $searchValue = FALSE;
    }

    if (!$searchValue) {
      if (!$notPresent) {
        $where =  " ( civicrm_entity_batch.batch_id = {$entityID}
        AND civicrm_entity_batch.entity_table = 'civicrm_financial_trxn'
        AND civicrm_entity_financial_trxn.entity_table = 'civicrm_contribution') ";
      }
      else {
        $where = " ( civicrm_financial_trxn.status_id = 1
        AND civicrm_entity_batch.batch_id IS NULL
        AND civicrm_entity_financial_trxn.entity_table = 'civicrm_contribution')";
      }
    }

    $sql = "SELECT {$select}
FROM {$from}
WHERE {$where}
{$orderBy}
{$limit}
";
 
    $result = CRM_Core_DAO::executeQuery($sql);
    return $result;
  }

  /**
   * function to get batch names
   * @param string $batchIds
   *
   * @return array array of batches
   */
  static function getBatcheNames($batchIds) {
    $query = 'SELECT id, title
      FROM civicrm_batch
      WHERE id IN ('.$batchIds.')';

    $batches = array();
    $dao = CRM_Core_DAO::executeQuery($query);
    while ( $dao->fetch( ) ) {
      $batches[$dao->id] = $dao->title;
    }
    return $batches;
  }
}