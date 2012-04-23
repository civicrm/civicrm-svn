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
 * form to process actions on Membership
 */
class CRM_Member_Form_MembershipBlock extends CRM_Contribute_Form_ContributionPage 
{
    /**
     * store membership price set id
     */
    protected $_memPriceSetId = null;
    
    /**
     * This function sets the default values for the form. Note that in edit/view mode
     * the default values are retrieved from the database
     *
     * @access public
     * @return void
     */
    function setDefaultValues()
    {
        //parent::setDefaultValues();
        $defaults = array();
        if ( isset( $this->_id ) ) {
            $defaults = CRM_Member_BAO_Membership::getMembershipBlock( $this->_id );
        }

     

        $defaults['member_is_active'] = $defaults['is_active'];

        // Set Display Minimum Fee default to true if we are adding a new membership block
        if ( ! isset( $defaults['id'] ) ) {
            $defaults['display_min_fee'] = 1;
        } else {
            $this->assign('membershipBlockId', $defaults['id']);
        }
        if ( $this->_id &&
             ( $priceSetId = CRM_Price_BAO_Set::getFor( 'civicrm_contribution_page', $this->_id, 3, 1 )) ) {
            $defaults['member_price_set_id'] = $priceSetId;
            $this->_memPriceSetId = $priceSetId; 
        } 
        else{
            // for membership_types
            // if ( isset( $defaults['membership_types'] ) ) {
            $priceSetId = CRM_Price_BAO_Set::getFor( 'civicrm_contribution_page', $this->_id, 3 );
             $this->_memPriceSetId = $priceSetId; 
             $pFIDs = array( );
            if( $priceSetId ){
                CRM_Core_DAO::commonRetrieveAll( 'CRM_Price_DAO_Field', 'price_set_id', $priceSetId, $pFIDs, $return = array( 'html_type', 'name' ) );
                foreach( $pFIDs as $pid => $pValue ){
                    if( $pValue['html_type'] == 'Radio' && $pValue['name'] == 'membership_amount' ){
                        $defaults['mem_price_field_id'] = $pValue['id'];                      
                    }
                }
                   
                if( CRM_Utils_Array::value( 'mem_price_field_id', $defaults ) ){
                    $options = array( );
                    $priceFieldOptions = CRM_Price_BAO_FieldValue::getValues( $defaults['mem_price_field_id'], $options );
                    foreach( $options as $k => $v ) {
                        $newMembershipType[$v['membership_type_id']] = 1;
                        //$defaults["auto_renew_$v"] = $defaults['auto_renew'][$v];
                    }
                    $defaults['membership_type'] = $newMembershipType;
                }
            }
        }

        return $defaults;
    }
    

    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        $membershipTypes = CRM_Member_BAO_MembershipType::getMembershipTypes();
        
        if (! empty( $membershipTypes ) ) {
            $this->addElement('checkbox', 'member_is_active', ts('Membership Section Enabled?') );
        
            $this->addElement('text', 'new_title', ts('Title - New Membership'), CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'new_title'));
            
