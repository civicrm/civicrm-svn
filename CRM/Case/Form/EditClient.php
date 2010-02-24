<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';
require_once 'CRM/Case/BAO/Case.php';

/**
 * This class assigns the current case to another client
 * 
 */
class CRM_Case_Form_EditClient extends CRM_Core_Form
{
    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    public function preProcess( ) 
    {
        $this->_contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );
        $this->_caseId    = CRM_Utils_Request::retrieve( 'id', 'Positive', $this );
    }
    
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->add( 'text', 'change_client_id', ts( 'Assign to another Client' ) );
        $this->add( 'hidden', 'contact_id', '', array( 'id' => 'contact_id') );
        $this->addElement( 'submit', 
                           $this->getButtonName( 'next', 'edit_client' ), 
                           ts('Assign'), 
                           array( 'class'   => 'form-submit-inline',
                                  'onclick' => "return checkSelection( this );") );

        $this->assign( 'contactId', $this->_contactId );
    }
    
    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcess()
    {
        $params = $this->controller->exportValues( $this->_name );
        
        //assign case to another client.
        $mainCaseId = CRM_Case_BAO_Case::mergeCases( $params['contact_id'], $this->_caseId, $this->_contactId, null, true );
                
        // user context
        $url = CRM_Utils_System::url( 'civicrm/contact/view/case',
                                      "reset=1&action=view&cid={$params['contact_id']}&id={$mainCaseId[0]}&show=1" );
        $session = CRM_Core_Session::singleton( ); 
        $session->pushUserContext( $url );
    }
    
}