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

class CRM_Core_BAO_FinancialTrxn extends CRM_Financial_DAO_FinancialTrxn {
  function __construct() {
    parent::__construct();
  }

  /**
   * takes an associative array and creates a financial transaction object
   *
   * @param array  $params (reference ) an assoc array of name/value pairs
   *
   * @param string $trxnEntityTable entity_table
   *
   * @return object CRM_Core_BAO_FinancialTrxn object
   * @access public
   * @static
   */
  static function create(&$params, $trxnEntityTable = null ) {
    $trxn = new CRM_Financial_DAO_FinancialTrxn();
    $trxn->copyValues($params);
    $fids = array();
    if (!CRM_Utils_Rule::currencyCode($trxn->currency)) {
      $config = CRM_Core_Config::singleton();
      $trxn->currency = $config->defaultCurrency;
    }

    $trxn->save();

    $contributionAmount = CRM_Utils_Array::value('net_amount', $params);
    if (!$contributionAmount && isset($params['total_amount'])) {
      $contributionAmount = $params['total_amount'];
    }

    // save to entity_financial_trxn table
    $entityFinancialTrxnParams =
      array(
        'entity_table' => "civicrm_contribution",
        'financial_trxn_id' => $trxn->id,
        //use net amount to include all received amount to the contribution
        'amount' => $contributionAmount,
        'currency' => $trxn->currency,
      );

    if (!empty($trxnEntityTable)) {
      $entityFinancialTrxnParams['entity_table'] = $trxnEntityTable['entity_table'];
      $entityFinancialTrxnParams['entity_id']    = $trxnEntityTable['entity_id'];
    }
    else {
      $entityFinancialTrxnParams['entity_id'] =  $params['contribution_id'];
    }

    $entityTrxn = new CRM_Financial_DAO_EntityFinancialTrxn();
    $entityTrxn->copyValues($entityFinancialTrxnParams);
    $entityTrxn->save();
    return $trxn;
  }

  /**
   * Takes a bunch of params that are needed to match certain criteria and
   * retrieves the relevant objects. Typically the valid params are only
   * contact_id. We'll tweak this function to be more full featured over a period
   * of time. This is the inverse function of create. It also stores all the retrieved
   * values in the default array
   *
   * @param array $params   (reference ) an assoc array of name/value pairs
   * @param array $defaults (reference ) an assoc array to hold the flattened values
   *
   * @return object CRM_Contribute_BAO_ContributionType object
   * @access public
   * @static
   */
  static function retrieve( &$params, &$defaults ) {
    $financialItem = new CRM_Financial_DAO_FinancialTrxn( );
    $financialItem->copyValues($params);
    if ($financialItem->find(true)) {
      CRM_Core_DAO::storeValues( $financialItem, $defaults );
      return $financialItem;
    }
    return null;
  }

  /**
   *
   * Given an entity_id and entity_table, check for corresponding entity_financial_trxn and financial_trxn record.
   * NOTE: This should be moved to separate BAO for EntityFinancialTrxn when we start adding more code for that object.
   *
   * @param string $entityTable name of the entity table usually 'civicrm_contact'
   * @param int $entityID id of the entity usually the contactID.
   *
   * @return array( ) reference $tag array of catagory id's the contact belongs to.
   *
   * @access public
   * @static
   */
  static function getFinancialTrxnIds($entity_id, $entity_table = 'civicrm_contribution') {
    $ids = array('entityFinancialTrxnId' => NULL, 'financialTrxnId' => NULL);

    $query = "
            SELECT id, financial_trxn_id
            FROM civicrm_entity_financial_trxn
            WHERE entity_id = %1
            AND entity_table = %2
        ";

    $params = array(1 => array($entity_id, 'Integer'), 2 => array($entity_table, 'String'));
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    if ($dao->fetch()) {
      $ids['entityFinancialTrxnId'] = $dao->id;
      $ids['financialTrxnId'] = $dao->financial_trxn_id;
    }
    return $ids;
  }

