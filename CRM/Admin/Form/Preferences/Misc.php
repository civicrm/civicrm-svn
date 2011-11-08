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
class CRM_Admin_Form_Preferences_Misc extends CRM_Admin_Form_Preferences
{
    function preProcess( ) {
        CRM_Utils_System::setTitle(ts('CiviMail Settings'));
        $this->_varNames = array( CRM_Core_BAO_Setting::MAILING_PREFERENCES_NAME =>
            array( 
                'profile_double_optin'               => array( 'html_type'    => 'checkbox',
                                                                'title'        => ts( 'Enable Double opt-in for Profiles' ),
                                                                'description'  => ts( 'When CiviMail is enabled, if a profile includes a Groups checkbox the user will receive a confirmation email which they must respond to before they are added to a group.'),
                                                             ),
                'profile_add_to_group_double_optin'  => array( 'html_type'    => 'checkbox',
                                                                'title'        => ts( 'Enable Double opt-in for Profiles which have automatic Add Contact to Group setting' ),
                                                                'description'  => ts( 'When CiviMail is enabled and a profile uses the "Add to Group" setting, the user will receive a confirmation email which they must respond to before they are added to the group.'),
                                                             ),
                'track_civimail_replies'             => array( 'html_type'    => 'checkbox',
                                                                'title'        => ts( 'Track CiviMail replies using VERP in Reply-To header' ),
                                                                'description'  => ts( ''),
                                                             ),
                'civimail_workflow'                  => array( 'html_type'    => 'checkbox',
                                                                'title'        => ts( 'Enable workflow support for CiviMail' ),
                                                                'description'  => ts( 'Drupal-only. Rules module must be enabled (beta feature - use with caution).' ),
                                                             ),
                'civimail_server_wide_lock'          => array( 'html_type'    => 'checkbox',
                                                                      'title'        => ts( 'Enable global server wide lock for CiviMail' ),
                                                                      'description'  => ts( '' ),
                                                             ),
            )
        );

        parent::preProcess( );
    }

    function setDefaultValues( ) {
        $defaults = array( );

        foreach ( $this->_varNames as $groupName => $settings ) {
            foreach ( $settings as $settingName => $dontcare ) {
                $defaults[$settingName] = 
                    isset( $this->_config->$settingName ) ?
                    $this->_config->$settingName :
                    null;
            }
        }

        return $defaults;
    }

    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        parent::buildQuickForm( );
    }

       
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $config = CRM_Core_Config::singleton();
        if ( $this->_action == CRM_Core_Action::VIEW ) {
            return;
        }

        $this->_params = $this->controller->exportValues( $this->_name );
        
        parent::postProcess( );
    }//end of function

}


