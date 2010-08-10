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
  
        if ( isset( $params['contactId'] ) ) {
        
        	// add signature as activity with survey id as source id
        	// get the activity type id associated with this survey        	
        	$surveyInfo = CRM_Campaign_BAO_Petition::getSurveyInfo($params['sid']);

	        require_once 'CRM/Activity/BAO/Activity.php';
			// create activity 
			// activity status id (from /civicrm/admin/optionValue?reset=1&action=browse&gid=25)
			// 1-Schedule, 2-Completed
	        
			$activityParams = array ( 'source_contact_id'  => $params['contactId'],
			                          'source_record_id'   => $params['sid'],
									  'activity_type_id'   => $surveyInfo['activity_type_id'],
									  'activity_date_time' => date("YmdHis"), 
									  'status_id'          => 2 );
									  			
			//activity creation
        	// *** check for activity using source id - if already signed
			$activity = CRM_Activity_BAO_Activity::create( $activityParams );	
			
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
           $signature[$dao->id]['contactId'] = $dao->source_contact_id;
        }

        return $signature;
    }    

    /**
     * This function returns all entities assigned to a specific tag
     * 
     * @param object  $tag    an object of a tag.
     *
     * @return  array   $contactIds    array of contact ids
     * @access public
     */
    function getEntitiesByTag($tag)
    {
	    require_once 'CRM/Core/DAO/EntityTag.php';
        $contactIds = array();
        $entityTagDAO = new CRM_Core_DAO_EntityTag();
        $entityTagDAO->tag_id = $tag['id'];
        $entityTagDAO->find();

        while($entityTagDAO->fetch()) {
            $contactIds[] = $entityTagDAO->entity_id;
        }
        return $contactIds;
    }

    /**
     * takes an associative array and sends a thank you or email verification email
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     *
     * @return 
     * @access public
     * @static
     */
    static function sendEmail( $params, $sendEmailMode )
    {
    
    /* sendEmailMode
     * 1 = connected user via login/pwd - thank you
	 * 	 or dedupe contact matched who doesn't have a tag CIVICRM_TAG_UNCONFIRMED - thank you
	 * 2 = login using fb connect - thank you + click to add msg to fb wall
	 * 3 = send a confirmation request email     
	 */

		define('PETITION_CONTACT_GROUP','Petition Contacts');
		
		if (defined('PETITION_CONTACT_GROUP')) {
			// check if 'Petition Contacts' group exists, else create it
			require_once 'api/v2/Group.php';
			$group_params['title'] = PETITION_CONTACT_GROUP;
			$groups = civicrm_group_get($group_params);
			if (($groups['is_error'] == 1) && ($groups['error_message'] == 'No such group exists')) {
				$group_params['is_active'] = 1;
				$group_params['visibility'] = 'Public Pages';
				$newgroup = civicrm_group_add($group_params);
				if ($newgroup['is_error'] == 0) {
					$group_id[0] = $newgroup['result'];
				}
			} else {
				$group_id = array_keys($groups);
			}
    	}
		
		switch ($sendEmailMode) {
			case 1:	    	
    			break;
    			
			case 2:	    	
    			break;
    			
			case 3:
				// create mailing event subscription record for this contact
				require_once 'CRM/Mailing/Event/BAO/Subscribe.php';
				$se = CRM_Mailing_Event_BAO_Subscribe::subscribe( $group_id[0], 
																	$params['email-Primary'] , 
																	$params['contactId'] );			

				require_once 'CRM/Core/BAO/MessageTemplates.php';
				$template_data = array();
				/***??? fix hardcoded template msg_title ***/
				$template_params['msg_title'] = 'ConfirmEmail';
				CRM_Core_BAO_MessageTemplates::retrieve($template_params,$template_data);

				$config = CRM_Core_Config::singleton();
		
				require_once 'CRM/Core/BAO/Domain.php';
				$domain =& CRM_Core_BAO_Domain::getDomain();
				
				//get the default domain email address.
				list( $domainEmailName, $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( );
				
				require_once 'CRM/Core/BAO/MailSettings.php';
				$localpart   = CRM_Core_BAO_MailSettings::defaultLocalpart();
				$emailDomain = CRM_Core_BAO_MailSettings::defaultDomain();
		
				require_once 'CRM/Utils/Verp.php';
				$confirm = implode($config->verpSeparator,
								   array($localpart . 'c',
										 $se->contact_id,
										 $se->id,
										 $se->hash)
								  ) . "@$emailDomain";			
	
				$headers = array(
								 'Subject'   => $template_data['msg_subject'],
								 'From'      => "\"{$domainEmailName}\" <{$domainEmailAddress}>",
								 'To'        => $params['email-Primary'],
								 'Reply-To'  => $confirm,
								 'Return-Path'   => "do-not-reply@$emailDomain"
								 );
				
				$url = CRM_Utils_System::url( 'civicrm/petition/confirm',
											  "reset=1&cid={$se->contact_id}&sid={$se->id}&h={$se->hash}",
											  true );
				
				$html = $template_data['msg_html'];
		
				if (isset($template_data['msg_text'])) {
					$text = $template_data['msg_text'];
				} else {
					$text = CRM_Utils_String::htmlToText($template_data['msg_html']);
				}			
				
				require_once 'CRM/Mailing/BAO/Mailing.php';
				$bao = new CRM_Mailing_BAO_Mailing();
				$bao->body_text = $text;
				$bao->body_html = $html;
				$tokens = $bao->getTokens();
		
				require_once 'CRM/Utils/Token.php';
				$html = CRM_Utils_Token::replaceDomainTokens($html, $domain, true, $tokens['html'] );
				$html = CRM_Utils_Token::replaceSubscribeTokens($html, 
																'',
																$url, true);
				
				$text = CRM_Utils_Token::replaceDomainTokens($text, $domain, false, $tokens['text'] );
				$text = CRM_Utils_Token::replaceSubscribeTokens($text, 
																'',
																$url, false);
																
				// render the &amp; entities in text mode, so that the links work
				$text = str_replace('&amp;', '&', $text);
		
				$message = new Mail_mime("\n");
		
				$message->setHTMLBody($html);
				$message->setTxtBody($text);
				$b =& CRM_Utils_Mail::setMimeParams( $message );
				$h =& $message->headers($headers);
				$mailer =& $config->getMailer();
		
				require_once 'CRM/Mailing/BAO/Mailing.php';
				PEAR::setErrorHandling(PEAR_ERROR_CALLBACK,
									   array('CRM_Core_Error', 'nullHandler' ) );
				if ( is_object( $mailer ) ) {
					$mailer->send($params['email-Primary'], $h, $b);
					CRM_Core_Error::setCallback();
				}		
    			break;    			
    	}

	}

}