  /**
   * Given an entity_id and entity_table, check for corresponding entity_financial_trxn and financial_trxn record.
   * NOTE: This should be moved to separate BAO for EntityFinancialTrxn when we start adding more code for that object.
   *
   * @param string $entityTable name of the entity table usually 'civicrm_contact'
   * @param int $entityID id of the entity usually the contactID.
   *
   * @return array( ) reference $tag array of catagory id's the contact belongs to.
   *
   * @access public
   * @static
   */
  static function getFinancialTrxnTotal($entity_id) {
    $query = "
      SELECT (ft.amount+SUM(ceft.amount)) AS total FROM civicrm_entity_financial_trxn AS ft
LEFT JOIN civicrm_entity_financial_trxn AS ceft ON ft.financial_trxn_id = ceft.entity_id 
WHERE ft.entity_table = 'civicrm_contribution' AND ft.entity_id = %1
        ";

    $sqlParams = array(1 => array($entity_id, 'Integer'));
    return  CRM_Core_DAO::singleValueQuery($query, $sqlParams);

  }
  /**
   * Given an financial_trxn_id  check for previous entity_financial_trxn.
   *
   * @param int $financialTrxn_id id of the latest payment.
   *
   * @return array( ) $payment array of previous payments
   *
   * @access public
   * @static
   */
  static function getPayments($financial_trxn_id) {
    $query = "
SELECT ef1.financial_trxn_id, sum(ef1.amount) amount
FROM civicrm_entity_financial_trxn ef1
LEFT JOIN civicrm_entity_financial_trxn ef2 ON ef1.financial_trxn_id = ef2.entity_id
WHERE ef2.financial_trxn_id =%1
  AND ef2.entity_table = 'civicrm_financial_trxn'
  AND ef1.entity_table = 'civicrm_financial_item'
GROUP BY ef1.financial_trxn_id
UNION
SELECT ef1.financial_trxn_id, ef1.amount
FROM civicrm_entity_financial_trxn ef1
LEFT JOIN civicrm_entity_financial_trxn ef2 ON ef1.entity_id = ef2.entity_id
WHERE  ef2.financial_trxn_id =%1
  AND ef2.entity_table = 'civicrm_financial_trxn'
  AND ef1.entity_table = 'civicrm_financial_trxn'";

    $sqlParams = array(1 => array($financial_trxn_id, 'Integer'));
    $dao = CRM_Core_DAO::executeQuery($query, $sqlParams);
    $i = 0;
    $result = array();
    while ($dao->fetch()) {
      $result[$i]['financial_trxn_id'] = $dao->financial_trxn_id;
      $result[$i]['amount'] = $dao->amount;
      $i++;
    }

    if (empty($result)) {
      $query = "SELECT sum( amount ) amount FROM civicrm_entity_financial_trxn WHERE financial_trxn_id =%1 AND entity_table = 'civicrm_financial_item'";
      $sqlParams = array(1 => array($financial_trxn_id, 'Integer'));
      $dao = CRM_Core_DAO::executeQuery($query, $sqlParams);

      if ($dao->fetch()) {
        $result[0]['financial_trxn_id'] = $financial_trxn_id;
        $result[0]['amount'] = $dao->amount;
      }
    }
    return $result;
  }

  /**
   * Given an entity_id and entity_table, check for corresponding entity_financial_trxn and financial_trxn record.
   * NOTE: This should be moved to separate BAO for EntityFinancialTrxn when we start adding more code for that object.
   *
   * @param string $entityTable name of the entity table usually 'civicrm_contact'
   * @param int $entityID id of the entity usually the contactID.
   *
   * @return array(  ) reference $tag array of catagory id's the contact belongs to.
   *
   * @access public
   * @static
   */
  static function getFinancialTrxnLineTotal($entity_id, $entity_table = 'civicrm_contribution') {
    $query = "SELECT lt.price_field_value_id AS id, ft.financial_trxn_id,ft.amount AS amount FROM civicrm_entity_financial_trxn AS ft
LEFT JOIN civicrm_financial_item AS fi ON fi.id = ft.entity_id AND fi.entity_table = 'civicrm_line_item' AND ft.entity_table = 'civicrm_financial_item'
LEFT JOIN civicrm_line_item AS lt ON lt.id = fi.entity_id AND lt.entity_table = %2 
WHERE lt.entity_id = %1 ";

    $sqlParams = array(1 => array($entity_id, 'Integer'), 2 => array($entity_table, 'String'));
    $dao =  CRM_Core_DAO::executeQuery($query, $sqlParams);
    while($dao->fetch()){
      $result[$dao->financial_trxn_id][$dao->id] = $dao->amount;
    }
    if (!empty($result)) {
      return $result;
    }
    else {
      return null;
    }
  }

