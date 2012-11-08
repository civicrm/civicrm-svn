<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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

require_once 'CRM/Financial/DAO/FinancialItem.php';
require_once 'CRM/Financial/BAO/FinancialTypeAccount.php';
class CRM_Financial_BAO_FinancialItem extends CRM_Financial_DAO_FinancialItem 
{

  
    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
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
     * @return object CRM_Contribute_BAO_FinancialItem object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $financialItem = new CRM_Financial_DAO_FinancialItem( );
        $financialItem->copyValues( $params );
        if ( $financialItem->find( true ) ) {
            CRM_Core_DAO::storeValues( $financialItem, $defaults );
            return $financialItem;
        }
        return null;
    }

 
    /**
     * function to add the financial Items
     *
     * @param array $params reference array contains the values submitted by the form
     * @param array $ids    reference array contains the id
     * 
     * @access public
     * @static 
     * @return object
     */
    static function add( $lineItem, $contribution ) 
    {
        $params = array( 'transaction_date'  => $contribution->receive_date,
                         'contact_id'        => $contribution->contact_id, 
                         'amount'            => $lineItem->line_total,
                         'currency'          => $contribution->currency,
                         'status_id'         => 3,
                         'entity_table'      => 'civicrm_line_item',
                         'entity_id'         => $lineItem->id,
                         'description'       => ( $lineItem->qty != 1 ? $lineItem->qty . ' of ' : ''). ' ' . $lineItem->label,
                         );
        if( $lineItem->financial_type_id ){
            $searchParams = array( 'entity_table'         => 'civicrm_financial_type',
                                   'entity_id'            => $lineItem->financial_type_id,
                                   'account_relationship' => 1
                                   );
            $result = array( );
            CRM_Financial_BAO_FinancialTypeAccount::retrieve( $searchParams, $result );
            $params['financial_account_id'] = CRM_Utils_Array::value( 'financial_account_id', $result );
        }
        self::create( $params );
       
    } 
    
    static function create( &$params, $ids = null, $trxnId = null  ) {
        $financialItem = new CRM_Financial_DAO_FinancialItem( );
        $financialItem->copyValues( $params );
        if( CRM_Utils_Array::value( 'id', $ids ) ){
            $financialItem->id = $ids['id']; 
        }
        $financialItem->save( );
        if( CRM_Utils_Array::value( 'id', $trxnId ) ){
            $entity_financial_trxn_params = array(
                                                  'entity_table'      => "civicrm_financial_item",
                                                  'entity_id'         => $financialItem->id,
                                                  'financial_trxn_id' => $trxnId['id'],
                                                  'amount'            => $params['amount'],
                                                  );
            $entity_trxn = new CRM_Financial_DAO_EntityFinancialTrxn();
            $entity_trxn->copyValues( $entity_financial_trxn_params );
            if ( CRM_Utils_Array::value( 'entityFinancialTrxnId', $ids ) ) {
                $entity_trxn->id = $ids['entityFinancialTrxnId'];
            }
            $entity_trxn->save();
        }
        
        return $financialItem;
       
    }   
}

