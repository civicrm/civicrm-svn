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

require_once 'CRM/Financial/DAO/FinancialAccount.php';

class CRM_Financial_BAO_FinancialAccount extends CRM_Financial_DAO_FinancialAccount 
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
     * @return object CRM_Contribute_BAO_FinancialAccount object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $financialAccount = new CRM_Financial_DAO_FinancialAccount( );
        $financialAccount->copyValues( $params );
        if ( $financialAccount->find( true ) ) {
            CRM_Core_DAO::storeValues( $financialAccount, $defaults );
            return $financialAccount;
        }
        return null;
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */
    static function setIsActive( $id, $is_active ) 
    {
        return CRM_Core_DAO::setFieldValue( 'CRM_Financial_DAO_FinancialAccount', $id, 'is_active', $is_active );
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
    static function add(&$params, &$ids) 
    {
        
        $params['is_active'] =  CRM_Utils_Array::value( 'is_active', $params, false );
        $params['is_deductible'] =  CRM_Utils_Array::value( 'is_deductible', $params, false );
        $params['is_tax'] =  CRM_Utils_Array::value( 'is_tax', $params, false );
        $params['is_header_account'] =  CRM_Utils_Array::value( 'is_header_account', $params, false );
        $params['is_default'] =  CRM_Utils_Array::value( 'is_default', $params, false );
        if ( CRM_Utils_Array::value( 'is_default', $params ) ) {
            $query = 'UPDATE civicrm_financial_account SET is_default = 0';
            CRM_Core_DAO::executeQuery( $query );
        }   
        
        // action is taken depending upon the mode
        $financialAccount = new CRM_Financial_DAO_FinancialAccount( );
        $financialAccount->copyValues( $params );;
        
        $financialAccount->id = CRM_Utils_Array::value( 'contributionType', $ids );
        $financialAccount->save( );
        return $financialAccount;
    }
    
    /**
     * Function to delete financial Types 
     * 
     * @param int $financialAccountId
     * @static
     */
    
    static function financialAccountValidation($fields,&$errors) {
    $financialAccount = array( );
    if (CRM_Utils_Array::value('financial_type_id', $fields)) {
      CRM_Core_PseudoConstant::populate( $financialAccount,
                                         'CRM_Financial_DAO_EntityFinancialAccount',
                                         $all = True, 
                                         $retrieve = 'financial_account_id', 
                                         $filter = null, 
                                         " account_relationship = 6 AND entity_id = {$fields['financial_type_id']} " );
      if( !current( $financialAccount ) ) {
        $errors['financial_type_id'] = "Financial Account of account relationship of 'Asset Account of' is not configured for this Financial Type";
      }
    }
    }
    
    /**
     * Function to delete financial Types 
     * 
     * @param int $financialAccountId
     * @static
     */
    
    static function del($financialAccountId) 
    {
        //checking if financial type is present  
        $check = false;
        
        //check dependencies
        $dependancy = array( 
                            array('Core', 'FinancialTrxn', 'to_financial_account_id'), 
                            array('Financial', 'FinancialTypeAccount', 'financial_account_id' ), 
                            );
        foreach ($dependancy as $name) {
            require_once (str_replace('_', DIRECTORY_SEPARATOR, "CRM_" . $name[0] . "_BAO_" . $name[1]) . ".php");
            eval('$bao = new CRM_' . $name[0] . '_BAO_' . $name[1] . '();');
            $bao->$name[2] = $financialAccountId;
            if ($bao->find(true)) {
                $check = true;
            }
        }
        
        if ($check) {
            $session = CRM_Core_Session::singleton();
            CRM_Core_Session::setStatus( ts(
                'This financial account cannot be deleted since it is being used as a header account. Please remove it from being a header account before trying to delete it again.') );
            return CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/admin/financial/financialAccount', "reset=1&action=browse" ));
        }
        
        //delete from financial Type table
        require_once 'CRM/Contribute/DAO/Contribution.php';
        $financialAccount = new CRM_Financial_DAO_FinancialAccount( );
        $financialAccount->id = $financialAccountId;
        $financialAccount->delete();
    }
}

