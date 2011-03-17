<?php 

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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

class CRM_Upgrade_Incremental_php_ThreeFour {
    
    function verifyPreDBstate ( &$errors ) {
        return true;
    }
    
    function upgrade_3_4_alpha3( $rev ) 
    {
        // Handled for typo in 3.3.2.mysql.tpl, rename column visibilty to
        // visibility in table civicrm_mailing
        $mailingColumns =  CRM_Core_DAO::executeQuery("DESCRIBE civicrm_mailing");
        $renameColumnVisibility = false; 
        while ( $mailingColumns->fetch( ) ) {
            if ( $mailingColumns->Field == 'visibilty' ) {
                $renameColumnVisibility = true; 
                break;
            }
        }
        
        $upgrade =& new CRM_Upgrade_Form( );
        $upgrade->assign( 'renameColumnVisibility', $renameColumnVisibility);
        $upgrade->processSQL( $rev );
    }   

  }
