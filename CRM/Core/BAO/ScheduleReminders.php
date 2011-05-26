<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright (C) 2011 Marty Wright                                    |
 | Licensed to CiviCRM under the Academic Free License version 3.0.   |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Core/DAO/ActionSchedule.php';
require_once 'CRM/Core/DAO/ActionMapping.php';

/**
 * This class contains functions for managing Scheduled Reminders
 */
class CRM_Core_BAO_ScheduleReminders extends CRM_Core_DAO_ActionSchedule
{

    static function getMapping(  ) 
    {
        $dao  = new CRM_Core_DAO_ActionMapping( );
        $dao->find(  );

        $mapping = $defaults = array();
        while ( $dao->fetch( ) ) { 
            CRM_Core_DAO::storeValues( $dao, $defaults );
            $mapping[$dao->id] = $defaults;
        }
        return $mapping;
    }


    static function getSelection(  ) 
    {
        $mapping  = self::getMapping(  );

        require_once 'CRM/Core/PseudoConstant.php';
        require_once 'CRM/Event/PseudoConstant.php';
        $participantStatus = CRM_Event_PseudoConstant::participantStatus( null, null, 'label' );
        $activityStatus = CRM_Core_PseudoConstant::activityStatus();
        $event = CRM_Event_PseudoConstant::event( null, false, "( is_template IS NULL OR is_template != 1 )" );
        $activityType = CRM_Core_PseudoConstant::activityType(false);
        $eventType = CRM_Event_PseudoConstant::eventType();
        $activityContacts = CRM_Core_PseudoConstant::activityContacts();

        foreach ( $mapping as $value ) {
            $entityValue  = $value['entity_value'];
            $entityStatus = $value['entity_status'];
            $entityDate = $value['entity_date'];
            $entityRecipient = $value['entity_recipient'];
            $key = $value['id'];
            if( $entityValue == 'activity_type' &&
                $value['entity'] == 'civicrm_activity' ) {
                $val = 'Activity';
            } elseif( $entityValue == 'event_type' &&
                $value['entity'] == 'civicrm_participant') {
                $val ='Event Type';
            } elseif( $entityValue == 'civicrm_event' &&
                $value['entity'] == 'civicrm_participant' ) {
                $val = 'Event Name';
            }
            $sel1[$key] = $val;

            switch ($entityValue) {
            case 'activity_type':
                $sel2[$key] = $activityType;
                break;

            case 'event_type':
                $sel2[$key] = $eventType;
                break;

            case 'civicrm_event':
                $sel2[$key] = $event;
                break;
            }

            switch ($entityDate) {
            case 'activity_date_time':
                $sel4[$entityDate] = ts('Activity Date Time');
                break;

            case 'event_start_date':
                $sel4[$entityDate] = ts('Event Start Date');
                break;
            }

            switch ($entityRecipient) {
            case 'activity_contacts':
                $sel5[$entityRecipient] = $activityContacts + array(  ts('Manual') );
                break;
                
            case 'civicrm_participant_status_type':
                $sel5[$entityRecipient] = $participantStatus + array( ts('Manual') );
                break;
            }
            
        }
        $sel3 = $sel2;

        foreach ( $mapping as $value ) {
            $entityStatus = $value['entity_status'];
            $id = $value['id'];
                      
            switch ($entityStatus) {
            case 'activity_status':
                foreach( $sel3[$id] as $kkey => &$vval ) {
                    $vval = $activityStatus;
                }
                break;

            case 'civicrm_participant_status_type':
                foreach( $sel3[$id] as $kkey => &$vval ) {
                    $vval = $participantStatus;
                }
                break;
            }
        }

        return array( $sel1 , $sel2, $sel3, $sel4, $sel5 );
    }

