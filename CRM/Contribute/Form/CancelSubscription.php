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

/**
 * This class provides support for canceling recurring subscriptions
 * 
 */


class CRM_Contribute_Form_CancelSubscription extends CRM_Core_Form
{
    protected $_paymentProcessorObj = null;

    protected $_userContext = null;
    
    protected $_mid  = null;

    protected $_coid = null;

    protected $_crid = null;

    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    public function preProcess( )  
    {
        $this->_mid = CRM_Utils_Request::retrieve( 'mid', 'Integer', $this, false );
        if ( $this->_mid ) {
            if ( CRM_Member_BAO_Membership::isSubscriptionCancelled( $this->_mid ) ) {
                CRM_Core_Error::fatal( ts( 'The auto renewal option for this membership looks to have been cancelled already.' ) );
            }
            $this->_mode = 'auto_renew';
            $this->_paymentProcessorObj = 
                CRM_Core_BAO_PaymentProcessor::getProcessorForEntity( $this->_mid, 'membership', 'obj' );
            $this->_subscriptionDetails = CRM_Contribute_BAO_ContributionRecur::getSubscriptionDetails( $this->_mid, 'membership' );

            $membershipTypes  = CRM_Member_PseudoConstant::membershipType( );
            $membershipTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_Membership', $this->_mid, 'membership_type_id' );
            $this->assign( 'membershipType', CRM_Utils_Array::value( $membershipTypeId, $membershipTypes ) );
        }

        $this->_crid = CRM_Utils_Request::retrieve( 'crid', 'Integer', $this, false );
        if ( $this->_crid ) {
            $this->_paymentProcessorObj = 
                CRM_Core_BAO_PaymentProcessor::getProcessorForEntity( $this->_crid, 'recur', 'obj' );
            $this->_subscriptionDetails = CRM_Contribute_BAO_ContributionRecur::getSubscriptionDetails( $this->_crid );
        }

        $this->_coid = CRM_Utils_Request::retrieve( 'coid', 'Integer', $this, false );
        if ( $this->_coid ) {
            if ( CRM_Contribute_BAO_Contribution::isSubscriptionCancelled( $this->_coid ) ) {
                CRM_Core_Error::fatal( ts( 'The recurring contribution looks to have been cancelled already.' ) );
            }
            $this->_paymentProcessorObj = 
                CRM_Core_BAO_PaymentProcessor::getProcessorForEntity( $this->_coid, 'contribute', 'obj' );
            $this->_subscriptionDetails = CRM_Contribute_BAO_ContributionRecur::getSubscriptionDetails( $this->_coid, 'contribution' );
        }

        if ( (!$this->_crid && !$this->_coid && !$this->_mid) || 
             ($this->_subscriptionDetails == CRM_Core_DAO::$_nullObject) ) {
            CRM_Core_Error::fatal( 'Required information missing.' );
        }

        if ( !CRM_Core_Permission::check( 'edit contributions' ) ) {
            $userChecksum = CRM_Utils_Request::retrieve( 'cs', 'String', $this, false );
            if ( ! CRM_Contact_BAO_Contact_Utils::validChecksum( $this->_subscriptionDetails->contact_id, $userChecksum ) ) {
                CRM_Core_Error::fatal( ts( 'You do not have permission to cancel subscription.' ) );
            }
        } 

        // context redirection
        $cid     = CRM_Utils_Request::retrieve( 'cid', 'Integer', $this, false );
        $context = CRM_Utils_Request::retrieve( 'context', 'String', $this, false );
        $selectedChild = CRM_Utils_Request::retrieve( 'selectedChild', 'String', $this, false );
        if ( !$context ) {
            $context = CRM_Utils_Request::retrieve( 'compContext', 'String', $this, false );
        }
        $qfkey = CRM_Utils_Request::retrieve( 'key', 'String', $this, false );
        if ( $cid ) {
            $this->_userContext = CRM_Utils_System::url( 'civicrm/contact/view', 
                                                         "reset=1&force=1&selectedChild={$selectedChild}&cid={$cid}" );
        } else if ( $this->_mid ) {
            $this->_userContext = CRM_Utils_System::url( 'civicrm/member/search', 
                                                         "force=1&context={$context}&key={$qfkey}" );
            if ( $context == 'dashboard' ) {
                $this->_userContext = CRM_Utils_System::url( 'civicrm/member', 
                                                             "force=1&context={$context}&key={$qfkey}" );
            }
        }
        $session = CRM_Core_Session::singleton( ); 
        if ( $session->get( 'userID' ) ) {
            $session->pushUserContext( $this->_userContext );
        }
        CRM_Utils_System::setTitle( $this->_mid ? ts('Cancel Auto-renewal') : ts('Cancel Recurring Contribution') );
        $this->assign( 'mode', $this->_mode );

        if ( $this->_subscriptionDetails->contact_id ) {
            list($this->_donorDisplayName, $this->_donorEmail) = CRM_Contact_BAO_Contact::getContactDetails( $this->_subscriptionDetails->contact_id );
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
        if ( $this->_paymentProcessorObj->isSupported( 'cancelSubscription' ) ) {
            $searchRange = array( );
            $searchRange[] = $this->createElement( 'radio', null, null, ts( 'Yes' ), '1' );
            $searchRange[] = $this->createElement( 'radio', null, null, ts( 'No' ),  '0' );
            
            $this->addGroup( $searchRange, 'send_cancel_request', ts('Send cancellation request to %1 ?', array(1 => $this->_paymentProcessorObj->_processorName)));
        }
        if ( $this->_donorEmail ) {
            $this->add('checkbox', 'is_notify', ts('Send Notification ?'));
        }

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
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @param null
     * 
     * @return array    array of default values
     * @access public
     */
    function setDefaultValues()
    {
        $defaults = array();
        $defaults['is_notify'] = 1;
        return $defaults;
    }

    /** 
     * Function to process the form 
     * 
     * @access public 
     * @return None 
     */ 
    public function postProcess ( ) { 
        $status = $message  = null;
        $cancelSubscription = true;
        $params = $this->controller->exportValues( $this->_name );
        
        if ( $params['send_cancel_request'] == 1 ) {
            $cancelParams = array( 'subscriptionId' => $this->_subscriptionDetails->subscription_id );
            $cancelSubscription = $this->_paymentProcessorObj->cancelSubscription( $message, $cancelParams );
        }

        if ( is_a( $cancelSubscription, 'CRM_Core_Error' ) ) {
            CRM_Core_Error::displaySessionError( $cancelSubscription );
        } else if ( $cancelSubscription ) {
            $activityParams = array( 'source_contact_id' => $this->_subscriptionDetails->contact_id,
                                     'source_record_id'  => $this->_mid ? $this->_mid : $this->_coid,
                                     'subject'           => $this->_mid ? ts('Auto-renewal membership cancelled') : ts('Recurring contribution cancelled'),
                                     'details'           => $message,
                                     );
            $cancelStatus = CRM_Contribute_BAO_ContributionRecur::cancelRecurContribution( $this->_subscriptionDetails->recur_id,
                                                                                           CRM_Core_DAO::$_nullObject, $activityParams );
            if ( $cancelStatus ) {
                $tplParams = array( );
                if ( $this->_mid ) {
                    $status = ts( 'The auto-renewal option for your membership has been successfully cancelled. Your membership has not been cancelled. However you will need to arrange payment for renewal when your membership expires.' );
             
                    $inputParams = array( 'id' => $this->_mid );
                    CRM_Member_BAO_Membership::getValues( $inputParams, $tplParams );
                    $tplParams   = $tplParams[$this->_mid];
                    $tplParams['membership_status'] = 
                        CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipStatus', $tplParams['status_id'] );
                    $tplParams['membershipType']    = 
                        CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_MembershipType', $tplParams['membership_type_id'] );
                } else if ( $this->_coid ) {
                    $inputParams  = array( 'id' => $this->_subscriptionDetails->recur_id );
                    $recurDetails = array( );
                    CRM_Core_DAO::commonRetrieve('CRM_Contribute_DAO_ContributionRecur', $inputParams, $recurDetails );
                    $tplParams['recur_frequency_interval'] = $recurDetails['frequency_interval'];
                    $tplParams['recur_frequency_unit'] = $recurDetails['frequency_unit'];
                    $tplParams['amount'] = $recurDetails['amount'];
                    $tplParams['contact'] = array( 'display_name' => $this->_donorDisplayName );

                    $status = ts( 'The recurring contribution of %1, every %2 %3 has been cancelled.', 
                                  array( 1 => $recurDetails['amount'], 2 => $recurDetails['frequency_interval'], 3 => $recurDetails['frequency_unit'] ) );
                }

                if ( $this->_subscriptionDetails->contribution_page_id ) {
                    CRM_Core_DAO::commonRetrieveAll( 'CRM_Contribute_DAO_ContributionPage', 'id', 
                                                     $this->_subscriptionDetails->contribution_page_id, $value, array('title',
                                                                                                                      'receipt_from_name',
                                                                                                                      'receipt_from_email',
                                                                                                                      ) );
                    $receiptFrom = '"' . CRM_Utils_Array::value( 'receipt_from_name', $value[$this->_subscriptionDetails->contribution_page_id] ) . 
                        '" <' . $value [$this->_subscriptionDetails->contribution_page_id] ['receipt_from_email'] . '>';
                } else {
                    $domainValues = CRM_Core_BAO_Domain::getNameAndEmail();
                    $receiptFrom = "$domainValues[0] <$domainValues[1]>";
                }
                
                // send notification
                $sendTemplateParams = 
                    array(
                          'groupName' => $this->_mode == 'auto_renew' ? 'msg_tpl_workflow_membership'    : 'msg_tpl_workflow_contribution',
                          'valueName' => $this->_mode == 'auto_renew' ? 'membership_autorenew_cancelled' : 'contribution_recurring_cancelled',
                          'contactId' => $this->_subscriptionDetails->contact_id,
                          'tplParams' => $tplParams,
                          //'isTest'    => $isTest, set this from _objects
                          'PDFFilename' => 'receipt.pdf',
                          'from'      => $receiptFrom,
                          'toName'    => $this->_donorDisplayName,
                          'toEmail'   => $this->_donorEmail,
                          );
                list ( $sent ) = CRM_Core_BAO_MessageTemplates::sendTemplate( $sendTemplateParams );
            }
        } else {
            $status = ts( 'Auto renew could not be cancelled.' );
        }
        
        if ( $status ) {
            $session = CRM_Core_Session::singleton( );
            if ( $session->get( 'userID' ) ) {
                CRM_Core_Session::setStatus( $status );
            } else {
                CRM_Utils_System::setUFMessage( $message );
            }
        }
    }
}
