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

return array(
  'monetary_thousand_separator' => array(
    'group_name' => 'Localization Preferences',
    'group' => 'localization',
    'name' => 'monetary_thousand_separator',
    'type' => 'String',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
    ),
    'default' => ',',
    'add' => '4.3',
    'title' => 'Thousands Separator',
   'is_domain' => 1,
    'is_contact' => 0,
    'description' => null,
    'help_text' => null,
  ),
  'default_currency' => array(
    'group_name' => 'Localization Preferences',
    'group' => 'localization',
    'name' => 'default_currency',
    'type' => 'String',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'html_attributes' => array(
      'size' => 2,
    ),
    'default' => ',',
    'add' => '4.3',
    'title' => 'Default Currency',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Default currency assigned to contributions and other monetary transactions.',
    'help_text' => null,
  ),

);