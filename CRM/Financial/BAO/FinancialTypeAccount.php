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

require_once 'CRM/Financial/DAO/EntityFinancialAccount.php';

class CRM_Financial_BAO_FinancialTypeAccount extends CRM_Financial_DAO_EntityFinancialAccount
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
        $financialTypeAccount = new CRM_Financial_DAO_EntityFinancialAccount( );
        $financialTypeAccount->copyValues( $params );
        if ( $financialTypeAccount->find( true ) ) {
            CRM_Core_DAO::storeValues( $financialTypeAccount, $defaults );
            return $financialTypeAccount;
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
    // static function setIsActive( $id, $is_active ) 
    // {
    //     return CRM_Core_DAO::setFieldValue( 'CRM_Financial_DAO_FinancialTypeAccount', $id, 'is_active', $is_active );
    // }

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
        
        // action is taken depending upon the mode
        $financialTypeAccount              = new CRM_Financial_DAO_EntityFinancialAccount( );
        $financialTypeAccount->copyValues( $params );;
        
        $financialTypeAccount->id = CRM_Utils_Array::value( 'entityFinancialAccount', $ids );
        $financialTypeAccount->save( );
        return $financialTypeAccount;
    }
    
    /**
     * Function to delete contribution Types 
     * 
     * @param int $contributionTypeId
     * @static
     */
    
    static function del($financialTypeAccountId) 
    {
        //checking if financial type is present  
        $check = false;
        
        //check dependencies
        // $dependancy = array( 
        //                     array('Contribute', 'Contribution'), 
        //                     array('Contribute', 'ContributionPage'), 
        //                     array('Member', 'MembershipType')
        //                     );
        // foreach ($dependancy as $name) {
        //     require_once (str_replace('_', DIRECTORY_SEPARATOR, "CRM_" . $name[0] . "_BAO_" . $name[1]) . ".php");
        //     eval('$bao = new CRM_' . $name[0] . '_BAO_' . $name[1] . '();');
        //     $bao->financial_account_id = $financialTypeAccountId;
        //     if ($bao->find(true)) {
        //         $check = true;
        //     }
        // }
       
        // if ($check) {
        //     $session = CRM_Core_Session::singleton();
        //     CRM_Core_Session::setStatus( ts(
        //         'This financial type cannot be deleted because it is being referenced by one or more of the following types of records: Contributions, Contribution Pages, or Membership Types. Consider disabling this type instead if you no longer want it used.') );
        //     return CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/admin/financial/financialType/accounts', "reset=1&action=browse" ));
        // }
        
        //delete from contribution Type table
        require_once 'CRM/Contribute/DAO/Contribution.php';
        $financialType = new CRM_Financial_DAO_EntityFinancialAccount( );
        $financialType->id = $financialTypeAccountId;
        $financialType->delete();
    }
}