    /**
     * Retrieve list of Scheduled Reminders
     *
     * @param bool    $namesOnly    return simple list of names
     *
     * @return array  (reference)   label format list
     * @static
     * @access public
     */
    static function &getList( $namesOnly = false ) 
    {
        require_once 'CRM/Core/PseudoConstant.php';
        require_once 'CRM/Event/PseudoConstant.php';

        $activity_type = CRM_Core_PseudoConstant::activityType(false);
        $activity_status = CRM_Core_PseudoConstant::activityStatus();
        $event_type = CRM_Event_PseudoConstant::eventType();
        $civicrm_event = CRM_Event_PseudoConstant::event( null, false, "( is_template IS NULL OR is_template != 1 )" );
        $civicrm_participant_status_type = CRM_Event_PseudoConstant::participantStatus( null, null, 'label' );

        $query ="
SELECT 
       title,
       cas.id as id,
       cam.entity_value as entityValue, 
       cas.entity_value as entityValueIds,
       cam.entity_status as entityStatus,
       cas.entity_status as entityStatusIds,
       cam.entity_date as entityDate,
       cas.first_action_offset,
       cas.first_action_unit,
       cas.first_action_condition,
       is_repeat,
       is_active

FROM civicrm_action_schedule cas
LEFT JOIN civicrm_action_mapping cam ON (cam.id = cas.mapping_id)

";
        $dao = CRM_Core_DAO::executeQuery( $query );
        while ( $dao->fetch() ) {
            //CRM_Core_Error::debug( '$dao', $dao );
            $list[$dao->id]['title']  = $dao->title;
            $list[$dao->id]['first_action_offset']  = $dao->first_action_offset;
            $list[$dao->id]['first_action_unit']  = $dao->first_action_unit;
            $list[$dao->id]['first_action_condition']  = $dao->first_action_condition;
            $list[$dao->id]['entityDate']  = ucwords(str_replace('_', ' ', $dao->entityDate));
            $status = $dao->entityStatus;
            $statusIds = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, ' ,', $dao->entityStatusIds);
            foreach ($$status as $key => $val) {
                $statusIds = str_replace($key, $val, $statusIds);
            }
            
            $list[$dao->id]['status']  = $statusIds;
            $list[$dao->id]['is_repeat']  = $dao->is_repeat;
            $list[$dao->id]['is_active']  = $dao->is_active;
        }

