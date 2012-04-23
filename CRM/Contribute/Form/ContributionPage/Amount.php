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

require_once 'CRM/Price/BAO/FieldValue.php';

/**
 * form to process actions on the group aspect of Custom Data
 */
class CRM_Contribute_Form_ContributionPage_Amount extends CRM_Contribute_Form_ContributionPage 
{
    /**
     * contribution amount block.
     *
     * @var array
     * @access protected
     */
    protected $_amountBlock = array( );
    
    /** 
     * Constants for number of options for data types of multiple option. 
     */ 
    const NUM_OPTION = 11;
    
    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {

        // do u want to allow a free form text field for amount 
        $this->addElement('checkbox', 'is_allow_other_amount', ts('Allow other amounts' ), null, array( 'onclick' => "minMax(this);showHideAmountBlock( this, 'is_allow_other_amount' );" ) );  
        $this->add('text', 'min_amount', ts('Minimum Amount'), array( 'size' => 8, 'maxlength' => 8 ) ); 
        $this->addRule('min_amount', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('9.99', ' '))), 'money');

        $this->add('text', 'max_amount', ts('Maximum Amount'), array( 'size' => 8, 'maxlength' => 8 ) ); 
        $this->addRule('max_amount', ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');

        $default = array( );
        $this->add('hidden', "price_field_id", '', array( 'id' => "price_field_id") );
        $this->add('hidden', "price_field_other", '', array( 'id' => "price_field_option") );
        for ( $i = 1; $i <= self::NUM_OPTION; $i++ ) {
            // label 
            $this->add('text', "label[$i]", ts('Label'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_OptionValue', 'label')); 
            
            $this->add('hidden', "price_field_value[$i]", '', array( 'id' => "price_field_value[$i]") );
            
            // value 
            $this->add('text', "value[$i]", ts('Value'), CRM_Core_DAO::getAttribute('CRM_Core_DAO_OptionValue', 'value')); 
            $this->addRule("value[$i]", ts('Please enter a valid money value (e.g. %1).', array(1 => CRM_Utils_Money::format('99.99', ' '))), 'money');

            // default
            $default[] = $this->createElement('radio', null, null, null, $i); 
        }

        $this->addGroup( $default, 'default' );
        
        $this->addElement('checkbox', 'amount_block_is_active', ts('Contribution Amounts section enabled'), null, array( 'onclick' => "showHideAmountBlock( this, 'amount_block_is_active' );" ) );

        $this->addElement('checkbox', 'is_monetary', ts('Execute real-time monetary transactions') );
        
        $paymentProcessor = CRM_Core_PseudoConstant::paymentProcessor( );
        $recurringPaymentProcessor = array( );

        if ( !empty( $paymentProcessor ) ) {
            $paymentProcessorIds = implode( ',', array_keys( $paymentProcessor ) );
            $query = "
SELECT id
  FROM civicrm_payment_processor
 WHERE id IN ({$paymentProcessorIds})
   AND is_recur = 1";
            $dao = CRM_Core_DAO::executeQuery( $query );
            while ( $dao->fetch( ) ) {
                $recurringPaymentProcessor[] = $dao->id;
            } 
        }
        $this->assign( 'recurringPaymentProcessor', $recurringPaymentProcessor );
        if ( count($paymentProcessor) ) {
            $this->assign('paymentProcessor',$paymentProcessor);
        }

        $this->addCheckBox( 'payment_processor', ts('Payment Processor'),
                            array_flip($paymentProcessor),
                            null, null, null, null,
                            array( '&nbsp;&nbsp;', '&nbsp;&nbsp;', '&nbsp;&nbsp;', '<br/>' ) );

        
        //check if selected payment processor supports recurring payment
        if ( !empty( $recurringPaymentProcessor ) ) {
            $this->addElement( 'checkbox', 'is_recur', ts('Recurring contributions'), null, 
                               array('onclick' => "showHideByValue('is_recur',true,'recurFields','table-row','radio',false); showRecurInterval( );") );
            $this->addCheckBox( 'recur_frequency_unit', ts('Supported recurring units'), 
                                CRM_Core_OptionGroup::values( 'recur_frequency_units', false, false, false, null, 'name' ),
                                null, null, null, null,
                                array( '&nbsp;&nbsp;', '&nbsp;&nbsp;', '&nbsp;&nbsp;', '<br/>' ) );
            $this->addElement('checkbox', 'is_recur_interval', ts('Support recurring intervals') );
        }
        
        // add pay later options
        $this->addElement('checkbox', 'is_pay_later', ts( 'Pay later option' ),
                          null, array( 'onclick' => "payLater(this);" ) );
        $this->addElement('textarea', 'pay_later_text', ts( 'Pay later label' ),  
                          CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_ContributionPage', 'pay_later_text' ),
                          false );
        $this->addElement('textarea', 'pay_later_receipt', ts( 'Pay later instructions' ),  
                          CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_ContributionPage', 'pay_later_receipt' ),
                          false );
        // add price set fields
        $price = CRM_Price_BAO_Set::getAssoc( false, 'CiviContribute');
        if (CRM_Utils_System::isNull($price)) {
            $this->assign('price', false );
        } else {
            $this->assign('price', true );
        }
        $this->add('select', 'price_set_id', ts( 'Price Set' ),
                   array( '' => ts( '- none -' )) + $price,
                   null, array('onchange' => "showHideAmountBlock( this.value, 'price_set_id' );")
                   );
        //CiviPledge fields.
        $config = CRM_Core_Config::singleton( );
        if ( in_array('CiviPledge', $config->enableComponents) ) {
            $this->assign('civiPledge', true );
            $this->addElement( 'checkbox', 'is_pledge_active', ts('Pledges') , 
                               null, array('onclick' => "showHideAmountBlock( this, 'is_pledge_active' ); return showHideByValue('is_pledge_active',true,'pledgeFields','table-row','radio',false);") );
            $this->addCheckBox( 'pledge_frequency_unit', ts( 'Supported pledge frequencies' ), 
                                CRM_Core_OptionGroup::values( 'recur_frequency_units', false, false, false, null, 'name' ),
                                null, null, null, null,
                                array( '&nbsp;&nbsp;', '&nbsp;&nbsp;', '&nbsp;&nbsp;', '<br/>' ));
            $this->addElement( 'checkbox', 'is_pledge_interval', ts('Allow frequency intervals') );
            $this->addElement( 'text', 'initial_reminder_day', ts('Send payment reminder'), array('size'=>3) );
            $this->addElement( 'text', 'max_reminders', ts('Send up to'), array('size'=>3) );
            $this->addElement( 'text', 'additional_reminder_day', ts('Send additional reminders'), array('size'=>3) );
        }
        
        //add currency element.
        $this->addCurrency( 'currency', ts( 'Currency' ) );
        
        $this->addFormRule( array( 'CRM_Contribute_Form_ContributionPage_Amount', 'formRule' ), $this );
        
        parent::buildQuickForm( );
    }

    /** 
     * This function sets the default values for the form. Note that in edit/view mode 
     * the default values are retrieved from the database 
     * 
     * @access public 
     * @return void 
     */ 
    function setDefaultValues() 
    {
        $defaults = parent::setDefaultValues( );
        $title = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $this->_id, 'title' );
        CRM_Utils_System::setTitle(ts('Contribution Amounts (%1)', array(1 => $title)));
        
        if ( !CRM_Utils_Array::value( 'pay_later_text', $defaults ) ) {
            $defaults['pay_later_text'] = ts( 'I will send payment by check' );
        }
        
        if ( CRM_Utils_Array::value( 'amount_block_is_active', $defaults ) ) {
            
            // don't allow other amount option when price set present.
            //$this->assign( 'priceSetID', $this->_priceSetID );
            //if ( $this->_priceSetID ) return $defaults;
           
            if( $priceSetId = CRM_Price_BAO_Set::getFor( 'civicrm_contribution_page', $this->_id, null ) ){
                if( CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Set', $priceSetId, 'is_quick_config' ) ){

                    //$priceField = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Field', $priceSetId, 'id', 'price_set_id' );
                    $options = $pFIDs = array();
                    $priceFieldParams = array( 'price_set_id' => $priceSetId );
                    $priceFields = CRM_Core_DAO::commonRetrieveAll( 'CRM_Price_DAO_Field', 'price_set_id', $priceSetId, $pFIDs, $return = array( 'html_type', 'name' ) );
                    foreach( $priceFields as $priceField ){
                        if( $priceField['id'] && $priceField['html_type'] == 'Radio' && $priceField['name'] == 'contribution_amount' ){
                            $defaults['price_field_id'] = $priceField['id'];
                            $priceFieldOptions = CRM_Price_BAO_FieldValue::getValues( $priceField['id'], $options );
                    
                            foreach( $options as $optionId => $optionValue ){
                                $defaults['value'][$optionValue['weight']] = $optionValue['amount'];
                                $defaults['label'][$optionValue['weight']] = $optionValue['label'];
                                $defaults['name'][$optionValue['weight']] = $optionValue['name'];
                                $defaults['weight'][$optionValue['weight']] = $optionValue['weight'];
                        
                                $defaults["price_field_value"][$optionValue['weight']] = $optionValue['id'];
                                if( $optionValue['is_default'] ){
                                    $defaults['default'] = $optionValue['weight'];
                                }
                            }
                        }elseif( $priceField['id'] && $priceField['html_type'] == 'Text' && $priceField['name'] = 'other_amount' ){
                            $defaults['price_field_other'] = $priceField['id'];
                        }
                    }
                }
            }
                        
            if ( CRM_Utils_Array::value( 'value', $defaults ) && is_array( $defaults['value'] ) ) { 
                             
                // CRM-4038: fix value display
                foreach ($defaults['value'] as &$amount) {
                    $amount = trim(CRM_Utils_Money::format($amount, ' '));
                }
            }
        }
        
        // fix the display of the monetary value, CRM-4038 
        if (isset($defaults['min_amount'])) {
            $defaults['min_amount'] = CRM_Utils_Money::format($defaults['min_amount'], null, '%a');
        }
        if (isset($defaults['max_amount'])) {
            $defaults['max_amount'] = CRM_Utils_Money::format($defaults['max_amount'], null, '%a');
        }

        if ( CRM_Utils_Array::value( 'payment_processor', $defaults ) ) {
                $defaults['payment_processor'] =
                    array_fill_keys( explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                              $defaults['payment_processor'] ), '1' );
        }
        return $defaults;
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
    static function formRule( $fields, $files, $self ) 
    {  
        $errors = array( );
        //as for separate membership payment we has to have
        //contribution amount section enabled, hence to disable it need to
        //check if separate membership payment enabled, 
        //if so disable first separate membership payment option  
        //then disable contribution amount section. CRM-3801,
        
        $membershipBlock = new CRM_Member_DAO_MembershipBlock( );
        $membershipBlock->entity_table = 'civicrm_contribution_page';
        $membershipBlock->entity_id = $self->_id;
        $membershipBlock->is_active = 1;
        $hasMembershipBlk = false;
        if ( $membershipBlock->find( true ) ) {
            if ( CRM_Utils_Array::value('amount_block_is_active', $fields) &&
                 ($setID = CRM_Price_BAO_Set::getFor('civicrm_contribution_page',  $self->_id, null, 1 )) ) {
                $extends = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Set', $setID, 'extends' );
                if ( $extends && $extends == CRM_Core_Component::getComponentID( 'CiviMember' ) ) {
                    $errors['amount_block_is_active'] = ts( 'You cannot use a Membership Price Set when the Contribution Amounts section is enabled. Click the Memberships tab above, and select your Membership Price Set on that form. Membership Price Sets may include additional fields for non-membership options that require an additional fee (e.g. magazine subscription) or an additional voluntary contribution.' );
                    return $errors;
                }
            }
            $hasMembershipBlk = true;
            if ( $membershipBlock->is_separate_payment && !$fields['amount_block_is_active'] ) {
                $errors['amount_block_is_active'] = ts( 'To disable Contribution Amounts section you need to first disable Separate Membership Payment option from Membership Settings.' );
            }
        }

        $minAmount = CRM_Utils_Array::value( 'min_amount', $fields );
        $maxAmount = CRM_Utils_Array::value( 'max_amount', $fields );
        if ( ! empty( $minAmount) && ! empty( $maxAmount ) ) {
            $minAmount = CRM_Utils_Rule::cleanMoney( $minAmount );
            $maxAmount = CRM_Utils_Rule::cleanMoney( $maxAmount );
            if ( (float ) $minAmount > (float ) $maxAmount ) {
                $errors['min_amount'] = ts( 'Minimum Amount should be less than Maximum Amount' );
            }
        }
        
        if ( isset( $fields['is_pay_later'] ) ) {
            if ( empty( $fields['pay_later_text'] ) ) {
                $errors['pay_later_text'] = ts( 'Please enter the text for the \'pay later\' checkbox displayed on the contribution form.' );
            }
            if ( empty( $fields['pay_later_receipt'] ) ) {
                $errors['pay_later_receipt'] = ts( 'Please enter the instructions to be sent to the contributor when they choose to \'pay later\'.' );
            }
        }      
        
        // don't allow price set w/ membership signup, CRM-5095
        if ( $priceSetId = CRM_Utils_Array::value( 'price_set_id', $fields ) ) {
            // don't allow price set w/ membership.
            if ( $hasMembershipBlk ) {
                $errors['price_set_id'] = ts( 'You cannot enable both a Contribution Price Set and Membership Signup on the same online contribution page.' );  
            }
        } else {
            if ( isset( $fields['is_recur'] ) ) {
                if ( empty( $fields['recur_frequency_unit'] ) ) {
                    $errors['recur_frequency_unit'] = ts( 'At least one recurring frequency option needs to be checked.' );
                }
            }     
            
            // validation for pledge fields.
            if ( CRM_Utils_array::value( 'is_pledge_active', $fields ) ) {
                if ( empty( $fields['pledge_frequency_unit'] ) ) {
                    $errors['pledge_frequency_unit'] = ts( 'At least one pledge frequency option needs to be checked.' );
                }
                if ( CRM_Utils_array::value( 'is_recur', $fields ) ) {
                    $errors['is_recur'] = ts( 'You cannot enable both Recurring Contributions AND Pledges on the same online contribution page.' ); 
                }
            }
            
            // If Contribution amount section is enabled, then 
            // Allow other amounts must be enabeld OR the Fixed Contribution
            // Contribution options must contain at least one set of values.
            if ( CRM_Utils_Array::value( 'amount_block_is_active', $fields ) ) {
                if ( !CRM_Utils_Array::value( 'is_allow_other_amount', $fields ) &&
                     !$priceSetId ) {
                    //get the values of amount block
                    $values  = CRM_Utils_Array::value( 'value'  , $fields );
                    $isSetRow = false;
                    for ( $i = 1; $i < self::NUM_OPTION; $i++ ) {
                        if ( ( isset( $values[$i] ) && ( strlen( trim( $values[$i] ) ) > 0 ) ) ) { 
                            $isSetRow = true;
                        }
                    }
                    if ( !$isSetRow ) {
                        $errors['amount_block_is_active'] = 
                            ts ( 'If you want to enable the \'Contribution Amounts section\', you need to either \'Allow Other Amounts\' and/or enter at least one row in the \'Fixed Contribution Amounts\' table.' );
                    }
                }
            }
        }

        if ( CRM_Utils_Array::value( 'is_recur_interval', $fields ) ) {
            $paymentProcessorType = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_PaymentProcessor', 
                                                                 $fields['payment_processor'], 
                                                                 'payment_processor_type' );
            if ( $paymentProcessorType == 'Google_Checkout' ) {
                $errors['is_recur_interval'] = ts( 'Google Checkout does not support recurring intervals' );
            }
        }
      
        return $errors;
    }
    
    /**
     * Process the form
     *
     * @return void
     * @access public
     */
    public function postProcess()
    {
        // get the submitted form values.
        $params = $this->controller->exportValues( $this->_name );
        if ( array_key_exists( 'payment_processor', $params ) ) {
            if ( array_key_exists( CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_PaymentProcessor', 'AuthNet',
                                                                'id', 'payment_processor_type'), 
                                   CRM_Utils_Array::value( 'payment_processor', $params ) ) ) {
                CRM_Core_Session::setStatus( ts( ' Please note that the Authorize.net payment processor only allows recurring contributions and auto-renew memberships with payment intervals from 7-365 days or 1-12 months (i.e. not greater than 1 year).' ) );
            }
        }

        // check for price set.
        $priceSetID = CRM_Utils_Array::value( 'price_set_id', $params );
        
        // get required fields.
        $fields = array( 'id'                     => $this->_id, 
                         'is_recur'               => false,
                         'min_amount'             => "null",
                         'max_amount'             => "null",
                         'is_monetary'            => false,
                         'is_pay_later'           => false,
                         'is_recur_interval'      => false,
                         'recur_frequency_unit'   => "null",
                         'default_amount_id'      => "null",
                         'is_allow_other_amount'  => false,
                         'amount_block_is_active' => false
                         );
        $resetFields = array( );
        if ( $priceSetID ) {
            $resetFields = array( 'min_amount', 'max_amount', 'is_allow_other_amount' );
        }
        
        if ( !CRM_Utils_Array::value( 'is_recur', $params ) ) {
            $resetFields = array_merge( $resetFields, array( 'is_recur_interval', 'recur_frequency_unit' ) );
        }

        foreach ( $fields as $field => $defaultVal ) {
            $val = CRM_Utils_Array::value( $field, $params, $defaultVal );
            if ( in_array( $field, $resetFields ) ) $val = $defaultVal;
            
            if ( in_array( $field, array( 'min_amount', 'max_amount' ) ) ) {
                $val = CRM_Utils_Rule::cleanMoney( $val );
            }
             
            $params[$field] = $val;
        }
        
        if ( $params['is_recur'] ) {
            $params['recur_frequency_unit'] = 
                implode( CRM_Core_DAO::VALUE_SEPARATOR,
                         array_keys( $params['recur_frequency_unit'] ) );
            $params['is_recur_interval'] = CRM_Utils_Array::value( 'is_recur_interval', $params ,false );
        }

        if ( array_key_exists( 'payment_processor', $params ) &&
             ! CRM_Utils_System::isNull( $params['payment_processor'] ) ) {
                $params['payment_processor'] = implode( CRM_Core_DAO::VALUE_SEPARATOR, array_keys( $params['payment_processor'] ) );
        } else {
            $params['payment_processor'] = 'null';
        }

        $contributionPage   = CRM_Contribute_BAO_ContributionPage::create( $params );
        $contributionPageID = $contributionPage->id;
        
        // prepare for data cleanup.
        $deleteAmountBlk = $deletePledgeBlk = $deletePriceSet = false;
        if ( $this->_priceSetID    )         $deletePriceSet  = true;
        if ( $this->_pledgeBlockID )         $deletePledgeBlk = true;
        if ( !empty( $this->_amountBlock ) ) $deleteAmountBlk = true;
        
        if ( $contributionPageID ) {
            
            if ( CRM_Utils_Array::value('amount_block_is_active', $params ) ) {
                // handle price set.
                if ( $priceSetID ) {
                    // add/update price set.
                    $deletePriceSet = false;
                     if( CRM_Utils_Array::value( 'price_field_id', $params ) )
                         $deleteAmountBlk = true; 
                    
                    CRM_Price_BAO_Set::addTo( 'civicrm_contribution_page', $contributionPageID, $priceSetID );
                } else {
                    
                    $deletePriceSet = false;
                    // process contribution amount block
                    $deleteAmountBlk = false; 
                    
                    $labels  = CRM_Utils_Array::value( 'label', $params );
                    $values  = CRM_Utils_Array::value( 'value', $params );
                    $default = CRM_Utils_Array::value( 'default', $params ); 
                    
                    $options = array( );
                    for ( $i = 1; $i < self::NUM_OPTION; $i++ ) {
                        if ( isset( $values[$i] ) &&
                             ( strlen( trim( $values[$i] ) ) > 0 ) ) {
                            $options[] = array( 'label'      => trim( $labels[$i] ),
                                                'value'      => CRM_Utils_Rule::cleanMoney( trim( $values[$i] ) ),
                                                'weight'     => $i,
                                                'is_active'  => 1,
                                                'is_default' => $default == $i );
                        }
                    }
                    if( !empty( $options ) ){
                        
                        $usedPriceSetId = CRM_Price_BAO_Set::getFor( 'civicrm_contribution_page', $this->_id, 3 );
                        if( ! CRM_Utils_Array::value( 'price_field_id', $params ) && !$usedPriceSetId ){
                            require_once 'CRM/Price/BAO/Set.php';
                            $pageTitle = strtolower( CRM_Utils_String::munge( $this->_values['title'], '_', 245 ) );
                            $setParams['title'] = $this->_values['title'];
                            if( !CRM_Core_DAO::getFieldValue( 'CRM_Price_BAO_Set', $pageTitle, 'id', 'name' ) ){
                                $setParams['name'] = $pageTitle;
                            }
                            elseif( !CRM_Core_DAO::getFieldValue( 'CRM_Price_BAO_Set', $pageTitle.'_'.$this->_id, 'id', 'name' )){
                                $setParams['name'] = $pageTitle .'_'. $this->_id;   
                            }else{
                                $setParams['name'] = $pageTitle .'_'. rand(1, 99);  
                            }
                            $setParams['is_quick_config'] = 1; 
                            $setParams['extends'] = CRM_Core_Component::getComponentID( 'CiviContribute' );                            
                            $priceSet = CRM_Price_BAO_Set::create( $setParams );
                            $priceSetId = $priceSet->id;
                                                    
                        }elseif( $usedPriceSetId && ! CRM_Utils_Array::value( 'price_field_id', $params ) ){
                            $priceSetId = $usedPriceSetId;
                            
                        }else{
                            foreach( $params['price_field_value'] as $arrayID =>$fieldValueID ){
                                if( empty( $params['label'][$arrayID] ) && empty( $params['value'][$arrayID] ) && !empty( $fieldValueID ) ){
                                    CRM_Price_BAO_FieldValue::del($fieldValueID);
                                    unset( $params['price_field_value'][$arrayID] );
                                }
                                    
                            }
                            $fieldParams['id'] = CRM_Utils_Array::value( 'price_field_id', $params );
                            $fieldParams['option_id'] = $params['price_field_value'];
                            $priceSetId = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Field',  CRM_Utils_Array::value( 'price_field_id', $params ), 'price_set_id' );
                        }
                        
                        $fieldParams['name'] = strtolower( CRM_Utils_String::munge( "Contribution Amount", '_', 245 ) );
                        $fieldParams['label'] = "Contribution Amount";
                            
                        $fieldParams['price_set_id'] = $priceSetId;   
                        
                        if(  CRM_Utils_Array::value( 'is_allow_other_amount', $params ) ){
                            $fieldParams['is_required'] = 0;
                        }else{
                            $fieldParams['is_required'] = 1;
                        }
                        $fieldParams['html_type'] = 'Radio'; 
                        CRM_Price_BAO_Set::addTo( 'civicrm_contribution_page', $this->_id, $priceSetId );
                        $fieldParams['option_label'] = $params['label'];
                        $fieldParams['option_amount'] = $params['value'];
                        foreach( $options as $value )
                            $fieldParams['option_weight'][$value['weight']] = $value['weight'];
                        $fieldParams['default_option'] = $params['default'];
                        require_once 'CRM/Price/BAO/Field.php';
                        $priceField = CRM_Price_BAO_Field::create( $fieldParams );
                        if(  CRM_Utils_Array::value( 'is_allow_other_amount', $params ) && !CRM_Utils_Array::value( 'price_field_other', $params )){
                            $fieldParams['label']                = "Other Amount";
                            $fieldParams['name']                 = strtolower( CRM_Utils_String::munge( $fieldParams['label'], '_', 245 ) );
                            $fieldParams['price_set_id']         = $priceSetId;  
                            $fieldParams['html_type']            = 'Text'; 
                            $fieldParams['is_display_amounts']   = $fieldParams['is_required'] = 0; 
                            $fieldParams['weight']               = $fieldParams['option_weight'][1] = 2;
                            $fieldParams['option_label'][1]      = "Other Amount";
                            $fieldParams['option_amount'][1]     = 1;
                            $priceField = CRM_Price_BAO_Field::create( $fieldParams );
                        }elseif( !CRM_Utils_Array::value( 'is_allow_other_amount', $params ) && CRM_Utils_Array::value( 'price_field_other', $params ) ){
                            CRM_Price_BAO_Field::deleteField( $params['price_field_other'] );
                        }
                    }
                    
                    if ( CRM_Utils_Array::value('is_pledge_active', $params ) ) {
                        $deletePledgeBlk = false; 
                        $pledgeBlockParams = array( 'entity_id'    => $contributionPageID,
                                                    'entity_table' => ts( 'civicrm_contribution_page' ) );
                        if ( $this->_pledgeBlockID ) {
                            $pledgeBlockParams['id'] = $this->_pledgeBlockID;
                        }
                        $pledgeBlock = array( 'pledge_frequency_unit', 'max_reminders', 
                                              'initial_reminder_day', 'additional_reminder_day' );
                        foreach ( $pledgeBlock  as $key ) {
                            $pledgeBlockParams[$key] = CRM_Utils_Array::value( $key, $params );    
                        }
                        $pledgeBlockParams['is_pledge_interval'] = CRM_Utils_Array::value( 'is_pledge_interval', 
                                                                                           $params, false );
                        // create pledge block.
                        CRM_Pledge_BAO_PledgeBlock::create( $pledgeBlockParams );
                    }
                }
            }else{
                if( CRM_Utils_Array::value( 'price_field_id', $params ) ) {
                    $usedPriceSetId = CRM_Price_BAO_Set::getFor( 'civicrm_contribution_page', $this->_id, 3 );
                    if( $usedPriceSetId ){
                        if( CRM_Utils_Array::value( 'price_field_id', $params ) ){
                            CRM_Price_BAO_Field::deleteField( $params['price_field_id'] );
                        }
                        if( CRM_Utils_Array::value( 'price_field_other', $params ) ){
                            CRM_Price_BAO_Field::deleteField( $params['price_field_other'] );
                        }
                    $deleteAmountBlk = true;
                    $deletePriceSet = true;                        
                    
                    }elseif( CRM_Utils_Array::value( 'price_field_id', $params ) ){
                    $deleteAmountBlk = true;
                    $deletePriceSet = true;
                    }
                }
            }
            
            // delete pledge block.
            if ( $deletePledgeBlk ) {
                CRM_Pledge_BAO_PledgeBlock::deletePledgeBlock( $this->_pledgeBlockID );
            }
            
            // delete previous price set.
            if ( $deletePriceSet ) {
                CRM_Price_BAO_Set::removeFrom( 'civicrm_contribution_page', $contributionPageID ); 
            }
            
            // delete amount block.
            if ( $deleteAmountBlk ) {
                $pricefieldID = CRM_Utils_Array::value( 'price_field_id', $params );
                if( $pricefieldID ){
                    $priceSetID =  CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Field', $pricefieldID, 'price_set_id' );
                    CRM_Price_BAO_Set::deleteSet($priceSetID);
                }
                // CRM_Core_OptionGroup::deleteAssoc( "civicrm_contribution_page.amount.{$contributionPageID}" );
            }
        }
        parent::endPostProcess( );
    }
    
    /** 
     * Return a descriptive name for the page, used in wizard header 
     * 
     * @return string 
     * @access public 
     */ 
    public function getTitle( ) 
    {
        return ts( 'Amounts' );
    }
    
}

