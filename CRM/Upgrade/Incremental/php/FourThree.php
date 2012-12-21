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
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
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
class CRM_Upgrade_Incremental_php_FourThree {
  const BATCH_SIZE = 5000;

  function verifyPreDBstate(&$errors) {
    return TRUE;
  }

  /**
   * Compute any messages which should be displayed after upgrade
   *
   * @param $postUpgradeMessage string, alterable
   * @param $rev string, an intermediate version; note that setPostUpgradeMessage is called repeatedly with different $revs
   * @return void
   */
  function setPostUpgradeMessage(&$postUpgradeMessage, $rev) {
    if ($rev == '4.3.alpha1') {
      // check if CiviMember component is enabled
      $config = CRM_Core_Config::singleton();
      if (in_array('CiviMember', $config->enableComponents)) {
        $postUpgradeMessage .= '<br />' . ts('Membership renewal reminders must now be configured using the Schedule Reminders feature, which supports multiple renewal reminders  (Administer > Communications > Schedule Reminders). The Update Membership Statuses scheduled job will no longer send membershp renewal reminders. You can use your existing renewal reminder message template(s) with the Schedule Reminders feature.');
        $postUpgradeMessage .= '<br />' . ts('The Set Membership Reminder Dates scheduled job has been deleted since membership reminder dates stored in the membership table are no longer in use.');
      }
    }
  }

