<?php
// $Id: user.eval.inc,v 1.1.2.1 2010/12/16 09:30:43 fago Exp $

/**
 * @file
 * Contains rules integration for the user module needed during evaluation.
 *
 * @addtogroup rules
 * @{
 */
require_once 'utils.inc';

function civicrm_rules_rules_action_mailing_send_email( $to, $subject, $message, $from = NULL, $settings, RulesState $state, RulesPlugin $element ) {
    if( isset($state->variables['mailing']) && $state->variables['mailing']->id ) {
        $to       = str_replace( array( "\r", "\n" ), ',', $to );
        $toEmails = explode( ',', $to );
        if ( empty( $toEmails ) ) { 
            return;
        }
        require_once 'CRM/Utils/Token.php';
        $tokens['mailing'] = array( 'mailing.editUrl',
                                    'mailing.scheduleUrl', 
                                    'mailing.html', 
                                    'mailing.creatorEmail', 
                                    'mailing.creator' );

        $params = array( );
        $params['from']    = !empty( $from ) ? str_replace( array( "\r", "\n" ), '', $from ) : 'Admin';
        $params['subject'] = CRM_Utils_Token::replaceMailingTokens( $subject, $state->variables['mailing'], null, $tokens );
        $params['html']  = CRM_Utils_Token::replaceMailingTokens( $message, $state->variables['mailing'], null, $tokens );
        foreach ( $toEmails as $toEmail ) {
            $params['toEmail']    = CRM_Utils_Token::replaceMailingTokens( $toEmail, $state->variables['mailing'], null, $tokens );
            CRM_Utils_Mail::send( $params );
        }
    }
}

function civicrm_rules_rules_action_contact_send_email( $to, $subject, $message, $from = NULL, $settings, RulesState $state, RulesPlugin $element ) {

    require_once 'CRM/Contact/BAO/Contact.php';
    $to       = str_replace( array( "\r", "\n" ), ',', $to );
    $toEmails = explode( ',', $to );
    if ( empty( $toEmails ) ) { 
        return;
    }
    foreach( $toEmails as $toEmail ) {
            $params = array( );
            $params['from']    = !empty( $from ) ? str_replace( array( "\r", "\n" ), '', $from ) : 'Admin';
            $params['toEmail'] = $toEmail;
            $params['subject'] = $subject;
            $params['text']    = is_array( $message )? implode( "\n", $message ) : $message;
            
            CRM_Utils_Mail::send( $params );
    }
    // FIX ME: add watchdog
}

/**
 * Action contact: add a contact to a group
 */
function civicrm_rules_rules_action_add_to_group( $contact, $settings ) {
    $contactId = ( $contact->id ) ? $contact->id : $contact->getVar( '_contactId' );
	if ( $contactId ) {
		foreach ( $settings as $gid ) {
			_civicrm_rules_group_contact( $contactId, $gid );
		}
		return array( 'contact' => $contact );
	}
}

/**
 * Action contact: remove a contact from a group
 */
function civicrm_rules_rules_action_remove_from_group( $contact, $settings ) {
    $contactId = ( $contact->id ) ? $contact->id : $contact->getVar( '_contactId' );
	if ( $contactId ) {
		foreach ( $settings as $gid ) {
			_civicrm_rules_group_contact( $contactId, $gid, 'remove' );
		}
		return array( 'contact' => $contact );
	}
}

/**
 * Action: Creates a contact.
 * FIX ME: This function is not called, hence can be deleted 
 */
function civicrm_rules_rules_contact_create( $first_name, $last_name, $email ) {
    $params = array( 'first_name' => $first_name,
                     'last_name'  => $last_name ,
                     'email'      => $email );
    
    require_once 'api/v2/Contact.php';
    $contact = civicrm_contact_create( $params );

    if ( ! $contact['is_error'] ) {
        return array( 'contact' => _civicrm_rules_load_contact( $contact['id'] ) );
    } else {
        return array( 'contact' => null );
    }
}
