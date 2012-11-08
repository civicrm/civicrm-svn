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
        $session = CRM_Core_Session::singleton( );
        if( $this->_id ){
            $createdID = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Batch', $this->_id, 'created_id' );
            if ( ( CRM_Core_Permission::check( 'edit own manual batches' )  || CRM_Core_Permission::check( 'edit all manual batches' ) ) ){
                if ( CRM_Core_Permission::check( 'edit own manual batches' ) && $session->get( 'userID' ) != $createdID && !CRM_Core_Permission::check( 'edit all manual batches' )){
                    CRM_Core_Error::statusBounce( ts('You dont have permission to edit this batch') );
    }

            $status = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Batch', $this->_id, 'status_id' );            
            $batchStatus = CRM_Core_PseudoConstant::accountOptionValues( 'batch_status' );
            if( CRM_Utils_Array::value( $status, $batchStatus  ) != 'Open' ){
               CRM_Core_Error::statusBounce( ts("You cannot edit {$batchStatus[$status]} Batch") ); 
            }
            else{
                CRM_Core_Error::statusBounce( ts('You dont have permission to edit this batch') );
            }
        }
    }
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
            $params = array( 'batch_id' => $this->_id );
            CRM_Financial_BAO_FinancialBatch::retrieve( $params, $defaults );
            $this->_title = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_Batch', $this->_id, 'name' );
            // if( $contactId = CRM_Utils_Array::value( 'created_id', $defaults ) )
            //     $defaults['contact_name'] = CRM_Core_DAO::getFieldValue( 'CRM_Contact_DAO_Contact', $contactId, 'sort_name' );
            CRM_Utils_System::setTitle( $this->_title .' - '.ts( 'Financial Batch' ) );
            $this->assign( 'batchTitle', $this->_title );
            $this->setDefaults( $defaults );
        }
        if ( $this->_action & ( CRM_Core_Action::CLOSE | CRM_Core_Action::REOPEN  | CRM_Core_Action::EXPORT ) ){ 
            if( $this->_action &  CRM_Core_Action::CLOSE )
                $buttonName = 'Close Batch';
            elseif( $this->_action &  CRM_Core_Action::REOPEN )
                $buttonName = 'ReOpen Batch';
            elseif( $this->_action &  CRM_Core_Action::EXPORT )
                $buttonName = 'Export Batch';
            $this->addButtons(array( 
                                    array ( 'type'      => 'next', 
                                            'name'      => ts($buttonName), 
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                    array ( 'type'      => 'cancel', 
                                            'name'      => ts('Cancel') ), 
                                     ) 
                              );
            $this->assign( 'actionName', $buttonName );
            return;
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
       
        
            
        if ( $this->_action & CRM_Core_Action::UPDATE && $this->_id ){
            
            $element = $this->add( 'select', 
                                   'type_id', 
                                   ts( 'Batch Type' ), 
                                   array( ''=> ts( '- Select Batch Type -' ) ) + CRM_Core_PseudoConstant::accountOptionValues( 'batch_type' ) );
            
            $element->freeze();
            if($flag = CRM_Core_Permission::check( 'edit all manual batches' )){
            
            
                $dataURL = CRM_Utils_System::url( 'civicrm/ajax/getContactList', 'json=1&users=1', false, null, false );
                $this->assign('dataURL', $dataURL );
                $this->add('text', 'contact_name', ts('Created By'), CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_Batch', 'created_id' ) );
                $this->add('hidden', 'created_id', '', array( 'id' => 'created_id') );

            }
            $element = $this->add('text', 'modified_date', ts('Modified Date'), CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_Batch', 'modified_date' ) );
            $element->freeze();
            $this->add('text', 'created_date', ts('Opened Date'), CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_Batch', 'created_date' ) );
            
            $this->add( 'select', 
                        'status_id', 
                        ts( 'Batch Status' ), 
                        array( ''=> ts( '- Select Batch Status -' ) ) + CRM_Core_PseudoConstant::accountOptionValues( 'batch_status' , null , " AND v.label != 'Exported' " ),
                        true );
            
        }
        
        
        
        $this->add('text', 'name', ts('Batch Name'), CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_Batch', 'name' ), true );
        
        $this->add('text', 'description', ts('Description'), CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_Batch', 'description'));

        $this->add( 'select', 
                    'payment_instrument_id', 
                    ts( 'Payment Instrument' ), 
                    array( ''=> ts( '- Select Payment Instrument -' ) ) + CRM_Contribute_PseudoConstant::paymentInstrument( ),
                    false );
        
        $this->add('text', 'manual_total', ts('Total Amount'), CRM_Core_DAO::getAttribute('CRM_Financial_DAO_FinancialBatch', 'manual_total'), true);
        
        $this->add('text', 'manual_number_trans', ts('Number of Transactions'), CRM_Core_DAO::getAttribute( 'CRM_Financial_DAO_FinancialBatch', 'manual_number_trans' ), true);
         
         
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
        require_once 'CRM/Core/BAO/Batch.php';
        require_once 'CRM/Utils/Date.php';
        $session = CRM_Core_Session::singleton( );
        $params = $ids = array( );
        $params = $this->exportValues();
        $batchStatus = CRM_Core_PseudoConstant::accountOptionValues( 'batch_status' );
        if($this->_id){
            $ids['batchID'] = $this->_id;
        }
        // if( $this->_action & CRM_Core_Action::CLOSE ){
        //     $params['batch_status_id'] = CRM_Utils_Array::key( 'Closed', $batchStatus );
        // }
        if( $this->_action & CRM_Core_Action::EXPORT ){
            $params['status_id'] = CRM_Utils_Array::key( 'Exported', $batchStatus );
        }
        if( $this->_action & CRM_Core_Action::REOPEN ){
            $params['batch_status_id'] = CRM_Utils_Array::key( 'Open', $batchStatus );
        }
            // store the submitted values in an array
        $params['modified_date'] = date('YmdHis');
        $params['modified_id'] = $session->get( 'userID' );
        if ( $this->_action & CRM_Core_Action::EXPORT ) {
            CRM_Core_BAO_Batch::exportBatch( $ids, $params);
            
        } 
        if( CRM_Utils_Array::value( 'created_date', $params ) )
            $params['created_date'] = CRM_Utils_Date::processDate( $params['created_date'] );
        CRM_Core_Error::debug( '$paramssfsfs', $params );
            if( $this->_action & CRM_Core_Action::ADD ){
                $batchType = CRM_Core_PseudoConstant::accountOptionValues( 'batch_type' );
            $params['type_id'] = CRM_Utils_Array::key( 'Manual batch', $batchType );  
            $params['status_id'] = CRM_Utils_Array::key( 'Open', $batchStatus );
            $params['title'] = CRM_Utils_Array::value( 'name', $params );
            $params['created_date'] = date('YmdHis');
            $params['created_id'] = $session->get( 'userID' ); 
            }

        if ( $this->_action & CRM_Core_Action::UPDATE && $this->_id ) {
            
            $params['title'] = CRM_Utils_Array::value( 'name', $params );
            $details = "{$params['name']} batch has been edited by this contact.";
            if( CRM_Utils_Array::value( $params['status_id'], $batchStatus ) == 'Closed'){
            $details = "{$params['name']} batch has been closed by this contact.";
            }
            // if( CRM_Utils_Array::value( $params['batch_status_id'], $batchStatus ) == 'Open' && CRM_Utils_Array::value( 'close_date', $params ) ){
            //     $params['close_date'] = ''; 
            // }
                }
            
  
        $batch = CRM_Core_BAO_Batch::create($params, $ids, $context = 'financialBatch');
            $buttonName = $this->controller->getButtonName( );
                   
            $session = CRM_Core_Session::singleton( );
            if ( $buttonName == $this->getButtonName( 'next', 'new' ) ) {
            CRM_Core_Session::setStatus( ts(' You can add another Financial Batch.') );
                $session->replaceUserContext( CRM_Utils_System::url( 'civicrm/financial/batch', 
                                                                     "reset=1&action=add" ) );
        } elseif( CRM_Utils_Array::value( $batch->status_id, $batchStatus ) == 'Closed' ){
            if( $batch->name )
                CRM_Core_Session::setStatus( ts("'{$batch->name}' batch has been saved.") );
            $session->replaceUserContext( CRM_Utils_System::url( 'civicrm','reset=1' ) );
            } else {
            if( $batch->name )
                CRM_Core_Session::setStatus( ts("'{$batch->name}' batch  has been saved.") );
                $session->replaceUserContext( CRM_Utils_System::url( 'civicrm/financial/batch', 
                                                                 "reset=1&action=update&id={$batch->id}" ) );
            }     
    } 
    
}

 
