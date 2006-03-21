<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.4                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                  |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]socialsourcefoundation[DOT]org.  If you have |
 | questions about the Affero General Public License or the licensing |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | at http://www.openngo.org/faqs/licensing.html                       |
 +--------------------------------------------------------------------+
*/

/**
 * Component stores all the static and dynamic information of the various
 * CiviCRM components
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo (c) 2005
 * $Id$
 *
 */

class CRM_Core_Component {
    static $_info = null;

    static function &info( ) {
        if ( self::$_info == null ) {
            self::$_info = array( 
                                 'CiviContribute' => array( 'title' => 'CiviCRM Contribution Engine',
                                                            'path'  => 'CRM_Contribute_',
                                                            'url'   => 'contribute',
                                                            'perm'  => array( 'access CiviContribute',
                                                                              'edit contributions',
                                                                              'make online contributions' ) ),
                                 'CiviMail'       => array( 'title' => 'CiviCRM Mailing Engine',
                                                            'path'  => 'CRM_Mailing_',
                                                            'url'   => 'mailing',
                                                            'perm'  => array( 'access CiviMail' ) ),
                                 'Quest'          => array( 'title' => 'Quest Application Process',
                                                            'path'  => 'CRM_Quest_',
                                                            'url'   => 'quest',
                                                            'perm'  => array( 'access Quest Student Records' ) ),
                                 );
        }
        return self::$_info;
    }

    static function get( $name, $attribute = null) {
        $info =& self::info( );

        $comp = CRM_Utils_Array::value( $name, $info );
        if ( $attribute ) {
            return CRM_Utils_Array::value( $attribute, $comp );
        }
        return $comp;
    }

    static function invoke( &$args ) {
        $info =& self::info( );
        $config =& CRM_Core_Config::singleton( );

        foreach ( $info as $name => $value ) {
            if ( in_array( $name, $config->enableComponents ) &&
                 $info[$name]['url'] === $args[1] ) {
                $className = $info[$name]['path'] . 'Invoke';
                require_once(str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php');
                eval( $className . '::main( $args );' );
                return true;
            }
        }
        return false;
    }

    static function &menu( ) {
        $info =& self::info( );
        $config =& CRM_Core_Config::singleton( );

        $items = array( );
        foreach ( $info as $name => $value ) {
            if ( in_array( $name, $config->enableComponents ) ) {
                $className = $info[$name]['path'] . 'Menu';
                require_once(str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php');
                eval( '$ret = ' . $className . '::main( );' );
                $items = array_merge( $items, $ret );
            }
        }
        return $items;
    }

    static function addConfig( &$config ) {
        $info =& self::info( );

        foreach ( $info as $name => $value ) {
            if ( in_array( $name, $config->enableComponents ) ) {
                $className = $info[$name]['path'] . 'Config';
                require_once(str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php');
                eval( $className . '::add( $config );' );
            }
        }
        return;
    }

}

?>
