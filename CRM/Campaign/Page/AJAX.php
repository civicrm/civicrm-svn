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
 * This class contains all campaign related functions that are called using AJAX (jQuery)
 */
class CRM_Campaign_Page_AJAX
{
    
    static function registerInterview( )
    {
        $voterId    = CRM_Utils_Array::value( 'voter_id',    $_POST );
        $activityId = CRM_Utils_Array::value( 'activity_id', $_POST );
        $params     = array( 'voter_id'         => $voterId,
                             'activity_id'      => $activityId,
                             'result'           => CRM_Utils_Array::value( 'result',           $_POST ),
                             'interviewer_id'   => CRM_Utils_Array::value( 'interviewer_id',   $_POST ),
                             'activity_type_id' => CRM_Utils_Array::value( 'activity_type_id', $_POST ) );
        
        $customKey = "field_{$voterId}_custom";
        foreach ( $_POST as $key => $value ) {
            if ( strpos( $key, $customKey ) !== false ) {
                $customFieldKey = str_replace( str_replace( substr( $customKey, -6 ), '', $customKey ), '', $key );
                $params[$customFieldKey] = $value;
            }
        }
        
        require_once 'CRM/Campaign/Form/Task/Interview.php';
        $activityId = CRM_Campaign_Form_Task_Interview::registerInterview( $params );
        $result = array( 'status'       => ( $activityId ) ? 'success' : 'fail',
                         'voter_id'     => $voterId,
                         'activity_id'  => $interviewId );
        
        require_once "CRM/Utils/JSON.php";
        echo json_encode( $result );
        
        CRM_Utils_System::civiExit( );
    }
    
}