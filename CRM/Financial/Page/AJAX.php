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

/**
 * This class contains all the function that are called using AJAX
 */
class CRM_Financial_Page_AJAX
{
   

    /**
     * Function for building Event Type combo box
     */
    function financialAccount( )
    {
        require_once 'CRM/Utils/Type.php';
        $name = trim( CRM_Utils_Type::escape( $_GET['s'], 'String' ) );
        if( !$name ) {
            $name = '%';
        }
        $whereClause = " f.name LIKE '$name%' ";
        //if( CRM_Utils_Array::getValue( 'id',$_GET ) )
        if( array_key_exists( 'parentID', $_GET ) ){
            $parentID = $_GET['parentID'];
            $whereClause .= " AND f.id = {$parentID} ";
        }
        $query ="
SELECT f.name ,f.id
FROM   civicrm_financial_account as f
WHERE  {$whereClause}
ORDER by f.name";
       

        $dao = CRM_Core_DAO::executeQuery( $query );
            while ( $dao->fetch( ) ) {
                echo $elements = "$dao->name|$dao->id\n";
            }
            CRM_Utils_System::civiExit( );
    }

    function jqFinancial( $config ) {
        require_once 'CRM/Contribute/PseudoConstant.php';
        if ( ! isset( $_GET['_value'] ) ||
             empty( $_GET['_value'] ) ) {
            CRM_Utils_System::civiExit( );
        }
        if( $_GET['_value'] == 'select'  ){
            $result = CRM_Contribute_PseudoConstant::financialAccount( );        
        } else {
            $financialAccountType = array( '5' => 5, //expense
                                           '3' => 1, //AR relation
                                           '1' => 3, //revenue
                                           );
 
            $financialAccountType = "financial_account_type_id = {$financialAccountType[$_GET['_value']]}";
            $result = CRM_Contribute_PseudoConstant::financialAccount( null, $financialAccountType );
}
        $elements = array( array( 'name'  => ts('- Select Financial Account -'),
                                  'value' => 'select' ) );
        if( !empty( $result ) ){
            foreach ( $result as $id => $name ) {
                $elements[] = array( 'name'  => $name,
                                     'value' => $id );
            }
        }
        require_once "CRM/Utils/JSON.php";
        echo json_encode( $elements );
        CRM_Utils_System::civiExit( );
    }
    
    function jqFinancialRelation( $config ) {
        require_once 'CRM/Core/PseudoConstant.php';
        require_once 'CRM/Core/DAO.php';
        if ( ! isset( $_GET['_value'] ) ||
             empty( $_GET['_value'] ) ) {
            CRM_Utils_System::civiExit( );
        }  
        if( $_GET['_value'] == 'select'  ){
            $result = CRM_Core_PseudoConstant::accountOptionValues( 'account_relationship' );     
        } else {
            $financialAccountType = array( '5' => array('5','1'), //expense
                                           '1' => array('3'), //AR relation
                                           '3' => array('1'), //revenue
                                           );
            $financialAccountTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Financial_DAO_FinancialAccount', $_GET['_value'], 'financial_account_type_id' );
            $result = CRM_Core_PseudoConstant::accountOptionValues( 'account_relationship' ); 
        }
        $elements = array( array( 'name'  => ts('- Select Financial Account Relationship -'),
                                  'value' => 'select' ) );
        $countResult = count( $financialAccountTypeId[$financialAccountTypeId] );
        if( !empty( $result ) ){
            foreach ( $result as $id => $name ) {
                if( in_array( $id, $financialAccountType[$financialAccountTypeId] )  && $_GET['_value'] != 'select' ){
                    if ( $countResult != 1){
                        $elements[] = array( 'name'  => $name,
                                             'value' => $id );
                    }else{
                        $elements[] = array( 'name'     => $name,
                                             'value'    => $id,
                                             'selected' => 'Selected', );
                    }
                }else if( $_GET['_value'] == 'select' ){

                    $elements[] = array( 'name'  => $name,
                                             'value' => $id ); 
                                             }
                
            }
        }
        require_once "CRM/Utils/JSON.php";
        echo json_encode( $elements );
        CRM_Utils_System::civiExit( );
    }
 
    function jqFinancialType( $config ) {
        if ( ! isset( $_GET['_value'] ) ||
             empty( $_GET['_value'] ) ) {
            CRM_Utils_System::civiExit( );
}
        require_once 'CRM/Core/DAO.php';
        $elements = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_Product', $_GET['_value'], 'financial_type_id' );
        
        require_once "CRM/Utils/JSON.php";
        echo json_encode( $elements );
        CRM_Utils_System::civiExit( );
    }
}
