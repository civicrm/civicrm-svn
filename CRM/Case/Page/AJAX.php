<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
 *
 */

require_once 'CRM/Utils/Type.php';

/**
 * This class contains all case related functions that are called using AJAX (jQuery)
 */
class CRM_Case_Page_AJAX
{
    /**
     * Retrieve unclosed cases.
     */
    static function unclosedCases( ) 
    {
        $criteria =  explode( '-', CRM_Utils_Type::escape( CRM_Utils_Array::value( 's', $_GET ), 'String' ) );
        $limit    =  CRM_Utils_Type::escape( CRM_Utils_Array::value( 'limit', $_GET ), 'Integer' );
        $params   =  array( 'limit'     => $limit, 
                            'case_type' => trim( CRM_Utils_Array::value( 1, $criteria ) ),
                            'sort_name' => trim( CRM_Utils_Array::value( 0, $criteria ) ) );
        
        require_once 'CRM/Case/BAO/Case.php';
        $unclosedCases = CRM_Case_BAO_Case::getUnclosedCases( $params );
        
        $caseList = null;
        foreach ( $unclosedCases as $caseId => $details ) {
            echo $details['sort_name'] . ' - ' . $details['case_type'] . "|$caseId\n";
        }
        
        exit( );
    }
}
