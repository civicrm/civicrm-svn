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
  static $_exportFormat = null;

  /**
   * Create a new batch
   *
   * @return batch array
   * @access public
   */
  static function create(&$params, $ids = null, $context = null) {
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
   *
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
    $params['offset']   = ($params['page'] - 1) * $params['rp'];
    $params['rowCount'] = $params['rp'];
    $params['sort']     = CRM_Utils_Array::value('sortBy', $params);

    // get batches
    $batches = CRM_Batch_BAO_Batch::getBatchList($params);

    // add total
    $params['total'] = CRM_Batch_BAO_Batch::getBatchCount($params);

    // format params and add links
    $batchList = array();

    if (!empty($batches)) {
      foreach ($batches as $id => $value) {
        if (($params['context'] == 'financialBatch')) {
          $batchList[$id]['check'] = $value['check'];
        }
        $batchList[$id]['batch_name'] = $value['title'];
        $batchList[$id]['batch_type'] = $value['batch_type'];
        $batchList[$id]['item_count'] = $value['item_count'];
        $batchList[$id]['total_amount'] = CRM_Utils_Money::format($value['total']);
        $batchList[$id]['status'] = $value['batch_status'];
        $batchList[$id]['created_by'] = $value['created_by'];
        $batchList[$id]['links'] = $value['action'];
      }
      return $batchList;
    }
  }

  /**
   * This function to get list of batches
   *
   * @param  array   $params associated array for params
   * @access public
   */
  static function getBatchList(&$params) {
    $whereClause = self::whereClause($params, FALSE);

    if (!empty($params['rowCount']) &&
      $params['rowCount'] > 0
    ) {
      $limit = " LIMIT {$params['offset']}, {$params['rowCount']} ";
    }

    $orderBy = ' ORDER BY batch.id desc';
    if (CRM_Utils_Array::value('sort', $params)) {
      $orderBy = ' ORDER BY ' . CRM_Utils_Array::value('sort', $params);
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

    $values = array();
    while ($object->fetch()) {
      $newLinks = $links;
      $values[$object->id] = array();
      CRM_Core_DAO::storeValues($object, $values[$object->id]);
      $action = array_sum(array_keys($newLinks));

      if ($values[$object->id]['status_id'] == 2 && $params['context'] != 'financialBatch' ) {
        $newLinks = array();
      }
      elseif ($params['context'] == 'financialBatch') {
        $values[$object->id]['check'] = "<input type='checkbox' id='check_".$object->id."' name='check_".$object->id."' value='1' onclick='enableActions()'></input>";
        $status = $values[$object->id]['status_id'];
        switch ($status) {
          case '1':
            unset($newLinks['reopen']);
            unset($newLinks['download']);
            break;
          case '2':
            unset($newLinks['close']);
            unset($newLinks['edit']);
            unset($newLinks['download']);
            break;
          case '5':
            unset($newLinks['edit']);
            unset($newLinks['close']);
            unset($newLinks['reopen']);
        }
      }

      $values[$object->id]['batch_type'] = $batchTypes[$values[$object->id]['type_id']];
      $values[$object->id]['batch_status'] = $batchStatus[$values[$object->id]['status_id']];
      $values[$object->id]['created_by'] = $object->created_by;

      $values[$object->id]['action'] = CRM_Core_Action::formLink(
        $newLinks,
        $action,
        array('id' => $object->id)
      );
    }

    return $values;
  }

  static function getBatchCount(&$params) {
    $whereClause = self::whereClause($params, FALSE);
    $query = " SELECT COUNT(*) FROM civicrm_batch batch WHERE {$whereClause}";
    return CRM_Core_DAO::singleValueQuery($query, $params);
  }

  function whereClause(&$params, $sortBy = TRUE, $excludeHidden = TRUE) {
    $clauses = array();

    $title = CRM_Utils_Array::value('title', $params);
    if ($title) {
      $clauses[] = "batch.title LIKE %1";
      if (strpos($title, '%') !== FALSE) {
        $params[1] = array($title, 'String', FALSE);
      }
      else {
        $params[1] = array($title, 'String', TRUE);
      }
    }

    $status = CRM_Utils_Array::value('status', $params);
    if ($status) {
      $clauses[] = 'batch.status_id = %3';
      $params[3] = array($status, 'Integer');
    }

    if (empty($clauses)) {
      return '1';
    }
    return implode(' AND ', $clauses);
  }

  /**
   * Function to define action links
   *
   * @return array $links array of action links
   * @access public
   */
  function links($context = null) {
    if ($context == 'financialBatch') {
      $links = array(
        'transaction' =>  array(
          'name'  => ts('Transactions'),
          'url'   => 'civicrm/batchtransaction',
          'qs'    => 'reset=1&bid=%%id%%',
          'title' => ts( 'View all Transaction' )
        ),
        'edit' =>    array(
          'name'  => ts('Edit'),
          'url'   => 'civicrm/financial/batch',
          'qs'    => 'reset=1&action=update&id=%%id%%',
          'title' => ts( 'Edit Batch' )
        ),
        'close' =>   array(
          'name'  => ts('Close'),
          'title' => ts('Close Batch'),
          'extra' => 'onclick = "closeReopen( %%id%%,\'' . 'close' . '\' );"'
        ),
        'export' =>  array(
          'name'  => ts('Export'),
          'url'   => 'civicrm/financial/batch',
          'qs'    => 'reset=1&action=export&id=%%id%%',
          'title' => ts('Export Batch')
        ),
        'reopen' =>  array(
          'name'  => ts('ReOpen'),
          'title' => ts('ReOpen Batch'),
          'extra' => 'onclick = "closeReopen( %%id%%,\'' . 'reopen' . '\' );"'
        ),
        'delete' =>  array(
          'name'  => ts('Delete'),
          'title' => ts('Delete Batch')
        ),
        'download' => array(
          'name'  => ts('Download'),
          'url'   => 'civicrm/file',
          'qs'    => 'reset=1&id=%%fid%%&eid=%%id%%',
          'title' => ts('Download Batch')
        )
      );
    }
    else {
      $links = array(
        CRM_Core_Action::COPY => array(
          'name' => ts('Enter records'),
          'url' => 'civicrm/batch/entry',
          'qs' => 'id=%%id%%&reset=1',
          'title' => ts('Bulk Data Entry')
        ),
        CRM_Core_Action::UPDATE => array(
          'name' => ts('Edit'),
          'url' => 'civicrm/batch',
          'qs' => 'action=update&id=%%id%%&reset=1',
          'title' => ts('Edit Batch')
        ),
        CRM_Core_Action::DELETE => array(
          'name' => ts('Delete'),
          'url' => 'civicrm/batch',
          'qs' => 'action=delete&id=%%id%%',
          'title' => ts('Delete Batch')
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

  /*
   * @see http://wiki.civicrm.org/confluence/display/CRM/CiviAccounts+Specifications+-++Batches#CiviAccountsSpecifications-Batches-%C2%A0Overviewofimplementation
   */
  static function exportFinancialBatch( $batchIds ) {
    //TEST
    $batchIds = array(1);
    self::$_exportFormat = 'IIF';
    //ENDTEST
    // Instantiate appropriate exporter based on user-selected format.
    $exporterClass = "CRM_Financial_BAO_ExportFormat_" . self::$_exportFormat;
    if ( class_exists( $exporterClass ) ) {
      $exporter = new $exporterClass();
    }
    else {
      CRM_Core_Error::fatal("Could not locate exporter: $exporterClass");
    }

    $id_list = implode(',', $batchIds);

    $sql = "SELECT
      ft.id as financial_trxn_id,
      ft.trxn_date,
      ft.total_amount,
      fa_from.id as from_account_id,
      fa_from.name as from_account_name,
      fa_from.accounting_code as from_account_code,
      fa_from.financial_account_type_id as from_account_type_id,
      fa_from.description as from_account_description,
      ov_from.grouping as from_qb_account_type,
      fa_to.id as to_account_id,
      fa_to.name as to_account_name,
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
      LEFT JOIN civicrm_financial_account fa_to ON fa_to.id = ft.to_financial_account_id
      LEFT JOIN civicrm_option_group og_from ON og_from.name = 'financial_account_type'
      LEFT JOIN civicrm_option_value ov_from ON (ov_from.option_group_id = og_from.id AND ov_from.value = fa_from.financial_account_type_id)
      LEFT JOIN civicrm_option_group og_to ON og_to.name = 'financial_account_type'
      LEFT JOIN civicrm_option_value ov_to ON (ov_to.option_group_id = og_to.id AND ov_to.value = fa_to.financial_account_type_id)
      LEFT JOIN civicrm_contact contact_from ON contact_from.id = fa_from.contact_id
      LEFT JOIN civicrm_contact contact_to ON contact_to.id = fa_to.contact_id
      WHERE eb.batch_id IN ( %1 )";

    $params = array( 1 => array( $id_list, 'String' ) );

    // Keep running list of accounts and contacts used in this batch, since we need to
    // include those in the output. Only want to include ones used in the batch, not everything in the db,
    // since would increase the chance of messing up user's existing Quickbooks entries.
    $accounts = array();
    $contacts = array();

    $journalEntries = array();

    $dao = CRM_Core_DAO::executeQuery( $sql, $params );
    while ( $dao->fetch() ) {

      // add to running list of accounts
      if ( !empty( $dao->from_account_id ) && !isset( $accounts[$dao->from_account_id] ) ) {
        $accounts[$dao->from_account_id] = array(
          'name' => $exporter->format( $dao->from_account_name ),
          'account_code' => $exporter->format( $dao->from_account_code ),
          'description' => $exporter->format( $dao->from_account_description ),
          'type' => $exporter->format( $dao->from_qb_account_type )
        );
      }
      if ( !empty( $dao->to_account_id ) && !isset( $accounts[$dao->to_account_id] ) ) {
        $accounts[$dao->to_account_id] = array(
          'name' => $exporter->format( $dao->to_account_name ),
          'account_code' => $exporter->format( $dao->to_account_code ),
          'description' => $exporter->format( $dao->to_account_description ),
          'type' => $exporter->format( $dao->to_qb_account_type )
        );
      }

      // add to running list of contacts
      if ( !empty( $dao->contact_from_id ) && !isset( $contacts[$dao->contact_from_id] ) ) {
        $contacts[$dao->contact_from_id] = array(
          'name' => $exporter->format( $dao->contact_from_name ),
          'first_name' => $exporter->format( $dao->contact_from_first_name ),
          'last_name' => $exporter->format( $dao->contact_from_last_name )
        );
      }

      if ( !empty( $dao->contact_to_id ) && !isset( $contacts[$dao->contact_to_id] ) ) {
        $contacts[$dao->contact_to_id] = array(
          'name' => $exporter->format( $dao->contact_to_name ),
          'first_name' => $exporter->format( $dao->contact_to_first_name ),
          'last_name' => $exporter->format( $dao->contact_to_last_name )
        );
      }

      // set up the journal entries for this financial trxn
      $journalEntries[$dao->financial_trxn_id] = array(
        'to_account' => array(
          'trxn_date' => $exporter->format( $dao->trxn_date, 'date' ),
          'account_name' => $exporter->format( $dao->to_account_name ),
          'amount' => $exporter->format( $dao->total_amount ),
          'contact_name' => $exporter->format( $dao->contact_to_name )
        ),
        'splits' => array(),
      );

      /*
       * splits has two possibilities depending on FROM account     
       */
      if (empty($dao->from_account_name)) {
        // In this case, split records need to use the individual financial_item account for each item in the trxn
        $item_sql = "SELECT
          fa.id as account_id,
          fa.name as account_name,
          fa.accounting_code as account_code,
          fa.description as account_description,
          fi.id as financial_item_id,
          fi.transaction_date,
          fi.amount,
          ov.grouping as qb_account_type,
          contact.id as contact_id,
          contact.display_name as contact_name,
          contact.first_name as contact_first_name,
          contact.last_name as contact_last_name
          FROM civicrm_entity_financial_trxn eft
          LEFT JOIN civicrm_financial_item fi ON eft.entity_id = fi.id
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
              'description' => $exporter->format( $dao->account_description ),
              'type' => $exporter->format( $item_dao->qb_account_type )
            );
          }

          if (!empty($item_dao->contact_id) && !isset($contacts[$item_dao->contact_id])) {
            $contacts[$item_dao->contact_id] = array(
              'name' => $exporter->format( $item_dao->contact_name ),
              'first_name' => $exporter->format( $item_dao->contact_first_name ),
              'last_name' => $exporter->format( $item_dao->contact_last_name )
            );
          }

          // add split line for this item
          $journalEntries[$dao->financial_trxn_id]['splits'][$item_dao->financial_item_id] = array(
            'trxn_date' => $exporter->format( $item_dao->transaction_date, 'date' ),
            'account_name' => $exporter->format( $item_dao->account_name ),
            'amount' => $exporter->format( (-1) * $item_dao->amount ),
            'contact_name' => $exporter->format( $item_dao->contact_name )
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
          'contact_name' => $exporter->format( $dao->contact_from_name )
        );
      }
    }
    $dao->free();

    $exportParams = array(
      'accounts' => $accounts,
      'contacts' => $contacts,
      'journalEntries' => $journalEntries,
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

}
