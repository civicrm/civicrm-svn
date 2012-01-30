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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */

require_once 'CRM/Core/Page/Basic.php';

class CRM_Group_Page_Group extends CRM_Core_Page_Basic 
{
    protected $_sortByCharacter;

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
        return ts('Edit Group');
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
        $groupPermission =
            CRM_Core_Permission::check( 'edit groups' ) ? CRM_Core_Permission::EDIT : CRM_Core_Permission::VIEW;
        $this->assign( 'groupPermission', $groupPermission );
 
        $this->search( );
     
        return;

        require_once 'CRM/Contact/BAO/GroupNesting.php';
        $this->_sortByCharacter = CRM_Utils_Request::retrieve( 'sortByCharacter',
                                                               'String',
                                                               $this );
        if ( strtolower( $this->_sortByCharacter ) == 'all' || 
             ! empty( $_POST ) ) {
            $this->_sortByCharacter = '';
            $this->set( 'sortByCharacter', '' );
        }
        
        $query = " SELECT COUNT(*) FROM civicrm_group";
        $groupExists = CRM_Core_DAO::singleValueQuery( $query );
        $this->assign( 'groupExists',$groupExists );

        $this->search( );
        
        $config = CRM_Core_Config::singleton( );

        $params = array( );
        $whereClause = $this->whereClause( $params, false );
        $this->pagerAToZ( $whereClause, $params );
        
        $params      = array( );
        $whereClause = $this->whereClause( $params, true );
        $this->pager( $whereClause, $params );
        
        list( $offset, $rowCount ) = $this->_pager->getOffsetAndRowCount( );
        $select = $from = $where = "";
        if ( CRM_Core_Permission::check( 'administer Multiple Organizations' ) &&
             CRM_Core_Permission::isMultisiteEnabled( ) ) {
            $select = ", contact.display_name as orgName, contact.id as orgID";
            $from   = " LEFT JOIN civicrm_group_organization gOrg
                               ON gOrg.group_id = groups.id 
                        LEFT JOIN civicrm_contact contact
                               ON contact.id = gOrg.organization_id ";

            //get the Organization ID
            $orgID = CRM_Utils_Request::retrieve( 'oid', 'Positive', CRM_Core_DAO::$_nullObject );
            if ( $orgID ) { 
                $where = " AND gOrg.organization_id = {$orgID}";
            }
            $this->assign( 'groupOrg',true );    
        }
        $query = "
        SELECT groups.* {$select}
        FROM  civicrm_group groups 
              {$from}
        WHERE $whereClause {$where}
        ORDER BY groups.title asc
        LIMIT $offset, $rowCount";
        
        $object = CRM_Core_DAO::executeQuery( $query, $params, true, 'CRM_Contact_DAO_Group' );
       
        $groupPermission =
            CRM_Core_Permission::check( 'edit groups' ) ? CRM_Core_Permission::EDIT : CRM_Core_Permission::VIEW;
        $this->assign( 'groupPermission', $groupPermission );
        
        //FIXME CRM-4418, now we are handling delete separately
        //if we introduce 'delete for group' make sure to handle here.
        $groupPermissions = array( CRM_Core_Permission::VIEW );
        if ( CRM_Core_Permission::check( 'edit groups' ) ) {
            $groupPermissions[] = CRM_Core_Permission::EDIT;
            $groupPermissions[] = CRM_Core_Permission::DELETE;
        }
        
        require_once 'CRM/Core/OptionGroup.php';
        $links =& $this->links( );
        $allTypes = CRM_Core_OptionGroup::values( 'group_type' );
        $values   = array( );

        while ( $object->fetch( ) ) {
            $permission = $this->checkPermission( $object->id, $object->title );
            if ( $permission ) {
                $newLinks = $links;
                $values[$object->id] = array( );
                CRM_Core_DAO::storeValues( $object, $values[$object->id]);
                if ( $object->saved_search_id ) {
                    $values[$object->id]['title'] .= ' (' . ts('Smart Group') . ')';
                    // check if custom search, if so fix view link
                    $customSearchID = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_SavedSearch',
                                                                   $object->saved_search_id,
                                                                   'search_custom_id' );
                    if ( $customSearchID ) {
                        $newLinks[CRM_Core_Action::VIEW]['url'] = 'civicrm/contact/search/custom';
                        $newLinks[CRM_Core_Action::VIEW]['qs' ] = "reset=1&force=1&ssID={$object->saved_search_id}";
                    }
                }
                $action = array_sum(array_keys($newLinks));
                if ( array_key_exists( 'is_active', $object ) ) {
                    if ( $object->is_active ) {
                        $action -= CRM_Core_Action::ENABLE;
                    } else {
                        $action -= CRM_Core_Action::VIEW;
                        $action -= CRM_Core_Action::DISABLE;
                    }
                }
                                
                $action = $action & CRM_Core_Action::mask( $groupPermissions );
                
                $values[$object->id]['visibility'] = CRM_Contact_DAO_Group::tsEnum('visibility',
                                                                                   $values[$object->id]['visibility']);
                if ( isset( $values[$object->id]['group_type'] ) ) {
                    $groupTypes = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                           substr( $values[$object->id]['group_type'], 1, -1 ) );
                    $types = array( );
                    foreach ( $groupTypes as $type ) {
                        $types[] = CRM_Utils_Array::value( $type, $allTypes );
                    }
                    $values[$object->id]['group_type'] = implode( ', ', $types );
                }
                $values[$object->id]['action'] = CRM_Core_Action::formLink( $newLinks,
                                                                            $action,
                                                                            array( 'id'   => $object->id,
                                                                                   'ssid' => $object->saved_search_id ) );
                if ( array_key_exists( 'orgName', $object ) ) {
                    if ( $object->orgName ) {
                        $values[$object->id]['org_name'] = $object->orgName;
                        $values[$object->id]['org_id']   = $object->orgID;
                    }   
                }
            }
        }

        if ( isset( $values ) ) {
            $this->assign( 'rows', $values );
        }
    }
    
    function search( ) {
        if ( $this->_action &
             ( CRM_Core_Action::ADD    |
               CRM_Core_Action::UPDATE |
               CRM_Core_Action::DELETE ) ) {
            return;
        }

        $form = new CRM_Core_Controller_Simple( 'CRM_Group_Form_Search', ts( 'Search Groups' ), CRM_Core_Action::ADD );
        $form->setEmbedded( true );
        $form->setParent( $this );
        $form->process( );
        $form->run( );
    }
}
