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
    /**
     * various predefined settings that have been migrated to the setting table
     */
    const 
        ADDRESS_STANDARDIZATION_PREFERENCES_NAME = 'Address Standardization Preferences',
        CONFIGURATION_PREFERENCES_NAME           = 'Configuration Preferences',
        MAILING_PREFERENCES_NAME                 = 'Mailing Preferences',
        MULTISITE_PREFERENCES_NAME               = 'Multi Site Preferences',
        NAVIGATION_NAME                          = 'Navigation Menu',
        SYSTEM_PREFERENCES_NAME                  = 'CiviCRM Preferences';

    static $_cache = null;

    /**
     * Checks whether an item is present in the in-memory cache table
     *
     * @param string $group (required) The group name of the item
     * @param string $name  (required) The name of the setting
     * @param int    $componentID The optional component ID (so componenets can share the same name space)
     * @param int    $contactID    If set, this is a contactID specific setting, else its a global setting
     * @param int    $load  if true, load from local cache (typically memcache)
     *
     * @return boolean true if item is already in cache
     * @static
     * @access public
     */
    static function inCache( $group,
                             $name,
                             $componentID = null,
                             $contactID = null,
                             $load = false ) {
        if ( ! isset( self::$_cache ) ) {
            self::$_cache = array( );
        }

        $cacheKey = "CRM_Setting_{$group}_{$componentID}_{$contactID}";
        if ( $load &&
             ! isset( self::$_cache[$cacheKey] ) ) {
            // check in civi cache if present (typically memcache)
            require_once 'CRM/Utils/Cache.php';
            $globalCache = CRM_Utils_Cache::singleton( );
            $result = $globalCache->get( $cacheKey );
            if ( $result ) {
                self::$_cache[$cacheKey] = $result;
            }
        }

        return isset( self::$_cache[$cacheKey] ) ? $cacheKey : null;
    }

    static function setCache( $values,
                              $group,
                              $componentID = null,
                              $contactID = null ) {
        if ( ! isset( self::$_cache ) ) {
            self::$_cache = array( );
        }

        $cacheKey = "CRM_Setting_{$group}_{$componentID}_{$contactID}";

        self::$_cache[$cacheKey] = $values; 

        require_once 'CRM/Utils/Cache.php';
        $globalCache = CRM_Utils_Cache::singleton( );
        $result = $globalCache->set( $cacheKey, $values );

        return $cacheKey;
    }

    static function dao( $group, 
                         $name = null,
                         $componentID = null,
                         $contactID = null ) {
        $dao = new CRM_Core_DAO_Setting( );

        $dao->group_name   = $group;
        $dao->name         = $name;
        $dao->component_id = $componentID;
        $dao->domain_id    = CRM_Core_Config::domainID( );
        
        if ( $contactID ) {
            $dao->contact_id = $contactID;
            $dao->is_domain  = 0;
        } else {
            $dao->is_domain  = 1;
        }

        return $dao;
    }

    /**
     * Retrieve the value of a setting from the DB table
     *
     * @param string $group (required) The group name of the item
     * @param string $name  (required) The name under which this item is stored
     * @param int    $componentID The optional component ID (so componenets can share the same name space)
     * @param string $defaultValue The default value to return for this setting if not present in DB
     * @param int    $contactID    If set, this is a contactID specific setting, else its a global setting

     * @return object The data if present in the setting table, else null
     * @static
     * @access public
     */
    static function getItem( $group,
                             $name = null,
                             $componentID = null,
                             $defaultValue = null,
                             $contactID   = null ) {

        $cacheKey = self::inCache( $group, $name, $componentID, $contactID, true );
        if ( ! $cacheKey ) {
            $dao = self::dao( $group, null, $componentID, $contactID );
            $dao->find( );
                
            $values = array( );
            while ( $dao->fetch( ) ) {
                if ( $dao->value ) {
                    $values[$dao->name] = unserialize( $dao->value );
                } else {
                    $values[$dao->name] = null;
                }
            }
            $dao->free( );

            $cacheKey = self::setCache( $values, $group, $componentID, $contactID );
        }

        return 
            $name ? 
            CRM_Utils_Array::value( $name, self::$_cache[$cacheKey], $defaultValue ) :
            self::$_cache[$cacheKey];
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
    static function setItem( $value,
                             $group,
                             $name,
                             $componentID = null,
                             $contactID   = null,
                             $createdID   = null ) {

        $dao = self::dao( $group, $name, $componentID, $contactID );
        $dao->find( true );

        if ( $value ) {
            $dao->value = serialize( $value );
        } else {
            $dao->value = 'null';
        }

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
        $cacheKey = self::inCache( $group, $name, $componentID, $contactID, false );
        if ( $cacheKey ) {
            self::$_cache[$cacheKey][$name] = $value;
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
    static function deleteItem( $group, $name = null, $componentID = null, $contactID = null ) {
        $dao = self::dao( $group, $name, $componentID, $contactID );
        $dao->delete( );

        // also reset memory cache if any
        CRM_Utils_System::flushCache( );

        $cacheKey = self::inCache( $group, $name, $componentID, $contactID, false );
        if ( $cacheKey ) {
            if ( $name ) {
                unset( self::$_cache[$cacheKey][$name] );
            } else {
                unset( self::$_cache[$cacheKey] );
            }
        }
    }

    static function valueOptions( $groupName, $name, $system = true, $userID = null, $localize = false,
                                  $returnField = 'name', $returnNameANDLabels = false, $condition = null ) {
        $optionValue = self::getItem( $groupName, $name );

        require_once 'CRM/Core/OptionGroup.php';
        $groupValues = CRM_Core_OptionGroup::values( $name, false, false, $localize, $condition, $returnField );

        //enabled name => label require for new contact edit form, CRM-4605
        if ( $returnNameANDLabels ) {
            $names = $labels = $nameAndLabels = array( );
            if ( $returnField == 'name' ) {
                $names  = $groupValues;
                $labels = CRM_Core_OptionGroup::values( $name, false, false, $localize, $condition, 'label' );
            } else {
                $labels = $groupValues;
                $names  = CRM_Core_OptionGroup::values( $name, false, false, $localize, $condition, 'name' );
            }
        }
        
        $returnValues = array( );
        foreach ( $groupValues as $gn => $gv ) {
            $returnValues[$gv] = 0;
        }
        
        if ( $optionValue && !empty( $groupValues ) ) {
            require_once 'CRM/Core/BAO/CustomOption.php';
            $dbValues = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                 substr( $optionValue, 1, -1 ) ); 
            
            if ( !empty( $dbValues ) ) { 
                foreach ( $groupValues as $key => $val ) { 
                    if ( in_array( $key, $dbValues ) ) {
                        $returnValues[$val] = 1;
                        if ( $returnNameANDLabels ) {
                            $nameAndLabels[$names[$key]] = $labels[$key]; 
                        }
                    }
                }
            }
        }
        
        return ( $returnNameANDLabels ) ? $nameAndLabels : $returnValues;
    }

}
