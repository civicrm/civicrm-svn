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
   * @return object CRM_Core_BAO_FinancialTrxn object
   * @access public
   * @static
   */
  static function create(&$params, $trxnEntityTable = null ) {
    $trxn = new CRM_Financial_DAO_FinancialTrxn();
    $trxn->copyValues($params);

    if (!CRM_Utils_Rule::currencyCode($trxn->currency)) {
      $config = CRM_Core_Config::singleton();
      $trxn->currency = $config->defaultCurrency;
    }

    // if a transaction already exists for a contribution id, lets get the finTrxnId and entityFinTrxId
    if (!$trxnEntityTable ) {
      $fids = self::getFinancialTrxnIds($params['contribution_id'], 'civicrm_contribution');
    }
    if (CRM_Utils_Array::value( 'financialTrxnId', $fids ) && empty ( $trxnEntityTable )) {
      $trxn->id = $fids['financialTrxnId'];
    }
    $trxn->save();

    $contributionAmount = CRM_Utils_Array::value('net_amount', $params);
    if (!$contributionAmount && isset($params['total_amount'])) {
      $contributionAmount = $params['total_amount'];
    }
    
    // save to entity_financial_trxn table
    $entity_financial_trxn_params =
      array(
      'entity_table' => "civicrm_contribution",
      'financial_trxn_id' => $trxn->id,
      //use net amount to include all received amount to the contribution
      'amount' => $contributionAmount,
      'currency' => $trxn->currency,
    );
    
    if (!empty($trxnEntityTable)) {
      $entity_financial_trxn_params['entity_table'] = $trxnEntityTable['entity_table'];
      $entity_financial_trxn_params['entity_id']    = $trxnEntityTable['entity_id'];
    } 
    else {
      $entity_financial_trxn_params['entity_id'] =  $params['contribution_id'];
    }

    $entity_trxn = new CRM_Financial_DAO_EntityFinancialTrxn();
    $entity_trxn->copyValues($entity_financial_trxn_params);
    if (CRM_Utils_Array::value('entityFinancialTrxnId', $fids) && empty($trxnEntityTable)) {
      $entity_trxn->id = $fids['entityFinancialTrxnId'];
    }
    $entity_trxn->save();
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
LEFT JOIN civicrm_financial_item AS fi ON fi.id = ft.entity_id AND fi.entity_table = 'civicrm_line_item'
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
  static  function deleteFinancialTrxn($entity_id, $entity_table = 'civicrm_contribution') {
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
}

