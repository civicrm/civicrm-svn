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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Event/Form/Registration.php';
require_once 'CRM/Core/Payment.php';

/**
 * This class generates form components for processing Event  
 * 
 */
class CRM_Event_Form_Registration_Register extends CRM_Event_Form_Registration
{
    /** 
     * Function to set variables up before form is built 
     *                                                           
     * @return void 
     * @access public 
     */ 
    function preProcess( ) 
    {
        parent::preProcess( );

        //To check if the user is already registered for the event(CRM-2426) 
        self::checkRegistration(null , $this);
    }

    /**
     * This function sets the default values for the form. For edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {  
        // check if the user is registered and we have a contact ID
        $session =& CRM_Core_Session::singleton( );
        $contactID = $session->get( 'userID' ); 
        if ( $contactID ) {
            $options = array( );
            $fields = array( );

            require_once "CRM/Core/BAO/CustomGroup.php";
            if ( ! empty($this->_fields)) {
                $removeCustomFieldTypes = array ('Participant');
                foreach ( $this->_fields as $name => $dontCare ) {
                    if ( substr( $name, 0, 7 ) == 'custom_' ) {  
                        $id = substr( $name, 7 );
                        if ( ! CRM_Core_BAO_CustomGroup::checkCustomField( $id, $removeCustomFieldTypes )) {
                            continue;
                        }
                    } else if ( ( substr( $name, 0, 12 ) == 'participant_' ) ) { //ignore component fields
                        continue;
                    }
                    $fields[$name] = 1;
                }
            }
            $names = array("first_name", "middle_name", "last_name");
            foreach ($names as $name) {
                $fields[$name] = 1;
            }
            $fields["state_province-{$this->_bltID}"] = 1;
            $fields["country-{$this->_bltID}"       ] = 1;
            $fields["email-{$this->_bltID}"         ] = 1;
            $fields["email-Primary"                 ] = 1;
            
            require_once 'CRM/Core/BAO/UFGroup.php';
            CRM_Core_BAO_UFGroup::setProfileDefaults( $contactID, $fields, $this->_defaults );

            // use primary email address if billing email address is empty
            if ( empty( $this->_defaults["email-{$this->_bltID}"] ) &&
                 ! empty( $this->_defaults["email-Primary"] ) ) {
                $this->_defaults["email-{$this->_bltID}"] = $this->_defaults["email-Primary"];
            }

            foreach ($names as $name) {
                if ( isset( $this->_defaults[$name] ) ) {
                    $this->_defaults["billing_" . $name] = $this->_defaults[$name];
                }
            }
        }
        //if event is monetary and pay later is enabled and payment
        //processor is not available then freeze the pay later checkbox with
        //default check
        if ( CRM_Utils_Array::value( 'is_pay_later' , $this->_values['event_page'] ) &&
             ! is_array( $this->_paymentProcessor ) ) {
            $this->_defaults['is_pay_later'] = 1;
        }

        //set custom field defaults
        if ( ! empty( $this->_fields ) ) {
            require_once "CRM/Core/BAO/CustomField.php";
            foreach ( $this->_fields as $name => $field ) {
                if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID($name) ) {
                    if ( !isset( $this->_defaults[$name] )) { //fix for CRM-1743 
                        CRM_Core_BAO_CustomField::setProfileDefaults( $customFieldID, $name, $this->_defaults,
                                                                      null, CRM_Profile_Form::MODE_REGISTER );
                    }
                }
            }
        }

        return $this->_defaults;
    }

    /** 
     * Function to build the form 
     * 
     * @return None 
     * @access public 
     */ 

