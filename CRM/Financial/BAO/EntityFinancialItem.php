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

require_once 'CRM/Financial/DAO/EntityFinancialItem.php';

class CRM_Financial_BAO_EntityFinancialItem extends CRM_Financial_DAO_EntityFinancialItem
{

    /**
     * static holder for the default LT
     */
    static $_defaultContributionType = null;
    

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
     * @return object CRM_Contribute_BAO_ContributionType object
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
     * function to add the financial financial items to batch
     *
     * @param array $params reference array contains the values submitted by the form
     * @param array $ids    reference array contains the id
     * 
     * @access public
     * @static 
     * @return object
     */
    static function add($params, $ids=null) 
    {
     
        // action is taken depending upon the mode
        $financialItem               = new CRM_Financial_DAO_EntityFinancialItem( );
        CRM_Core_Error::debug( '$params', $params );
        $financialItem->copyValues( $params );;
        
        $financialItem->save( );
        
        return $financialItem;
    }
     static function remove($params) 
    {
     
        // action is taken depending upon the mode
        $financialItem               = new CRM_Financial_DAO_EntityFinancialItem( );
        CRM_Core_Error::debug( '$params', $params );
        $financialItem->copyValues( $params );
        $financialItem->delete();
        return $financialItem;
    }

    /**
     * function to add the financial types
     *
     * @param array $params reference array contains the values submitted by the form
     * @param array $ids    reference array contains the id
     * 
     * @access public
     * @static 
     * @return object
     */
    static function getBatchFinancialItems( $entityID, $returnvalues, $notPresent = null, $sort = 'id' ) 
    {
     
        // action is taken depending upon the mode
        $select = ' `civicrm_financial_item`.id ';
        if( !empty( $returnvalues ) )
            $select .= " , ".implode( ' , ', $returnvalues );

        if( $sort ){
            $orderBy = " ORDER BY {$sort}";
        }
            

        $from = " `civicrm_financial_item`
LEFT JOIN civicrm_contact ON civicrm_contact.id = `civicrm_financial_item`.contact_id
LEFT JOIN civicrm_entity_financial_item ON civicrm_entity_financial_item.financial_item_id= `civicrm_financial_item`.id
LEFT JOIN civicrm_financial_account ON civicrm_financial_account.id = `civicrm_financial_item`.financial_account_id ";
        if( !$notPresent ){
            $where =  " ( civicrm_entity_financial_item.entity_id = {$entityID} AND civicrm_entity_financial_item.entity_table = 'civicrm_batch' ) ";
        }else{
            $where = "( civicrm_entity_financial_item.financial_item_id IS NULL ) AND ( civicrm_financial_item.status_id = 1 )";
        }
   
    $sql = "SELECT {$select}
FROM {$from}
WHERE {$where}
{$orderBy}
";

    $result = CRM_Core_DAO::executeQuery( $sql );
    
    return $result;
    }

    
}
