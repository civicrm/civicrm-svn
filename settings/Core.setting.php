<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
/*
 * Settings metadata file
 */
return array (
  'contact_view_options' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'contact_view_options',
    'type' => 'String',
    'html_type' => 'Checkboxes',
    'default' => null,
    'add' => '4.1',
    'title' => 'Tag for Unconfirmed Petition Signers',
    'is_domain' => '1',
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
  ),

  'contact_edit_options' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'contact_edit_options',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
     'help_text' => null,
  ),
  'advanced_search_options' => array(
    'group_name' => 'CiviCRM Preferences',
    'name' => 'advanced_search_options',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
     'help_text' => null,
  ),
 'user_dashboard_options' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'user_dashboard_options',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
     'help_text' => null,
  ),
  'address_options' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'address_options',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
  ),
  'address_format' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'address_format',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => "{contact.address_name}\n{contact.street_address}\n{contact.supplemental_address_1}\n{contact.supplemental_address_2}\n{contact.city}{, }{contact.state_province}{ }{contact.postal_code}\n{contact.country}",
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
     'help_text' => null,
  ),
  'mailing_format' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'mailing_format',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => "{contact.addressee}\n{contact.street_address}\n{contact.supplemental_address_1}\n{contact.supplemental_address_2}\n{contact.city}{, }{contact.state_province}{ }{contact.postal_code}\n{contact.country}",
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
     'help_text' => null,
  ),
  'display_name_format' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'display_name_format',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => '{contact.individual_prefix}{ }{contact.first_name}{ }{contact.last_name}{ }{contact.individual_suffix}',
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
  ),
  'sort_name_format' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'sort_name_format',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => '{contact.last_name}{, }{contact.first_name}',
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
     'help_text' => null,
  ),
  'editor_id' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'editor_id',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
  ),
  'contact_ajax_check_similar' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'contact_ajax_check_similar',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
  ),
  'activity_assignee_notification' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'activity_assignee_notification',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
     'help_text' => null,
  ),
  'activity_assignee_notification_ics' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'activity_assignee_notification_ics',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.3',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
     'help_text' => null,
  ),
  'contact_autocomplete_options' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'contact_autocomplete_options',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
     'help_text' => null,
  ),
  'contact_reference_options' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'contact_reference_options',
    'type' => 'String',
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.1',
    'title' => null,
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
     'help_text' => null,
  ),
  'max_attachments' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'max_attachments',
    'legacy_key' => 'maxAttachments',
    'prefetch' => 0,
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
      'maxlength' => 8,
     ),
     'default' => 3,
     'add' => '4.3',
     'title' => 'Maximum Attachments',
     'is_domain' => 1,
     'is_contact' => 0,
     'description' => 'Maximum number of files (documents, images, etc.) which can attached to emails or activities.',
     'help_text' => null,
  ),
  'maxFileSize' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'maxFileSize',
    'prefetch' => 1,
    'config_only' => 1,
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
      'maxlength' => 8,
    ),
    'default' => 3,
    'add' => '4.3',
    'title' => 'Maximum File Size (in MB)',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Maximum Size of file (documents, images, etc.) which can attached to emails or activities.<br />Note: php.ini should support this file size.',
    'help_text' => null,
  ),
  'contact_undelete' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'contact_undelete',
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => 1,
    'add' => '4.3',
    'title' => 'Contact Trash and Undelete',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'If enabled, deleted contacts will be moved to trash (instead of being destroyed). Users with the proper permission are able to search for the deleted contacts and restore them (or delete permanently).',
    'help_text' => null,
  ),
  'versionCheck' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'versionCheck',
    'prefetch' => 1,
    'config_only'=> 1,
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => 1,
    'add' => '4.3',
    'title' => 'Version Check & Statistics Reporting',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => "If enabled, CiviCRM automatically checks availablity of a newer version of the software. New version alerts will be displayed on the main CiviCRM Administration page.
When enabled, statistics about your CiviCRM installation are reported anonymously to the CiviCRM team to assist in prioritizing ongoing development efforts. The following information is gathered: CiviCRM version, versions of PHP, MySQL and framework (Drupal/Joomla/standalone), and default language. Counts (but no actual data) of the following record types are reported: contacts, activities, cases, relationships, contributions, contribution pages, contribution products, contribution widgets, discounts, price sets, profiles, events, participants, tell-a-friend pages, grants, mailings, memberships, membership blocks, pledges, pledge blocks and active payment processor types.",
    'help_text' => null,
  ),
  'doNotAttachPDFReceipt' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'doNotAttachPDFReceipt',
    'prefetch' => 1,
    'config_only'=> 1,
    'type' => 'Boolean',
    'quick_form_type' => 'YesNo',
    'default' => 1,
    'add' => '4.3',
    'title' => 'Attach PDF copy to receipts',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => "If enabled, CiviCRM sends PDF receipt as an attachment during event signup or online contribution.",
    'help_text' => null,
  ),
  'wkhtmltopdfPath' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'wkhtmltopdfPath',
    'prefetch' => 1,
    'config_only'=> 1,
    'type' => 'String',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 64,
      'maxlength' => 256,
    ),
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.3',
    'title' => 'Path to wkhtmltopdf executable',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
  ),
  'recaptchaPublicKey' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'recaptchaPublicKey',
    'prefetch' => 1,
    'config_only'=> 1,
    'type' => 'String',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 64,
      'maxlength' => 64,
    ),
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.3',
    'title' => 'Recaptcha Public Key',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
  ),
  'recaptchaPrivateKey' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'recaptchaPrivateKey',
    'prefetch' => 1,
    'config_only'=> 1,
    'type' => 'String',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 64,
      'maxlength' => 64,
    ),
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.3',
    'title' => 'Recaptcha Private Key',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
  ),
  'recaptchaPrivateKey' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'recaptchaPrivateKey',
    'prefetch' => 1,
    'config_only'=> 1,
    'type' => 'String',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 64,
      'maxlength' => 64,
    ),
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.3',
    'title' => 'Recaptcha Private Key',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
  ),
  'dashboardCacheTimeout' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'dashboardCacheTimeout',
    'prefetch' => 1,
    'config_only'=> 1,
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 3,
      'maxlength' => 5,
    ),
    'html_type' => 'Text',
    'default' => null,
    'add' => '4.3',
    'title' => 'Dashboard cache timeout',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
    ),
  'checksumTimeout' => array(
    'group_name' => 'CiviCRM Preferences',
    'group' => 'core',
    'name' => 'checksumTimeout',
    'prefetch' => 1,
    'config_only'=> 1,
    'type' => 'Integer',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
      'maxlength' => 8,
    ),
    'html_type' => 'Text',
    'default' => 7,
    'add' => '4.3',
    'title' => 'Dashboard cache timeout',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
  ),
);