    public function buildQuickForm( ) 
    {  
        $config =& CRM_Core_Config::singleton( );

        $this->add( 'text',
                    "email-{$this->_bltID}",
                    ts( 'Email Address' ),
                    array( 'size' => 30, 'maxlength' => 60 ), true );
        if ( $this->_values['event_page']['is_multiple_registrations'] ) {
            $this->add( 'text', 'additional_participants', ts('How many additional people?'), array( 'size' => 10, 'maxlength' => 10) );
            $this->addRule( 'additional_participants', ts( 'Please enter a valid No Of People (numbers only).' ), 'positiveInteger' );
        }
        if ( $this->_values['event']['is_monetary'] ) {
            self::buildAmount( $this );

            if ( $this->_values['event_page']['is_pay_later'] ) {
                $attributes = null;
                $this->assign( 'hidePaymentInformation', false );
                if ( !in_array( $this->_paymentProcessor['payment_processor_type'], 
                                array( 'PayPal_Standard', 'Google_Checkout', 'PayPal_Express' ) ) && 
                     is_array( $this->_paymentProcessor ) ) {
                    $attributes = array('onclick' => "return showHideByValue('is_pay_later','','payment_information',
                                                     'table-row','radio',true);");
                    
                    $this->assign( 'hidePaymentInformation', true );
                }
                
                $element = $this->addElement( 'checkbox', 'is_pay_later', 
                                              $this->_values['event_page']['pay_later_text'], null, $attributes );
                //if payment processor is not available then freeze
                //the paylater checkbox with default checked.
                if ( ! is_array( $this->_paymentProcessor ) ) {
                    $element->freeze();
                }
            }            
            require_once 'CRM/Core/Payment/Form.php';
            CRM_Core_Payment_Form::buildCreditCard( $this );
        }
        
        $this->buildCustom( $this->_values['custom_pre_id'] , 'customPre'  );
        $this->buildCustom( $this->_values['custom_post_id'], 'customPost' );
        
        $session =& CRM_Core_Session::singleton( );
        $userID = $session->get( 'userID' );

        if ( ! $userID ) {
            $createCMSUser = false;
            if ( $this->_values['custom_pre_id'] ) {
                $profileID = $this->_values['custom_pre_id'];
                $createCMSUser = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', $profileID, 'is_cms_user' );
            }
            if ( ! $createCMSUser &&
                 $this->_values['custom_post_id'] ) {
                $profileID = $this->_values['custom_post_id'];
                $createCMSUser = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', $profileID , 'is_cms_user');
            }
            if ( $createCMSUser ) {
                require_once 'CRM/Core/BAO/CMSUser.php';
                CRM_Core_BAO_CMSUser::buildForm( $this, $profileID , true );
            }
        }
        
        $uploadNames = $this->get( 'uploadNames' );
        $buttonName = empty( $uploadNames ) ? 'next' : 'upload';
        
        // if payment is via a button only, dont display continue
        if ( $this->_paymentProcessor['billing_mode'] != CRM_Core_Payment::BILLING_MODE_BUTTON ||
             ! $this->_values['event']['is_monetary']) {
            $this->addButtons(array( 
                                    array ( 'type'      => $buttonName, 
                                            'name'      => ts('Continue >>'), 
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                     ) 
                              );
        }
        $this->addFormRule( array( 'CRM_Event_Form_Registration_Register', 'formRule' ),
                            $this );
        
    }
    
    /**
     * build the radio/text form elements for the amount field
     * 
     * @param object   $form form object
     * @param boolean  $required  true if you want to add formRule
     * @param int      $discountId discount id for the event
     *
     * @return void
     * @access public
     * @static
     */
    
    static public function buildAmount( &$form, $required = true, $discountId = null ) 
    {
        $elements = array( );
        $form->addGroup( $elements, 'amount', ts('Event Fee(s)'), '<br />' );      
        if ( isset($form->_priceSetId) ) {
            $form->add( 'hidden', 'priceSetId', $form->_priceSetId );
            $form->assign( 'priceSet', $form->_priceSet );
            require_once 'CRM/Core/BAO/PriceField.php';                       
            foreach ( $form->_values['custom']['fields'] as $field ) {
                $fieldId = $field['id'];
                $elementName = 'price_' . $fieldId;
                CRM_Core_BAO_PriceField::addQuickFormElement(
                                                             $form, $elementName, $fieldId, false,
                                                             $field['is_required']
                                                             );
                
            }
        } else if ( ! empty( $form->_values['custom']['label'] ) ) {
            $feeBlock = $form->_values['custom'];
            if ( isset( $form->_values['discount'] ) ) {
                if ( !$discountId ) {
                    require_once 'CRM/Core/BAO/Discount.php';
                    $discountId = CRM_Core_BAO_Discount::findSet( $form->_eventId, 'civicrm_event' );
                }

                if ( $discountId ) {
                    $feeBlock = $form->_values['discount'][$discountId];
                }
            }
            
            require_once 'CRM/Utils/Money.php';
            for ( $index = 1; $index <= count(  $feeBlock['label'] ); $index++ ) {
                $elements[] =& $form->createElement('radio', null, '',
                                                    CRM_Utils_Money::format( $feeBlock['value'][$index]) . ' ' . 
                                                    $feeBlock['label'][$index], 
                                                    $feeBlock['amount_id'][$index] );
            }

            $form->_defaults['amount'] = CRM_Utils_Array::value('default_fee_id',$form->_values['event_page']);
            $element =& $form->addGroup( $elements, 'amount', ts('Event Fee(s)'), '<br />' ); 
            if ( $form->_online ) {
                $element->freeze();
            }
            if ( $required ) {
                $form->addRule( 'amount', ts('Fee Level is a required field.'), 'required' );
            }
        }
    }
    
    /** 
     * Function to add all the credit card fields
     * 
     * @return None 
     * @access public 
     */
    function buildCreditCard( ) 
    {
        $config =& CRM_Core_Config::singleton( );
        if ( $this->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_FORM ) {
            foreach ( $this->_fields as $name => $field ) {
                $this->add( $field['htmlType'],
                            $field['name'],
                            $field['title'],
                            $field['attributes'],
                            $field['is_required'] );
            }
            
            $this->addRule( 'cvv2', ts( 'Please enter a valid value for your card security code. This is usually the last 3-4 digits on the card\'s signature panel.' ), 'integer' );
            
            $this->addRule( 'credit_card_exp_date', ts('Select a valid date greater than today.'), 'currentDate');
        }            
        
        if ( $this->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_BUTTON ) {
            $this->_expressButtonName = $this->getButtonName( 'next', 'express' );
            $this->add('image',
                       $this->_expressButtonName,
                       $this->_paymentProcessor['url_button'],
                       array( 'class' => 'form-submit' ) );
        }
    }
    
    
    /** 
     * global form rule 
     * 
     * @param array $fields  the input form values 
     * @param array $files   the uploaded files if any 
     * @param array $options additional user data 
     * 
     * @return true if no errors, else array of errors 
     * @access public 
     * @static 
     */ 
    static function formRule(&$fields, &$files, &$self) 
    {
        //To check if the user is already registered for the event(CRM-2426)
        self::checkRegistration($fields, $self);
        //check for atleast one pricefields should be selected
        if ( $fields['priceSetId'] ) {
            $priceField = new CRM_Core_DAO_PriceField( );
            $priceField->price_set_id = $fields['priceSetId'];
            $priceField->find( );
            
            $check = array( );
            
            while ( $priceField->fetch( ) ) {
                if ( ! empty( $fields["price_{$priceField->id}"] ) ) {
                    $check[] = $priceField->id; 
                }
            }
            
            if ( empty( $check ) ) {
                $errors['_qf_default'] = ts( "Select atleast one option from Event Fee(s)" );
            }
        }

        if ( $self->_values['event']['is_monetary'] ) {
            if ( is_array( $self->_paymentProcessor ) ) {
                $payment =& CRM_Core_Payment::singleton( $self->_mode, 'Event', $self->_paymentProcessor );
                $error   =  $payment->checkConfig( $self->_mode );
                if ( $error ) {
                    $errors['_qf_default'] = $error;
                }
            }
            // return if this is express mode
            $config =& CRM_Core_Config::singleton( );
            if ( $self->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_BUTTON ) {
                if ( CRM_Utils_Array::value( $self->_expressButtonName . '_x', $fields ) ||
                     CRM_Utils_Array::value( $self->_expressButtonName . '_y', $fields ) ||
                     CRM_Utils_Array::value( $self->_expressButtonName       , $fields ) ) {
                    return empty( $errors ) ? true : $errors;
                }
            } 
            //validation for the user who attemp the amount value zero
            //is an already member
            $session =& CRM_Core_Session::singleton( );
            $userID  = $session->get( 'userID' );
            if ( $fields['priceSetId'] ) { 
                $zeroAmount = array( );
                foreach( $fields as $key => $val  )  {
                    if ( substr( $key, 0, 6 ) == 'price_' ){
                        if ( is_array( $val) ) {
                            foreach( $val as $keys => $vals  )  {
                                $zeroAmount[] = $keys;
                            }
                        } else {
                            $zeroAmount[] = $val;
                        }
                    }
                } 
                foreach( $zeroAmount as $keyes => $values  )  {
                    if( $values && !$userID && 
                        CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue', $values, 'value', 'id' ) == 0 ) {
                        $errors['amount'] =  ts( "The Zero amount facility is only for the valid members" );
                    }
                }
            } else {
                $zeroAmount = $fields['amount'];
                if ( $zeroAmount && !$userID && 
                     CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue',$zeroAmount, 'value', 'id' ) == 0 ) {
                    $errors['amount'] =  ts( "The Zero amount facility is only for the valid members" );
                }
            }
            // also return if paylater mode or zero fees for valid members
            if ( CRM_Utils_Array::value( 'is_pay_later', $fields ) ) {
                if ( $fields['priceSetId'] ) { 
                    foreach( $fields as $key => $val  )  {
                        if ( substr( $key, 0, 6 ) == 'price_' && $val != 0) {
                            return empty( $errors ) ? true : $errors;
                        }
                    }
                } else {
                    return empty( $errors ) ? true : $errors;
                }
            } else if ( $fields['priceSetId'] ) { 
                $check = array( );
                foreach( $fields as $key => $val  )  {
                    if ( substr( $key, 0, 6 ) == 'price_' && $val != 0) {
                        if ( is_array( $val) ) {
                            foreach( $val as $keys => $vals  )  {
                                $check[] = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue', $keys, 'value');
                            }
                        } else {
                            $check[] = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue', $val, 'value');
                        }
                    }
                }
                $level = count ( $check );
                $j = null;
                for ($i = 0; $i < $level; $i++ ) {
                    if ( $check[$i] == 0 ) {
                        $j++;
                    }   
                }
                if ( $j == $level && isset( $j ) ) {
                    return empty( $errors ) ? true : $errors;
                } 
            } else if ( $zeroAmount ) {
                if ( CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue', $zeroAmount, 'value', 'id' ) == 0 ) {
                    return empty( $errors ) ? true : $errors;
                }
            }
            //is pay later and priceset is used avoid credit card and
            //billing address validation  
            if ( CRM_Utils_Array::value( 'is_pay_later', $fields ) && $fields['priceSetId'] ) {
                return empty( $errors ) ? true : $errors;
            }
            
            foreach ( $self->_fields as $name => $fld ) {
                if ( $fld['is_required'] &&
                     CRM_Utils_System::isNull( CRM_Utils_Array::value( $name, $fields ) ) ) {
                    $errors[$name] = ts( '%1 is a required field.', array( 1 => $fld['title'] ) );
                    
                }
            }
        }
        
        // make sure that credit card number and cvv are valid
        require_once 'CRM/Utils/Rule.php';
        if ( CRM_Utils_Array::value( 'credit_card_type', $fields ) ) {
            if ( CRM_Utils_Array::value( 'credit_card_number', $fields ) &&
                 ! CRM_Utils_Rule::creditCardNumber( $fields['credit_card_number'], $fields['credit_card_type'] ) ) {
                $errors['credit_card_number'] = ts( "Please enter a valid Credit Card Number" );
            }
            
            if ( CRM_Utils_Array::value( 'cvv2', $fields ) &&
                 ! CRM_Utils_Rule::cvv( $fields['cvv2'], $fields['credit_card_type'] ) ) {
                $errors['cvv2'] =  ts( "Please enter a valid Credit Card Verification Number" );
            }
        }
        
        return empty( $errors ) ? true : $errors;
    }
    
    
    /**
     * Function to process the form
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        // get the submitted form values. 
        $params = $this->controller->exportValues( $this->_name ); 
        if ($this->_values['event']['is_monetary']) {
            $config =& CRM_Core_Config::singleton( );
            
            // we first reset the confirm page so it accepts new values
            $this->controller->resetPage( 'Confirm' );
            
            // get the submitted form values. 
            
            $params['currencyID']     = $config->defaultCurrency;

            //added for discount
            require_once 'CRM/Core/BAO/Discount.php';
            $discountId = CRM_Core_BAO_Discount::findSet( $this->_eventId, 'civicrm_event' );

            if ( ! empty( $this->_values['discount'][$discountId] ) ) {
                $params['amount_level'] = $this->_values['discount'][$discountId]['label']
                    [array_search( $params['amount'], $this->_values['discount'][$discountId]['amount_id'])];
                
                $params['amount'] = $this->_values['discount'][$discountId]['value']
                    [array_search( $params['amount'], $this->_values['discount'][$discountId]['amount_id'])];
                
            }else if ( empty( $params['priceSetId'] ) ) {
                $params['amount_level'] = $this->_values['custom']['label'][array_search( $params['amount'], 
                                                                                          $this->_values['custom']['amount_id'])];
                
                $params['amount']       = $this->_values['custom']['value'][array_search( $params['amount'], 
                                                                                          $this->_values['custom']['amount_id'])];
            } else {
                $lineItem = array( );
                self::processPriceSetAmount( $this->_values['custom']['fields'], $params, $lineItem );
                $this->set( 'lineItem', $lineItem );
            }

            $this->set( 'amount', $params['amount'] ); 
            $this->set( 'amount_level', $params['amount_level'] ); 
            
            // generate and set an invoiceID for this transaction
            $invoiceID = $this->get( 'invoiceID' );
            if ( ! $invoiceID ) {
                $invoiceID = md5(uniqid(rand(), true));
            }
            $this->set( 'invoiceID', $invoiceID );
            if ( is_array( $this->_paymentProcessor ) ) {
                $payment =& CRM_Core_Payment::singleton( $this->_mode, 'Event', $this->_paymentProcessor ); 
            }
            // default mode is direct
            $this->set( 'contributeMode', 'direct' ); 
            
            if ( $this->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_BUTTON ) {
                //get the button name  
                $buttonName = $this->controller->getButtonName( );  
                if ( in_array( $buttonName, 
                               array( $this->_expressButtonName, $this->_expressButtonName. '_x', $this->_expressButtonName. '_y' ) ) && 
                     ! isset( $params['is_pay_later'] ) ) { 
                    $this->set( 'contributeMode', 'express' ); 
                    
                    $params['cancelURL' ] = CRM_Utils_System::url( 'civicrm/event/register', '_qf_Register_display=1', true, null, false ); 
                    $params['returnURL' ] = CRM_Utils_System::url( 'civicrm/event/register', '_qf_Confirm_display=1&rfp=1', true, null, false ); 
                    $params['invoiceID' ] = $invoiceID;
                    
                    $token = $payment->setExpressCheckout( $params ); 
                    if ( is_a( $token, 'CRM_Core_Error' ) ) { 
                        CRM_Core_Error::displaySessionError( $token ); 
                        CRM_Utils_System::redirect( $params['cancelURL' ] );
                    } 
                    
                    $this->set( 'token', $token ); 
                    
                    $paymentURL =
                        $this->_paymentProcessor['url_site'] .
                        "/cgi-bin/webscr?cmd=_express-checkout&token=$token"; 
                    
                    CRM_Utils_System::redirect( $paymentURL ); 
                }
            } else if ( $this->_paymentProcessor['billing_mode'] & CRM_Core_Payment::BILLING_MODE_NOTIFY ) {
                $this->set( 'contributeMode', 'notify' );
            }
        } else {
            $session =& CRM_Core_Session::singleton( );
            $contactID = $session->get( 'userID' );
            if ( $this->_values['event']['default_role_id'] ) {
                $this->_params['participant_role_id'] = $this->_values['event']['default_role_id'];
            }
            $this->_params                = $params;
            $this->_params['description'] = ts( 'Online Event Registration' ) . ' ' . $this->_values['event']['title'];
            
            require_once 'CRM/Event/Form/Registration/Confirm.php';
            CRM_Event_Form_Registration_Confirm::fixLocationFields( $this->_params, $fields );
            $contactID =& CRM_Event_Form_Registration_Confirm::updateContactFields( $contactID, $this->_params, $fields );
            $session->set( 'userID', $contactID );
            $this->confirmPostProcess( $contactID );
            $this->set( 'params', $this->_params );
        }
    }//end of function
    


    
    static function processPriceSetAmount( &$fields, &$params, &$lineItem ) 
    {
        // using price set
        $totalPrice    = 0;
        $radioLevel    = $checkboxLevel = $selectLevel = $textLevel = array( );
        
        foreach ( $fields as $id => $field ) {
            if ( empty( $params["price_{$id}"] ) ) {
                // skip if nothing was submitted for this field
                continue;
            }
            
            switch ( $field['html_type'] ) {
            case 'Text':
                $params["price_{$id}"] = array( key( $field['options'] ) => $params["price_{$id}"] );
                
                self::getLineItem( $id, $params, $field, $lineItem );
                
                $totalPrice += $lineItem[key( $field['options'] )]['line_total'];
                break;
                
            case 'Radio':
                $params["price_{$id}"] = array( $params["price_{$id}"] => 1 );
                
                $optionValueId    = CRM_Utils_Array::key( 1, $params["price_{$id}"] );
                $optionLabel      = $field['options'][$optionValueId]['label'];
                $params['amount_priceset_level_radio']                = array( );
                $params['amount_priceset_level_radio'][$optionValueId]= $optionLabel;
                
                if( isset( $radioLevel ) ) {
                    $radioLevel   = array_merge( $radioLevel,
                                                 array_keys( $params['amount_priceset_level_radio'] ) );   
                } else {
                    $radioLevel   = array_keys($params['amount_priceset_level_radio']);
                }
                
                self::getLineItem( $id, $params, $field, $lineItem );
                
                $totalPrice += $lineItem[$optionValueId]['line_total'];
                
                break;
            case 'Select': 
                $params["price_{$id}"] = array( $params["price_{$id}"] => 1 );
                                
                $optionValueId    = CRM_Utils_Array::key( 1, $params["price_{$id}"] );
                $optionLabel      = $field['options'][$optionValueId]['label'];
                $params['amount_priceset_level_select']                 = array();
                $params['amount_priceset_level_select']
                    [CRM_Utils_Array::key( 1, $params["price_{$id}"] )] = $optionLabel;
                
                if( isset( $selectLevel ) ) {
                    $selectLevel   = array_merge($selectLevel,array_keys($params['amount_priceset_level_select']));   
                } else {
                    $selectLevel   = array_keys($params['amount_priceset_level_select']);
                }
                
                self::getLineItem( $id, $params, $field, $lineItem );
                
                $totalPrice += $lineItem[$optionValueId]['line_total'];
                
                break;
                
            case 'CheckBox':
                $params['amount_priceset_level_checkbox'] = $optionIds = array( );
                foreach ( $params["price_{$id}"] as $optionId => $option ) {
                    $optionIds[] = $optionId;
                    $optionLabel = $field['options'][$optionId]['label'];
                    $params['amount_priceset_level_checkbox']["{$field['options'][$optionId]['id']}"]= $optionLabel;
                    
                    if( isset($checkboxLevel) ){
                        $checkboxLevel=array_unique( 
                                                    array_merge(
                                                                $checkboxLevel, 
                                                                array_keys($params['amount_priceset_level_checkbox'])
                                                                )
                                                     );
                    } else {
                        $checkboxLevel=array_keys($params['amount_priceset_level_checkbox']);
                    }
                }
                
                self::getLineItem( $id, $params, $field, $lineItem );
                
                foreach( $optionIds as $optionId ) {
                    $totalPrice += $lineItem[$optionId]['line_total'];
                }
                
                break;
            }
        }
        
        $amount_level = array( );
        
        foreach( $lineItem as $values ) {
            if ( $values['html_type'] == 'Text' ) {
                $amount_level[] = $values['label'] . ' - ' . $values['qty'];
                continue;
            }
            $amount_level[] = $values['label'];
        }
        
        require_once 'CRM/Core/BAO/CustomOption.php';
        $params['amount_level'] =
            CRM_Core_BAO_CustomOption::VALUE_SEPERATOR .
            implode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, $amount_level ) .
            CRM_Core_BAO_CustomOption::VALUE_SEPERATOR; 
        $params['amount']       = $totalPrice;
    }
    
    /**
     * This method will create the lineItem array required for
     * processPriceSetAmount method
     *
     * @param  int   $fid       price set field id
     * @param  array $params    referance to form values
     * @param  array $fields    referance to array of fields belonging
     *                          to the price set used for particular event
     * @param  array $values    referance to the values array(this is
     *                          lineItem array)
     *
     * @return void
     * @access static
     */
    static function getLineItem( $fid, &$params, &$fields, &$values )
    {
        foreach( $params["price_{$fid}"] as $oid => $qty ) {
            $price        = $fields['options'][$oid]['value'];
            
            $values[$oid] = array(
                                  'price_field_id'   => $fid,
                                  'option_value_id'  => $oid,
                                  'label'            => CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionValue',
                                                                                     $oid, 'label', 'id' ),
                                  'qty'              => $qty,
                                  'unit_price'       => $price,
                                  'line_total'       => $qty * $fields['options'][$oid]['value'],
                                  'html_type'        => $fields['html_type']
                                  );
        }
    }

