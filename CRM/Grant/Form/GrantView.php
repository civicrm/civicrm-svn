<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2008                                |
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
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for processing a Grant
 * 
 */
class CRM_Grant_Form_GrantView extends CRM_Core_Form
{

    /**  
     * Function to set variables up before form is built  
     *                                                            
     * @return void  
     * @access public  
     */
    public function preProcess( ) 
    {
        $this->_contactID = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );
        $this->_id        = CRM_Utils_Request::retrieve( 'id', 'Positive', $this );
        $values = array( ); 
        $ids    = array( ); 
        $params['id'] =  $this->_id;
        require_once 'CRM/Grant/BAO/Grant.php';
        CRM_Grant_BAO_Grant::retrieve( $params, $values);
        require_once 'CRM/Grant/PseudoConstant.php';
        $grantType   = CRM_Grant_PseudoConstant::grantType( );
        $grantStatus = CRM_Grant_PseudoConstant::grantStatus( );
        $this->assign('grantType',  $grantType[$values['grant_type_id']] );
        $this->assign('grantStatus',$grantStatus[$values['status_id']] );
        $allGrant = array( 'amount_total','amount_requested','amount_granted',
                           'rationale','grant_report_received', 'application_received_date', 
                           'decision_date', 'money_transfer_date', 'grant_due_date' );
        foreach ( $allGrant as $grants ) {
            $this->assign( $grants, $values[$grants] );
            }
        if ( $this->_id) {
            require_once 'CRM/Core/BAO/Note.php';
            $noteDAO               = & new CRM_Core_BAO_Note();
            $noteDAO->entity_table = 'civicrm_grant';
            $noteDAO->entity_id    = $this->_id;
            if ( $noteDAO->find(true) ) {
                $this->_noteId = $noteDAO->id;
            }
        }

        if ( $this->_noteId ) {
            $this->assign( 'note', CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Note', $this->_noteId, 'note' ) );
        }
        
        $this->_groupTree =& CRM_Core_BAO_CustomGroup::getTree( "Grant", $this->_id, 0 );
        CRM_Core_BAO_CustomGroup::buildViewHTML( $this, $this->_groupTree );
             
    }


    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        $this->addButtons(array(  
                                array ( 'type'      => 'next',  
                                        'name'      => ts('Done'),  
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  
                                        'isDefault' => true   )
                                )
                          );
    }


}