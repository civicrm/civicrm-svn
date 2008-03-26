<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
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
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Core/Page.php';

/**
 * Page for displaying Administer CiviCRM Control Panel
 */
class CRM_Admin_Page_Admin extends CRM_Core_Page
{
    function run ( ) {

        // ensure that all CiviCRM tables are InnoDB, else abort
        if ( CRM_Core_DAO::isDBMyISAM( ) ) {
            $errorMessage = ts('Your database is configured to use the MyISAM database engine. CiviCRM  requires InnoDB. You will need to convert any MyISAM tables in your database to InnoDB before proceeding.');            
            CRM_Core_Error::fatal( $errorMessage );
        }

        require_once 'CRM/Core/Menu.php';
        $items =& CRM_Core_Menu::items( );
        $groups = array( ts('Customize'), ts('Configure'), ts('Manage'), ts('Option Lists') );

        $config =& CRM_Core_Config::singleton( );
        if ( in_array("CiviContribute", $config->enableComponents) ) {
            $groups[] = 'CiviContribute';
        }
        
        if ( in_array("CiviMember", $config->enableComponents) ) {
            $groups[] = 'CiviMember';
        }

        if ( in_array("CiviEvent", $config->enableComponents) ) {
            $groups[] = 'CiviEvent';
        }

         if ( in_array("CiviMail", $config->enableComponents) ) {
            $groups[] = 'CiviMail';
        }

       $adminPanel = array( );
       require_once 'CRM/Core/ShowHideBlocks.php';
       $this->_showHide =& new CRM_Core_ShowHideBlocks( );
       foreach ( $groups as $group ) {
           // Hide (compress) all panel groups by default. We'll remember last state of each when we save user prefs later.
           $this->_showHide->addShow( "id_{$group}_show" );
           $this->_showHide->addHide( "id_{$group}" );
           $adminPanel[$group] = array( );
           $v = CRM_Core_ShowHideBlocks::links($this, $group, '' , '', false);
           $adminPanel[$group]['show'] = $v['show'];
           $adminPanel[$group]['hide'] = $v['hide'];
           $i = 0;
            foreach ( $items as $item ) {
                if ( CRM_Utils_Array::value( 'adminGroup', $item ) == $group ) {
                    $i++;
                    $value = array( 'title' => $item['title'],
                                    'desc'  => $item['desc'],
                                    'id'    => strtr($item['title'], array('('=>'_', ')'=>'', ' '=>'',
                                                                           ','=>'_', '/'=>'_' 
                                                                           )
                                                     ),
                                    'url'   => CRM_Utils_System::url( $item['path'],
                                                                      CRM_Utils_Array::value( 'query', $item ) ),
                                    'icon'  => $item['icon'],
                                    'extra' => CRM_Utils_Array::value( 'extra', $item ) );
                    $adminPanel[$group][$item['weight'] . '.' . $item['title']] = $value;
                }
            }
            $adminPanel[$group]['perColumn'] = round($i / 2);
            ksort( $adminPanel[$group] );
        }

        require_once 'CRM/Utils/VersionCheck.php';
        $versionCheck =& CRM_Utils_VersionCheck::singleton();
        $this->assign('newVersion',   $versionCheck->newerVersion());
        $this->assign('localVersion', $versionCheck->localVersion);

        $this->assign('adminPanel', $adminPanel);
        $this->_showHide->addToTemplate( );
        return parent::run( );
    }
}