        // CRM_Core_Error::debug( '$list', $list );
        return $list;

    }

    static function sendReminder( $contactId, $email, $mappingID, $from ) {
        require_once "CRM/Core/BAO/Domain.php";
        require_once "CRM/Utils/String.php";
        require_once "CRM/Utils/Token.php";

        $schedule = new CRM_Core_DAO_ActionSchedule( );
        $schedule->mapping_id = $mappingID;

        $domain = CRM_Core_BAO_Domain::getDomain( );
        $result = null;
        $hookTokens = array();
        
        if ( $schedule->find(true) ) {
            $body_text = $schedule->body_text;
            $body_html = $schedule->body_html;
            $body_subject = $schedule->subject;
            if (!$body_text) {
                $body_text = CRM_Utils_String::htmlToText($body_html);
            }
            
            $params = array(array('contact_id', '=', $contactId, 0, 0));
            list($contact, $_) = CRM_Contact_BAO_Query::apiQuery($params);

            //CRM-4524
            $contact = reset( $contact );
            
            if ( !$contact || is_a( $contact, 'CRM_Core_Error' ) ) {
                return null;
            }

            //CRM-5734
            require_once 'CRM/Utils/Hook.php';
            CRM_Utils_Hook::tokenValues( $contact, $contactId );
            
            CRM_Utils_Hook::tokens( $hookTokens );
            $categories = array_keys( $hookTokens );
            
            $type = array('html', 'text');
            
            foreach( $type as $key => $value ) {
                require_once 'CRM/Mailing/BAO/Mailing.php';
                $dummy_mail = new CRM_Mailing_BAO_Mailing();
                $bodyType = "body_{$value}";
                $dummy_mail->$bodyType = $$bodyType;
                $tokens = $dummy_mail->getTokens();
                
                if ( $$bodyType ) {
                    $$bodyType = CRM_Utils_Token::replaceDomainTokens($$bodyType, $domain, true, $tokens[$value], true );
                    $$bodyType = CRM_Utils_Token::replaceContactTokens($$bodyType, $contact, false, $tokens[$value], false, true );
                    $$bodyType = CRM_Utils_Token::replaceComponentTokens($$bodyType, $contact, $tokens[$value], true );
                    $$bodyType = CRM_Utils_Token::replaceHookTokens ( $$bodyType, $contact , $categories, true );
                }
            }
            $html = $body_html;
            $text = $body_text;
            
            require_once 'CRM/Core/Smarty/resources/String.php';
            civicrm_smarty_register_string_resource( );
            $smarty =& CRM_Core_Smarty::singleton( );
            foreach( array( 'text', 'html') as $elem) {
                $$elem = $smarty->fetch("string:{$$elem}");
            }
            
            $message = new Mail_mime("\n");
            
            /* Do contact-specific token replacement in text mode, and add to the
             * message if necessary */
            if ( !$html || $contact['preferred_mail_format'] == 'Text' ||
                 $contact['preferred_mail_format'] == 'Both') 
                {
                    // render the &amp; entities in text mode, so that the links work
                    $text = str_replace('&amp;', '&', $text);
                    $message->setTxtBody($text);
                    
                    unset( $text );
                }
            
            if ($html && ( $contact['preferred_mail_format'] == 'HTML' ||
                           $contact['preferred_mail_format'] == 'Both'))
                {
                    $message->setHTMLBody($html);
                    
                    unset( $html );
                }
            $recipient = "\"{$contact['display_name']}\" <$email>";
            
            $matches = array();
            preg_match_all( '/(?<!\{|\\\\)\{(\w+\.\w+)\}(?!\})/',
                            $body_subject,
                            $matches,
                            PREG_PATTERN_ORDER);
            
            $subjectToken = null;
            if ( $matches[1] ) {
                foreach ( $matches[1] as $token ) {
                    list($type,$name) = preg_split( '/\./', $token, 2 );
                    if ( $name ) {
                        if ( ! isset( $subjectToken['contact'] ) ) {
                            $subjectToken['contact'] = array( );
                        }
                        $subjectToken['contact'][] = $name;
                    }
                }
            }
            
            $messageSubject = CRM_Utils_Token::replaceContactTokens($body_subject, $contact, false, $subjectToken);
            $messageSubject = CRM_Utils_Token::replaceDomainTokens($messageSubject, $domain, true, $tokens[$value] );
            $messageSubject = CRM_Utils_Token::replaceComponentTokens($messageSubject, $contact, $tokens[$value], true );
            $messageSubject = CRM_Utils_Token::replaceHookTokens ( $messageSubject, $contact, $categories, true );
          
            $messageSubject = $smarty->fetch("string:{$messageSubject}");

            $headers = array(
                             'From'      => $from,
                             'Subject'   => $messageSubject,
                             );
            $headers['To'] = $recipient;
            
            $mailMimeParams = array(
                                    'text_encoding' => '8bit',
                                    'html_encoding' => '8bit',
                                    'head_charset'  => 'utf-8',
                                    'text_charset'  => 'utf-8',
                                    'html_charset'  => 'utf-8',
                                    );
            $message->get($mailMimeParams);
            $message->headers($headers);

            $config = CRM_Core_Config::singleton();
            $mailer =& $config->getMailer();
            
            $body = $message->get();
            $headers = $message->headers();
            
            CRM_Core_Error::ignoreException( );
            $result = $mailer->send($recipient, $headers, $body);
            CRM_Core_Error::setCallback();
        }
        $schedule->free( );
        
        return $result;
    }
 
    /**
     * Function to add the schedules reminders in the db
     *
     * @param array $params (reference ) an assoc array of name/value pairs
     * @param array $ids    the array that holds all the db ids  
     *
     * @return object CRM_Core_DAO_ActionSchedule
     * @access public
     * @static
     *
     */
    static function add( &$params, &$ids ) 
    {
        $actionSchedule = new CRM_Core_DAO_ActionSchedule( );
        $actionSchedule->copyValues( $params );
        
        return $actionSchedule->save( );
    }
  
    // function to retrieve a list of contact-ids that belongs to the given action mapping.
    static function getRecipientContacts( $mappingID )
    {
        $contacts = array();

        require_once 'CRM/Core/BAO/ActionMapping.php';
        $mapping = new CRM_Core_DAO_ActionMapping( );
        $mapping->id = $mappingID;
        $mapping->find( true );

        $actionSchedule = new CRM_Core_DAO_ActionSchedule( );
        $actionSchedule->id = $mappingID;
        
        if ( $actionSchedule->find( true ) ) {
            $value  = explode( CRM_Core_DAO::VALUE_SEPARATOR, $actionSchedule->entity_value  );
            $value  = implode( ',', $value );

            $status = explode( CRM_Core_DAO::VALUE_SEPARATOR, $actionSchedule->entity_status );
            $status = implode( ',', $status );
        
            // FIXME: Need to generalize the query generation mechanism
            switch ( $mapping->entity ) {
            case 'civicrm_activity' :
                $query = "
SELECT assignee_contact_id as contact_id
FROM   civicrm_activity ca
INNER JOIN civicrm_activity_assignment aa ON  aa.activity_id = ca.id
WHERE ca.activity_type_id IN ({$value}) AND ca.status_id IN ({$status})";
                break;
            default:
                CRM_Core_Error::fatal( "Not sure how to find recipient contacts." );
            }
        }

        // FIXME: store contacts in temp table and make cron handle mailings in batches
        if ( $query ) {
            $dao = CRM_Core_DAO::executeQuery( $query );
            while ( $dao->fetch() ) {
                $contacts[$dao->contact_id] = $dao->contact_id;
            }
        }
        return $contacts;
    }
}