  /**
   * Delete financial transaction
   *
   * @return true on success, false otherwise
   * @access public
   * @static
   */
  static function deleteFinancialTrxn($entity_id, $entity_table = 'civicrm_contribution') {
    $fids = self::getFinancialTrxnIds($entity_id, $entity_table);

    if ($fids['financialTrxnId']) {
      // delete enity financial transaction before financial transaction since financial_trxn_id will be set to null if financial transaction deleted first
      $query = 'DELETE FROM civicrm_entity_financial_trxn  WHERE financial_trxn_id = %1';
      CRM_Core_DAO::executeQuery($query, array(1 => array($fids['financialTrxnId'], 'Integer')));

      // delete financial transaction
      $query = 'DELETE FROM civicrm_financial_trxn WHERE id = %1';
      CRM_Core_DAO::executeQuery($query, array(1 => array($fids['financialTrxnId'], 'Integer')));
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * create financial transaction for premium
   *
   * @access public
   * @static
   */
  static function createPremiumTrxn($params) {
    if ((!CRM_Utils_Array::value('financial_type_id', $params) || !CRM_Utils_Array::value('contributionId', $params)) && !CRM_Utils_Array::value('oldPremium', $params)) {
      return;
    }
    
    if (CRM_Utils_Array::value('cost', $params)) {
      $fids = self::getFinancialTrxnIds($params['contributionId'], 'civicrm_contribution');
      $contributionStatuses = CRM_Contribute_PseudoConstant::contributionStatus(NULL, 'name');
      $financialAccountType = CRM_Contribute_PseudoConstant::financialAccountType($params['financial_type_id']);
      $accountRelationship = CRM_Core_PseudoConstant::accountOptionValues('account_relationship', NULL, " AND label IN ('Premiums Inventory Account is', 'Cost of Sales Account is')");
      $toFinancialAccount = CRM_Utils_Array::value('isDeleted', $params) ? 'Premiums Inventory Account is' : 'Cost of Sales Account is';
      $fromFinancialAccount = CRM_Utils_Array::value('isDeleted', $params) ? 'Cost of Sales Account is': 'Premiums Inventory Account is';
      $accountRelationship = array_flip($accountRelationship);
      $financialtrxn = array(
        'to_financial_account_id' => $financialAccountType[$accountRelationship[$toFinancialAccount]],
        'from_financial_account_id' => $financialAccountType[$accountRelationship[$fromFinancialAccount]],
        'trxn_date' => date('YmdHis'),
        'total_amount' => CRM_Utils_Array::value('cost', $params) ? $params['cost'] : 0,
        'currency' => CRM_Utils_Array::value('currency', $params),
        'status_id' => array_search('Completed', $contributionStatuses)
      );
      $trxnEntityTable['entity_table'] = 'civicrm_financial_trxn';
      $trxnEntityTable['entity_id'] = $fids['financialTrxnId'];
      CRM_Core_BAO_FinancialTrxn::create($financialtrxn, $trxnEntityTable);
    }

    if (CRM_Utils_Array::value('oldPremium', $params)) {
      $premiumParams = array(
        'id' => $params['oldPremium']['product_id']
      );
      $productDetails = array();
      CRM_Contribute_BAO_ManagePremiums::retrieve($premiumParams, $productDetails);
      $params = array(
        'cost' => CRM_Utils_Array::value('cost', $productDetails),
        'currency' => CRM_Utils_Array::value('currency', $productDetails),
        'financial_type_id' => CRM_Utils_Array::value('financial_type_id', $productDetails),
        'contributionId' => $params['oldPremium']['contribution_id'],
        'isDeleted' => TRUE
      );
      CRM_Core_BAO_FinancialTrxn::createPremiumTrxn($params);
    }
  }
  /**
   * create financial trxn and items when fee is charged
   *
   * @params params to create trxn entries
   *
   * @access public
   * @static
   */

  static function recordFees($params) {
    $expenseTypeId = key(CRM_Core_PseudoConstant::accountOptionValues('account_relationship', NULL, " AND v.name LIKE 'Expense Account is' "));
    $financialAccount = CRM_Contribute_PseudoConstant::financialAccountType($params['financial_type_id'], $expenseTypeId);
    $trxnParams = 
      array(
        'from_financial_account_id' => $params['to_financial_account_id'],
        'to_financial_account_id' => $financialAccount,
        'trxn_date' => date('YmdHis'),
        'total_amount' => $params['fee_amount'],
        'status_id' => CRM_Core_OptionGroup::getValue('contribution_status','Completed','name'),
      );
    $trxnParams['contribution_id'] = $params['contribution']->id;
    $trxn = self::create($trxnParams);
    $fItemParams = 
      array(
        'financial_account_id' => $financialAccount,
        'contact_id' => $params['contact_id'],
        'created_date' => date('YmdHis'),
        'transaction_date' => date('YmdHis'),
        'amount' => CRM_Utils_Array::value('fee_amount', $params) ? CRM_Utils_Array::value('fee_amount', $params) : 0,
        'description' => 'Fee',
        'status_id' => CRM_Core_OptionGroup::getValue('financial_item_status','Paid','name'),
        'entity_table' => 'civicrm_financial_trxn',
        'entity_id' => $params['entity_id'],
      );
    $trxnIDS['id'] = $trxn->id;
    $financialItem = CRM_Financial_BAO_FinancialItem::create($fItemParams, NULL, $trxnIDS);
  }
}

