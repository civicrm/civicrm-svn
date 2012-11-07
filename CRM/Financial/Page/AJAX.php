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

 
}
