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
 * $Id$
 *
 */


require_once 'CRM/Campaign/BAO/Survey.php';

Class CRM_Campaign_BAO_Petition extends CRM_Campaign_BAO_Survey
{
    
    /**
     * takes an associative array and creates a petition signature activity
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     *
     * @return object CRM_Campaign_BAO_Petition
     * @access public
     * @static
     */
    static function createSignature( &$params ) 
    {
        if ( empty( $params ) ) {
            return;
        }
        
        if ( !isset( $params['sid'] ) ) {
            $statusMsg = ts( 'No survey sid parameter. Cannot process signature.' );
            CRM_Core_Session::setStatus( $statusMsg );			
			return;
		}
  
        if ( isset( $params['cid'] ) ) {
        
        	// add signature as activity with survey id as source id
        	// get the activity type id associated with this survey        	
        	$surveyInfo = CRM_Campaign_BAO_Petition::getSurveyInfo($params['sid']);

	        require_once 'CRM/Activity/BAO/Activity.php';
			// create activity 
			// activity status id (from /civicrm/admin/optionValue?reset=1&action=browse&gid=25)
			// 1-Schedule, 2-Completed
	        
			$activityParams = array ( 'source_contact_id'  => $params['cid'],
			                          'source_record_id'   => $params['sid'],
									  'activity_type_id'   => $surveyInfo['activity_type_id'],
									  'activity_date_time' => date("YmdHis"), 
									  'status_id'          => 2 );
									  			
			//activity creation
        	// *** check for activity using source id - if already signed
			$activity = CRM_Activity_BAO_Activity::create( $activityParams );	

			if ( isset( $params['tag'] ) ) {
	        	// contact 'email confirmed' tag is set, so set this tag against the activity too
				require_once 'CRM/Core/BAO/EntityTag.php';
				$entityId = array($activity->id);
				CRM_Core_BAO_EntityTag::addEntitiesToTag($entityId, $params['tag'], 'civicrm_activity');
	        }
			
		}
		
        return $contact;
    }

    
    public function getSurveyInfo( $surveyId=null ) 
    {
		$surveyInfo = array( );

        $sql = "
SELECT 	s.activity_type_id AS activity_type_id, 
		s.campaign_id AS campaign_id,
		s.title AS title,
		ov.label AS activity_type
FROM  civicrm_survey s, civicrm_option_value ov, civicrm_option_group og
WHERE s.id = " . $surveyId ."
AND s.activity_type_id = ov.value
AND ov.option_group_id = og.id
AND og.name = 'activity_type'";
        
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {      
           //$survey['campaign_id'] = $dao->campaign_id;  
           //$survey['campaign_name'] = $dao->campaign_name; 
           $surveyInfo['activity_type'] = $dao->survey_type; 
           $surveyInfo['activity_type_id'] = $dao->activity_type_id;   
           $surveyInfo['title'] = $dao->title; 
        }
           	
        return $surveyInfo ;
    }
    
     /**
     * Function to get Petition Signature Details 
     * 
     * @param boolean $all
     * @param int $id
     * @static
     */
    static function getPetitionSignature( $surveyId ) {
    
    	$surveyInfo = CRM_Campaign_BAO_Petition::getSurveyInfo($surveyId);
    	//$activityTypeID = $surveyInfo['activity_type_id'];
        $signature = array( );	

        $sql = "
SELECT 	a.id AS id,
		a.source_record_id AS source_record_id,
		a.source_contact_id AS source_contact_id,
		a.activity_date_time AS activity_date_time,
		a.activity_type_id AS activity_type_id,
		a.status_id AS status_id," .
		"'" . $surveyInfo['title'] . "'" ." AS survey_title 
FROM  	civicrm_activity a
WHERE 	a.source_record_id = " . $surveyId . " 
	AND a.activity_type_id = " . $surveyInfo['activity_type_id'];


        require_once 'CRM/Contact/BAO/Contact.php'; 

        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
           $signature[$dao->id]['id'] = $dao->id;     
           $signature[$dao->id]['source_record_id'] = $dao->source_record_id;
           $signature[$dao->id]['source_contact_id'] = CRM_Contact_BAO_Contact::displayName($dao->source_contact_id);
           $signature[$dao->id]['activity_date_time'] = $dao->activity_date_time;
           $signature[$dao->id]['activity_type_id'] = $dao->activity_type_id;   
           $signature[$dao->id]['status_id'] = $dao->status_id;
           $signature[$dao->id]['survey_title'] = $dao->survey_title;
           $signature[$dao->id]['cid'] = $dao->source_contact_id;
        }

        return $signature;
    }    


}

?>