  function upgrade_4_3_alpha1($rev) {
    self::createDomainContacts();
    self::task_4_3_alpha1_checkDBConstraints();
    $upgrade = new CRM_Upgrade_Form();
    $upgrade->processSQL($rev);

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contact');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contact');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade timestamps (%1 => %2)', array(
                 1 => $startId,
                 2 => $endId,
               ));
      $this->addTask($title, 'convertTimestamps', $startId, $endId);
    }

    // CRM-10893
    // fix WP access control
    $config = CRM_Core_Config::singleton( );
    if ($config->userFramework == 'WordPress') {
      civicrm_wp_set_capabilities( );
    }

    // Update phones CRM-11292.
    $this->addTask(ts('Upgrade Phone Numbers'), 'phoneNumeric');
    
    //CRM-11514 create financial records for contributions
    $this->addTask(ts('Create financial records for contributions'), 'createFinancialRecords');
    
    return TRUE;
  }

  function createFinancialRecords() {
    //fetch completed and pending contributions
    $sql = "SELECT con.id, con.payment_instrument_id, con.currency, con.total_amount, con.net_amount, con.fee_amount, con.trxn_id, con.contribution_status_id, con.payment_instrument_id, con.contact_id, con.receive_date, con.check_number, con.is_pay_later FROM civicrm_contribution con 
      WHERE con.contribution_status_id IN (%1, %2)";
    
    $contributionStatus = CRM_Contribute_PseudoConstant::contributionStatus(NULL, 'name');
    $completedStatus = array_search('Completed', $contributionStatus);
    $pendingStatus = array_search('Pending', $contributionStatus);
    $queryParams = array(
      1 => array($completedStatus, 'Integer'),
      2 => array($pendingStatus, 'Integer')
    );
    $dao = CRM_Core_DAO::executeQuery($sql, $queryParams);
    $trxnIds = array();
    $financialItemStatusRecord = array();

    $accountType = key(CRM_Core_PseudoConstant::accountOptionValues('financial_account_type', NULL, " AND v.name = 'Asset' "));
    $financialAccountId =
      CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_financial_account WHERE is_default = 1 AND financial_account_type_id = {$accountType}");

    $accountRelationsips = CRM_Core_PseudoConstant::accountOptionValues('account_relationship', NULL, " AND v.name IN ('Income Account is', 'Accounts Receivable Account is') ");
    $financialItemStatus = CRM_Core_PseudoConstant::accountOptionValues('financial_item_status');
    
    $paymentInstrumentIds = CRM_Financial_BAO_FinancialTypeAccount::getInstrumentFinancialAccount();

    //record financial transaction
    while($dao->fetch()) {
      if ($dao->is_pay_later && $dao->contribution_status_id == $pendingStatus) {
        //evaluate financial item status
        $financialItemStatusRecord[$dao->id] = array_search('Unpaid', $financialItemStatus);
        
        $toFinancialAccountId = self::_getFinancialAccountId($dao->financial_type_id, 
          array_search('Accounts Receivable Account is', $accountRelationsips));
      }
      elseif ($dao->contribution_status_id == $completedStatus) {
        $financialItemStatusRecord[$dao->id] = array_search('Paid', $financialItemStatus);
        if ($dao->payment_instrument_id) {
          $toFinancialAccountId = $paymentInstrumentIds[$dao->payment_instrument_id];
        }
        else {
          $toFinancialAccountId = $financialAccountId;
        } 
      }
     
      $trxnParams = array(
        'contribution_id' => $dao->id,
        'to_financial_account_id' => $toFinancialAccountId,
        'from_financial_account_id' => NULL,
        'trxn_date' => date('YmdHis'),
        'total_amount' =>$dao->total_amount,
        'fee_amount' => $dao->net_amount,
        'net_amount' => $dao->fee_amount,
        'currency' =>$dao->currency,
        'trxn_id' => $dao->trxn_id,
        'payment_instrument_id' => $dao->payment_instrument_id,
        'check_number' => $dao->check_number,
        'status_id' => $dao->contribution_status_id
      );
      $trxn = CRM_Core_BAO_FinancialTrxn::create($trxnParams);
      $trxnIds[$dao->id] = $trxn->id;
    }
        
    //update all linked line_item rows - set line_item.financial_type_id = contribution.financial_type_id
    $updateLineItemSql = "UPDATE civicrm_line_item li
      INNER JOIN civicrm_contribution con ON (li.entity_id = con.id)
      SET li.financial_type_id = con.financial_type_id
      WHERE li.entity_table = 'civicrm_contribution'
      AND con.contribution_status_id IN (%1, %2)";
    CRM_Core_DAO::executeQuery($updateLineItemSql, $queryParams);
    
    //add financial_item entries so loop the line item and build appropriate details needed for financial_item records
    $lineItemSql = "
      SELECT li.entity_id as contribution_id, con.contact_id as con_contact_id, li.line_total, con.currency,
        li.id as line_item_id, li.label as line_item_label, con.receive_date, li.financial_type_id
      FROM civicrm_line_item li
      INNER JOIN civicrm_contribution con ON (li.entity_id = con.id)
      WHERE li.entity_table = 'civicrm_contribution'
      AND con.contribution_status_id IN (%1, %2)";
    $data = CRM_Core_DAO::executeQuery($lineItemSql, $queryParams);
    
    $relationshipId = array_search('Income Account is', $accountRelationsips);
    //looping the line items
    while($data->fetch()) {
      $financialItemEntry = array(
        'transaction_date' => CRM_Utils_Date::isoToMysql($data->receive_date),
        'contact_id'    => $data->con_contact_id, 
        'amount'        => $data->line_total,
        'currency'      => $data->currency,
        'entity_table'  => 'civicrm_line_item',
        'entity_id'     => $data->line_item_id,
        'description'   => $data->line_item_label,
        'status_id'     => $financialItemStatusRecord[$data->contribution_id]
      );
    
      if ($data->financial_type_id) {
        $financialItemEntry['financial_account_id'] = self::_getFinancialAccountId($data->financial_type_id, $relationshipId);
      }
      $trxnId['id'] = $trxnIds[$data->contribution_id];
      CRM_Financial_BAO_FinancialItem::create($financialItemEntry, NULL, $trxnId);
    }
    return TRUE;
  }

  static function _getFinancialAccountId($financialTypeId, $relationshipId) {
    $cacheKey = "FINANCIAL_{$financialTypeId}_{$relationshipId}";
    $cache = CRM_Utils_Cache::singleton();
    $accId = $cache->get($cacheKey);
    if ($accId) {
      return $accId;
    }

    $searchParams = array( 
      'entity_table' => 'civicrm_financial_type',
      'entity_id' => $financialTypeId,
      'account_relationship' => $relationshipId
    );
    $result = array();
    CRM_Financial_BAO_FinancialTypeAccount::retrieve($searchParams, $result);
    $accId = CRM_Utils_Array::value('financial_account_id', $result);
    $cache->set($cacheKey, $accId);
    return $accId;
  }
  
  function createDomainContacts() {
    $domainParams = array();
    $locParams['entity_table'] = CRM_Core_BAO_Domain::getTableName();
    $query = "
ALTER TABLE `civicrm_domain` ADD `contact_id` INT( 10 ) UNSIGNED NULL DEFAULT NULL COMMENT 'FK to Contact ID. This is specifically not an FK to avoid circular constraints',
 ADD CONSTRAINT `FK_civicrm_domain_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`);";
    CRM_Core_DAO::executeQuery($query, $params, TRUE, NULL, FALSE, FALSE);
    $dao = new CRM_Core_DAO_Domain();
    $dao->find();
    while($dao->fetch()) {
      $params = array(
        'sort_name' => $dao->name,
        'display_name' => $dao->name,
        'legal_name' => $dao->name,
        'organization_name' => $dao->name,
        'contact_type' => 'Organization'
      );

      $contact = CRM_Contact_BAO_Contact::add($params);
      $domainParams['contact_id'] = $contact->id;
      CRM_Core_BAO_Domain::edit($domainParams, $dao->id);
    }
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_domain` DROP loc_block_id;", $params, TRUE, NULL, FALSE, FALSE);
  }

  function task_4_3_alpha1_checkDBConstraints() {
    //checking whether the foreign key exists before dropping it CRM-11260
    $config = CRM_Core_Config::singleton();
    $dbUf = DB::parseDSN($config->dsn);
    $params = array();
    $tables = array(
      'autorenewal_msg_id' => array('tableName' => 'civicrm_membership_type', 'fkey' => 'FK_civicrm_membership_autorenewal_msg_id'),
      'to_account_id' =>  array('tableName' => 'civicrm_financial_trxn', 'constraintName' => 'civicrm_financial_trxn_ibfk_2'),
      'from_account_id' => array('tableName' =>  'civicrm_financial_trxn', 'constraintName' => 'civicrm_financial_trxn_ibfk_1'),
      'contribution_type_id' => array('tableName' => 'civicrm_contribution_recur', 'fkey' => 'FK_civicrm_contribution_recur_contribution_type_id'),
    );
    $query = "SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
WHERE table_name = 'civicrm_contribution_recur'
AND constraint_name = 'FK_civicrm_contribution_recur_contribution_type_id'
AND TABLE_SCHEMA = '{$dbUf['database']}'";

    $dao = CRM_Core_DAO::executeQuery($query, $params, TRUE, NULL, FALSE, FALSE);
    foreach($tables as $columnName => $value){
      if ($value['tableName'] == 'civicrm_membership_type' || $value['tableName'] == 'civicrm_contribution_recur') {
        $foreignKeyExists = CRM_Core_DAO::checkConstraintExists($value['tableName'], $value['fkey']);
        $fKey = $value['fkey'];
      } else {
        $foreignKeyExists = CRM_Core_DAO::checkFKConstraintInFormat($value['tableName'], $columnName);
        $fKey = "`FK_{$value['tableName']}_{$columnName}`";
      }
      if ($foreignKeyExists || $value['tableName'] == 'civicrm_financial_trxn') {
        if ($value['tableName'] != 'civicrm_contribution_recur' || ($value['tableName'] == 'civicrm_contribution_recur' && $dao->N)) {
          $constraintName  = $foreignKeyExists ? $fKey : $value['constraintName'];
          CRM_Core_DAO::executeQuery("ALTER TABLE {$value['tableName']} DROP FOREIGN KEY {$constraintName}", $params, TRUE, NULL, FALSE, FALSE);
        }
        CRM_Core_DAO::executeQuery("ALTER TABLE {$value['tableName']} DROP INDEX {$fKey}", $params, TRUE, NULL, FALSE, FALSE);
      }
    }
    // check if column contact_id is present or not in civicrm_financial_account
    $fieldExists = CRM_Core_DAO::checkFieldExists('civicrm_financial_account', 'contact_id', FALSE);
    if (!$fieldExists) {
      $query = "ALTER TABLE civicrm_financial_account ADD `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'Version identifier of financial_type' AFTER `name`, ADD CONSTRAINT `FK_civicrm_financial_account_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(id);";
      CRM_Core_DAO::executeQuery($query, $params, TRUE, NULL, FALSE, FALSE);
    }
  }

  /**
   * Read creation and modification times from civicrm_log; add
   * them to civicrm_contact.
   */
  function convertTimestamps(CRM_Queue_TaskContext $ctx, $startId, $endId) {
    $sql = "
      SELECT entity_id, min(modified_date) AS created, max(modified_date) AS modified
      FROM civicrm_log
      WHERE entity_table = 'civicrm_contact'
      AND entity_id BETWEEN %1 AND %2
      GROUP BY entity_id
    ";
    $params = array(
      1 => array($startId, 'Integer'),
      2 => array($endId, 'Integer'),
    );
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while ($dao->fetch()) {
      // FIXME civicrm_log.modified_date is DATETIME; civicrm_contact.modified_date is TIMESTAMP
      CRM_Core_DAO::executeQuery(
        'UPDATE civicrm_contact SET created_date = %1, modified_date = %2 WHERE id = %3',
        array(
          1 => array($dao->created, 'String'),
          2 => array($dao->modified, 'String'),
          3 => array($dao->entity_id, 'Integer'),
        )
      );
    }

    return TRUE;
  }

  /**
   * Update phones CRM-11292
   *
   * @return bool TRUE for success
   */
  static function phoneNumeric(CRM_Queue_TaskContext $ctx) {
    CRM_Core_DAO::executeQuery(CRM_Contact_BAO_Contact::DROP_STRIP_FUNCTION_43);
    CRM_Core_DAO::executeQuery(CRM_Contact_BAO_Contact::CREATE_STRIP_FUNCTION_43);
    CRM_Core_DAO::executeQuery("UPDATE civicrm_phone SET phone_numeric = civicrm_strip_non_numeric(phone)");
    return TRUE;
  }

  /**
   * Syntatic sugar for adding a task which (a) is in this class and (b) has
   * a high priority.
   *
   * After passing the $funcName, you can also pass parameters that will go to
   * the function. Note that all params must be serializable.
   */
  protected function addTask($title, $funcName) {
    $queue = CRM_Queue_Service::singleton()->load(array(
      'type' => 'Sql',
      'name' => CRM_Upgrade_Form::QUEUE_NAME,
    ));

    $args = func_get_args();
    $title = array_shift($args);
    $funcName = array_shift($args);
    $task = new CRM_Queue_Task(
      array(get_class($this), $funcName),
      $args,
      $title
    );
    $queue->createItem($task, array('weight' => -1));
  }
}
