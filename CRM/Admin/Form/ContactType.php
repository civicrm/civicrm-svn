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
        $this->add( 'select', 'parent_id', ts('Basic Contact Type'),
                    array('1'=>'Individual','2'=>'Household','3'=>'Organization'),'required');
        $this->add('text', 'description', ts('Description'),   
                   CRM_Core_DAO::getAttribute( 'CRM_Contact_DAO_ContactType', 'description' ) );
        $this->add('checkbox', 'is_active', ts('Enabled?'));
    }
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess( ) {
        if( $this->_action & CRM_Core_Action::DELETE ) {
            CRM_Contact_BAO_ContactType::del( $this->_id );
            CRM_Core_Session::setStatus( ts('Selected Contact type has been deleted.') );
            return;
        }
        // store the submitted values in an array
        $params = $this->exportValues();
        if ($this->_action & CRM_Core_Action::UPDATE ) {
            $params['id'] = $this->_id;
        }  
        if ( $this->_action & CRM_Core_Action::ADD ){
            $params['name'] = ucfirst($params['label']);
        }
        $ContactType = CRM_Contact_BAO_ContactType::add( $params );
        CRM_Core_Session::setStatus( ts('The Contact type \'%1\' has been saved.',
                                        array( 1 => $ContactType->label )) );
    }     
}


