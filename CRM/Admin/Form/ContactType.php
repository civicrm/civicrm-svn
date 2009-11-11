<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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

require_once 'CRM/Admin/Form.php';

/**
 * This class generates form components for ContactSub Type
 * 
 */
class CRM_Admin_Form_ContactType extends CRM_Admin_Form
{
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        parent::buildQuickForm( );
        if ($this->_action & CRM_Core_Action::DELETE ) { 
            return;
        }
        $this->applyFilter('__ALL__', 'trim');
        $this->add('text', 'label', ts('Name'), 
                   CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_ContactType', 'label' ),
                   true );
        $this->addRule( 'label',
                        ts('This contact type name already exists in database. Contact type names must be unique.'),
                        'objectExists',
                        array( 'CRM_Contact_DAO_ContactType', $this->_id ) );
        $contactType = $this->add( 'select', 'parent_id', ts('Basic Contact Type'),
                                   CRM_Contact_BAO_ContactType::basicTypePairs( false, null, 'id' ) );
        if ($this->_action & CRM_Core_Action::UPDATE ) {
            $contactType->freeze( );
            // We'll display actual "name" for built-in types (for reference) when editing their label / image_URL
            $contactTypeName = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_ContactType', $this->_id, 'name');
            $this->assign('contactTypeName', ts($contactTypeName));            
        }
        $this->addElement('text','image_URL', ts('Image URL'));  
        $this->add('text', 'description', ts('Description'), 
                   CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_ContactType', 'description' ) );
        $this->add('checkbox', 'is_active', ts('Enabled?'));
        $this->assign('id', $this->_id );
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess( ) {
        if( $this->_action & CRM_Core_Action::DELETE ) { 
            $isDelete = CRM_Contact_BAO_ContactType::del( $this->_id );
            if ( $isDelete ) {
                CRM_Core_Session::setStatus( ts('Selected contact type has been deleted.') );
            } else {
                CRM_Core_Session::setStatus( ts( 'Selected contact type can not be deleted. Make sure contact type doesn\'t have any associated custom data or group.' ) );
            }
            return;
        }
        // store the submitted values in an array
        $params = $this->exportValues();
        if ($this->_action & CRM_Core_Action::UPDATE ) {
            $params['id'] = $this->_id;
        }  
        if ( $this->_action & CRM_Core_Action::ADD ){
            $params['name'] = ucfirst( CRM_Utils_String::munge($params['label'] ) );
        }
        $contactType = CRM_Contact_BAO_ContactType::add( $params );
        CRM_Core_Session::setStatus( ts("The Contact Type '%1' has been saved.",
                                        array( 1 => $contactType->label )) );
    }     
}


