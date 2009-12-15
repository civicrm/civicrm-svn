<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
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
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */
 
require_once 'CRM/Core/Page.php';

/**
 * CiviCRM Dashlet
 *
 */
class CRM_Contact_Page_Dashlet extends CRM_Core_Page
{
        
    /**
     * Run dashboard
     *
     * @return none
     * @access public
     */
    function run( ) {
        CRM_Utils_System::setTitle( ts('Dashlets') );
        
        // get all dashlets
        require_once 'CRM/Core/BAO/Dashboard.php';
        $allDashlets = CRM_Core_BAO_Dashboard::getDashlets( false );
        
        // get dashlets for logged in contact
        $currentDashlets  = CRM_Core_BAO_Dashboard::getContactDashlets( );

        $contactDashlets = $availableDashlets = array( );
        foreach( $allDashlets as $dashletID => $values ) {
            if ( ! empty( $currentDashlets ) && CRM_Utils_Array::value( '0', $currentDashlets ) 
                 && array_key_exists( $dashletID, $currentDashlets[0] ) ) {
                // we need append state of dashlet to id
                $key = "{$dashletID}-{$currentDashlets[0][$dashletID]}";
                $contactDashlets[0][$key] = $values['label'];
            } else if ( ! empty( $currentDashlets ) && CRM_Utils_Array::value( '1', $currentDashlets ) 
                        && array_key_exists( $dashletID, $currentDashlets[1] ) ) {
                $key = "{$dashletID}-{$currentDashlets[1][$dashletID]}";
                $contactDashlets[1][$key] = $values['label'];
            } else {
                // always keep maximize state for available
                $key = "{$dashletID}-0";
                $availableDashlets[$key] = $values['label'];
            }
        }

        $this->assign( 'contactDashlets', $contactDashlets );
        $this->assign( 'availableDashlets', $availableDashlets );
        
        return parent::run( );
    }
}
