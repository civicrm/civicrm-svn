<?php

 /*
  +--------------------------------------------------------------------+
  | CiviCRM version 3.3                                                |
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

 /**
  *
  */
class CRM_Mailing_Form_Approve extends CRM_Core_Form 
{

    public function redirectToListing( )
    {
        $url = CRM_Utils_System::url( 'civicrm/mailing/browse/unscheduled', 'reset=1&scheduled=false' );
        CRM_Utils_System::redirect( $url );
    }
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    public function preProcess()  
    {
        require_once 'CRM/Mailing/Info.php';
        if ( CRM_Mailing_Info::workflowEnabled( ) ) {
            if ( ! CRM_Core_Permission::check('approve mailings' ) ) {
                $this->redirectToListing( );
            }
        } else {
            $this->redirectToListing( );
        }

        $this->_mailingID = CRM_Utils_Request::retrieve( 'mid', 'Integer', $this, true );
        $this->_approve   = CRM_Utils_Request::retrieve( 'approve',
                                                         'Integer', $this, false, 1 );
        
        require_once 'CRM/Mailing/PseudoConstant.php';
        $mailApprovalStatus = CRM_Mailing_PseudoConstant::approvalStatus( );
        $approved = array_search( 'Approved', $mailApprovalStatus );
        $rejected = array_search( 'Rejected', $mailApprovalStatus );

        if ( ! ( $this->_approve == $approved || 
                 $this->_approve == $rejected ) ) {
            $this->_approve = $approved;
        } 

        if ( $this->_approve == $approved ) {
            $flipURL     = CRM_Utils_System::url( 'civicrm/mailing/approve', 
                                                  "reset=1&mid={$this->_mailingID}&approve={$rejected}" );
            $flipMessage = ts( 'Do you want to reject this message instead?' );
        } else if ( $this->_approve == $rejected ) {
            $flipURL     = CRM_Utils_System::url( 'civicrm/mailing/approve', 
                                                  "reset=1&mid={$this->_mailingID}&approve={$approved}" );
            $flipMessage = ts( 'Do you want to approve this message instead?' );
        }

        $this->assign( 'flipURL'    , $flipURL     );
        $this->assign( 'flipMessage', $flipMessage );

        $session =& CRM_Core_Session::singleton( );
        $this->_contactID = $session->get( 'userID' );
        
        require_once 'CRM/Mailing/BAO/Mailing.php';
        $this->_mailing     = new CRM_Mailing_BAO_Mailing();
        $this->_mailing->id = $this->_mailingID;
        if ( ! $this->_mailing->find( true ) ) {
            $this->redirectToListing( );
        }
    }
    
    /**
     * This function sets the default values for the form.
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        $defaults = array( );
        return $defaults;
    }

    /**
     * Build the form for the last step of the mailing wizard
     *
     * @param
     * @return void
     * @access public
     */
    public function buildQuickform() 
    {
        if ( $this->_approve == 1  ) {
            $note    = ts('Approval Note');
            $title   = ts('Approve Mailing');
            $btnName = ts('Approve');
        } else  {
            $note    = ts('Rejection Note');
            $title   = ts('Reject Mailing');
            $btnName = ts('Reject');
        }

        CRM_Utils_System::setTitle( $title );
        $this->addElement( 'textarea', 'approval_note', $note );

        $buttons = array( array( 'type'      => 'next',
                                 'name'      => $btnName,
                                 'spacing'   => '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;',
                                 'isDefault' => true),
                          array( 'type'      => 'cancel',
                                 'name'      => ts('Cancel') ),
                          );
        
        $this->addButtons( $buttons );

        // add the preview elements
        $preview = array( );

        $preview['subject'] = CRM_Core_DAO::getFieldValue( 'CRM_Mailing_DAO_Mailing',
                                                           $this->_mailingID,
                                                           'subject' );
        $preview['viewURL'] = CRM_Utils_System::url( 'civicrm/mailing/view', "reset=1&id={$this->_mailingID}" );

        require_once 'CRM/Core/BAO/File.php';
        $preview['attachment'] = CRM_Core_BAO_File::attachmentInfo( 'civicrm_mailing',
                                                                    $this->_mailingID );
        
        $this->assign_by_ref( 'preview', $preview );
    }

    /**
     * Process the posted form values.  Create and schedule a mailing.
     *
     * @param
     * @return void
     * @access public
     */
    public function postProcess() 
    {
        // get the submitted form values.  
        $params = $this->controller->exportValues( $this->_name );
        $ids    = array( );              
        if ( isset( $this->_mailingID ) ) {
            $ids['mailing_id'] = $this->_mailingID;
        } else {
            $ids['mailing_id'] = $this->get('mailing_id');
        }
        
        $params['approver_id']        = $this->_contactID;
        $params['approval_date']      = date('YmdHis');
        $params['approval_status_id'] = $this->_approve;

        CRM_Mailing_BAO_Mailing::create( $params, $ids );

        $session = CRM_Core_Session::singleton( );
        $session->pushUserContext( CRM_Utils_System::url( 'civicrm/mailing/browse/scheduled', 
                                                          'reset=1&scheduled=true' ) );
    }
    
    /**
     * Display Name of the form
     *
     * @access public
     * @return string
     */
    public function getTitle( ) 
    {
        return ts( 'Approve Mailing' );
    }

}
