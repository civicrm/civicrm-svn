<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.9                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
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
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
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

require_once 'CRM/Core/Page/Basic.php';

class CRM_Group_Page_Group extends CRM_Core_Page_Basic 
{
    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     */
    static $_links = null;
    
    /**
     * The action links that we need to display for saved search items
     *
     * @var array
     */
    static $_savedSearchLinks = null;
    
    function getBAOName( ) 
    {
        return 'CRM_Contact_BAO_Group';
    }

    /**
     * Function to define action links
     *
     * @return array self::$_links array of action links
     * @access public
     */
    function &links()
    {
        $disableExtra = ts('Are you sure you want to disable this Group?');
        
        if (!(self::$_links)) {
            self::$_links = array(
                                  CRM_Core_Action::VIEW => array(
                                                                 'name'  => ts('Members'),
                                                                 'url'   => 'civicrm/group/search',
                                                                 'qs'    => 'reset=1&force=1&context=smog&gid=%%id%%',
                                                                 'title' => ts('Group Members')
                                                                 ),
                                  CRM_Core_Action::UPDATE => array(
                                                                   'name'  => ts('Settings'),
                                                                   'url'   => 'civicrm/group',
                                                                   'qs'    => 'reset=1&action=update&id=%%id%%',
                                                                   'title' => ts('Edit Group')
                                                                   ),
                                  CRM_Core_Action::DISABLE => array( 
                                                                    'name'  => ts('Disable'),
                                                                    'url'   => 'civicrm/group',
                                                                    'qs'    => 'reset=1&action=disable&id=%%id%%',
                                                                    'extra' => 'onclick = "return confirm(\''. $disableExtra . '\');"',
                                                                    'title' => ts('Disable Group') 
                                                                    ),
                                  CRM_Core_Action::ENABLE  => array( 
                                                                    'name'  => ts('Enable'),
                                                                    'url'   => 'civicrm/group',
                                                                    'qs'    => 'reset=1&action=enable&id=%%id%%',
                                                                    'title' => ts( 'Enable Group' ) 
                                                                    ),
                                  CRM_Core_Action::DELETE => array(
                                                                   'name'  => ts('Delete'),
                                                                   'url'   => 'civicrm/group',
                                                                   'qs'    => 'reset=1&action=delete&id=%%id%%',
                                                                   'title' => ts('Delete Group')
                                                                   )
                                  
                                  );
        }
        return self::$_links;
    }
    
    /**
     * Function to define action links for saved search
     *
     * @return array self::$_savedSearchLinks array of action links
     * @access public
     */
    function &savedSearchLinks( ) 
    {
        if ( ! self::$_savedSearchLinks ) {
            $deleteExtra = ts('Do you really want to remove this Smart Group?');
            self::$_savedSearchLinks =
                array(
                      CRM_Core_Action::VIEW   => array(
                                                       'name'  => ts('Show Group Members'),
                                                       'url'   => 'civicrm/contact/search/advanced',
                                                       'qs'    => 'reset=1&force=1&ssID=%%ssid%%',
                                                       'title' => ts('Search')
                                                       ),
                      CRM_Core_Action::UPDATE => array(
                                                       'name'  => ts('Edit'),
                                                       'url'   => 'civicrm/group',
                                                       'qs'    => 'reset=1&action=update&id=%%id%%',
                                                       'title' => ts('Edit Group')
                                                       ),
                      CRM_Core_Action::DELETE => array(
                                                       'name'  => ts('Delete'),
                                                       'url'   => 'civicrm/contact/search/saved',
                                                       'qs'    => 'action=delete&id=%%ssid%%',
                                                       'extra' => 'onclick="return confirm(\'' . $deleteExtra . '\');"',
                                                       ),
                      );
        }
        return self::$_savedSearchLinks;
    }

    /**
     * return class name of edit form
     *
     * @return string
     * @access public
     */
    function editForm( ) 
    {
        return 'CRM_Group_Form_Edit';
    }
    
    /**
     * return name of edit form
     *
     * @return string
     * @access public
     */
    function editName( ) 
    {
        return 'Edit Group';
    }

    /**
     * return class name of delete form
     *
     * @return string
     * @access public
     */
    function deleteForm( ) 
    {
        return 'CRM_Group_Form_Delete';
    }
    
    /**
     * return name of delete form
     *
     * @return string
     * @access public
     */
    function deleteName( ) 
    {
        return 'Delete Group';
    }
    
    /**
     * return user context uri to return to
     *
     * @return string
     * @access public
     */
    function userContext( $mode = null ) 
    {
        return 'civicrm/group';
    }
    
    /**
     * return user context uri params
     *
     * @return string
     * @access public
     */
    function userContextParams( $mode = null ) 
    {
        return 'reset=1&action=browse';
    }

    /**
     * make sure that the user has permission to access this group
     *
     * @param int $id   the id of the object
     * @param int $name the name or title of the object
     *
     * @return string   the permission that the user has (or null)
     * @access public
     */
    function checkPermission( $id, $title ) 
    {
        return CRM_Contact_BAO_Group::checkPermission( $id, $title );
    }
    
    /**
     * We need to do slightly different things for groups vs saved search groups, hence we
     * reimplement browse from Page_Basic
     * @param int $action
     *
     * @return void
     * @access public
     */
    function browse($action = null) 
    {
        $config =& CRM_Core_Config::singleton( );
        $values =  array( );
        
        $object =& new CRM_Contact_BAO_Group( );
        $object->domain_id = $config->domainID( );
        $object->orderBy ( 'title asc' );
        $object->find();
        
        $groupPermission = CRM_Core_Permission::check( 'edit groups' ) ? CRM_Core_Permission::EDIT : CRM_Core_Permission::VIEW;
        $this->assign( 'groupPermission', $groupPermission );

        require_once 'CRM/Core/OptionGroup.php';
        $groupTypes = CRM_Core_OptionGroup::values( 'group_type' );
        while ($object->fetch()) {
            $permission = $this->checkPermission( $object->id, $object->title );
            if ( $permission ) {
                $values[$object->id] = array( );
                CRM_Core_DAO::storeValues( $object, $values[$object->id]);
                if ( $object->saved_search_id ) {
                    $values[$object->id]['title'] .= ' (' . ts('Smart Group') . ')';
                }
                $links =& $this->links( );
                $action = array_sum(array_keys($links));
                if ( array_key_exists( 'is_active', $object ) ) {
                    if ( $object->is_active ) {
                        $action -= CRM_Core_Action::ENABLE;
                    } else {
                        $action -= CRM_Core_Action::VIEW;
                        $action -= CRM_Core_Action::DISABLE;
                    }
                }
                $action = $action & CRM_Core_Action::mask( $groupPermission );
                
                $values[$object->id]['visibility'] = CRM_Contact_DAO_Group::tsEnum('visibility', $values[$object->id]['visibility']);
                if ( $values[$object->id]['group_type'] ) {
                    $groupTypes = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                           substr( $values[$object->id]['group_type'], 1, -1 ) );
                    $allTypes   = CRM_Core_OptionGroup::values( 'group_type' );
                    $types = array( );
                    foreach ( $groupTypes as $type ) {
                        $types[] = $allTypes[$type];
                    }
                    $values[$object->id]['group_type'] = implode( ', ', $types );
                }
                $values[$object->id]['action'] = CRM_Core_Action::formLink( $links,
                                                                            $action,
                                                                            array( 'id'   => $object->id,
                                                                                   'ssid' => $object->saved_search_id ) );
            }
        }
        
        $this->assign( 'rows', $values );
    }           
    
}

?>
