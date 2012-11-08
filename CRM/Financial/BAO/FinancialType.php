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

require_once 'CRM/Financial/DAO/FinancialType.php';

class CRM_Financial_BAO_FinancialType extends CRM_Financial_DAO_FinancialType
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
        $financialType = new CRM_Financial_DAO_FinancialType( );
        $financialType->copyValues( $params );
        if ( $financialType->find( true ) ) {
            CRM_Core_DAO::storeValues( $financialType, $defaults );
            return $financialType;
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
        return CRM_Core_DAO::setFieldValue( 'CRM_Financial_DAO_FinancialType', $id, 'is_active', $is_active );
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
        $params['is_reserved'] =  CRM_Utils_Array::value( 'is_reserved', $params, false );
         
        
        // action is taken depending upon the mode
        $financialType               = new CRM_Financial_DAO_FinancialType( );
        $financialType->copyValues( $params );;
        
        if( CRM_Utils_Array::value( 'financialType', $ids ) ){            
            $oldFinancialType      = new CRM_Financial_DAO_FinancialType( );
            $oldFinancialType->id  = CRM_Utils_Array::value( 'financialType', $ids );
            $oldFinancialType->is_current_revision = 0;
            if( $originalId = CRM_Core_DAO::getFieldValue( 'CRM_Financial_DAO_FinancialType', $oldFinancialType->id, 'original_id' ) )
                $financialType->original_id = $originalId;
            else
                $financialType->original_id = $oldFinancialType->id;
              
        }

        $financialType->save( );
        if( CRM_Utils_Array::value( 'financialType', $ids ) ){
            $oldFinancialType->save( ); 
        }
        return $financialType;
    }
    
    /**
     * Function to delete financial Types 
     * 
     * @param int $contributionTypeId
     * @static
     */
    
    static function del($financialTypeId) 
    {
        //checking if financial type is present  
        $check = false;
        
        //check dependencies
        $dependancy = array( 
                            array('Contribute', 'Contribution'), 
                            array('Contribute', 'ContributionPage'), 
                            array( 'Member', 'MembershipType' ),
                            array( 'Price', 'FieldValue' ),
                            array( 'Grant', 'Grant' ),
                            array( 'Contribute', 'ManagePremiums' ),
                            );
        foreach ($dependancy as $name) {
            require_once (str_replace('_', DIRECTORY_SEPARATOR, "CRM_" . $name[0] . "_BAO_" . $name[1]) . ".php");
            eval('$bao = new CRM_' . $name[0] . '_BAO_' . $name[1] . '();');
            $bao->financial_type_id = $financialTypeId;
            if ($bao->find(true)) {
                $check = true;
                break;
            }
        }
        if ($check) {
        return TRUE;
        }
        
        //delete from financial Type table
        $financialType = new CRM_Financial_DAO_FinancialType( );
        $financialType->id = $financialTypeId;
        $financialType->delete();
        
        $entityFinancialType = new CRM_Financial_DAO_EntityFinancialAccount( );
        $entityFinancialType->entity_id = $financialTypeId;
        $entityFinancialType->entity_table = 'civicrm_financial_type';
        $entityFinancialType ->delete();
      return FALSE;
    }
}

