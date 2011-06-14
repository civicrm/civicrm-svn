<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
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

require_once 'utils.inc';
require_once 'civicrm_rules_entities.rules.inc';

/**
 * Implements hook_rules_file_info() on behalf of the user module.
 */
function civicrm_rules_rules_file_info( ) {
  return array( 'civicrm_rules.eval' );
}

/**
 * Implementation of hook_rules_event_info().
 */
function civicrm_rules_rules_event_info( ) {
    $defaults = array( 'access callback' => 'civicrm_rules_rules_integration_access',
                       'module'          => 'civicrm', 
                       );
    return array(
                 'contact_create' => array(
                                           'label'     => t( 'Contact has been created' ),
                                           'module'    => 'CiviCRM Contact',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Created contact' ),
                                                                                               'contact' ),
                                           ),
                 'contact_edit' => array(
                                         'label'     => t( 'Contact has been updated' ),
                                         'module'    => 'CiviCRM Contact',
                                         'arguments' => rules_events_hook_civicrm_arguments( t( 'Updated contact' ),
                                                                                             'contact' ),
                                         ),
                 'contact_view'   => array(
                                           'label'     => t( 'Contact has been viewed' ),
                                           'module'    => 'CiviCRM Contact',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Viewed contact' ),
                                                                                               'contact' ),
                                           ),
                 'contact_delete' => array(
                                           'label'     => t( 'Contact has been deleted' ),
                                           'module'    => 'CiviCRM Contact',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Deleted contact' ),
                                                                                               'contact' ),
                                           ),
                 'mailing_create' => array(
                                           'label'     => t( 'Mailing has been created' ),
                                           'module'    => 'CiviCRM Mailing',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Created mailing' ),
                                                                                               'mailing' ),
                                           ),
                 'mailing_edit' => array(
                                         'label'     => t( 'Mailing has been updated' ),
                                         'module'    => 'CiviCRM Mailing',
                                         'arguments' => rules_events_hook_civicrm_arguments( t( 'Updated Mailing' ),
                                                                                             'mailing' ),
                                         ),
                 'mailing_uploaded' => array(
                                             'label'     => t( 'Mailing content has been uploaded' ),
                                             'module'    => 'CiviCRM Mailing',
                                             'arguments' => rules_events_hook_civicrm_arguments( t( 'Mailing content uploaded' ),
                                                                                                 'mailing' ),
                                             ),
                 'mailing_scheduled' => array(
                                              'label'     => t( 'Mailing has been scheduled' ),
                                              'module'    => 'CiviCRM Mailing',
                                              'arguments' => rules_events_hook_civicrm_arguments( t( 'Scheduled mailing' ),
                                                                                                  'mailing' ),
                                              ),
                 'mailing_approved' => array(
                                             'label'     => t( 'Mailing has been approved/rejected' ),
                                             'module'    => 'CiviCRM Mailing',
                                             'arguments' => rules_events_hook_civicrm_arguments( t( 'Approved mailing' ),
                                                                                                 'mailing' ),
                                             ),
                 'mailing_inform'     => array(
                                               'label'     => t( 'Inform scheduler about the mailing' ),
                                               'module'    => 'CiviCRM Mailing',
                                               'arguments' => rules_events_hook_civicrm_arguments( t( 'Inform Mailing' ),
                                                                                                   'mailing' ),
                                               ),
                 'mailing_queued' => array(
                                           'label'     => t( 'Mailing has been queued' ),
                                           'module'    => 'CiviCRM Mailing',
                                           'arguments' => rules_events_hook_civicrm_arguments( t( 'Queued mailing' ),
                                                                                               'mailing' ),
                                           ),
                 'mailing_complete' => array(
                                             'label'     => t( 'Mailing has been completed' ),
                                             'module'    => 'CiviCRM Mailing',
                                             'arguments' => rules_events_hook_civicrm_arguments( t( 'Completed mailing' ),
                                                                                                 'mailing' ),
                                             )
                 )+ civicrm_rules_entities_event_info();
               
}


function civicrm_rules_condition_info() {
    return civicrm_rules_entities_condition_info();
}

/*
 * Implementation of hook_token_list().
 */
function civicrm_rules_token_list($type = 'all') {
    $tokens['civicrm_event']['creator_drupal_id'] = t('The user ID of the creator');
    $tokens['civicrm_event']['creator_drupal_id'] = t('The user ID of the creator');
    return $tokens;
}

/**
 * Implementation of hook_token_values().
 */
function civicrm_rules_token_values($type, $object = NULL) {
    switch ($type) {
    case 'civicrm_event':
     
        if (!empty($object)) {
            $values['creator drupal id'] = 1;
        }
        break;
    }

    return $values;
}

/**
 * CiviCRM integration access callback.
 * Returns some arguments suitable for hook_civicrm
 */
function civicrm_rules_rules_integration_access( $type, $name ) {
    if ($type == 'event' || $type == 'condition') {
        return entity_access( 'access', 'civicrm' );
    }
}

/**
 * CiviCRM integration admin access callback.
 */
function civicrm_rules_rules_admin_access( ) {
  return user_access( 'administer civicrm' );
 }

/**
 * Returns some parameter suitable for using it with CiviCRM Rules
 */
