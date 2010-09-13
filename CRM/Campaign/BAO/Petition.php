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
    function __construct() {
       parent::__construct();
       $this->cookieExpire = (1000 * 60 * 60 * 24); // expire cookie in one day
    }
		
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
									  'status_id'          => $params['statusId'] );
									  			
			//activity creation
        	// *** check for activity using source id - if already signed
			$activity = CRM_Activity_BAO_Activity::create( $activityParams );
			
			// save activity custom data
		    if ( CRM_Utils_Array::value( 'custom', $params ) &&
            		is_array( $params['custom'] ) ) {
            	require_once 'CRM/Core/BAO/CustomValueTable.php';
            	CRM_Core_BAO_CustomValueTable::store( $params['custom'], 'civicrm_activity', $activity->id );
        	}
        	
			
		}
		
        return $activity;
    }

    function confirmSignature($activity_id,$contact_id,$petition_id) {
      //change activity status to completed (status_id=2)	
          $query = "UPDATE civicrm_activity SET status_id = 2 
                WHERE 	id = $activity_id 
                AND  	source_contact_id = $contact_id";
          CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );

      // remove 'Unconfirmed' tag for this contact
      define('CIVICRM_TAG_UNCONFIRMED','Unconfirmed');
      
      if (defined('CIVICRM_TAG_UNCONFIRMED')) {
        // Check if contact 'email confirmed' tag exists, else create one
        // This should be in the petition module initialise code to create a default tag for this
        require_once 'api/v2/Tag.php';	
        $tag_params['name'] = CIVICRM_TAG_UNCONFIRMED;
        $tag = civicrm_tag_get($tag_params); 
        
        require_once 'api/v2/EntityTag.php';				
        unset($tag_params);
        $tag_params['contact_id'] = $contact_id;
        $tag_params['tag_id'] = $tag['id'];			
        $tag_value = civicrm_entity_tag_remove($tag_params);	
      }
          
      // set permanent cookie to indicate this users email address now confirmed
      require_once 'CRM/Campaign/BAO/Petition.php';
      setcookie('confirmed_'.$petition_id, $activity_id, time() + $this->cookieExpire, '/');						
          return true;
    }    


     /**
     * Function to get Petition Signature Total 
     * 
     * @param boolean $all
     * @param int $id
     * @static
     */
    static function getPetitionSignatureTotalbyCountry ( $surveyId ) {
        $countries = array( );	
        $sql = "
SELECT count(civicrm_address.country_id) as total,
    IFNULL(country_id,'') as country_id,IFNULL(iso_code,'') as country_iso, IFNULL(civicrm_country.name,'') as country
 FROM  	civicrm_activity a, civicrm_survey, civicrm_contact
  LEFT JOIN civicrm_address ON civicrm_address.contact_id = civicrm_contact.id  
  LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id
WHERE 
  a.source_contact_id = civicrm_contact.id AND
  a.activity_type_id = civicrm_survey.activity_type_id AND
  civicrm_survey.id =  $surveyId AND  
	a.source_record_id =  $surveyId  ";
     if ($status_id)
       $sql .= " AND status_id = ". (int) $status_id;
  $sql .= " GROUP BY civicrm_address.country_id";
     $fields = array ('total','country_id','country_iso','country');
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
           $row = array();
           foreach ($fields as $field) {
             $row[$field] = $dao->$field;
           }
           $countries [] = $row;
        }
        return $countries;
    }    

     /**
     * Function to get Petition Signature Total 
     * 
     * @param boolean $all
     * @param int $id
     * @static
     */
    static function getPetitionSignatureTotal( $surveyId ) {
    	$surveyInfo = CRM_Campaign_BAO_Petition::getSurveyInfo((int) $surveyId);
    	//$activityTypeID = $surveyInfo['activity_type_id'];
        $signature = array( );	
        
        $sql = "
SELECT 
		status_id,count(id) as total
 FROM  	civicrm_activity
WHERE 
	source_record_id = " . (int) $surveyId  . 
	" AND activity_type_id = " . (int)  $surveyInfo['activity_type_id'] . 
" GROUP BY status_id";
        require_once 'CRM/Contact/BAO/Contact.php'; 

        $statusTotal = array();$total =0;
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
          $total += $dao->total;
          $statusTotal['status'][$dao->status_id] = $dao->total;
        }
        $statusTotal['count']=$total;
        return $statusTotal;
    }    

    
    public function getSurveyInfo( $surveyId=null ) 
    {
		$surveyInfo = array( );

        $sql = "
SELECT 	activity_type_id, 
		campaign_id,
		title,
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
    static function getPetitionSignature( $surveyId, $status_id=null ) {
    
      $surveyId = (int)$surveyId;// sql injection protection
        $signature = array( );	

        $sql = "
SELECT 	a.id,
		a.source_record_id as survey_id,
		a.activity_date_time,
		a.status_id,
		civicrm_contact.id as contact_id,
    civicrm_contact.contact_type,civicrm_contact.contact_sub_type,image_URL,
    first_name,last_name,sort_name,
    employer_id,organization_name,
    mail_to_household_id,household_name,
    IFNULL(gender_id,'') AS gender_id,
    IFNULL(state_province_id,'') AS state_province_id,
    IFNULL(country_id,'') as country_id,IFNULL(iso_code,'') as country_iso, IFNULL(civicrm_country.name,'') as country
 FROM  	civicrm_activity a, civicrm_survey, civicrm_contact
  LEFT JOIN civicrm_address ON civicrm_address.contact_id = civicrm_contact.id  
  LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id
WHERE 
  a.source_contact_id = civicrm_contact.id AND
  a.activity_type_id = civicrm_survey.activity_type_id AND
  civicrm_survey.id =  $surveyId AND  
	a.source_record_id =  $surveyId  ";
     if ($status_id)
       $sql .= " AND status_id = ". (int) $status_id;
     $fields = array ('id','survey_id','contact_id','activity_date_time','activity_type_id','status_id','first_name','last_name', 'sort_name','gender_id','country_id','state_province_id','country_iso','country');
        $dao =& CRM_Core_DAO::executeQuery( $sql );
        while ( $dao->fetch() ) {
           $row = array();
           foreach ($fields as $field) {
             $row[$field] = $dao->$field;
           }
           $signature [] = $row;
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
     * Function to check if contact has signed this petition
     * 
     * @param int $surveyId
     * @param int $contactId
     * @static
     */
    static function checkSignature( $surveyId, $contactId ) {
    
    	$surveyInfo = CRM_Campaign_BAO_Petition::getSurveyInfo($surveyId);
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
	AND a.activity_type_id = " . $surveyInfo['activity_type_id'] . "
	AND a.source_contact_id = " . $contactId;

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
     * takes an associative array and sends a thank you or email verification email
     *
     * @param array  $params (reference ) an assoc array of name/value pairs
     *
     * @return 
     * @access public
     * @static
     */
    function sendEmail( $params, $sendEmailMode )
    {
    
    /* sendEmailMode
     * CRM_Campaign_Form_Petition_Signature::EMAIL_THANK
     * 		connected user via login/pwd - thank you
	 * 	 	or dedupe contact matched who doesn't have a tag CIVICRM_TAG_UNCONFIRMED - thank you
	 *  	or login using fb connect - thank you + click to add msg to fb wall
	 *
	 * CRM_Campaign_Form_Petition_Signature::EMAIL_CONFIRM
	 *		send a confirmation request email     
	 */
		require_once 'CRM/Campaign/Form/Petition/Signature.php';
		
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

		// get petition info
		$petitionParams['id'] = $params['sid'];
		$petitionInfo = array();
		CRM_Campaign_BAO_Survey::retrieve($petitionParams,$petitionInfo);
		if (empty($petitionInfo)) {
			CRM_Core_Error::fatal( 'Petition doesn\'t exist.' );
		} 	

		require_once 'CRM/Core/BAO/Domain.php';
		//get the default domain email address.
		list( $domainEmailName, $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( );		

		require_once 'CRM/Core/BAO/MailSettings.php';
		$emailDomain = CRM_Core_BAO_MailSettings::defaultDomain();
		
		require_once 'CRM/Contact/BAO/Contact.php';
		$toName = CRM_Contact_BAO_Contact::displayName($params['contactId']);		
		
		$replyTo = "do-not-reply@$emailDomain";

		switch ($sendEmailMode) {
			case CRM_Campaign_Form_Petition_Signature::EMAIL_THANK:
				require_once 'CRM/Core/BAO/MessageTemplates.php';
				if ($params['email-Primary']) {
					self::sendTemplate(
						array(
							'groupName' => 'msg_tpl_workflow_petition',
							'valueName' => 'petition_sign',
							'contactId' => $params['contactId'],
							'tplParams' => array(),
							'from'    => "\"{$domainEmailName}\" <{$domainEmailAddress}>",
							'toName'  => $toName,
							'toEmail' => $params['email-Primary'],
							'replyTo' => $replyTo,
							'petitionId' => $params['sid'],
							'petitionTitle' => $petitionInfo['title'],
						)
					);
				}			
				
				// set permanent cookie to indicate this petition already signed on the computer
				setcookie('signed_'.$params['sid'], $params['activityId'], time() + $this->cookieExpire, '/');
				
				// set permanent cookie to indicate this users email address already confirmed
				setcookie('confirmed_'.$params['sid'], $params['activityId'], time() + $this->cookieExpire, '/');
				
    			break;
    			
    			
			case CRM_Campaign_Form_Petition_Signature::EMAIL_CONFIRM:
				// create mailing event subscription record for this contact
				// this will allow using a hash key to confirm email address by sending a url link
				require_once 'CRM/Mailing/Event/BAO/Subscribe.php';
				$se = CRM_Mailing_Event_BAO_Subscribe::subscribe( $group_id[0], 
																	$params['email-Primary'] , 
																	$params['contactId'] );			
		
//				require_once 'CRM/Core/BAO/Domain.php';
//				$domain =& CRM_Core_BAO_Domain::getDomain();
				$config = CRM_Core_Config::singleton();
				$localpart   = CRM_Core_BAO_MailSettings::defaultLocalpart();
		
				require_once 'CRM/Utils/Verp.php';
				$replyTo = implode($config->verpSeparator,
								   array($localpart . 'c',
										 $se->contact_id,
										 $se->id,
										 $se->hash)
								  ) . "@$emailDomain";			

				
				$confirmUrl = CRM_Utils_System::url( 'civicrm/petition/confirm',
											  "reset=1&cid={$se->contact_id}&sid={$se->id}&h={$se->hash}&a={$params['activityId']}&p={$params['sid']}",
											  true );
						
				require_once 'CRM/Core/BAO/MessageTemplates.php';
				if ($params['email-Primary']) {
					self::sendTemplate(
						array(
							'groupName' => 'msg_tpl_workflow_petition',
							'valueName' => 'petition_confirmation_needed',
							'contactId' => $params['contactId'],
							'tplParams' => array(),
							'from'    => "\"{$domainEmailName}\" <{$domainEmailAddress}>",
							'toName'  => $toName,
							'toEmail' => $params['email-Primary'],
							'replyTo' => $replyTo,
							'petitionId' => $params['sid'],
							'petitionTitle' => $petitionInfo['title'],
							'confirmUrl' => $confirmUrl, 
						)
					);
				}		
				
				// set permanent cookie to indicate this petition already signed on the computer
				setcookie('signed_'.$params['sid'], $params['activityId'], time() + $this->cookieExpire, '/');
    			break;    			
    	}
	}

    /**
     * Send an email from the specified template based on an array of params
     *
     * @param array $params  a string-keyed array of function params, see function body for details
     *
     * @return array  of four parameters: a boolean whether the email was sent, and the subject, text and HTML templates
     */
    static function sendTemplate($params)
    {
        $defaults = array(
            'groupName'   => null,    // option group name of the template
            'valueName'   => null,    // option value name of the template
            'contactId'   => null,    // contact id if contact tokens are to be replaced
            'tplParams'   => array(), // additional template params (other than the ones already set in the template singleton)
            'from'        => null,    // the From: header
            'toName'      => null,    // the recipient’s name
            'toEmail'     => null,    // the recipient’s email - mail is sent only if set
            'cc'          => null,    // the Cc: header
            'bcc'         => null,    // the Bcc: header
            'replyTo'     => null,    // the Reply-To: header
            'attachments' => null,    // email attachments
            'isTest'      => false,   // whether this is a test email (and hence should include the test banner)
        );
        $params = array_merge($defaults, $params);

        if (!$params['groupName'] or !$params['valueName']) {
            CRM_Core_Error::fatal(ts("Message template's option group and/or option value missing."));
        }

        // fetch the three elements from the db based on option_group and option_value names
        $query = 'SELECT msg_subject subject, msg_text text, msg_html html
                  FROM civicrm_msg_template mt
                  JOIN civicrm_option_value ov ON workflow_id = ov.id
                  JOIN civicrm_option_group og ON ov.option_group_id = og.id
                  WHERE og.name = %1 AND ov.name = %2 AND mt.is_default = 1';
        $sqlParams = array(1 => array($params['groupName'], 'String'), 2 => array($params['valueName'], 'String'));
        $dao = CRM_Core_DAO::executeQuery($query, $sqlParams);
        $dao->fetch();

        if (!$dao->N) {
            CRM_Core_Error::fatal(ts('No such message template: option group %1, option value %2.', array(1 => $params['groupName'], 2 => $params['valueName'])));
        }

        $subject = $dao->subject;
        $text    = $dao->text;
        $html    = $dao->html;
        $dao->free( );

        // add the test banner (if requested)
        if ($params['isTest']) {
            $query = "SELECT msg_subject subject, msg_text text, msg_html html
                      FROM civicrm_msg_template mt
                      JOIN civicrm_option_value ov ON workflow_id = ov.id
                      JOIN civicrm_option_group og ON ov.option_group_id = og.id
                      WHERE og.name = 'msg_tpl_workflow_meta' AND ov.name = 'test_preview' AND mt.is_default = 1";
            $testDao = CRM_Core_DAO::executeQuery($query);
            $testDao->fetch();

            $subject = $testDao->subject . $subject;
            $text    = $testDao->text    . $text;
            $html    = preg_replace('/<body(.*)$/im', "<body\\1\n{$testDao->html}", $html);
            $testDao->free( );
        }

        // replace tokens in the three elements (in subject as if it was the text body)
        require_once 'CRM/Utils/Token.php';
        require_once 'CRM/Core/BAO/Domain.php';
        require_once 'api/v2/Contact.php';
        require_once 'CRM/Mailing/BAO/Mailing.php';

        $domain = CRM_Core_BAO_Domain::getDomain();
        if ($params['contactId']) {
            $contactParams = array('contact_id' => $params['contactId']);
            $contact =& civicrm_contact_get($contactParams);
        }

        $mailing = new CRM_Mailing_BAO_Mailing;
        $mailing->body_text = $text;
        $mailing->body_html = $html;
        $tokens = $mailing->getTokens();

        $subject = CRM_Utils_Token::replaceDomainTokens($subject, $domain, true, $tokens['text'], true);
        $text    = CRM_Utils_Token::replaceDomainTokens($text,    $domain, true, $tokens['text'], true);
        $html    = CRM_Utils_Token::replaceDomainTokens($html,    $domain, true, $tokens['html'], true);
        if ($params['contactId']) {
            $subject = CRM_Utils_Token::replaceContactTokens($subject, $contact, false, $tokens['text'], false, true);
            $text    = CRM_Utils_Token::replaceContactTokens($text,    $contact, false, $tokens['text'], false, true);
            $html    = CRM_Utils_Token::replaceContactTokens($html,    $contact, false, $tokens['html'], false, true);
        }
        if ($params['petitionId']) {
            $subject = self::replacePetitionTokens($subject, $params['petitionTitle'], $params['confirmUrl']);
            $text    = self::replacePetitionTokens($text,    $params['petitionTitle'], $params['confirmUrl']);
            $html    = self::replacePetitionTokens($html,    $params['petitionTitle'], $params['confirmUrl']);
        }
        
        // strip whitespace from ends and turn into a single line
        $subject = "{strip}$subject{/strip}";

        // parse the three elements with Smarty
        require_once 'CRM/Core/Smarty/resources/String.php';
        civicrm_smarty_register_string_resource();
        $smarty = CRM_Core_Smarty::singleton();
        foreach ($params['tplParams'] as $name => $value) {
            $smarty->assign($name, $value);
        }
        foreach (array('subject', 'text', 'html') as $elem) {
            $$elem = $smarty->fetch("string:{$$elem}");
        }

        // send the template, honouring the target user’s preferences (if any)
        $sent = false;

        // create the params array
        $params['subject'] = $subject;
        $params['text'   ] = $text;
        $params['html'   ] = $html;

        if ($params['toEmail']) {
            $contactParams = array('email' => $params['toEmail']);
            $contact =& civicrm_contact_get($contactParams);
            $prefs = array_pop($contact);

            if ( isset($prefs['preferred_mail_format']) and $prefs['preferred_mail_format'] == 'HTML' ) {
                $params['text'] = null;
            }

            if ( isset($prefs['preferred_mail_format']) and $prefs['preferred_mail_format'] == 'Text' ) {
                $params['html'] = null;
            }

            require_once 'CRM/Utils/Mail.php';
            $sent = CRM_Utils_Mail::send( $params );
        }

        return array($sent, $subject, $text, $html);
    }


    /**
     * Replace petition-specific-tokens
     * 
     * @param string $str           The string with tokens to be replaced
     * @param string $title         The petition title
     * @param string $confirmUrl	Confirmation url for email verification
     * @return str		            The processed string
     * @access public
     * @static
     */
    public static function &replacePetitionTokens($str, $title, $confirmUrl) 
    {
    	require_once 'CRM/Utils/Token.php';
    	
    	if (CRM_Utils_Token::token_match('petition', 'title', $str)) {
            CRM_Utils_Token::token_replace('petition', 'title', $title, $str);
        }
        
        if (CRM_Utils_Token::token_match('petition', 'confirmUrl', $str)) {
            CRM_Utils_Token::token_replace('petition', 'confirmUrl', $confirmUrl, $str);
        }
        return $str;
    }



}


