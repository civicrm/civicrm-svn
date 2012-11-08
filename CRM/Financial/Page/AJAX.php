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
  function financialAccount() {
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
SELECT CONCAT_WS(' :: ', f.name, accounting_code) as name, f.id
FROM   civicrm_financial_account as f
WHERE  {$whereClause}
ORDER by f.name";
       

        $dao = CRM_Core_DAO::executeQuery( $query );
            while ( $dao->fetch( ) ) {
                echo $elements = "$dao->name|$dao->id\n";
            }
            CRM_Utils_System::civiExit( );
    }

    /*
     * Function to get finacial accounts of required account relationship
     * $financialAccountType array with key account relationship and value financial account type option groups
     *
     */
    function jqFinancial( $config ) {
        if ( ! isset( $_GET['_value'] ) ||
             empty( $_GET['_value'] ) ) {
            CRM_Utils_System::civiExit( );
        }
        if( $_GET['_value'] == 'select'  ){
            $result = CRM_Contribute_PseudoConstant::financialAccount( );        
        } else {
      $financialAccountType = array( 
        '5' => 5, //expense
                                           '3' => 1, //AR relation
                                           '1' => 3, //revenue
        '6' => 1, // asset
        '7' => 4, //cost of sales
        '8' => 1, //premium inventory
        '9' => 3, //discount account is
                                           );
 
            $financialAccountType = "financial_account_type_id = {$financialAccountType[$_GET['_value']]}";
      $result = CRM_Contribute_PseudoConstant::financialAccount(NULL, $financialAccountType);
}
    $elements = array( 
      array( 
        'name'  => ts('- Select Financial Account -'),
        'value' => 'select' 
      )
    );
        if( !empty( $result ) ){
            foreach ( $result as $id => $name ) {
        $elements[] = array( 
          'name'  => $name,
          'value' => $id 
        );
            }
        }
        echo json_encode( $elements );
        CRM_Utils_System::civiExit( );
    }
    
    function jqFinancialRelation( $config ) {
        if ( ! isset( $_GET['_value'] ) ||
             empty( $_GET['_value'] ) ) {
            CRM_Utils_System::civiExit( );
        }  
        if( $_GET['_value'] == 'select'  ){
            $result = CRM_Core_PseudoConstant::accountOptionValues( 'account_relationship' );     
        } else {
      $financialAccountType = array( 
        '5' => array(5), //expense
        '1' => array(3, 6, 8), //Asset
        '3' => array(1, 9), //revenue
        '4' => array(7), //cost of sales
                                           );
            $financialAccountTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Financial_DAO_FinancialAccount', $_GET['_value'], 'financial_account_type_id' );
            $result = CRM_Core_PseudoConstant::accountOptionValues( 'account_relationship' ); 
        }
    $elements = array( 
      array( 
        'name'  => ts('- Select Financial Account Relationship -'),
        'value' => 'select' 
        ) 
    );
        $countResult = count( $financialAccountType[$financialAccountTypeId] );
        if( !empty( $result ) ){
            foreach ( $result as $id => $name ) {
                if( in_array( $id, $financialAccountType[$financialAccountTypeId] )  && $_GET['_value'] != 'select' ){
                    if ( $countResult != 1){
            $elements[] = array( 
              'name'  => $name,
              'value' => $id 
            );
                    }else{
            $elements[] = array( 
              'name'     => $name,
                                             'value'    => $id,
              'selected' => 'Selected', 
            );
                    }
                }else if( $_GET['_value'] == 'select' ){
          $elements[] = array( 
            'name'  => $name,
            'value' => $id 
          ); 
                                             }
            }
        }
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
    
    function getContactList( ) 
    {
        // if context is 'customfield' 
        if ( CRM_Utils_Array::value( 'context', $_GET ) == 'customfield' ) {
            return self::contactReference( );
}

        $name   = CRM_Utils_Array::value( 's', $_GET );
        $name   = CRM_Utils_Type::escape( $name, 'String' );
        
        $limit = 10;
        if ( CRM_Utils_Array::value( 'limit', $_GET) ) {
            $limit = CRM_Utils_Type::escape( $_GET['limit'], 'Positive' );
        }

        if ( CRM_Utils_Array::value( 'cid', $_GET) ) {
            $cid = CRM_Utils_Type::escape( $_GET['cid'], 'Positive' );
        }

        $userPerm = 0;
        if ( CRM_Utils_Array::value( 'users', $_GET) ) {
            $userPerm = CRM_Utils_Type::escape( $_GET['users'], 'Integer' );
        }
        require_once 'DB.php';   
        $config = CRM_Core_Config::singleton( );
        $db_uf = DB::parseDSN($config->userFrameworkDSN);
        $select = ' sort_name, email ';
        $from = " LEFT JOIN civicrm_email eml ON ( cc.id = eml.contact_id AND eml.is_primary = 1 ) 
LEFT  JOIN civicrm_uf_match as cum on cum.contact_id = cc.id
LEFT  JOIN {$db_uf['database']}.users as users on users.uid = cum.uf_id
LEFT  JOIN {$db_uf['database']}.users_roles as users_roles ON users.uid = users_roles.uid
INNER JOIN {$db_uf['database']}.role_permission AS permission  ON ( permission.rid = users_roles.rid ) "; 
        $where = " AND permission.permission LIKE 'edit own manual batches' or permission.permission LIKE 'create manual batch' or permission.permission LIKE 'edit all manual batches' AND users.status = 1 AND cc.is_deleted = 0";
        $whereClause = " WHERE ( email LIKE '%{$name}%' OR sort_name LIKE '%{$name}%'  ) ";
        $exactWhereClause = " WHERE ( email LIKE '$name' OR sort_name LIKE '$name'  ) ";
        $query = "
        SELECT DISTINCT(id), data, {$select}
        FROM   (
            ( SELECT 0 as exactFirst, cc.id as id, {$select},CONCAT_WS( ' :: ', {$select} ) as data
            FROM   civicrm_contact cc {$from}
    {$exactWhereClause}
    GROUP BY sort_name
    LIMIT 0, {$limit} )
    UNION
    ( SELECT 1 as exactFirst, cc.id as id, {$select}, CONCAT_WS( ' :: ', {$select} ) as data
    FROM   civicrm_contact cc {$from}
    {$whereClause}
    GROUP BY sort_name
    ORDER BY sort_name
    LIMIT 0, {$limit} )
) t
ORDER BY exactFirst, sort_name
LIMIT    0, {$limit}
    ";
        
        $dao =CRM_Core_DAO::executeQuery( $query );
        while( $dao->fetch() ){
            echo $contactList = "{$dao->data}|{$dao->id}\n";
            
        }
        CRM_Utils_System::civiExit( );
    } 

 /**
     * Function to perform remove/assign actions on record.
     *
     */
    static function assignRemove( ) {
        $op        = CRM_Utils_Type::escape( $_POST['op'       ],  'String'   );
        $recordID  = CRM_Utils_Type::escape( $_POST['recordID' ],  'Positive' );
        $recordBAO = CRM_Utils_Type::escape( $_POST['recordBAO'],  'String'   );

        $entityID  = CRM_Utils_Array::value( 'entityID', $_POST );
        $methods = array( 'assign' => 'add',
                          'remove' => 'remove',
                          'reopen' => 'create',
                          'close'  => 'create' );
        $params = array( );
        $status = array( 'status' => 'record-updated-fail' );
        if ( isset( $recordID ) ) { 
            // first munge and clean the recordBAO and get rid of any non alpha numeric characters
            $recordBAO = CRM_Utils_String::munge( $recordBAO );
            $recordClass = explode( '_', $recordBAO );
            $ids = null;
            // make sure recordClass is in the CRM namespace and
            // at least 3 levels deep
            if ( $recordClass[0] == 'CRM' &&
                 count( $recordClass ) >= 3 ) {
                require_once(str_replace('_', DIRECTORY_SEPARATOR, $recordBAO) . ".php");
                switch ($op){
                case 'assign':
                 
                case 'remove': 
                    $params = array( 'entity_id' => $entityID,
                                     'entity_table' => 'civicrm_batch',
                                     'financial_item_id' => $recordID,
                                     );
                    break;
                case 'reopen': 
                case 'close': 
                    if( $op == 'close' ){
                        $status = 'Closed';
                    }
                    else {
                        $status = 'Open';
                    }
                    $ids['batchID'] = $recordID;
                    $batchStatus = CRM_Core_PseudoConstant::accountOptionValues( 'batch_status' );
                    $params['batch_status_id'] = CRM_Utils_Array::key( $status, $batchStatus );
                    $session = CRM_Core_Session::singleton( );
                    $params['modified_date'] = date('YmdHis');
                    $params['modified_id'] = $session->get( 'userID' );
                    break;
                }
                

                if ( method_exists( $recordBAO, $methods[$op] ) ) {
                    $updated = call_user_func_array( array( $recordBAO, $methods[$op] ),
                                                     array( $params, $ids ) );
                    if ( $updated ) {   
                        $status = array( 'status' => 'record-updated-success' );
                    }

                    // // call hook enableDisable
                    // CRM_Utils_Hook::enableDisable( $recordBAO, $recordID, $isActive );
                }
            }
            echo json_encode( $status );
            CRM_Utils_System::civiExit( );
        }
    }
}