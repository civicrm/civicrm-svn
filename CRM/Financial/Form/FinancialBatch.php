<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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

require_once 'CRM/Contribute/Form.php';
require_once 'CRM/Core/PseudoConstant.php';
require_once 'CRM/Contribute/PseudoConstant.php';
/**
 * This class generates form components for Contribution Batch
 * 
 */
class CRM_Financial_Form_FinancialBatch extends CRM_Contribute_Form
{
    
    
    /**
     * The financial batch id, used when editing the field
     *
     * @var int
     * @access protected
     */
    protected $_id;

    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */

    public function preProcess(){ 
        $this->_id = CRM_Utils_Request::retrieve( 'id', 'Positive', $this );
        parent::preProcess( );
    }




    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ){
         parent::buildQuickForm( );
         //if ( isset( $this->_id ) ) {
             // require_once 'CRM/Financial/BAO/FinancialTypeAccount.php';
             //$params = array( 'id' => $this->_id );
            // CRM_Financial_BAO_FinancialTypeAccount::retrieve( $params, $defaults );
            // $this->setDefaults( $defaults );
            //$financialAccountTitle = CRM_Core_DAO::getFieldValue(
            // 'CRM_Financial_DAO_FinancialAccount',
            // $defaults['financial_account_id'], 'name' );
            
              if ( isset( $this->_id ) ) {
              require_once 'CRM/Financial/BAO/FinancialBatch.php';
            $params = array( 'id' => $this->_id );
            $this->_title = CRM_Core_DAO::getFieldValue( 'CRM_Financial_DAO_FinancialBatch', $this->_id, 'name' );
            CRM_Utils_System::setTitle( $this->_title .' - '.ts( 'Financial Batch' ) );
            $this->assign( 'batchTitle', $this->_title );
        }

              //}
         
        $this->applyFilter('__ALL__', 'trim');
        
        $this->addButtons( array(
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Save'),
                                         'isDefault' => true ),
                                 array ( 'type'      => 'next',
                                         'name'      => ts('Save and New'),
                                         'subName'   => 'new'),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
       
        
        if ( $this->_action & CRM_Core_Action::UPDATE ){
            
            $element = $this->add( 'select', 
                                   'batch_type_id', 
                                   ts( 'Batch Type' ), 
                                   array( ''=> ts( '- Select Batch Type -' ) ) + CRM_Contribute_PseudoConstant::accountOptionValues( 'batch_type' ) );
            
            $element->freeze();
            
            $element = $this->add('text', 'close_date', ts('Closed Date'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'close_date' ) );
            
            $element->freeze();
            
            $element = $this->add('text', 'open_date', ts('Open Date'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'open_date' ) );
            
            $this->add( 'select', 
                        'batch_status_id', 
                        ts( 'Batch Status' ), 
                        array( ''=> ts( '- Select Batch Status -' ) ) + CRM_Contribute_PseudoConstant::accountOptionValues( 'batch_status' ),
                        true );
            
            
            
            $element->freeze( );
        }
        
        
        
        $this->add('text', 'name', ts('Batch Name'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'name' ), true );
        
        $this->add('text', 'description', ts('Description'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'description' ), true );

        $this->add( 'select', 
                    'payment_instrument_id', 
                    ts( 'Payment Instrument' ), 
                    array( ''=> ts( '- Select Payment Instrument -' ) ) + CRM_Contribute_PseudoConstant::paymentInstrument( ),
                    false );
        
        $this->add('text', 'manual_total', ts('Entered total'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'manual_total' ) );
        
        $this->add('text', 'manual_number_trans', ts('Entered transactions'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'manual_number_trans' ) );
         
         
         //$this->addFormRule( array( 'CRM_Financial_Form_FinancialBatch', 'formRule'), $this );
         
    } 
    

    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $values, $files, $self ){
        
    } 
    
      
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess(){
        require_once 'CRM/Financial/BAO/FinancialBatch.php';
       
  
        $batchStatus = CRM_Core_PseudoConstant::accountOptionValues( 'batch_status' );
            $params = $ids = array( );
            // store the submitted values in an array
            $params = $this->exportValues();
            if( $this->_action & CRM_Core_Action::ADD ){
                $batchType = CRM_Core_PseudoConstant::accountOptionValues( 'batch_type' );
                $params['batch_type_id'] = CRM_Utils_Array::key( 'Manual batch', $batchType );  
                $params['batch_status_id'] = CRM_Utils_Array::key( 'Open', $batchStatus );
            }
            if( !CRM_Utils_Array::value( 'contact_id', $params ) ){
                $session = CRM_Core_Session::singleton( );
                $params['contact_id'] = $session->get( 'userID' );

            }
            
            if ($this->_action & CRM_Core_Action::UPDATE) {
                $ids['financialBatch'] = $this->_id;
                if( CRM_Utils_Array::value( $params['batch_status_id'], $batchStatus ) == 'Closed' && ! CRM_Utils_Array::value( 'close_date', $params ) ){
                    $params['close_date'] = date('YmdHis');
                }
                if( CRM_Utils_Array::value( $params['batch_status_id'], $batchStatus ) == 'Open' && CRM_Utils_Array::value( 'close_date', $params ) ){
                    $params['close_date'] = ''; 
                }
            }
            
            $financialBatch = CRM_Financial_BAO_FinancialBatch::add($params, $ids);
            $buttonName = $this->controller->getButtonName( );
                   
            $session = CRM_Core_Session::singleton( );
            if ( $buttonName == $this->getButtonName( 'next', 'new' ) ) {
                CRM_Core_Session::setStatus( ts(' You can add another Financial Account Type.') );
                $session->replaceUserContext( CRM_Utils_System::url( 'civicrm/financial/batch', 
                                                                     "reset=1&action=add" ) );
            } else {
                $session->replaceUserContext( CRM_Utils_System::url( 'civicrm/financial/batch', 
                                                                     "reset=1&action=update&id={$financialBatch->id}" ) );
            }     
    } 
    
}

 
