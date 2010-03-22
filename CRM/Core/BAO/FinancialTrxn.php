<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/DAO/FinancialTrxn.php';
require_once 'CRM/Core/DAO/EntityFinancialTrxn.php';

class CRM_Core_BAO_FinancialTrxn extends CRM_Core_DAO_FinancialTrxn
{
    function __construct()
    {
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
    static function create(&$params) {
        $trxn = new CRM_Core_DAO_FinancialTrxn();
        $trxn->copyValues($params);

        require_once 'CRM/Utils/Rule.php';
        if (!CRM_Utils_Rule::currencyCode($trxn->currency)) {
            require_once 'CRM/Core/Config.php';
            $config = CRM_Core_Config::singleton();
            $trxn->currency = $config->defaultCurrency;
        }
        // if a transaction already exists for a contribution id, lets get the financial transaction id
        $financial_trxn_id = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_EntityFinancialTrxn',
                                                          $params['contribution_id'],
                                                          'financial_trxn_id',
                                                          'entity_id' );
        if ( $financial_trxn_id ) {
            $trxn->id = $financial_trxn_id;
            //get the entity financial transaction id here
            $entity_financial_trxn_id = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_EntityFinancialTrxn',
                                                                     $financial_trxn_id,
                                                                     'id',
                                                                     'financial_trxn_id' );
        }
                                          
        $trxn->save();
        // save to entity_financia_trxn table
        $entity_financial_trxn_params=array(
            'entity_type'      => "contribution",
            'entity_id'      => $params['contribution_id'],
            'financial_trxn_id'      => $trxn->id,
            'amount'      => $params['net_amount'],//use net amount to include all received amount to the contribution
        );
        $entity_trxn =& new CRM_Core_DAO_EntityFinancialTrxn();
        $entity_trxn->copyValues($entity_financial_trxn_params);
        if ( $financial_trxn_id ) {
            $entity_trxn->id = $entity_financial_trxn_id;
        }
        $entity_trxn->save();
        return $trxn;
        
    }

}
