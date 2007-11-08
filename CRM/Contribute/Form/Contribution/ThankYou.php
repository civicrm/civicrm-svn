<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2007                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org.  If you have questions about the       |
 | Affero General Public License or the licensing  of CiviCRM,        |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2007
 * $Id$
 *
 */

require_once 'CRM/Contribute/Form/ContributionBase.php';

/**
 * form for thank-you / success page - 3rd step of online contribution process
 */
class CRM_Contribute_Form_Contribution_ThankYou extends CRM_Contribute_Form_ContributionBase {

    /**
     * Function to set variables up before form is built
     *
     * @return void
     * @access public
     */
    public function preProcess()
    {
        parent::preProcess( );

        $this->_params = $this->get( 'params' );
        
        $is_deductible = $this->get('is_deductible');
        $this->assign('is_deductible',$is_deductible);
        $this->assign( 'thankyou_title', $this->_values['thankyou_title'] );
        $this->assign( 'thankyou_text' , $this->_values['thankyou_text']  );
        $this->assign( 'thankyou_footer' , CRM_Utils_Array::value('thankyou_footer',$this->_values));

        CRM_Utils_System::setTitle($this->_values['thankyou_title']);
    }
    
    /**
     * overwrite action, since we are only showing elements in frozen mode
     * no help display needed
     * @return int
     * @access public
     */
    function getAction( ) 
    {
        if ( $this->_action & CRM_Core_Action::PREVIEW ) {
            return CRM_Core_Action::VIEW | CRM_Core_Action::PREVIEW;
        } else {
            return CRM_Core_Action::VIEW;
        }
    }
    
    /**
     * Function to actually build the form
     *
     * @return void
     * @access public
     */
    public function buildQuickForm()
    {
        $this->assignToTemplate( );
        $productID    = $this->get ('productID');
        $option       = $this->get ('option');
        $membershipTypeID = $this->get ('membershipTypeID');

        if ( $productID ) {
            require_once 'CRM/Contribute/BAO/Premium.php';  
            CRM_Contribute_BAO_Premium::buildPremiumBlock( $this , $this->_id ,false ,$productID, $option);
        }

        $params = $this->_params;
     
        $honor_block_is_active = $this->get( 'honor_block_is_active'); 
        if ( $honor_block_is_active &&
             ( ( ! empty( $params["honor_first_name"] ) && ! empty( $params["honor_last_name"] ) ) ||
               ( ! empty( $params["honor_email"] ) ) ) ) {
            $this->assign( 'honor_block_is_active', $honor_block_is_active );
            $this->assign( 'honor_block_title',     $this->_values['honor_block_title'] );
          
            require_once "CRM/Core/PseudoConstant.php";
            $prefix = CRM_Core_PseudoConstant::individualPrefix();
            $honor  = CRM_Core_PseudoConstant::honor( );             
            $this->assign( 'honor_type', $honor[$params["honor_type_id"]] );
            $this->assign( 'honor_prefix', $prefix[$params["honor_prefix_id"]] );
            $this->assign( 'honor_first_name', $params["honor_first_name"] );
            $this->assign( 'honor_last_name', $params["honor_last_name"] );
            $this->assign( 'honor_email', $params["honor_email"] );
        
        }

        if ( $membershipTypeID ) {
            $transactionID     = $this->get( 'membership_trx_id' );
            $membershipAmount  = $this->get( 'membership_amount' );
            $renewalMode       = $this->get( 'renewal_mode' );
            $this->assign( 'membership_trx_id', $transactionID );
            $this->assign( 'membership_amount', $membershipAmount );
            $this->assign( 'renewal_mode'     , $renewalMode );
            
            CRM_Member_BAO_Membership::buildMembershipBlock( $this,
                                                             $this->_id,
                                                             false,
                                                             $membershipTypeID,
                                                             true );
        }
        
        $this->buildCustom( $this->_values['custom_pre_id'] , 'customPre'  );
        $this->buildCustom( $this->_values['custom_post_id'], 'customPost' );

        $this->assign( 'trxn_id', 
                       CRM_Utils_Array::value( 'trxn_id',
                                               $this->_params ) );
        $this->assign( 'receive_date', 
                       CRM_Utils_Date::mysqlToIso( $this->_params['receive_date'] ) );

        $defaults = array();
        $options = array( );
        $fields = array( );
        require_once "CRM/Core/BAO/CustomGroup.php";
        $removeCustomFieldTypes = array ('Contribution');
        foreach ( $this->_fields as $name => $dontCare ) {
            $fields[$name] = 1;
        }
        $fields['state_province'] = $fields['country'] = $fields['email'] = 1;
        $contact = $this->_params = $this->controller->exportValues( 'Main' );

        foreach ($fields as $name => $dontCare ) {
            if ( isset( $contact[$name] ) ) {
                if ( substr( $name, 0, 7 ) == 'custom_' ) {
                    $id = substr( $name, 7 );
                    $defaults[$name] = CRM_Core_BAO_CustomField::getDefaultValue( $contact[$name],
                                                                                  $id,
                                                                                  $options );
                } else {
                    $defaults[$name] = $contact[$name];
                } 
            }
        }

        $this->setDefaults( $defaults );
        require_once 'CRM/Friend/BAO/Friend.php';
        $values['entity_id'] = $this->_id;
        $values['entity_table'] = 'civicrm_contribution_page';
        
        CRM_Friend_BAO_Friend::retrieve( $values, $data ) ;

        if ( $data['is_active'] ) {               
            $friendText = ts( $data['title'] ) ;
            $this->assign( 'friendText', $friendText );
            if ( $this->_action & CRM_Core_Action::PREVIEW ) {
                $url = CRM_Utils_System::url("civicrm/friend", 
                                             "eid={$this->_id}&reset=1&action=preview&page=contribution" );
            } else {
                $url = CRM_Utils_System::url("civicrm/friend", 
                                         "eid={$this->_id}&reset=1&page=contribution" );
            }
            $this->assign( 'friendURL', $url );
        }
        
        $this->freeze();
        // can we blow away the session now to prevent hackery
        $this->controller->reset( );
    }
}

?>
