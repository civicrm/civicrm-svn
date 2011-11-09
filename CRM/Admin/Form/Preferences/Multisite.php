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
class CRM_Admin_Form_Preferences_Multisite extends CRM_Admin_Form_Preferences
{
    function preProcess( ) {
        CRM_Utils_System::setTitle(ts('CiviCampaign Component Settings'));
        $this->_varNames = 
            array( CRM_Core_BAO_Setting::MULTISITE_PREFERENCES_NAME =>
                   array( 
                       'is_enabled'                         => ts( 'Enable Multi Site' ),
                       'uniq_email_per_site'                => ts( 'Ensure multi sites have a unique email per site' ),
                       'domain_group_id'                    => ts( 'The parent group for this domain' ),
                       'event_price_set_domain_id'          => ts( 'Should events respect domain' ),
                       'is_enabled'                         => array( 'html_type'    => 'checkbox',
                                                                      'title'        => ts( 'Enable Multi-site' ),
                                                                      'weight'       => 1,
                                                                      'description'  => ts( ''),
                                                                      ),
                      'is_enabled'                         => array( 'html_type'    => 'checkbox',
                                                                     'title'        => ts( 'Enable Multi-site' ),
                                                                     'weight'       => 1,
                                                                     'description'  => ts( ''),
                                                                     ),
                         'tag_unconfirmed'      => array( 'html_type'    => 'text',
                                                                        'title'        => ts( 'Tag for Unconfirmed Petition Signers' ),
                                                                        'weight'       => 1,
                                                                        'description'  => ts( 'If set, new contacts that are created when signing a petition are assigned a tag of this name.')
                                                                        ),
                         'petition_contacts'  => array( 'html_type'    => 'text',
                                                                        'title'        => ts( 'Petition Signers Group' ),
                                                                        'weight'       => 2,
                                                                        'description'  => ts( 'All contacts that have signed a CiviCampaign petition will be added to this group. The group will be created if it does not exist (it is required for email verification).'),
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