function civicrm_rules_rules_events_variables( $label, $type = 'contact' ) {
	return array( $type => array( 'type'  => $type,
                                  'label' => $label )
                  );
}

/**
 * Implementation of hook_rules_action_info().
 */
function civicrm_rules_rules_action_info( ) {
    civicrm_initialize( );
    //get the default from email address.
    require_once 'CRM/Core/OptionGroup.php';
    $defaultAddress = CRM_Core_OptionGroup::values( 'from_email_address', null, null, null, ' AND is_default = 1' );
    
    $settings['message'] = '
A new mailing has been scheduled: {mailing.name}

{mailing.viewUrl}
You can review the mail here: {mailing.editUrl}
You can schedule the mail for here: {mailing.scheduleUrl}

The content of the email is:
<pre>
{mailing.html}
</pre>
';
    $settings['from'] = ( isset( $defaultAddress[1] ) ) ? $defaultAddress[1] : null;

    if ( !CRM_Utils_Array::value('subject', $settings ) ) {
        $settings['subject'] = 'New Email: {mailing.subject}';
    }
    
    $defaults = 
        array( 'parameter' => array( 'contact'       => array( 'type'  => 'contact',
                                                               'label' => t( 'Contact' ),
                                                               'save'  => TRUE,
                                                               ),
                                     'civicrm_group' => array( 'type'         => 'list<integer>',
                                                               'label'        => t( 'CiviCRM Groups' ),
                                                               'options list' => 'civicrm_rules_options_list',
                                                               ),
                                     ),
               'group'     => t( 'CiviCRM Contact' ),
               'access callback' => 'civicrm_rules_rules_admin_access',
               );
    
    $items['add_to_group']       = $defaults + array( 'label'    => t( 'Add Contact to CiviCRM Group' ),
                                                      'base'     => 'civicrm_rules_rules_action_add_to_group',
                                                      );
    $items['remove_from_group']  = $defaults + array( 'label'    => t( 'Remove Contact from CiviCRM Group' ),
                                                      'base'     => 'civicrm_rules_rules_action_remove_from_group',
                                                      );
    
    $defaults = 
        array( 'parameter' => array( 'to'      => array( 'type' => 'text',
                                                         'label' => t( 'To' ),
                                                         'description' => t( 'The e-mail address or addresses where the message will be sent to. The formatting of this string must comply with RFC 2822.' ),
                                                         ),
                                     'subject' => array( 'type' => 'text',
                                                         'label' => t( 'Subject' ),
                                                         'description' => t( "The mail's subject." ),
                                                         ),
                                     'message' => array( 'type' => 'text',
                                                         'label' => t( 'Message' ),
                                                         'description' => t( "The mail's message body." ),
                                                         ),
                                     'from'    => array( 'type' => 'text',
                                                         'label' => t( 'From' ),
                                                         'description' => t( "The mail's from address. Leave it empty to use the site-wide configured address." ),
                                                         'optional' => TRUE,
                                                         ),
                                     ),
               'group'     => t( 'CiviCRM Contact' ),
               'access callback' => 'civicrm_rules_rules_admin_access',
               );
    
    $items['contact_send_email'] = $defaults + array( 'label'    => t( 'Send Email' ),
                                                      'base'     => 'civicrm_rules_rules_action_contact_send_email',
                                                      );
    
    $defaults = 
        array( 'parameter' => array( 'to'      => array( 'type' => 'text',
                                                         'label' => t( 'To' ),
                                                         'description' => t( 'The e-mail address or addresses where the message will be sent to. The formatting of this string must comply with RFC 2822.' ),
                                                         ),
                                     'subject' => array( 'type' => 'text',
                                                         'label' => t( 'Subject' ),
                                                         'description' => t( "The mail's subject." ),
                                                         'default value' => $settings['subject'],
                                                         ),
                                     'message' => array( 'type' => 'text',
                                                         'label' => t( 'Message' ),
                                                         'description' => t( "The mail's message body." ),
                                                         'default value' => $settings['message'],
                                                         ),
                                     'from'    => array( 'type' => 'text',
                                                         'label' => t( 'From' ),
                                                         'description' => t( "The mail's from address. Leave it empty to use the site-wide configured address." ),
                                                         'default value' => $settings['from'],
                                                         'optional' => TRUE,
                                                         ),
                                     ),
               'group'     => t( 'CiviCRM Mailing' ),
               );
    
    $items['mailing_send_email'] = $defaults + array( 'label' => t( 'Send Email' ),
                                                      'base'  => 'civicrm_rules_rules_action_mailing_send_email',
                                                      );
    return $items;
}

/**
 * Options list callback for possible lising of CiviCRM Groups
 */
function civicrm_rules_options_list( ) {
    $settings['groups'] = _civicrm_get_groups( );
    return $settings;
}


/**
 * Implementation of hook_rules_data_type_info().
 */
function civicrm_rules_rules_data_info() {
    return array(
                 'contact' => array(
                                    'label' => t( 'Contact' ),
                                    'class' => 'rules_data_type_contact',
                                    'savable' => FALSE,
                                    'identifiable' => TRUE,
                                    ),
                 'mailing' => array(
                                    'label' => t( 'Mailing' ),
                                    'class' => 'rules_data_type_mailing',
                                    'savable' => FALSE,
                                    'identifiable' => TRUE,
                                    ),
                 );
                 

}

