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
                             'details'          => CRM_Utils_Array::value( 'note',             $_POST ),
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
        
        if ( isset($_POST['field']) &&
             CRM_Utils_Array::value( $voterId, $_POST['field']) ) {
            foreach( $_POST['field'][$voterId] as $fieldKey => $value ) {
                if ( !empty($value) ) {
                    $params[$fieldKey] = $value;
                }
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
    
    static function loadOptionGroupDetails( ) {

        $id       = CRM_Utils_Array::value( 'option_group_id', $_POST );
        $status   = 'fail';
        $opValues = array( );
        
        if ( $id ) {
            require_once 'CRM/Core/OptionValue.php';
            $groupParams['id'] = $id;
            CRM_Core_OptionValue::getValues( $groupParams, $opValues );
        }

        $surveyId = CRM_Utils_Array::value( 'survey_id', $_POST );
        if ( $surveyId ) {
            require_once 'CRM/Campaign/DAO/Survey.php';
            $survey = new CRM_Campaign_DAO_Survey( );
            $survey->id        = $surveyId;
            $survey->result_id = $id;
            if ( $survey->find( true ) ) {
                if ( $survey->recontact_interval ) {
                    $recontactInterval = unserialize( $survey->recontact_interval );
                    foreach( $opValues as $opValId => $opVal ) {
                        if ( CRM_Utils_Array::value( $opVal['value'], $recontactInterval) ) {
                            $opValues[$opValId]['interval'] = $recontactInterval[$opVal['value']];
                        }
                    }
                }
            }
        }

        if ( !empty($opValues) ) {
            $status = 'success';
        }

        $result = array( 'status' => $status,
                         'result' => $opValues);
        
        echo json_encode( $result );
        CRM_Utils_System::civiExit( );
    }
}