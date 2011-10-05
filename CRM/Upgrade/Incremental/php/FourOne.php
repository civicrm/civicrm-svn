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
 | License along with this program; if not, contact CiviCRM LLC       |
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

class CRM_Upgrade_Incremental_php_FourOne {
    
    function verifyPreDBstate ( &$errors ) {
    	$config = CRM_Core_Config::singleton( );
        if ( in_array( 'CiviCase', $config->enableComponents ) ) {
            if (! CRM_Core_DAO::checkTriggerViewPermission( true, false ) ) {
                $errors[] = ts('CiviCase now requires CREATE VIEW and DROP VIEW permissions for the database user.');
                return false;
            }
        }
        
        return true;
    }
    
    function upgrade_4_1_alpha1( $rev ) {
    	$config = CRM_Core_Config::singleton( );
        if ( in_array( 'CiviCase', $config->enableComponents ) ) {
            require_once 'CRM/Case/BAO/Case.php';
        	if ( ! CRM_Case_BAO_Case::createCaseViews( ) ) {
                $template = CRM_Core_Smarty::singleton( );
                $afterUpgradeMessage = '';
		        if ( $afterUpgradeMessage = $template->get_template_vars('afterUpgradeMessage') ) {
		        	$afterUpgradeMessage .= "<br/><br/>";
		        }
		        $afterUpgradeMessage .= '<div class="crm-upgrade-case-views-error" style="background-color: #E43D2B; padding: 10px;">' . ts( "There was a problem creating CiviCase database views. Please create the following views manually before using CiviCase:" );
		        $afterUpgradeMessage .= '<div class="crm-upgrade-case-views-query"><div>'
		            . CRM_Case_BAO_Case::createCaseViewsQuery( 'upcoming' ) . '</div><div>'
		            . CRM_Case_BAO_Case::createCaseViewsQuery( 'recent' ) . '</div>'
		            . '</div></div>';
		        $template->assign('afterUpgradeMessage', $afterUpgradeMessage);
        	}
        }

        $upgrade = new CRM_Upgrade_Form( );
        $upgrade->processSQL( $rev );

        $this->transferPreferencesToSettings( );
    }

    function transferPreferencesToSettings( ) {
        require_once 'CRM/Core/BAO/Setting.php';

        // first transfer system preferences
        $domainColumnNames = array( CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME => array( 
                                                                                           'contact_view_options',
                                                                                           'advanced_search_options',
                                                                                           'user_dashboard_options',
                                                                                           'address_options',
                                                                                           'address_format',
                                                                                           'mailing_format',
                                                                                           'display_name_format',
                                                                                           'sort_name_format',
                                                                                           'editor_id',
                                                                                           'contact_autocomplete_options',
                                                                                            ),
                                    CRM_Core_BAO_Setting::ADDRESS_STANDARDIZATION_PREFERENCES_NAME => array(
                                                                                                            'address_standardization_provider',
                                                                                                            'address_standardization_userid',
                                                                                                            'address_standardization_url',
                                                                                                            ),
                                    CRM_Core_BAO_Setting::MAILING_PREFERENCES_NAME => array(
                                                                                            'mailing_backend',
                                                                                            ),
                                    );

        $userColumnNames = array( CRM_Core_BAO_Setting::NAVIGATION_NAME => array(
                                                                                 'navigation',
                                                                                 ),
                                  );

        $sql = "
SELECT *
FROM   civicrm_preferences
WHERE  domain_id = %1
";
        $params = array( 1 => array( CRM_Core_Config::domainID( ), 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $sql, $params );

        $domainID    = CRM_Core_Config::domainID( );
        $createdDate = date( 'YmdHis' );
        $session     = CRM_Core_Session::singleton( );
        $createdID   = $session->get( 'userID' );

        while ( $dao->fetch( ) ) {
            if ( $dao->is_domain ) {
                $values = array( );
                foreach ( $domainColumnNames as $groupName => $settingNames ) {
                    foreach ( $settingNames as $settingName ) {
                        $value = empty( $dao->$settingName ) ? null : serialize( $dao->$settingName );
                        $values[] = array( "'$groupName'",
                                           "'$settingName'",
                                           "'$value'",
                                           $domainID,
                                           null,
                                           1,
                                           '$createdDate',
                                           $createdID );
                    }
                }
            } else {
                // this is a user setting
                foreach ( $userColumnNames as $groupName => $settingNames ) {
                    foreach ( $settingNames as $settingName ) {
                        $value = empty( $dao->$settingName ) ? null : serialize( $dao->$settingName );
                        $values[] = array( "'$groupName'",
                                           "'$settingName'",
                                           "'$value'",
                                           $domainID,
                                           $dao->contact_id,
                                           0,
                                           '$createdDate',
                                           $createdID );
                    }
                }
            }

            $sql = "
INSERT INTO civicrm_setting( group_name, name, value, domain_id, contact_id, is_domain, created_date, created_id )
VALUES
";
            $sql .= implode( ",\n", $values );
            CRM_Core_DAO::executeQuery( $sql );
        }

        // now drop the civicrm_preferences table
        $sql = "DROP TABLE civicrm_preferences";
        CRM_Core_DAO::executeQuery( $sql );
    }
}