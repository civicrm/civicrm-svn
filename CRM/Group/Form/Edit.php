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

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/BAO/CustomGroup.php';
/**
 * This class is to build the form for adding Group
 */
class CRM_Group_Form_Edit extends CRM_Core_Form {

    /**
     * the group id, used when editing a group
     *
     * @var int
     */
    protected $_id;
 
    /**
     * The title of the group being deleted
     *
     * @var string
     */
    protected $_title;

    /**
     * Store the tree of custom data and fields
     *
     * @var array
     */
    protected $_groupTree;

    /**
     * set up variables to build the form
     *
     * @return void
     * @acess protected
     */
    function preProcess( ) {
        $this->_id    = $this->get( 'id' );
        
        if ( $this->_id ) {
            $breadCrumbPath = CRM_Utils_System::url( 'civicrm/group', 'reset=1' );
            CRM_Utils_System::appendBreadCrumb( ts('Manage Groups') , $breadCrumbPath);
        }
        
        if ($this->_action == CRM_Core_Action::DELETE) {    
            if ( isset($this->_id) ) {
                $params   = array( 'id' => $this->_id );
                CRM_Contact_BAO_Group::retrieve( $params, $defaults );
                
                $this->_title = $defaults['title'];
                $this->assign( 'name' , $this->_title );
                $this->assign( 'count', CRM_Contact_BAO_Group::memberCount( $this->_id ) );
                CRM_Utils_System::setTitle( ts('Confirm Group Delete') );
            }
        } else {
            $this->_groupTree =& CRM_Core_BAO_CustomGroup::getTree('Group',$this->_id,0);
            if ( isset($this->_id) ) {
                $params   = array( 'id' => $this->_id );
                CRM_Contact_BAO_Group::retrieve( $params, $defaults );
                $groupValues = array( 'id'              => $this->_id,
                                      'title'           => $defaults['title'],                                     
                                      'saved_search_id' => (isset($defaults['saved_search_id'])) ? $defaults['saved_search_id'] : "");
                $this->assign_by_ref( 'group', $groupValues );
                CRM_Utils_System::setTitle( ts('Group Settings: %1', array( 1 => $defaults['title'])));
            }
        }
    }
    
    /*
     * This function sets the default values for the form. LocationType that in edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return None
     */
    function setDefaultValues( ) {
        $defaults = array( );
        $params   = array( );

        if ( isset( $this->_id ) ) {
            $params = array( 'id' => $this->_id );
            CRM_Contact_BAO_Group::retrieve( $params, $defaults );

            if ( $defaults['group_type'] ) {
                $types = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                  substr( $defaults['group_type'], 1, -1 ) );
                $defaults['group_type'] = array( );
                foreach ( $types as $type ) {
                    $defaults['group_type'][$type] = 1;
                }
            }
        }

