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

require_once 'CRM/Core/Form.php';

/**
 * This class generates form components for processing a Grant
 * 
 */
class CRM_Grant_Form_Payment_View extends CRM_Core_Form
{

    /**  
     * Function to set variables up before form is built  
     *                                                            
     * @return void  
     * @access public  
     */
    public function preProcess( ) 
    {
        $this->_id        = CRM_Utils_Request::retrieve( 'id', 'Positive', $this );
        $context          = CRM_Utils_Request::retrieve( 'context', 'String', $this );
        $this->assign( 'context', $context );
        $values = array( ); 
        $params['id'] = $this->_id;
        CRM_Grant_BAO_GrantPayment::retrieve( $params, $values);
        $paymentStatus = CRM_Core_OptionGroup::values( 'grant_payment_status' );
        $contributionTypes = CRM_Contribute_PseudoConstant::financialType();
        $this->assign('payment_status_id',  $paymentStatus[$values['payment_status_id']] );
        $this->assign('financial_type_id',  $contributionTypes[$values['financial_type_id']] );
 
        $grantTokens = array( 'payment_batch_number','payment_number',
                              'payment_created_date','payment_date', 'payable_to_name', 
                              'payable_to_address', 'amount', 'currency', 'payment_reason', 'replaces_payment_id' );

        foreach ( $grantTokens as $token ) {
            $this->assign( $token, CRM_Utils_Array::value( $token, $values ) );
        }

        $this->assign( 'id', $this->_id );
    }
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) 
    {
        if (  $this->_action & CRM_Core_Action::VIEW ) {
 
            $this->addButtons(array(  
                                    array ( 'type'      => 'cancel',  
                                            'name'      => ts('Done'),  
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  
                                            'isDefault' => true   )
                                      )
                              );     
        } elseif (  ( $this->_action & CRM_Core_Action::STOP ) || (  $this->_action &  CRM_Core_Action::REPRINT  ) || (  $this->_action &  CRM_Core_Action::WITHDRAW  ) ) { 
            $this->addButtons(array( 
                                    array ( 'type'      => 'submit',  
                                            'name'      => ts('OK'),  
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  
                                            'isDefault' => true   ), 
                                    array ( 'type'      => 'cancel',  
                                            'name'      => ts('Cancel'),  
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',  
                                            'isDefault' => true   )
                                     )
                              );
        }
    }

    public function postProcess( ) {
        // $session = CRM_Core_Session::singleton();
        // $url = CRM_Utils_System::url('civicrm/grant/payment', 'reset=1&action=reprint&id='.$this->_id.'&context=search&key='.$_POST['qfKey'] );
        // $session->pushUserContext( $url );

        CRM_Utils_System::flushCache( 'CRM_Grant_DAO_GrantPayment' );
        if ( $this->_action & CRM_Core_Action::STOP ) {
            $domainID = CRM_Core_Config::domainID( );
            $dao = new CRM_Grant_DAO_GrantPayment( );
            $dao->id                    = $this->_id;
            $dao->domain_id             = $domainID;
            $dao->payment_status_id     = 2;
            $dao->save( );
            CRM_Core_Session::setStatus( ts( 'Selected Grant Payment has been stopped successfully.' ) );
            CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/grant/payment/search', 'reset=1&force=1' ) );
        } elseif (  $this->_action & CRM_Core_Action::REPRINT  ) {
            CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/grant/payment/reprint', 'reset=1&prid='.$this->_id.'&qfKey='.$_POST['qfKey'] ) );
            
        } elseif (  $this->_action & CRM_Core_Action::WITHDRAW  ) {
            $domainID = CRM_Core_Config::domainID( );
            $dao = new CRM_Grant_DAO_GrantPayment( );
            $dao->id                    = $this->_id;
            $dao->domain_id             = $domainID;
            $dao->payment_status_id     = 4;
            $dao->save( );
            CRM_Core_Session::setStatus( ts( 'Selected Grant Payment has been withdraw successfully.' ) );
            CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/grant/payment/search', 'reset=1&force=1' ) );
        }
    }  
}