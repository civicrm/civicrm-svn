<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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
 * This class stores logic for managing CiviCRM extensions.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */


class CRM_Core_Extensions_Module
{
    public function __construct( $ext ) {
        $this->ext = $ext;

        $this->config = CRM_Core_Config::singleton( );
    }

    
    public function install( ) {
        if ( array_key_exists( $this->ext->file, $this->config->civiModules ) ) {
            // CRM_Core_Error::fatal( 'This civiModule is already registered.' );
        }

        self::commonInstall( 'install' );
    }

    private function callHook( $moduleName, $modulePath, $hookName ) {
        include_once( $modulePath . DIRECTORY_SEPARATOR . $moduleName . '.php' );
        $fnName = "{$moduleName}_civicrm_{$hookName}";
        if ( function_exists( $fnName ) ) {
            $fnName( );
        }
    }

    private function commonInstall( $type = 'install' ) {
        $params = array( );
        $params['civiModules'] = $this->config->civiModules;
        $params['civiModules'][$this->ext->file] = $this->ext->key . DIRECTORY_SEPARATOR . $this->ext->file . ".php";

        CRM_Admin_Form_Setting::commonProcess( $params );

        $this->callHook( $this->ext->file,
                         $this->ext->path,
                         $type );
    }

    public function uninstall( ) {
        if( !array_key_exists( $this->ext->file, $this->config->civiModules ) ) {
            CRM_Core_Error::fatal( 'This civiModule is not registered.' );
        }

        $this->commonUNInstall( 'uninstall' );
    }

    private function commonUNInstall( $type = 'uninstall' ) {
        $params = array( );
        $params['civiModules'] = array_diff( $this->config->civiModules,
                                             array( $this->ext->key ) );
        CRM_Admin_Form_Setting::commonProcess( $params );

        $this->callHook( $this->ext->file,
                         $this->ext->path,
                         $type );
    }
    
    public function disable() {
        $this->commonUNInstall( 'disable' );
    }
    
    public function enable() {
        $this->commonInstall( 'enable' );
    }
    
}