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

/**
 * This class provides support for canceling recurring subscriptions
 * 
 */

require_once 'CRM/Core/Form.php';
require_once 'CRM/Core/Payment.php';
require_once 'CRM/Member/PseudoConstant.php';
require_once 'CRM/Core/BAO/PaymentProcessor.php';

class CRM_Contribute_Form_CancelSubscription extends CRM_Core_Form
{
    protected $_subscriptionId = null;

    protected $_objects = array( );

    protected $_contributionRecurId = null;

    protected $_paymentObject = null;

    protected $_userContext = null;
    
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    public function preProcess( )  
    {
        $mid = CRM_Utils_Request::retrieve( 'mid', 'Integer', $this, false );
        $cid = CRM_Utils_Request::retrieve( 'cid', 'Integer', $this, false );
        $context = CRM_Utils_Request::retrieve( 'context', 'String', $this, false );
        $selectedChild = CRM_Utils_Request::retrieve( 'selectedChild', 'String', $this, false );
        if ( !$context ) {
            $context = CRM_Utils_Request::retrieve( 'compContext', 'String', $this, false );
            
        }
        
        $qfkey = CRM_Utils_Request::retrieve( 'key', 'String', $this, false );
        
        if ( $cid ) {
            $this->_userContext = CRM_Utils_System::url( 'civicrm/contact/view', 
                                          "reset=1&force=1&selectedChild={$selectedChild}&cid={$cid}" );
        } else if ( $mid ) {
            $this->_userContext = CRM_Utils_System::url( 'civicrm/member/search', 
                                          "force=1&context={$context}&key={$qfkey}" );
            if ( $context == 'dashboard' ) {
                $this->_userContext = CRM_Utils_System::url( 'civicrm/member', 
                                          "force=1&context={$context}&key={$qfkey}" );
            }
        }
        
        $session = CRM_Core_Session::singleton( ); 
        $session->pushUserContext( $this->_userContext );
        
        if ( $mid ) {
            $membershipTypes  = CRM_Member_PseudoConstant::membershipType( );
            $membershipTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_Membership', $mid, 'membership_type_id' );
            $this->assign( 'membershipType', CRM_Utils_Array::value( $membershipTypeId, $membershipTypes ) );

            require_once 'CRM/Member/BAO/Membership.php';
            $isCancelSupported = CRM_Member_BAO_Membership::isCancelSubscriptionSupported( $mid );
        }
        if ( $isCancelSupported ) {
            $sql = " 
    SELECT mp.contribution_id, rec.id as recur_id, rec.processor_id 
      FROM civicrm_membership_payment mp 
INNER JOIN civicrm_membership         mem ON ( mp.membership_id = mem.id ) 
INNER JOIN civicrm_contribution_recur rec ON ( mem.contribution_recur_id = rec.id )
INNER JOIN civicrm_contribution       con ON ( con.id = mp.contribution_id )
     WHERE mp.membership_id = {$mid}";
            
            $dao = CRM_Core_DAO::executeQuery( $sql );
            if ( $dao->fetch( ) ) { 
                $this->_contributionRecurId = $dao->recur_id;
                $this->_subscriptionId      = $dao->processor_id;
                $contributionId        = $dao->contribution_id;
            }

            if ( $contributionId ) {
                require_once 'CRM/Contribute/BAO/Contribution.php';
                $contribution = new CRM_Contribute_DAO_Contribution();
                $contribution->id = $contributionId;
                $contribution->find(true);
                $contribution->receive_date = CRM_Utils_Date::isoToMysql( $recur->receive_date );
                $contribution->receipt_date = CRM_Utils_Date::isoToMysql( $recur->receipt_date );
                
                $this->_objects['contribution'] = $contribution;
                
                $this->_paymentObject = 
                    CRM_Core_BAO_PaymentProcessor::getProcessorForEntity( $mid, 'membership', 'obj' );
            }
        } else {
            CRM_Core_Error::fatal( ts( 'Could not detect payment processor OR the processor does not support cancellation of subscription.' ) );
        }
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
                                        'name'      => ts('Cancel Subscription'), 
                                        'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                        'isDefault' => true   ), 
                                array ( 'type'      => 'cancel', 
                                        'name'      => ts('Not Now') ), 
                                 ) 
                          );
    }
   
    /** 
     * Function to process the form 
     * 
     * @access public 
     * @return None 
     */ 
    public function postProcess ( ) { 
        $status = null;

        $this->_paymentObject->_setParam( 'subscriptionId', $this->_subscriptionId );
        $cancelSubscription = $this->_paymentObject->cancelSubscription( );

        if ( is_a( $cancelSubscription, 'CRM_Core_Error' ) ) {
            CRM_Core_Error::displaySessionError( $cancelSubscription );
        } else if ( $cancelSubscription ) {
            $status = ts( 'Subscription is cancelled successfully.' );
            require_once 'CRM/Contribute/BAO/ContributionRecur.php';
            $cancelled = CRM_Contribute_BAO_ContributionRecur::cancelRecurContribution( $this->_contributionRecurId, 
                                                                                        $this->_objects );
        } else {
            $status = ts( 'Subscription could not be cancelled.' );
        }
        
        if ( $status ) {
            CRM_Core_Session::setStatus( $status );
        }
    }
}
