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
 * $Id: Display.php 36505 2011-10-03 14:19:56Z lobo $
 *
 */

require_once 'CRM/Admin/Form/Preferences.php';

/**
 * This class generates form components for the display preferences
 * 
 */
class CRM_Admin_Form_Preferences_Contact extends CRM_Admin_Form_Preferences
{
    function preProcess( ) {
        CRM_Utils_System::setTitle(ts('Contact Settings'));
        $this->_varNames = 
            array( CRM_Core_BAO_Setting::MAILING_PREFERENCES_NAME =>
                   array( 
                         'activity_assignee_notification'     => array( 'html_type'    => 'checkbox',
                                                                        'title'        => ts( 'Enable Double Opt-in for Profile Group(s) field' ),
                                                                        'weight'       => 1,
                                                                        'description'  => ts( 'When CiviMail is enabled, users who "subscribe" to a group from a profile Group(s) checkbox will receive a confirmation email. They must respond (opt-in) before they are added to the group.'),
                                                                        ),
                         'contact_ajax_check_similar'         => array( 'html_type'    => 'checkbox',
                                                                        'title'        => ts( 'Enable Double Opt-in for Profiles which use the "Add to Group" setting' ),
                                                                        'weight'       => 2,
                                                                        'description'  => ts( 'When CiviMail is enabled and a profile uses the "Add to Group" setting, users who complete the profile form will receive a confirmation email. They must respond (opt-in) before they are added to the group.'),
                                                                        ),
                          )
                   );

        parent::preProcess( );
    }
}


