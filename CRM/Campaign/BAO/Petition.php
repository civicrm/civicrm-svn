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
     * the function extract all the params it needs to initialize the create a
     * contact object. the params array could contain additional unused name/value
     * pairs
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

		// Check if contact exists - can be logged in, or in database, or need to create new contact
		$contact_exists = 0;
		// Is this contact the logged in user?
        if ( isset( $params['cid'] ) ) {
        	$contact_exists = 1;
        }
        
        if (!$contact_exists) {
       		require_once 'CRM/Contact/BAO/Contact.php'; 
			// check if email entered into signature form already in database
			// *** CiviCRM allows multiple contacts with same email 
			// *** this returns first one found 
			$contact = CRM_Contact_BAO_Contact::matchContactOnEmail( $params['email'], $ctype = 'Individual' );
			if (isset($contact)) {
				$params['cid'] = $contact->contact_id;
				$contact_exists = 1;
			} else {
				// create new contact
				// *** CiviCRM allows multiple contacts with same email 
        		$params[contact_id] = '';
        		$params[contact_type] = 'Individual';
        		
        		if ( ($email = CRM_Utils_Array::value( 'email', $params ) ) && !is_array( $params['email'] ) ) {
            		require_once 'CRM/Core/BAO/LocationType.php';
            		$defLocType = CRM_Core_BAO_LocationType::getDefault( );
            		$params['email'] = array( 1 => array( 'email'            => $email,
                                                  'is_primary'       => 1, 
                                                  'location_type_id' => ($defLocType->id)?$defLocType->id:1
                                                  ),
                                     );
        		}
        		
        		$contact =& CRM_Contact_BAO_Contact::create($params);
				$params['cid'] = $contact->id;
				$contact_exists = 1;
			}
		}

        if ($contact_exists) {
        	// add signature as activity with survey id as source id
        	// get the activity type id and campaign id associated with this survey
        	$surveyInfo = CRM_Campaign_BAO_Petition::getSurveyInfo($params['sid']);

	        require_once 'CRM/Activity/BAO/Activity.php';
			// create activity 
			// activity status id (from /civicrm/admin/optionValue?reset=1&action=browse&gid=25)
			// 1-Schedule, 2-Completed
			$activityParams = array ( 'source_contact_id'  => $params['cid'],
			                          'source_record_id'   => $params['sid'],
									  'activity_type_id'   => $surveyInfo['survey_type_id'],
									  'campaign_id'		   => $surveyInfo['campaign_id'],
									  'activity_date_time' => date("YmdHis"), 
									  'status_id'          => 2 );
									  			
			//activity creation
			$activity = CRM_Activity_BAO_Activity::create( $activityParams );			
		}
		
        return $contact;
    }

    
    public function getSurveyInfo( $surveyId=null ) 
    {
		$survey = array( );

        $sql = "
SELECT 	s.survey_type_id AS survey_type_id, 
		s.campaign_id AS campaign_id,
		c.name AS campaign_name,
		ov.label AS survey_type
FROM  civicrm_survey s, civicrm_campaign c, civicrm_option_value ov, civicrm_option_group og
WHERE s.id = " . $surveyId ."
AND s.campaign_id = c.id
AND s.survey_type_id = ov.value
AND ov.option_group_id = og.id
AND og.name = 'activity_type'";
        
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {      
           $survey['campaign_id'] = $dao->campaign_id;  
           $survey['campaign_name'] = $dao->campaign_name; 
           $survey['survey_type'] = $dao->survey_type; 
           $survey['survey_type_id'] = $dao->survey_type_id;             
        }
        
        return $survey ;
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
    	
        $signature = array( );	

        $sql = "
SELECT 	a.id AS id,
		a.source_record_id AS source_record_id,
		a.source_contact_id AS source_contact_id,
		a.activity_date_time AS activity_date_time,
		a.activity_type_id AS activity_type_id,
		a.status_id AS status_id,  '".
		$surveyInfo['campaign_id'] . "'  AS campaign_id, '" .
		$surveyInfo['campaign_name'] . "'  AS campaign_name, '" .
		$surveyInfo['survey_type'] . "' AS survey_type " . ",
		ov.label AS activity_type
FROM  	civicrm_activity a, civicrm_option_value ov, civicrm_option_group og
WHERE 	a.campaign_id = " . $surveyInfo['campaign_id'] . "
	AND a.source_record_id = " . $surveyId . " 
	AND a.activity_type_id = ov.value
	AND ov.option_group_id = og.id
	AND og.name = 'activity_type'";	


        require_once 'CRM/Contact/BAO/Contact.php'; 

        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
           $signature[$dao->id]['id'] = $dao->id;
           $signature[$dao->id]['activity_type_id'] = $dao->activity_type_id;        
           $signature[$dao->id]['campaign_id'] = $dao->campaign_id;  
           $signature[$dao->id]['campaign_name'] = $dao->campaign_name;
           $signature[$dao->id]['survey_type'] = $dao->survey_type;  
           $signature[$dao->id]['source_record_id'] = $dao->source_record_id;
           $signature[$dao->id]['source_contact_id'] = CRM_Contact_BAO_Contact::displayName($dao->source_contact_id);
           $signature[$dao->id]['activity_date_time'] = $dao->activity_date_time;
           $signature[$dao->id]['activity_type'] = $dao->activity_type;
           $signature[$dao->id]['status_id'] = $dao->status_id;
        }

        return $signature;
    }    

}

?>