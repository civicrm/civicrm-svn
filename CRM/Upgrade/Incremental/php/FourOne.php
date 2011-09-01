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
        	if ( ! CRM_Case_BAO_Case::createCaseViews( ) ) {
                $template = CRM_Core_Smarty::singleton( );
                $afterUpgradeMessage = '';
		        if ( $afterUpgradeMessage = $template->get_template_vars('afterUpgradeMessage') ) {
		        	$afterUpgradeMessage .= "<br/><br/>";
		        }
		        $afterUpgradeMessage .= '<div class="crm-upgrade-case-views-error">' . ts( "Error while creating CiviCase database views. Please create the following views manually before using CiviCase:" );
		        $afterUpgradeMessage .= '<div class="crm-upgrade-case-views-query"><div>'
		            . CRM_Case_BAO_Case::createCaseViewsQuery( 'upcoming' ) . '</div><div>'
		            . CRM_Case_BAO_Case::createCaseViewsQuery( 'recent' ) . '</div>'
		            . '</div></div>';
		        $template->assign('afterUpgradeMessage', $afterUpgradeMessage);
        	}
        }
    }
    
  }