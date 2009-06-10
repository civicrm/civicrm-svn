<?php

/*
  +--------------------------------------------------------------------+
  | CiviCRM version 2.2                                                |
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

require_once 'CRM/Core/DAO/Navigation.php';

class CRM_Core_BAO_Navigation extends CRM_Core_DAO_Navigation {
    /**
     * class constructor
     */
    function __construct( ) {
        parent::__construct( );
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * 
     * @access public
     * @static
     */
    static function setIsActive( $id, $is_active ) {
        return CRM_Core_DAO::setFieldValue( 'CRM_Core_DAO_Navigation', $id, 'is_active', $is_active );
    }

    /**
     * Function to get existing / build navigation for CiviCRM Admin Menu
     *
     * @static
     * @return array associated array
     */
    static function getMenus( ) {
        $menus = array( );
        require_once "CRM/Core/DAO/Menu.php";
        $menu  =& new CRM_Core_DAO_Menu( );
        $menu->find();
        while ( $menu->fetch() ) {
            if ( $menu->title ) {
                $menus[$menu->path] = $menu->title;
            }
        }    
        return $menus;        
    }
    
    /**
     * Function to add/update navigation record
     *
     * @param array associated array of submitted values
     *
     * @return object navigation object
     * @static
     */
    static function add( &$params ) {
        require_once "CRM/Core/DAO/Navigation.php";
        $navigation  =& new CRM_Core_DAO_Navigation( );
        if ( !isset( $params['id'] ) ) {
            $params['name']   = $params['label'];
            $params['weight'] = self::calculateWeight( $params['parent_id'] );
        }
                        
        $params['permission'] = implode( ',', $params['permission'] );

        $navigation->copyValues( $params );
        $navigation->save();
        return $navigation;
    } 
      
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Core_BAO_Navigation object on success, null otherwise
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) {
        $navigation =& new CRM_Core_DAO_Navigation( );
        $navigation->copyValues( $params );
        if ( $navigation->find( true ) ) {
            CRM_Core_DAO::storeValues( $navigation, $defaults );
            return $navigation;
        }
        return null;
    }
      
    /**
     * Calculate navigation weight
     *
     * @param $parentID parent_id of a menu
     * @param $menuID  menu id
     *
     * @return $weight string
     * @static
     */
    static function calculateWeight( $parentID = null, $menuID = null ) {
        $weight = 1;
        // we reset weight for each parent, i.e we start from 1 to n
        // calculate max weight for top level menus, if parent id is absent
        if ( !$parentID ) {
            $query = "SELECT max(weight) as weight FROM civicrm_navigation WHERE parent_id IS NULL";
        } else {
            // if parent is passed, we need to get max weight for that particular parent
            $query = "SELECT max(weight) as weight FROM civicrm_navigation WHERE parent_id = {$parentID}";
        }

        $dao = CRM_Core_DAO::executeQuery( $query );
        $dao->fetch();
        return $weight = $weight + $dao->weight;
    }
       
    /**
     * Get formatted menu list
     * 
     * @param array   $navigations navigation array
     * @param boolean $flatList if result array is flat array or associated array
     * @param int     $parentID  parent id
     * @param string  $separtor, separtor to show children
     *
     * @return array $navigations returns associated array
     * @static
     */
    static function getNavigationList( &$navigations, $flatList = true, $parentID = null, $separtor = '&nbsp;&nbsp;' ) {
        $whereClause = " parent_id IS NULL";
        if (  $parentID ) {
            $whereClause = " parent_id = {$parentID}"; 
            $separtor .= $separtor;
        } else {
            $separator = '';
        }

        $query = "SELECT id, label, parent_id, weight, is_active FROM civicrm_navigation WHERE {$whereClause} ORDER BY parent_id, weight ASC";
        $navigation = CRM_Core_DAO::executeQuery( $query );

        while ( $navigation->fetch() ) {
            if ( !$navigation->parent_id ) {
                $label = "{$navigation->label}";
            } else {
                $label = "{$separtor}{$navigation->label}";
            }

            if ( $flatList ) {
                $navigations[$navigation->id] = $label;
            } else {
                $navigations[$navigation->id] = array( 'label'     => $label,
                                                       'is_active' => $navigation->is_active,
                                                       'parent_id' => $navigation->parent_id );
            }

            self::getNavigationList( $navigations, $flatList, $navigation->id, $separtor );
        }

        return $navigations;           
    }
        
    /**
     * Function to build navigation tree
     * 
     * @param array $navigationTree nested array of menus
     * @param int   $parentID       parent id 
     *
     * @return array $navigationTree nested array of menus
     * @static
     */
    static function buildNavigationTree( &$navigationTree, $parentID ) {
        $whereClause = " parent_id IS NULL";

        if (  $parentID ) {
            $whereClause = " parent_id = {$parentID}"; 
        }

        // get the list of menus
        $query = "
SELECT id, label, url, permission, permission_operator, has_separator 
FROM civicrm_navigation 
WHERE {$whereClause} 
AND is_active = 1
ORDER BY parent_id, weight";

        $navigation = CRM_Core_DAO::executeQuery( $query );

        while ( $navigation->fetch() ) { 
            // for each menu get their children
            $navigationTree[$navigation->id] = array( 'attributes' => array( 'label'      => $navigation->label,
                                                                             'url'        => $navigation->url,
                                                                             'permission' => $navigation->permission,
                                                                             'operator'   => $navigation->permission_operator,
                                                                             'separator'  => $navigation->has_separator ) );
            self::buildNavigationTree( $navigationTree[$navigation->id]['child'], $navigation->id );
        }

        return $navigationTree;
    }
        
    /**
     * Function to build menu 
     * 
     * @param boolean $json by default output is html
     * 
     * @return returns html or json object
     * @static
     */
    static function buildNavigation( $json = false ) {
        $navigations = array( );
        self::buildNavigationTree( $navigations, $parent = NULL );
        $navigationString = null;

        foreach( $navigations as $key => $value ) {
            if ( $json ) {
                if ( $navigationString ) {
                    $navigationString .= '},';
                }
                $navigationString .= ' { attributes: { id : "node_'.$key.'" }, data: "'. $value['attributes']['label']. '"';
            } else {
                $name = self::getMenuName( $value );
                if ( $name ) { 
                    $navigationString .= '<li>' . $name;
                }
            }
            
            self::recurseNavigation( $value, $navigationString, $json );
        }
        
        if ( $json ) {
            $navigationString = '[' .$navigationString . '}]';
        }

        return $navigationString;
    }
         
    /**
     * Recursively check child menus
     */
    function recurseNavigation(&$value, &$navigationString, $json ) {
        if ( $json ) {
            if ( !empty( $value['child'] ) ) {
                $navigationString .= ', children : [ ';
            } else {
                return $navigationString ;
            }

            if ( !empty( $value['child'] ) ) {
                $appendComma = false;
                foreach($value['child'] as $k => $val ) {
                    $appendComma = true;                        
                    $navigationString .= ' { attributes: { id : "node_'.$k.'" }, data: "'. $val['attributes']['label'] .'"';
                    self::recurseNavigation($val, $navigationString, $json );
                    if ( $appendComma ) {
                        $navigationString .= ' },';
                    }
                }
            }

            if ( !empty( $value['child'] ) ) {
                $navigationString .= ' ]';
            }
            
        } else {
            if ( !empty( $value['child'] ) ) {
                $navigationString .= '<ul>';  
            } else {
                $navigationString .= '</li>';
                if ( isset( $value['attributes']['separator'] ) ) {
                    $navigationString .= '<li class="menu-separator"></li>';
                } 
            }

            if ( !empty( $value['child'] ) ) {
                foreach($value['child'] as $val ) {
                    $name = self::getMenuName( $val );
                    if ( $name ) { 
                        $navigationString .= '<li>' . $name;
                        self::recurseNavigation($val, $navigationString, $json );
                    }
                }
            }
            if ( !empty( $value['child'] ) ) {
                $navigationString .= '</ul></li>';
            }
        }
        return $navigationString;
    }

    /**
     *  Get Menu name
     */
    function getMenuName( &$value ) {
        $name       = $value['attributes']['label'];
        $url        = $value['attributes']['url'];
        $permission = $value['attributes']['permission'];
        $operator   = $value['attributes']['operator'];
              
        $makeLink = false;
        if ( isset( $url ) && $url) {
            if ( substr( $url, 0, 4 ) === 'http' ) {
                $url = $url;
            } else {
                $url = CRM_Utils_System::url( $url );
            }
            $makeLink = true;
        }
                    
        if ( isset( $permission) && $permission ) {
            $permissions = explode(',', $permission ); 
            $config  =& CRM_Core_Config::singleton( );
            
            $showItem = true;
            foreach ( $permissions as $key ) {
                //hack to determine if it's a component related permission
                if ( $key != 'access CiviCRM' && substr( $key, 0, 6 ) === 'access' ) {
                    $componentName = trim(substr( $key, 6 ));
                    if ( !in_array( $componentName, $config->enableComponents ) ) {
                        $showItem = false;
                        if ( $operator == 'AND' ) {
                            return $showItem;
                        }
                    }
               } else if ( !CRM_Core_Permission::check( $key ) ) {
                     $showItem = false;
                     if ( $operator == 'AND' ) {
                         return $showItem;
                     }
                }
            }
            
            if ( !$showItem ) {
                return false;
            }   
        }
              
        if ( $makeLink ) {
            return $name = "<a href='{$url}'>{$name}</a>";
        }
 
        return $name;
    }
          
    /**
     * Function to create navigation for CiviCRM Admin Menu
     */
    static function createNavigation(  ) {
        $session=& CRM_Core_Session::singleton( );
        $contactID = $session->get('userID');

        $navigation = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Preferences', $contactID, 'navigation', 'contact_id' );
        if ( ! $navigation ) {
            //retrieve navigation if it's not cached.       
            require_once 'CRM/Core/BAO/Navigation.php';
            $navigation = self::buildNavigation( );
            
            //add additional navigation items
            $logoutURL       = CRM_Utils_System::url( 'civicrm/logout', 'reset=1');
            $appendSring     = "<li id='menu-logout'><a href={$logoutURL} title=". ts('Logout') .">". ts('Logout')."</a></li>";

            $homeURL       = CRM_Utils_System::url( 'civicrm/dashboard', 'reset=1');
            $prepandString = "<li><a href={$homeURL} title=". ts('CiviCRM Home') .">". ts('Home')."</a>";

            $config =& CRM_Core_Config::singleton( );

            if ( ( $config->userFramework == 'Drupal' ) && module_exists('admin_menu') ) {
               $prepandString .= "<ul><li><a href={$homeURL} title=". ts('CiviCRM Home') .">". ts('CiviCRM Home')."</a></i><li><a href='#' onclick='cj(\".cmDiv\").toggle();' title=". ts('Drupal Menu') .">".ts('Drupal Menu')."</a></li></ul>";
            }

            $prepandString .= "</li>";

            $navigation = $prepandString.$navigation.$appendSring;
            
            // save in preference table for this particular user
            require_once 'CRM/Core/DAO/Preferences.php';
            $preference =& new CRM_Core_DAO_Preferences();
            $preference->contact_id = $contactID;
            $preference->find(true);
            $preference->navigation = $navigation;
            $preference->save();
        }
        return $navigation;
    }

    /**
     * Reset navigation for all contacts
     */
    static function resetNavigation( ) {
        $query = "UPDATE civicrm_preferences SET navigation = NULL WHERE contact_id IS NOT NULL";
        CRM_Core_DAO::executeQuery( $query );
    }          

    /**
     * Function to process navigation
     *
     * @param array $params associated array, $_GET 
     *
     * @return void
     * @static
     */
     static function processNavigation( &$params ) {
         $nodeID      = (int)str_replace("node_","",$params['id']);
         $referenceID = (int)str_replace("node_","",$params['ref_id']);
         $moveType    = $params['move_type'];
         $type        = $params['type'];
         
         switch ( $type ) {
             case "move":
                self::processMove( $nodeID, $referenceID, $moveType ) ;
             break;
         }
         exit();
     }
     
     /**
      * Function to process move action
      */
      static function processMove( $nodeID, $referenceID, $moveType ) {
          if ( !in_array($moveType, array("after", "before", "inside") ) ) return false;
          
            crm_core_error::debug( '$nodeID', $nodeID );
            
            crm_core_error::debug( '$referenceID', $referenceID );
            
            crm_core_error::debug( '$moveType', $moveType );
            
      }
 }

