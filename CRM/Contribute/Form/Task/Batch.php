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

require_once 'CRM/Profile/Form.php';
require_once 'CRM/Contribute/Form/Task.php';

/**
 * This class provides the functionality for batch profile update for contributions
 */
class CRM_Contribute_Form_Task_Batch extends CRM_Contribute_Form_Task {

    /**
     * the title of the group
     *
     * @var string
     */
    protected $_title;

    /**
     * maximum profile fields that will be displayed
     *
     */
    protected $_maxFields = 9;


    /**
     * variable to store redirect path
     *
     */
    protected $_userContext;


    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) 
    {
        /*
         * initialize the task and row fields
         */
        parent::preProcess( );
        
        //get the contact read only fields to display.
        require_once 'CRM/Core/BAO/Preferences.php';
        $readOnlyFields = array_merge( array( 'sort_name' => ts( 'Name' ) ),
                                       CRM_Core_BAO_Preferences::valueOptions( 'contact_autocomplete_options',
                                                                               true, null, false, 'name', true ) );
        //get the read only field data.
        $returnProperties  = array_fill_keys( array_keys( $readOnlyFields ), 1 );
        require_once 'CRM/Contact/BAO/Contact/Utils.php';
        $contactDetails = CRM_Contact_BAO_Contact_Utils::contactDetails( $this->_contributionIds, 
                                                                         'CiviContribute', $returnProperties );
        $this->assign( 'contactDetails', $contactDetails );
        $this->assign( 'readOnlyFields', $readOnlyFields );
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) 
    {
        $ufGroupId = $this->get('ufGroupId');
        
        if ( ! $ufGroupId ) {
            CRM_Core_Error::fatal( 'ufGroupId is missing' );
        }
        require_once "CRM/Core/BAO/UFGroup.php";
        require_once "CRM/Core/BAO/CustomGroup.php";
        $this->_title = ts('Batch Update for Contributions') . ' - ' . CRM_Core_BAO_UFGroup::getTitle ( $ufGroupId );
        CRM_Utils_System::setTitle( $this->_title );
        
        $this->addDefaultButtons( ts('Save') );
        $this->_fields  = array( );
        $this->_fields  = CRM_Core_BAO_UFGroup::getFields( $ufGroupId, false, CRM_Core_Action::VIEW );

        // remove file type field and then limit fields
        $suppressFields = false;
        $removehtmlTypes = array( 'File', 'Autocomplete-Select' );
        foreach ($this->_fields as $name => $field ) {
            if ( $cfID = CRM_Core_BAO_CustomField::getKeyID($name) && 
                 in_array( $this->_fields[$name]['html_type'], $removehtmlTypes ) ) {                        
                $suppressFields = true;
                unset($this->_fields[$name]);
            }
            
            //fix to reduce size as we are using this field in grid
            if ( is_array( $field['attributes'] ) && $this->_fields[$name]['attributes']['size'] > 19 ) {
                //shrink class to "form-text-medium"
                $this->_fields[$name]['attributes']['size'] = 19;
            }
        }

        $this->_fields  = array_slice($this->_fields, 0, $this->_maxFields);
        
        $this->addButtons( array(
                                 array ( 'type'      => 'submit',
                                         'name'      => ts('Update Contribution(s)'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        
        
        $this->assign( 'profileTitle', $this->_title );
        $this->assign( 'componentIds', $this->_contributionIds );
        $fileFieldExists = false;
 
        //load all campaigns.
        if ( array_key_exists( 'contribution_campaign_id', $this->_fields ) ) {
            $this->_componentCampaigns = array( );
            CRM_Core_PseudoConstant::populate( $this->_componentCampaigns,
                                               'CRM_Contribute_DAO_Contribution',
                                               true, 'campaign_id', 'id', 
                                               ' id IN ('. implode(' , ',array_values( $this->_contributionIds ) ) .' ) ');
        }
        
        //fix for CRM-2752
        require_once "CRM/Core/BAO/CustomField.php";
        $customFields = CRM_Core_BAO_CustomField::getFields( 'Contribution' );
        foreach ( $this->_contributionIds as $contributionId ) {
            $typeId = CRM_Core_DAO::getFieldValue( "CRM_Contribute_DAO_Contribution", $contributionId, 'financial_account_id' ); 
            foreach ( $this->_fields as $name => $field ) {
                if ( $customFieldID = CRM_Core_BAO_CustomField::getKeyID( $name ) ) {
                    $customValue = CRM_Utils_Array::value( $customFieldID, $customFields );
                    if ( CRM_Utils_Array::value( 'extends_entity_column_value', $customValue ) ) {
                        $entityColumnValue = explode( CRM_Core_DAO::VALUE_SEPARATOR,
                                                      $customValue['extends_entity_column_value'] );
                    }

                    if ( CRM_Utils_Array::value( $typeId, $entityColumnValue ) ||
                         CRM_Utils_System::isNull( $entityColumnValue[$typeId] ) ) {
                        CRM_Core_BAO_UFGroup::buildProfile( $this, $field, null, $contributionId );
                    }
                } else {
                    // handle non custom fields
                    CRM_Core_BAO_UFGroup::buildProfile( $this, $field, null, $contributionId );
                }
            }
        }
        
        $this->assign( 'fields', $this->_fields );
        
        // don't set the status message when form is submitted.
        $buttonName = $this->controller->getButtonName('submit');

        if ( $suppressFields && $buttonName != '_qf_Batch_next' ) {
            CRM_Core_Session::setStatus( "FILE or Autocomplete Select type field(s) in the selected profile are not supported for Batch Update and have been excluded." );
        }

        $this->addDefaultButtons( ts( 'Update Contributions' ) );
    }

    /**
     * This function sets the default values for the form.
     * 
     * @access public
     * @return None
     */
    function setDefaultValues( ) 
    {
        if (empty($this->_fields)) {
            return;
        }
        
        $defaults = array( );
        foreach ($this->_contributionIds as $contributionId) {
            $details[$contributionId] = array( );
            CRM_Core_BAO_UFGroup::setProfileDefaults( null, $this->_fields, $defaults, false, $contributionId, 'Contribute' );
        }
        
        return $defaults;
    }


    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() 
    {
        $params     = $this->exportValues( );
        $dates = array( 'receive_date',
                        'receipt_date',
                        'thankyou_date',
                        'cancel_date'
                        );
        if ( isset( $params['field'] ) ) {
            foreach ( $params['field'] as $key => $value ) {
                                
                $value['custom'] = CRM_Core_BAO_CustomField::postProcess( $value,
                                                                          CRM_Core_DAO::$_nullObject,
                                                                          $key,
                                                                          'Contribution' );
                                
                $ids['contribution'] = $key;
                foreach ( $dates as $val ) {
                    if ( isset( $value[$val] ) ) {
                        $value[$val] = CRM_Utils_Date::processDate( $value[$val] );
                    }
                }
                if ($value['financial_account']) {
                    $value['financial_account_id'] = $value['financial_account'];
                }

                if ( CRM_Utils_Array::value( 'payment_instrument', $value ) ) {
                    $value['payment_instrument_id'] = $value['payment_instrument'];
                }
                
                if ( CRM_Utils_Array::value( 'contribution_source', $value ) ) {
                    $value['source'] = $value['contribution_source'];
                }
                
                unset($value['financial_account']);
                unset($value['contribution_source']);
                $contribution = CRM_Contribute_BAO_Contribution::add( $value ,$ids ); 
                
                // add custom field values           
                if ( CRM_Utils_Array::value( 'custom', $value ) &&
                     is_array( $value['custom'] ) ) {
                    require_once 'CRM/Core/BAO/CustomValueTable.php';
                    CRM_Core_BAO_CustomValueTable::store( $value['custom'], 'civicrm_contribution', $contribution->id );
                }            
            }
            CRM_Core_Session::setStatus("Your updates have been saved."); 
        } else {
            CRM_Core_Session::setStatus("No updates have been saved.");
        }
    }//end of function
} 

