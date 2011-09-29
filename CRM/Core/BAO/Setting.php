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
 * $Id$
 *
 */

require_once 'CRM/Core/DAO/Setting.php';

/**
 * BAO object for civicrm_setting table. This table is used to store civicrm settings that are not used
 * very frequently (i.e. not on every page load)
 *
 * The group column is used for grouping together all settings that logically belong to the same set.
 * Thus all settings in the same group are retrieved with one DB call and then cached for future needs.
 *
 */

class CRM_Core_BAO_Setting extends CRM_Core_DAO_Setting
{
    static $_cache = null;

    /**
     * Retrieve the value of a setting from the DB table
     *
     * @param string $group (required) The group name of the item
     * @param string $name  (required) The name under which this item is stored
     * @param int    $componentID The optional component ID (so componenets can share the same name space)
     * @param ???    $defaultValue The default value to return for this setting if not present in DB
     *
     * @return object The data if present in the setting table, else null
     * @static
     * @access public
     */
    static function &get( $group, $name, $componentID = null, $defaulValue = null ) {

        if ( ! isset( self::$_cache ) ) {
            self::$_cache = array( );
        }

        if ( ! isset( self::$_cache[$group] ) ) {
            // check in civi cache if present (typically memcache)
            $cacheKey = "CRM_Setting_{$group}_{$componentID}";
            require_once 'CRM/Utils/Cache.php';
            $globalCache = CRM_Utils_Cache::singleton( );
            $result = $cache->get( $cacheKey );
            if ( $result === null ) {
                $dao = new CRM_Core_DAO_Setting( );

                $dao->group = $group;
                $dao->component_id = $componentID;
                $dao->find( );
                
                self::$_cache[$group] = array( );
                while ( $dao->fetch( ) ) {
                    self::$_cache[$group][$dao->name] = unserialize( $dao->value );
                }
                $dao->free( );

                $cache->set( $cacheKey, self::$_cache[$group] );
            } else {
                self::$_cache[$group] = $result;
            }
        }

        return CRM_Utils_Array::value( $name, self::$_cache[$group], $defaultValue );
    }

    /**
     * Store an item in the setting table
     *
     * @param object $value (required) The value that will be serialized and stored
     * @param string $group (required) The group name of the item
     * @param string $name  (required) The name of the setting
     * @param int    $componentID The optional component ID (so componenets can share the same name space)
     * @param int    $createdID   An optional ID to assign the creator to. If not set, retrieved from session
     *
     * @return void
     * @static
     * @access public
     */
    static function set( $value
                         $group,
                         $name,
                         $componentID = null,
                         $createdID = null ) {

        $dao = new CRM_Core_DAO_Setting( );

        $dao->group = $group;
        $dao->name  = $name;
        $dao->component_id = $componentID;

        $dao->find( true );

        $dao->value        = serialize( $value );
        $dao->created_date = date( 'Ymdhis' );

        if ( $createdID ) {
            $dao->created_id = $createdID;
        } else {
            $session = CRM_Core_Session::singleton( );
            $dao->created_id   = $session->get( 'userID' );
        }

        $dao->save( );
        $dao->free( );

        // also save in cache if needed
        if ( ! isset( self::$_cache ) ) {
            self::$_cache = array( );
        }

        // only set it if present in the cache
        // since we retrieve all group items at the same time
        if ( isset( self::$_cache[$group] ) ) {
            self::$_cache[$group][$name] = $value;
        }
    }

    /**
     * Delete some or all of the items in the settings table
     *
     * @param string $group The group name of the entries to be deleted
     * @param string $name  The name of the setting to be deleted
     * @param int    $componentID The optional component ID (so componenets can share the same name space)
     * 
     * @return void
     * @static
     * @access public
     */
    static function delete( $group = null, $componentID = null ) {
        $dao = new CRM_Core_DAO_Setting( );
        
        $dao->group = $group;
        $dao->name  = $name;
        $dao->component_id = $componentID;

        $dao->delete( );

        // also reset memory cache if any
        CRM_Utils_System::flushCache( );
    }

}
