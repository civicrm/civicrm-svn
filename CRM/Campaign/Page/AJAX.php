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
    static function getSurveyCustomGroup( )
    {
        //$customGroups  = array( );
        require_once 'CRM/Campaign/BAO/Survey.php';
        require_once 'CRM/Core/OptionGroup.php';
        $sid      = CRM_Utils_Array::value('sid', $_REQUEST);
        $surveyId = CRM_Core_OptionGroup::getValue('activity_type','Survey','name');
        $params[] = $surveyId;
        if ( $sid ) {
            $params[] = $sid;
        }
       
        // get survey custom groups
        $customGroups = CRM_Campaign_BAO_Survey::getSurveyCustomGroups( $params );
        
        $elements[] = array( 'name'  => ts('- select -'),
                             'value' => '');
        $selectGroups = array( );
        if ( !empty($customGroups ) ) {
             foreach ( $customGroups as $gid => $group ) {
                 if ( CRM_Utils_Array::value('extends', $group) ) {
                     $extends = explode( CRM_Core_DAO::VALUE_SEPARATOR, $group['extends']);
                     foreach( $extends as $tid ) {
                         if ($tid) {
                             $selectGroups[$gid] = $gid;  
                         }
                     }
                 }
             }
        }
        
        if ( !empty($selectGroups) ) {
            foreach( $selectGroups as $gid ) {
                $elements[] = array( 'name'  => ts( $customGroups[$gid]['title']),
                                     'value' => $gid);
            }
        }
        require_once "CRM/Utils/JSON.php";
        echo json_encode( $elements );
        
        CRM_Utils_System::civiExit( );
        
    }
    
    static function registerInterview( )
    {
        $voterId = CRM_Utils_Array::value( 'voter_id', $_POST );
        $params  = array( 'voter_id'       => $voterId,
                          'interviewer_id' => CRM_Utils_Array::value( 'interviewer_id', $_POST ),
                          'survey_type_id' => CRM_Utils_Array::value( 'survey_type_id', $_POST ) );
        
        $customKey = "field_{$voterId}_custom";
        foreach ( $_POST as $key => $value ) {
            if ( strpos( $key, $customKey ) !== false ) {
                $customFieldKey = str_replace( str_replace( substr( $customKey, -6 ), '', $customKey ), '', $key );
                $params[$customFieldKey] = $value;
            }
        }
        $interviewId = CRM_Utils_Array::value( "field_{$voterId}_interview_id", $_POST );
        if ( $interviewId ) $params['interview_id'] = $interviewId; 
        
        require_once 'CRM/Campaign/Form/Task/Interview.php';
        $interviewId = CRM_Campaign_Form_Task_Interview::registerInterview( $params );
        $result = array( 'status'       => ( $interviewId ) ? 'success' : 'fail',
                         'voter_id'     => $voterId,
                         'interview_id' => $interviewId );
        
        require_once "CRM/Utils/JSON.php";
        echo json_encode( $result );
        
        CRM_Utils_System::civiExit( );
    }
    
}