        if( isset($this->_groupTree) ) {
            CRM_Core_BAO_CustomGroup::setDefaults( $this->_groupTree, $defaults, false, false );
        }
        return $defaults;
    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        
        if ($this->_action == CRM_Core_Action::DELETE) {
            $this->addButtons( array(
                                     array ( 'type'      => 'next',
                                             'name'      => ts('Delete Group'),
                                             'isDefault' => true   ),
                                     array ( 'type'       => 'cancel',
                                             'name'      => ts('Cancel') ),
                                     )
                               );
            
        } else {

            $this->applyFilter('__ALL__', 'trim');
            $this->add('text', 'title'       , ts('Name:') . ' ' ,
                       CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_Group', 'title' ),true );
            $this->addRule( 'title', ts('Name already exists in Database.'),
                            'objectExists', array( 'CRM_Contact_DAO_Group', $this->_id, 'title' ) );
            
            $this->add('text', 'description', ts('Description:') . ' ', 
                       CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_Group', 'description' ) );

            require_once 'CRM/Core/OptionGroup.php';
            $this->addCheckBox( 'group_type',
                                ts( 'Group Type' ),
                                CRM_Core_OptionGroup::values( 'group_type', true ),
                                null, null, null, null, '&nbsp;&nbsp;&nbsp;' );

            $this->add( 'select', 'visibility', ts('Visibility'        ), CRM_Core_SelectValues::ufVisibility( ), true ); 
            
            $session = & CRM_Core_Session::singleton( );
            $uploadNames = $session->get( 'uploadNames' );
            if ( is_array( $uploadNames ) && ! empty ( $uploadNames ) ) {
                $buttonType = 'upload';
            } else {
                $buttonType = 'next';
            }
            
            
            $this->addButtons( array(
                                     array ( 'type'      => $buttonType,
                                             'name'      => ( $this->_action == CRM_Core_Action::ADD ) ? ts('Continue') : ts('Save'),
                                             'isDefault' => true   ),
                                     array ( 'type'       => 'cancel',
                                             'name'      => ts('Cancel') ),
                                     )
                               );

            
            CRM_Core_BAO_CustomGroup::buildQuickForm( $this, $this->_groupTree, 'showBlocks1', 'hideBlocks1' );
        }
    }
    /**
     * Process the form when submitted
     *
     * @return void
     * @access public
     */
    public function postProcess( ) {
        
        if ($this->_action & CRM_Core_Action::DELETE ) {
            CRM_Contact_BAO_Group::discard( $this->_id );
            CRM_Core_Session::setStatus( ts('The Group "%1" has been deleted.', array(1 => $this->_title)) );        
        } else {
            // store the submitted values in an array
            //$params = $this->exportValues();
            $params = $this->controller->exportValues( $this->_name );
            
            $params['domain_id'] = CRM_Core_Config::domainID( );
            $params['is_active'] = 1;

            if ( is_array( $params['group_type'] ) ) {
                $params['group_type'] =
                    CRM_Core_DAO::VALUE_SEPARATOR . 
                    implode( CRM_Core_DAO::VALUE_SEPARATOR,
                             array_keys( $params['group_type'] ) ) .
                    CRM_Core_DAO::VALUE_SEPARATOR;
            } else {
                $params['group_type'] = '';
            }

            if ($this->_action & CRM_Core_Action::UPDATE ) {
                $params['id'] = $this->_id;
            }

            // format custom data
            // get mime type of the uploaded file
            if ( !empty($_FILES) ) {
                foreach ( $_FILES as $key => $value) {
                    $files = array( );
                if ( $params[$key] ) {
                    $files['name'] = $params[$key];
                }
                if ( $value['type'] ) {
                    $files['type'] = $value['type']; 
                }
                $params[$key] = $files;
                }
            }
            
            $customData = array( );
            foreach ( $params as $key => $value ) {
                if ( $customFieldId = CRM_Core_BAO_CustomField::getKeyID($key) ) {
                    CRM_Core_BAO_CustomField::formatCustomField( $customFieldId, $customData,
                                                                 $value, 'Group', null, $this->_id);
                }
            }
            
            if (! empty($customData) ) {
                $params['custom'] = $customData;
            }

            //special case to handle if all checkboxes are unchecked
            $customFields = CRM_Core_BAO_CustomField::getFields( 'Group' );
            
            if ( !empty($customFields) ) {
                foreach ( $customFields as $k => $val ) {
                    if ( in_array ( $val[3], array ('CheckBox','Multi-Select') ) &&
                         ! CRM_Utils_Array::value( $k, $params['custom'] ) ) {
                        CRM_Core_BAO_CustomField::formatCustomField( $k, $params['custom'],
                                                                     '', 'Group', null, $this->_id);
                    }
                }
            }
            
            require_once 'CRM/Contact/BAO/Group.php';
            $group =& CRM_Contact_BAO_Group::create( $params );
            
            CRM_Core_Session::setStatus( ts('The Group "%1" has been saved.', array(1 => $group->title)) );        
            
            /*
             * Add context to the session, in case we are adding members to the group
             */
            if ($this->_action & CRM_Core_Action::ADD ) {
                $this->set( 'context', 'amtg' );
                $this->set( 'amtgID' , $group->id );
                
                $session =& CRM_Core_Session::singleton( );
                $session->pushUserContext( CRM_Utils_System::url( 'civicrm/group/search', 'reset=1&force=1&context=smog&gid=' . $group->id ) );
            }
        }
    }

}

?>
