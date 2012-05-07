<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * This class generates form components generic to recurring contributions
 * 
 * It delegates the work to lower level subclasses and integrates the changes
 * back in. It also uses a lot of functionality with the CRM API's, so any change
 * made here could potentially affect the API etc. Be careful, be aware, use unit tests.
 *
 */
class CRM_Contribute_Form_UpdateSubscription extends CRM_Core_Form
{
    /**
     * The recurring contribution id, used when editing the recurring contribution
     *
     * @var int
     */
    protected $_crid = null;

    protected $_coid = null;

    protected $_subscriptionDetails = null;

    public $_paymentProcessor = null;

    public $_paymentProcessorObj = null;
    /**
     * the id of the contact associated with this recurring contribution
     *
     * @var int
     * @public
     */
    public $_contactID;

    function preProcess( ) {
        
        $this->_crid = CRM_Utils_Request::retrieve( 'crid', 'Integer', $this, false );
        if ( $this->_crid ) {
            $this->_paymentProcessor =
                CRM_Core_BAO_PaymentProcessor::getProcessorForEntity( $this->_crid, 'recur', 'info' );
            $this->_paymentProcessorObj =
                CRM_Core_BAO_PaymentProcessor::getProcessorForEntity( $this->_crid, 'recur', 'obj' );
            $this->_subscriptionDetails = CRM_Contribute_BAO_ContributionRecur::getSubscriptionDetails( $this->_crid );
        }

        $this->_coid = CRM_Utils_Request::retrieve( 'coid', 'Integer', $this, false );
        if ( $this->_coid ) {
            $this->_paymentProcessor =
                CRM_Core_BAO_PaymentProcessor::getProcessorForEntity( $this->_coid, 'contribute', 'info' );
            $this->_paymentProcessorObj =
                CRM_Core_BAO_PaymentProcessor::getProcessorForEntity( $this->_coid, 'contribute', 'obj' );
            $this->_subscriptionDetails = CRM_Contribute_BAO_ContributionRecur::getSubscriptionDetails( $this->_coid, 'contribution' );
        }

        if ( ( !$this->_crid && !$this->_coid ) ||
             ( $this->_subscriptionDetails == CRM_Core_DAO::$_nullObject ) ) {
            CRM_Core_Error::fatal( 'Required information missing.' );
        }

        if ( $this->_subscriptionDetails->membership_id && $this->_subscriptionDetails->auto_renew ) {
            CRM_Core_Error::fatal( ts( 'You cannot update the subscription.' ) ); 
        }
        
        if ( ! CRM_Core_Permission::check( 'edit contributions' ) ) {
            if ( ! CRM_Contact_BAO_Contact_Utils::validChecksum( $this->_subscriptionDetails->contact_id, $userChecksum ) ) {
                CRM_Core_Error::fatal( ts( 'You do not have permission to update subscription.' ) );
            }
        }
        
        if ( !$this->_paymentProcessorObj->isSupported( 'changeSubscriptionAmount' ) ) {
            $message = "<span class='font-red'>" . ts( 'WARNING: Updates made using this form will change the recurring contribution information stored in your CiviCRM database, but will NOT be sent to the payment processor. You must enter the same changes using the payment processor web site.',
                                                       array( 1 => $this->_paymentProcessorObj->_processorName ) ) . '</span>';
            CRM_Core_Session::setStatus( $message );
            
        }
        
        $this->assign( 'paymentProcessor', $this->_paymentProcessor );
        
        $this->_contactID = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );
    }
    
    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) {

        $this->_defaults = array();
        $this->_defaults['amount'] = $this->_subscriptionDetails->amount;
        $this->_defaults['installments'] =$this->_subscriptionDetails->installments;
        $this->_defaults['is_notify'] = 1;
                
        return $this->_defaults;
    }

    /**
     * Function to actually build the components of the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        // define the fields
        $this->addMoney( 'amount', ts('Recurring Contribution Amount'), true,
                         array( 'size' => 20 ), true,
                         'currency', null, true );
        
        $this->add('text', 'installments' , ts('Number of Installments') , array('size' => 20), true);
        
        $this->addElement('checkbox', 'is_notify', ts( 'Send Notification' ) , null);
        
        $type = 'submit';
        if ( $this->_crid ) {
            $type = 'next';
        }

        // define the buttons
        $this->addButtons( array(
                                 array ( 'type'      => $type,
                                         'name'      => ts('Save'),
                                         'isDefault' => true   ),
                                 array ( 'type'       => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        
    }

    /**
     * This function is called after the user submits the form
     * 
     * @access public
     * @return None
     */
    public function postProcess( )
    {
        // store the submitted values in an array
        $processorParams = $params = $this->exportValues();

        // if this is an update of an existing recurring contribution, pass the ID
        $params['id'] = $this->_subscriptionDetails->recur_id;
                
        // save the changes
        CRM_Contribute_BAO_ContributionRecur::add( $params );

        $params['subscriptionId'] = $this->_subscriptionDetails->subscription_id;
        $updateSubscription = $this->_paymentProcessorObj->changeSubscriptionAmount( $message, $params );

        if ( is_a( $updateSubscription, 'CRM_Core_Error' ) ) {
            CRM_Core_Error::displaySessionError( $updateSubscription ); 
        } else if ( $updateSubscription ) {
            $status  = ts( 'Recurring contribution details has been updated for the subscription.' );
            $contactID = $this->_subscriptionDetails->contact_id;
            
            $activityParams =
                array( 'source_contact_id' => $contactID, 
                       'activity_type_id'  => CRM_Core_OptionGroup::getValue( 'activity_type',
                                                                              'Update Recurring Contribution',
                                                                              'name' ),
                       'subject'            => ts('Recurring Contribution Updated'),
                       'details'            => $message,
                       'activity_date_time' => date('Ymd'),
                       'status_id'          => CRM_Core_OptionGroup::getValue( 'activity_status',
                                                                               'Completed',
                                                                               'name' ),
                   );
            $session = CRM_Core_Session::singleton();
            $cid     = $session->get('userID');
            if ( $cid ) {
                $activityParams['source_contact_id']   = $cid;
                $activityParams['target_contact_id'][] = $activityParams['source_contact_id'];
            }
            CRM_Activity_BAO_Activity::create( $activityParams );
            
            if ( CRM_Utils_Array::value( 'is_notify', $params ) ) {
                $domainValues = CRM_Core_BAO_Domain::getNameAndEmail();
                $receiptFrom  = "$domainValues[0] <$domainValues[1]>";
                
                list($donorDisplayName, $donorEmail) = CRM_Contact_BAO_Contact::getContactDetails( $contactID );
                $tplParams = array( 'recur_frequency_interval' => $this->_subscriptionDetails->frequency_interval,
                                    'recur_frequency_unit'     => $this->_subscriptionDetails->frequency_unit,
                                    'amount'                   => CRM_Utils_Money::format( $params['amount'] ),
                                    'installments'             => $params['installments'] );
                
                $tplParams['contact'] = array( 'display_name' => $donorDisplayName );
                
                $receiptFrom = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage',
                                                            $this->_subscriptionDetails->contribution_page_id, 'receipt_from_email' );
                
                $tplParams['receipt_from_email'] = $receiptFrom; 
                $sendTemplateParams =
                    array(
                          'groupName' => 'msg_tpl_workflow_contribution',
                          'valueName' => 'contribution_recurring_edit',
                          'contactId' => $contactID,
                          'tplParams' => $tplParams,
                          // 'isTest'    => $this->_subscriptionDetails->is_test,
                          'PDFFilename' => 'receipt.pdf',
                          'from'      => $receiptFrom,
                          'toName'    => $donorDisplayName,
                          'toEmail'   => $donorEmail,
                          );
                list ( $sent ) = CRM_Core_BAO_MessageTemplates::sendTemplate( $sendTemplateParams );
            }                                                                                                      
            //   CRM_Core_Session::setStatus( ts('Your recurring contribution has been saved.') );
        }
        
        if ( $status ) {
            $session = CRM_Core_Session::singleton( );
            if ( $session->get( 'userID' ) ) {
                CRM_Core_Session::setStatus( $status );
            } else {
                CRM_Utils_System::setUFMessage( $status );
            }
        }
        
    }//end of function
}

