<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

/**
 * This class contains all the function that are called using AJAX (jQuery)
 */
class CRM_Activity_Page_AJAX
{
    static function getCaseActivity( ) 
    {
        $caseID     = CRM_Utils_Type::escape( $_GET['caseID'], 'Integer' );
        $contactID  = CRM_Utils_Type::escape( $_GET['cid'], 'Integer' );
        
        $params     = $_POST;

        // get the activities related to given case
        require_once "CRM/Case/BAO/Case.php";
        $activities = CRM_Case_BAO_Case::getCaseActivity( $caseID, $params, $contactID );

        $page  = CRM_Utils_Array::value( 'page', $_POST );
        $total = $params['total'];

        require_once "CRM/Utils/JSON.php";
        $selectorElements = array( 'display_date', 'subject', 'type', 'with_contacts', 'reporter', 'status', 'links', 'class' );
        echo CRM_Utils_JSON::encodeSelector( $activities, $page, $total, $selectorElements );
        exit();
    }
   
    static function convertToCaseActivity()
    {
        $caseID           = CRM_Utils_Array::value( 'caseID', $_POST );
        $activityID       = CRM_Utils_Array::value( 'activityID', $_POST );
        $contactID        = CRM_Utils_Array::value( 'contactID', $_POST );
        $newSubject       = CRM_Utils_Array::value( 'newSubject', $_POST );
        $targetContactIds = CRM_Utils_Array::value( 'targetContactIds', $_POST );
        $mode             = CRM_Utils_Array::value( 'mode', $_POST );
        
        require_once "CRM/Activity/DAO/Activity.php";
        $otherActivity = new CRM_Activity_DAO_Activity();
        $otherActivity->id = $activityID;
        $otherActivity->find( true );
        
        $mainActVals  = array( );
        $mainActivity = new CRM_Activity_DAO_Activity( );
        CRM_Core_DAO::storeValues( $otherActivity, $mainActVals );
        if ( !empty( $newSubject ) ) $mainActVals['subject'] = $newSubject;
        $mainActivity->copyValues( $mainActVals );
        $mainActivity->id = null;
        $mainActivity->activity_date_time = CRM_Utils_Date::isoToMysql( $otherActivity->activity_date_time );
        $mainActivity->save( );
        $mainActivityId = $mainActivity->id;
        $mainActivity->free( );
        $otherActivity->free( ); 
        if( in_array( $mode, array( 'move', 'file' ) ) ) {
            CRM_Core_DAO::setFieldValue( 'CRM_Activity_DAO_Activity', $activityID, 'is_deleted', 1 );
        }
                
        require_once "CRM/Activity/BAO/Activity.php";
        $targetContacts = array( $contactID );
        if ( !empty( $targetContactIds ) ) {
            $targetContacts = array_merge( $targetContacts, explode( ',', $targetContactIds ) );
        }
        $targetContacts = array_unique( $targetContacts );
        foreach ( $targetContacts as $key => $value ) { 
            $params = array( 'activity_id' => $mainActivityId, 
                             'target_contact_id' => $value );
            CRM_Activity_BAO_Activity::createActivityTarget( $params );
        }
        require_once "CRM/Case/DAO/CaseActivity.php";
        $caseActivity = new CRM_Case_DAO_CaseActivity( );
        $caseActivity->case_id = $caseID;
        $caseActivity->activity_id = $mainActivityId;
        $caseActivity->save( );
        
		$error_msg = $caseActivity->_lastError;
		$caseActivity->free( ); 
        echo json_encode(array('error_msg' => $error_msg));
    }
}