            $this->addWysiwyg( 'new_text', ts('Introductory Message - New Memberships'),CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'new_text'));

            $this->addElement('text', 'renewal_title', ts('Title - Renewals'), CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'renewal_title'));

            $this->addWysiwyg( 'renewal_text', ts('Introductory Message - Renewals'),CRM_Core_DAO::getAttribute('CRM_Member_DAO_MembershipBlock', 'renewal_text'));
            
            $this->addElement('checkbox', 'is_required', ts('Require Membership Signup') );
            $this->addElement('checkbox', 'display_min_fee', ts('Display Membership Fee') );
            $this->addElement('checkbox', 'is_separate_payment', ts('Separate Membership Payment') );
            
            $paymentProcessor = CRM_Core_PseudoConstant::paymentProcessor( false, false, 'is_recur = 1' );
            
            $paymentProcessorId = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', 
                                                               $this->_id, 'payment_processor' );
            $isRecur = false;
            $membership        = array();
            $membershipDefault = array();
            foreach ( $membershipTypes as $k => $v ) {
                $membership[]      = $this->createElement('advcheckbox', $k , null, $v );
                $membershipDefault[] = $this->createElement('radio',null ,null,null, $k );
                if ( is_array( $paymentProcessor ) && 
                     CRM_Utils_Array::value( $paymentProcessorId, $paymentProcessor ) ) {
                    $isRecur = true;
                    $autoRenew = CRM_Core_DAO::getFieldValue('CRM_Member_DAO_MembershipType', $k, 'auto_renew' );
                    $autoRenewOptions = array( );
                    if ( $autoRenew ) {
                        $autoRenewOptions = array( ts('Not offered'), ts('Give option'), ts('Required') );
                        $this->addElement('select', "auto_renew_$k", ts('Auto-renew'), $autoRenewOptions );
                        $this->_renewOption[$k] = $autoRenew;
                    } 
                } else {
                    $isRecur = false;
                }
            }
            
            $this->add('hidden', "mem_price_field_id", '', array( 'id' => "mem_price_field_id") );
            $this->assign( 'is_recur', $isRecur );
            if ( isset( $this->_renewOption ) ) {
                $this->assign( 'auto_renew', $this->_renewOption );
            }
            $this->addGroup($membership, 'membership_type', ts('Membership Types'));
            $this->addGroup($membershipDefault, 'membership_type_default', ts('Membership Types Default'));
            
            $this->addFormRule(array('CRM_Member_Form_MembershipBlock', 'formRule') , $this->_id);
        }
        $price = CRM_Price_BAO_Set::getAssoc( false, 'CiviMember');
        if ( CRM_Utils_System::isNull( $price ) ) {
            $this->assign('price', false );
        } else {
            $this->assign('price', true );
        }
        $this->add( 'select', 'member_price_set_id', ts( 'Membership Price Set' ), (array( '' => ts( '- none -' )) + $price) );
        
        $session = CRM_Core_Session::singleton();
        $single = $session->get('singleForm');
        if ( $single ) {
            $this->addButtons(array(
                                    array ( 'type'      => 'next',
                                            'name'      => ts('Save'),
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                                            'isDefault' => true   ),
                                    array ( 'type'      => 'cancel',
                                            'name'      => ts('Cancel') ),
                                    )
                              );
        } else {
            parent::buildQuickForm( );
        }
    }

    /**
     * Function for validation
     *
     * @param array $params (ref.) an assoc array of name/value pairs
     *
     * @return mixed true or array of errors
     * @access public
     * @static
     */
    static function formRule( $params, $files, $contributionPageId = null ) 
    {
        $errors = array( );
        if ( CRM_Utils_Array::value( 'member_is_active', $params ) ) {
            
            // don't allow price set w/ membership signup, CRM-5095 
            if ( $contributionPageId && ( $setID = CRM_Price_BAO_Set::getFor( 'civicrm_contribution_page', $contributionPageId, null, 1 ) ) ) {

                $extends = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Set', $setID, 'extends' );
                if ( $extends != CRM_Core_Component::getComponentID( 'CiviMember' ) ) {
                    $errors['member_is_active'] = ts( 'You cannot enable both Membership Signup and a Contribution Price Set on the same online contribution page.' );  
                    return $errors;
                }
            }
            
            if ( $contributionPageId && CRM_Utils_Array::value('member_price_set_id', $params) && CRM_Core_DAO::getFieldValue('CRM_Contribute_DAO_ContributionPage', $contributionPageId, 'amount_block_is_active') ) {
                $errors['member_price_set_id'] = ts( 'You cannot use Membership Price Sets with the Contribution Amounts section. However, a membership price set may include additional fields for non-membership options that requires an additional fee (e.g. magazine subscription) or an additional voluntary contribution.' );
            }
            
            if ( CRM_Utils_Array::value('member_price_set_id', $params) ) {
                return $errors; 
            }

            if ( !  isset ( $params['membership_type'] ) ||
                 ( ! is_array( $params['membership_type'] ) ) ) {
                $errors['membership_type'] = ts( 'Please select at least one Membership Type to include in the Membership section of this page.' );
            } else {
                $membershipType = array_values($params['membership_type']);
                if ( array_sum($membershipType) == 0 ) {
                    $errors['membership_type'] = ts( 'Please select at least one Membership Type to include in the Membership section of this page.' );
                }
            }

            //for CRM-1302
            //if Membership status is not present, then display an error message
            $dao = new CRM_Member_BAO_MembershipStatus();
            if ( ! $dao->find( ) ) {
                $errors['_qf_default'] = ts( 'Add status rules, before configuring membership' );
            }    
            
            //give error if default is selected for an unchecked membership type
            if ( isset($params['membership_type_default']) && !$params['membership_type'][$params['membership_type_default']] ) {
                $errors['membership_type_default'] = ts( 'Can\'t set default option for an unchecked membership type.' );
            }

            if ( $contributionPageId ) {
                $amountBlock = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_ContributionPage', $contributionPageId, 'amount_block_is_active' );
                
                if ( !$amountBlock &&  CRM_Utils_Array::value( 'is_separate_payment', $params ) ) {
                    $errors['is_separate_payment'] = ts( 'Please enable the contribution amount section to use this option.' );
                }
            }
        }

        return empty($errors) ? true : $errors;
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
        $deletePriceSet = 0;
        if ( $params['membership_type'] ) {
            // we do this in case the user has hit the forward/back button
            $dao = new CRM_Member_DAO_MembershipBlock();
            $dao->entity_table = 'civicrm_contribution_page';
            $dao->entity_id = $this->_id; 
            $dao->find(true);
            $membershipID = $dao->id;
            if ( $membershipID ) {
                $params['id'] = $membershipID;
            }
            
            $membershipTypes = array();
            if ( is_array($params['membership_type']) ) {
                foreach( $params['membership_type'] as $k => $v) {
                    if ( $v ) {
                        $membershipTypes[$k] = CRM_Utils_Array::value( "auto_renew_$k", $params );
                    }
                }
            }
            
            // check for price set.
            $priceSetID = CRM_Utils_Array::value( 'member_price_set_id', $params );
            if ( $params['member_is_active'] && is_array( $membershipTypes ) && !$priceSetID ) {
                $usedPriceSetId = CRM_Price_BAO_Set::getFor( 'civicrm_contribution_page', $this->_id, 2 );
                if( ! CRM_Utils_Array::value( 'mem_price_field_id', $params)  && !$usedPriceSetId ){
                    $pageTitle = strtolower( CRM_Utils_String::munge( $this->_values['title'], '_', 245 ) );
                    $setParams['title'] = $this->_values['title'];
                    if(  !CRM_Core_DAO::getFieldValue( 'CRM_Price_BAO_Set', $pageTitle, 'id', 'name' ) ){
                        $setParams['name'] = $pageTitle;
                    }
                    elseif( !CRM_Core_DAO::getFieldValue( 'CRM_Price_BAO_Set', $pageTitle.'_'.$this->_id, 'id', 'name' )){
                        $setParams['name'] = $pageTitle .'_'. $this->_id;   
                    }else{
                        $setParams['name'] = $pageTitle .'_'. rand(1, 99);  
                    }
                    $setParams['is_quick_config'] = 1; 
                    $setParams['extends'] = CRM_Core_Component::getComponentID( 'CiviMember' );  
                    $setParams['contribution_type_id'] = CRM_Utils_Array::value( 'contribution_type_id', $this->_values );
                    $priceSet = CRM_Price_BAO_Set::create( $setParams );
                    $priceSetID = $priceSet->id;       
                    $fieldParams['price_set_id'] = $priceSet->id;  
                }elseif( $usedPriceSetId ){
                    $setParams['extends'] = CRM_Core_Component::getComponentID( 'CiviMember' ); 
                    $setParams['contribution_type_id'] = CRM_Utils_Array::value( 'contribution_type_id', $this->_values ); 
                    $setParams['id'] = $usedPriceSetId;                    
                    $priceSet = CRM_Price_BAO_Set::create( $setParams );
                    $priceSetID = $priceSet->id;       
                    $fieldParams['price_set_id'] = $priceSet->id;
                }else{
                    $fieldParams['id'] = CRM_Utils_Array::value( 'mem_price_field_id', $params );
                    CRM_Price_BAO_FieldValue::deleteValues( $params['mem_price_field_id'] );
                    $priceSetID = CRM_Core_DAO::getFieldValue( 'CRM_Price_DAO_Field',  CRM_Utils_Array::value( 'mem_price_field_id', $params ), 'price_set_id' );
                } 
                
                $fieldParams['name'] = strtolower( CRM_Utils_String::munge( 'Membership Amount', '_', 245 ) );
                $fieldParams['label'] = CRM_Utils_Array::value( 'new_title', $params ) ? $params['new_title'] : 'Membership Amount';
                $fieldParams['html_type'] = 'Radio'; 
                if( !CRM_Utils_Array::value( 'mem_price_field_id', $params ) )
                    CRM_Utils_Weight::updateOtherWeights( 'CRM_Price_DAO_Field', 0, 1, array( 'price_set_id' => $priceSetID ) );
                $fieldParams['weight'] = 1;
                $fieldParams['is_required'] = CRM_Utils_Array::value( 'is_required', $params )? 1 : 0;
                $fieldParams['is_display_amounts'] = CRM_Utils_Array::value( 'display_min_fee', $params )? 1 : 0;
                $rowCount = 1;
                foreach( $membershipTypes as $memType => $memAutoRenew ){

                    $membetype =  CRM_Member_BAO_MembershipType::getMembershipTypeDetails( $memType );
                    $fieldParams['option_label'][$rowCount] = $membetype['name'];
                    $fieldParams['option_amount'][$rowCount] = $membetype['minimum_fee'];
                    $fieldParams['option_weight'][$rowCount] = $membetype['weight'];
                    $fieldParams['option_description'][$rowCount] = $membetype['description'];
                    
                    $fieldParams['membership_type_id'][$rowCount] = $memType;
                    // [$rowCount] = $membetype[''];
                    $rowCount++;
                
                }
                
                $priceField = CRM_Price_BAO_Field::create( $fieldParams );
            }else{
                $deletePriceSet = 1;
            }
            
            $params['is_required'] = CRM_Utils_Array::value( 'is_required', $params, false );
            $params['is_active']   = CRM_Utils_Array::value( 'member_is_active', $params, false );

            if ( $priceSetID ) {
                $params['membership_types']    = 'null';
                $params['membership_type_default'] = CRM_Utils_Array::value( 'membership_type_default', $params, 'null' );
                //$params['membership_types']        = serialize( $membershipTypes );
                $params['display_min_fee']         = CRM_Utils_Array::value( 'display_min_fee', $params, false );
                $params['is_separate_payment']     = CRM_Utils_Array::value( 'is_separate_payment', $params, false );
            }
            $params['entity_table'] = 'civicrm_contribution_page';
            $params['entity_id']    = $this->_id;
            
            $dao = new CRM_Member_DAO_MembershipBlock();
            $dao->copyValues($params);
            $dao->save();            
           
            if ( $priceSetID && $params['is_active'] ) {
                CRM_Price_BAO_Set::addTo( 'civicrm_contribution_page', $this->_id, $priceSetID );
            } 

            if( $deletePriceSet || !CRM_Utils_Array::value( 'member_is_active', $params, false ) ) {
                
                if ( $this->_memPriceSetId ) {
                    $pFIDs = array( );
                    $conditionParams = array( 'price_set_id' => $this->_memPriceSetId,
                                              'html_type'    => 'radio',
                                              'name'         => 'contribution_amount' );
                    
                    CRM_Core_DAO::commonRetrieve( 'CRM_Price_DAO_Field', $conditionParams, $pFIDs );
                    if( !CRM_Utils_Array::value( 'id', $pFIDs ) ){
                        CRM_Price_BAO_Set::removeFrom( 'civicrm_contribution_page', $this->_id );
                        CRM_Price_BAO_Set::deleteSet( $this->_memPriceSetId );
                    }else{
                        $setParams = array( );
                         $setParams['extends'] = CRM_Core_Component::getComponentID( 'CiviContribute' );
                         $setParams['contribution_type_id'] = null; 
                         $setParams['id'] = $this->_memPriceSetId;
                         $priceSet = CRM_Price_BAO_Set::create( $setParams ); 
                          CRM_Price_BAO_Field::deleteField( $params['mem_price_field_id'] );
                    }
                }
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
        return ts( 'Memberships' );
    }
}

