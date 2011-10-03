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
        CRM_Utils_System::setTitle(ts('Settings - Site Preferences'));
        // add all the checkboxes
        $this->_checkbox = array(
                                 'profile_double_optin'               => ts( 'Enable Double opt-in for Profiles' ),
                                 'profile_add_to_group_double_optin'  => ts( 'Enable Double opt-in for groups in Add to Group(s) in Profiles' ),
                                 'track_civimail_replies'             => ts( 'Track CiviMail replies using VERP in Reply-To header' ),
                                 'civimail_workflow'                  => ts( 'Enable workflow support for CiviMail' ),
                                 'activity_assignee_notification'     => ts( 'Enable email notifications to Activity Assignees' ),
                                 'contact_ajax_check_similar'         => ts( 'Enable ajax check if similar contacts exist when creating a new contact' ),
                                 );

        $this->_text = array(
                             'tag_unconfirmed'   => ts( 'Tag to assign to contacts that are created when a petition is signed' ),
                             'petition_contacts' => ts( 'Group to assign all contacts that have signed a petition' )
                             );
        
        $this->_varNames = array( CRM_Core_BAO_Setting::MAILING_PREFERENCES_NAME => array( 
                                                                                         'profile_double_optin',
                                                                                         'profile_add_to_group_double_optin',
                                                                                         'track_civimail_replies',
                                                                                         'activity_assignee_notification',
                                                                                         'civimail_workflow',
                                                                                           ),
                                  CRM_Core_BAO_Setting::CONFIGURATION_PREFERENCES_NAME => array(
                                                                                                'contact_ajax_check_similar',
                                                                                                'tag_unconfirmed',
                                                                                                'petition_contacts',
                                                                                                ),
                                                                                        
                                  );


        $config = CRM_Core_Config::singleton( );
        if ( ! in_array( 'CiviMail', $config->enableComponents ) ) {
            unset( $this->_checkbox['profile_double_optin'] );
            unset( $this->_checkbox['profile_add_to_group_double_optin'] );
            unset( $this->_checkbox['track_civimail_replies'] );
            unset( $this->_checkbox['civimail_workflow'] );
        }

        parent::preProcess( );
    }

    function setDefaultValues( ) {
        $defaults = array( );

        foreach ( $this->_varNames as $groupName => $settingNames ) {
            foreach ( $settingNames as $settingName ) {
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
        
        if ( CRM_Utils_Array::value( 'contact_edit_preferences', $this->_params ) ) {
            $preferenceWeights = explode( ',' , $this->_params['contact_edit_preferences'] );
            foreach( $preferenceWeights as $key => $val ) {
                if ( !$val ) {
                    unset($preferenceWeights[$key]);
                }
            }
            require_once 'CRM/Core/BAO/OptionValue.php';
            $opGroupId = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup' , 'contact_edit_options', 'id', 'name' );
            CRM_Core_BAO_OptionValue::updateOptionWeights( $opGroupId, array_flip($preferenceWeights) );
        }

        if ( $config->userSystem->is_drupal == '1' && module_exists("wysiwyg")) {
            variable_set('civicrm_wysiwyg_input_format', $this->_params['wysiwyg_input_format']);
        }
        
        $this->_config->editor_id = $this->_params['editor_id'];
        $this->_config->display_name_format = $this->_params['display_name_format'];
        $this->_config->sort_name_format    = $this->_params['sort_name_format'];

        // set default editor to session if changed
        $session = CRM_Core_Session::singleton();
        $session->set( 'defaultWysiwygEditor', $this->_params['editor_id'] );
        
        parent::postProcess( );
    }//end of function

}


