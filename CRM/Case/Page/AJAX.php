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
    static function getOpenCases( ) 
    {
        $limit  = '10';
        
        require_once 'CRM/Case/BAO/Case.php';
        $unclosedCases = CRM_Case_BAO_Case::getUnclosedCases( );
        
        $caseList = null;
        foreach ( $unclosedCases as $caseId => $details ) {
            echo $caseList = 'Case ID: '.$caseId.' Type: '.$details['case_type'].' Start: '.$details['start_date'] .' Client: ' . $details['display_name'] . "|$caseId\n";
        }
        
        exit( );
    }
    
    /**
     * Retrieve and display related case details.
     */
    static function getRelatedCases( ) {
        $contactID  = CRM_Utils_Type::escape( $_GET['cid'],    'Integer' ); 
        $mainCaseID = CRM_Utils_Type::escape( $_GET['caseId'], 'Integer' );
        
        require_once 'CRM/Case/BAO/Case.php';
        $relatedCases = CRM_Case_BAO_Case::getRelatedCases( $mainCaseID, $contactID );
        
        foreach ( $relatedCases as $caseId => $caseDetails ) {
            echo 'Client Name : ' . $caseDetails['client_name'] . ' Case Type : ' . $caseDetails['case_type'];
        }
        
        exit( );
    }
}
