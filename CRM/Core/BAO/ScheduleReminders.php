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
        $sel1 = $sel2 = $sel3 = $sel4 = $sel5 = array();

        foreach ( $mapping as $value ) {
            $entityValue  = $value['entity_value'];
            $entityStatus = $value['entity_status'];
            $entityDate = $value['entity_date_start'];
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
                $sel2[$key] = array($value['entity_value_label'] ) + $activityType; 
                break;

            case 'event_type':
                $sel2[$key] = array($value['entity_value_label']) + $eventType;
                break;

            case 'civicrm_event':
                $sel2[$key] = array($value['entity_value_label']) + $event;
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
                $sel5[$entityRecipient] = $activityContacts + array( 'manual' => ts('Manual'), 'group' => ts('CiviCRM Group')  );
                break;
                
            case 'civicrm_participant_status_type':
                $sel5[$entityRecipient] = $participantStatus + array( 'manual' => ts('Manual'), 'group' => ts('CiviCRM Group') );
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
                    $vval =  array($value['entity_status_label']) + $activityStatus;
                }
                break;

            case 'civicrm_participant_status_type':
                foreach( $sel3[$id] as $kkey => &$vval ) {
                    $vval = array($value['entity_status_label']) + $participantStatus;
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
     * @return array  (reference)   reminder list
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
        $entity = array ( 'civicrm_activity'    => 'Activity',
                          'civicrm_participant' => 'Event');

        $query ="
SELECT 
       title,
       cam.entity,
       cas.id as id,
       cam.entity_value as entityValue, 
       cas.entity_value as entityValueIds,
       cam.entity_status as entityStatus,
       cas.entity_status as entityStatusIds,
       cam.entity_date_start as entityDate,
       cas.start_action_offset,
       cas.start_action_unit,
       cas.start_action_condition,
       is_repeat,
       is_active

FROM civicrm_action_schedule cas
LEFT JOIN civicrm_action_mapping cam ON (cam.id = cas.mapping_id)

";
        $dao = CRM_Core_DAO::executeQuery( $query );
        while ( $dao->fetch() ) {
            $list[$dao->id]['id']  = $dao->id;
            $list[$dao->id]['title']  = $dao->title;
            $list[$dao->id]['start_action_offset']  = $dao->start_action_offset;
            $list[$dao->id]['start_action_unit']  = $dao->start_action_unit;
            $list[$dao->id]['start_action_condition']  = $dao->start_action_condition;
            $list[$dao->id]['entityDate']  = ucwords(str_replace('_', ' ', $dao->entityDate));
            $status = $dao->entityStatus;
            $statusIds = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, ', ', $dao->entityStatusIds);
            foreach ($$status as $key => $val) {
                $statusIds = str_replace($key, $val, $statusIds);
            }
            
            $value = $dao->entityValue;
            $valueIds = str_replace(CRM_Core_DAO::VALUE_SEPARATOR, ', ', $dao->entityValueIds);
            foreach ($$value as $key => $val) {
              $valueIds   = str_replace($key, $val, $valueIds);
            }
            $list[$dao->id]['entity']     = $entity[$dao->entity];
            $list[$dao->id]['value']      = $valueIds;
            $list[$dao->id]['status']     = $statusIds;
            $list[$dao->id]['is_repeat']  = $dao->is_repeat;
            $list[$dao->id]['is_active']  = $dao->is_active;
        }

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

        $actionSchedule = new CRM_Core_DAO_ActionSchedule( );
        $actionSchedule->id = $mappingID;

        if ( $actionSchedule->find( true ) ) {
            $query = "SELECT MAX(id) FROM civicrm_action_log WHERE action_schedule_id = {$actionSchedule->id}";
            $actionLogID = CRM_Core_DAO::singleValueQuery( $query );

            $actionLog = new CRM_Core_DAO_ActionLog( );
            $actionLog->id = $actionLogID;
            $actionLog->find( true );

            if ( $actionLogID && !$actionSchedule->is_repeat ) {
                // if logs is present and repeat is turned off, then the reminder probably has already 
                // been sent & logged. And therefore no point in doing any work.
                return array( );
            }

            require_once 'CRM/Core/DAO/ActionMapping.php';
            $mapping = new CRM_Core_DAO_ActionMapping( );
            $mapping->id = $mappingID;
            $mapping->find( true );

            $select = $join = $where = array( );

            $value  = explode( CRM_Core_DAO::VALUE_SEPARATOR, $actionSchedule->entity_value  );
            $value  = implode( ',', $value );

            $status = explode( CRM_Core_DAO::VALUE_SEPARATOR, $actionSchedule->entity_status );
            $status = implode( ',', $status );
        
            $recipientOptions = CRM_Core_OptionGroup::values( $mapping->entity_recipient );

            $select[] = "e.id as entity_id";
            $select[] = "e.{$mapping->entity_date_start} as entity_date_start";
            $from     = "{$mapping->entity} e";

            if ( $mapping->entity == 'civicrm_activity' ) {
                switch ( $recipientOptions[$actionSchedule->recipient] ) {
                case 'Activity Assignees':
                    $select[] = "r.assignee_contact_id as contact_id";
                    $join[]   = "INNER JOIN civicrm_activity_assignment r ON  r.activity_id = e.id";
                    break;
                case 'Activity Source':
                    $select[] = "e.source_contact_id as contact_id";
                    break;
                case 'Activity Targets':
                    $select[] = "r.target_contact_id as contact_id";
                    $join[]   = "INNER JOIN civicrm_activity_target r ON  r.activity_id = e.id";
                    break;
                default:
                    break;
                }

                // build where clause
                if ( !empty($value) ) {
                    $where[]  = "e.activity_type_id IN ({$value})";
                }
                if ( !empty($status) ) {
                    $where[]  = "e.status_id IN ({$status})";
                }

                // datetime where clause
                if ( !$actionLogID ) {
                    // if NO logs are present:
                    $startEvent = ( $actionSchedule->start_action_condition == 'before' ? "DATE_SUB" : "DATE_ADD" ) . 
                        "(e.activity_date_time, INTERVAL {$actionSchedule->start_action_offset} {$actionSchedule->start_action_unit})";
                } else if ( $actionSchedule->is_repeat ) {
                    // if repeat is turned ON:
                    $endEvent = ( $actionSchedule->end_action == 'before' ? "DATE_SUB" : "DATE_ADD" ) . 
                        "(e.activity_date_time, INTERVAL {$actionSchedule->end_frequency_interval} {$actionSchedule->end_frequency_unit})";

                    if ( $actionSchedule->repetition_frequency_unit == 'day' ) {
                        $hrs = 24 * $actionSchedule->repetition_frequency_interval;
                    } else if ( $actionSchedule->repetition_frequency_unit == 'week' ) {
                        $hrs = 24 * $actionSchedule->repetition_frequency_interval * 7;
                    } else {
                        $hrs = $actionSchedule->repetition_frequency_interval;
                    }
                    $intervalClause = "( TIMEDIFF(NOW(), '{$actionLog->action_date_time}') >= TIME('{$hrs}:00:00') )";

                }

                // IF NO logs:
                // ( now >= date_built_from_start_time )
                // Otherwise IF repeat is turned ON:
                // ( (now <= repeat_end_time ) && ( diff(now && logged_date_time) >= repeat_interval ) )
                $where[] = $actionLogID ? "( NOW() <= {$endEvent} ) AND {$intervalClause}" : "( NOW() >= {$startEvent} )";;
            }

            // build final query
            $selectClause = "SELECT " . implode( ', ', $select );
            $fromClause   = "FROM $from";
            $joinClause   = !empty( $join ) ? implode( ' ', $join ) : '';
            $whereClause  = "WHERE " . (!empty( $where ) ? implode( ' AND ', $where ) : '(1)');
            
            $query = "$selectClause $fromClause $joinClause $whereClause";
            //CRM_Core_Error::debug( '$query', $query );
        }

        // FIXME: store contacts in temp table and make cron handle mailings in batches
        if ( $query ) {
            $dao = CRM_Core_DAO::executeQuery( $query );
            while ( $dao->fetch() ) {
                $contacts[$dao->contact_id] = $dao->entity_date_start;
            }
        }

        return $contacts;
    }
    
    static function retrieve( &$params, &$values ) 
    {
        if ( empty ( $params ) ) {
            return null;

        }
        $actionSchedule = new CRM_Core_DAO_ActionSchedule( );

        $actionSchedule->copyValues( $params );

        if ( $actionSchedule->find(true) ) {
            $ids['actionSchedule'] = $actionSchedule->id;

            CRM_Core_DAO::storeValues( $actionSchedule, $values );
            
            return $actionSchedule;
        }
        return null;

    }
    
    /**
     * Function to delete a Reminder
     * 
     * @param  int  $id     ID of the Reminder to be deleted.
     * 
     * @access public
     * @static
     */
    static function del( $id )
    {
        if ( $id ) {
            $dao = new CRM_Core_DAO_ActionSchedule( );
            $dao->id =  $id;
            if ( $dao->find( true ) ) {
                $dao->delete( );
                return;
            }
        }
        CRM_Core_Error::fatal( ts( 'Invalid value passed to delete function.' ) );
    }

    static function setIsActive( $id, $is_active ) {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_ActionSchedule', $id, 'is_active', $is_active );
    }
}