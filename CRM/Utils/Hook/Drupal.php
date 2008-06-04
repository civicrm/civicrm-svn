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

class CRM_Utils_Hook_Drupal {

    static function twoArgsHook( &$arg1, &$arg2, $fnSuffix ) {
        $result = array( );
        // copied from user_module_invoke
        if (function_exists('module_list')) {
            foreach ( module_list() as $module) { 
                $function = "{$module}_{$fnSuffix}";
                if ( function_exists( $function ) ) {
                    $fResult = $function( $arg1, $arg2 );
                    if ( is_array( $fResult ) ) {
                        $result = array_merge( $result, $fResult );
                    }
                }
            }
        }
        return empty( $result ) ? true : $result;
    }

    static function threeArgsHook( &$arg1, &$arg2, &$arg3,$fnSuffix ) {
        $result = array( );
        // copied from user_module_invoke
        if (function_exists('module_list')) {
            foreach ( module_list() as $module) { 
                $function = "{$module}_{$fnSuffix}";
                if ( function_exists( $function ) ) {
                    $fResult = $function( $arg1, $arg2, $arg3 );
                    if ( is_array( $fResult ) ) {
                        $result = array_merge( $result, $fResult );
                    }
                }
            }
        }
        return empty( $result ) ? true : $result;
    }
    
    static function fourArgsHook( &$arg1, &$arg2, &$arg3, &$arg4, $fnSuffix ) {
        $result = array( );
        // copied from user_module_invoke
        if (function_exists('module_list')) {
            foreach ( module_list() as $module) { 
                $function = "{$module}_{$fnSuffix}";
                if ( function_exists( $function ) ) {
                    $fResult = $function( $arg1, $arg2, $arg3, $arg4 );
                    if ( is_array( $fResult ) ) {
                        $result = array_merge( $result, $fResult );
                    }
                }
            }
        }
        return empty( $result ) ? true : $result;
    }

    static function fiveArgsHook( &$arg1, &$arg2, &$arg3, &$arg4, &$arg5, $fnSuffix ) {
        $result = array( );
        // copied from user_module_invoke
        if (function_exists('module_list')) {
            foreach ( module_list() as $module) { 
                $function = "{$module}_{$fnSuffix}";
                if ( function_exists( $function ) ) {
                    $fResult = $function( $arg1, $arg2, $arg3, $arg4, $arg5 );
                    if ( is_array( $fResult ) ) {
                        $result = array_merge( $result, $fResult );
                    }
                }
            }
        }
        return empty( $result ) ? true : $result;
   }

}
