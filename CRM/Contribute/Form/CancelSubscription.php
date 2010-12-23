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

class CRM_Contribute_Form_CancelSubscription extends CRM_Core_Form
{
    protected $_subscriptionId = null;

    protected $_objects = array( );

    protected $_ppID = null;

    protected $_mode = null;

    protected $_contributionRecurId = null;
    
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
        
        if ( $mid ) {
            $membershipTypes  = CRM_Member_PseudoConstant::membershipType( );
            $membershipTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_Membership', $mid, 'membership_type_id' );
            $this->assign( 'membershipType', CRM_Utils_Array::value( $membershipTypeId, $membershipTypes ) );

            require_once 'CRM/Member/BAO/Membership.php';
            $isCancelSupported = CRM_Member_BAO_Membership::isCancelSubscriptionSupported( $mid ); 
        }
        if ( $isCancelSupported ) {
            //FIXME: for offline contribution page id won't exist

            $sql = " 
    SELECT mp.contribution_id, rec.id as recur_id, rec.processor_id, mem.is_test, cp.payment_processor_id 
      FROM civicrm_membership_payment mp 
INNER JOIN civicrm_membership         mem ON ( mp.membership_id = mem.id ) 
INNER JOIN civicrm_contribution_recur rec ON ( mem.contribution_recur_id = rec.id )
INNER JOIN civicrm_contribution       con ON ( con.id = mp.contribution_id )
LEFT  JOIN civicrm_contribution_page   cp ON ( con.contribution_page_id = cp.id )
     WHERE mp.membership_id = {$mid}";
            
            $dao = CRM_Core_DAO::executeQuery( $sql );
            if ( $dao->fetch( ) ) { 
                $this->_subscriptionId      = $dao->processor_id;
                $this->_contributionRecurId = $dao->recur_id;
                $contributionId = $dao->contribution_id;
                $ppId = $dao->payment_processor_id;
            }

            if ( !$ppId && $contributionId ) {
                $sql = " 
    SELECT ft.payment_processor 
      FROM civicrm_financial_trxn ft 
INNER JOIN civicrm_entity_financial_trxn eft ON ( eft.financial_trxn_id = ft.id AND eft.entity_table = 'civicrm_contribution' ) 
     WHERE eft.entity_id = {$contributionId}";
                $ftDao = CRM_Core_DAO::executeQuery( $sql );
                $ftDao->fetch( );
                if ( $ftDao->payment_processor ) {
                    $params = array( 'payment_processor_type' => $ftDao->payment_processor,
                                     'is_test'                => $dao->is_test ? 1 : 0 );
                    require_once 'CRM/Core/BAO/PaymentProcessor.php';
                    CRM_Core_BAO_PaymentProcessor::retrieve( $params, $paymentProcessor );
                    $ppId = $paymentProcessor['id'];
                }
            }
            if ( $ppId ) {
                $this->_ppID    = $ppId;
                $this->_mode    = ( $dao->is_test ) ? 'test' : 'live';
            } else {
                CRM_Core_Error::fatal(ts('Could not figure out the Payment Processor.'));
            }

            if ( $contributionId ) {
                require_once 'CRM/Contribute/BAO/Contribution.php';
                $contribution = new CRM_Contribute_DAO_Contribution();
                $contribution->id = $contributionId;
                $contribution->find(true);
                $contribution->receive_date = CRM_Utils_Date::isoToMysql( $recur->receive_date );;

                $this->_objects['contribution'] = $contribution;
            }
            if ( !$this->_subscriptionId || empty( $this->_objects ) ) {
                CRM_Core_Error::fatal( ts( 'Invalid membership or subscription.' ) );
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
        $this->_paymentProcessor = CRM_Core_BAO_PaymentProcessor::getPayment( $this->_ppID,
                                                                              $this->_mode );

        $paymentObject =& CRM_Core_Payment::singleton( $this->_mode, $this->_paymentProcessor, $this );
        $paymentObject->_setParam( 'subscriptionId', $this->_subscriptionId );
        $cancelSubscription = $paymentObject->cancelSubscription( );

        if ( is_a( $cancelSubscription, 'CRM_Core_Error' ) ) {
            
            CRM_Core_Error::displaySessionError( $cancelSubscription );
            CRM_Utils_System::redirect( $this->_userContext );
        } else if ( $cancelSubscription ) {
            require_once 'CRM/Contribute/BAO/ContributionRecur.php';
            $cancelled = CRM_Contribute_BAO_ContributionRecur::cancelRecurContribution( $this->_contributionRecurId, 
                                                                           $this->_objects );
            CRM_Core_Session::setStatus( ts( 'Subscription is cancelled successfully.' ) );
            CRM_Utils_System::redirect( $this->_userContext );
        } else {
            CRM_Core_Session::setStatus( ts( 'Subscription could not be cancelled.' ) );
            CRM_Utils_System::redirect( $this->_userContext );
        }
    }
}