    /** 
     * Method to check if the user is already registered for the event   
     * and if result found redirect to the event info page
     *
     * @param array $fields  the input form values(anonymous user) 
     * @param array $self    event data 
     * 
     * @return void  
     * @access public 
     */ 
    function checkRegistration($fields, &$self)
    {
        $session =& CRM_Core_Session::singleton( );
        $contactID = $session->get( 'userID' );
        if ( ! $contactID &&
             ! empty( $fields ) &&
             isset( $fields['email-5'] ) ) {
            $emailString = trim( $fields['email-5'] );
            if ( ! empty( $emailString ) ) {
                require_once 'CRM/Core/BAO/Email.php';
                $email =&new CRM_Core_BAO_Email();
                $email->email = $emailString;
                $email->find(true);
                $contactID = $email->contact_id;
            }
        }

        if ( $contactID ) {
            require_once 'CRM/Event/BAO/Participant.php';
            $participant =&new CRM_Event_BAO_Participant();
            $participant->contact_id = $contactID;
            $participant->event_id = $self->_values['event']['id'];
            $participant->role_id = $self->_values['event']['default_role_id'];
            if ($self->_mode == 'test') {
                $participant->is_test = 1;
            } else {
                $participant->is_test = 0;
            }
            $participant->find();
            require_once 'CRM/Event/PseudoConstant.php';
            $statusTypes = CRM_Event_PseudoConstant::participantStatus( null, false );
            while($participant->fetch()) {
                if ( array_key_exists ( $participant->status_id, $statusTypes ) ) {
                    $status = ts("Oops. It looks like you are already registered for this event. If you want to change your registration, or you feel that you've gotten this message in error, please contact the site administrator."); 
                    $session->setStatus( $status );
                    $url = CRM_Utils_System::url( 'civicrm/event/info',
                                                  "reset=1&id={$self->_values['event']['id']}" );
                    CRM_Utils_System::redirect( $url );
                }
            }
        }
    }
}

