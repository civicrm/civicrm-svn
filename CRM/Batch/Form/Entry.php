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

require_once 'CRM/Profile/Form.php';

/**
 * This class provides the functionality for batch entry for contributions/memeberships
 */
class CRM_Batch_Form_Entry extends CRM_Core_Form {

    /**
     * maximum profile fields that will be displayed
     *
     */
    protected $_rowCount = 1;

    /**
     * Batch id
     */
    protected $_batchId;

    /**
     * Batch informtaion
     */
    protected $_batchInfo = array();

    /**
     * store the profile id associated with the batch type
     */
    protected $_profileId;

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        $this->_batchId = CRM_Utils_Request::retrieve( 'id', 'Positive', $this, true );
        
        if ( empty( $this->_batchInfo ) ) {
            $params = array( 'id' => $this->_batchId );
            require_once 'CRM/Core/BAO/Batch.php';
            CRM_Core_BAO_Batch::retrieve( $params, $this->_batchInfo );

            // get the profile id associted with this batch type
            $this->_profileId = CRM_Core_BAO_Batch::getProfileId( $this->_batchInfo['batch_type_id'] );
        }
    }
    
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {

        if ( ! $this->_profileId ) {
            CRM_Core_Error::fatal( 'Profile that is used for batch entry is missing.' );
        }

        $this->addDefaultButtons( ts('Save') );
        
        // get the profile information
        require_once "CRM/Core/BAO/UFGroup.php";
        require_once "CRM/Core/BAO/CustomGroup.php";
        CRM_Utils_System::setTitle( ts('Batch entry for Contributions') );
        
        $this->_fields  = array( );
        $this->_fields  = CRM_Core_BAO_UFGroup::getFields( $this->_profileId, false, CRM_Core_Action::VIEW );

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

        //should we restrict number of fields for batch entry
        //$this->_fields  = array_slice($this->_fields, 0, $this->_maxFields);
        
        $this->addButtons( array(
                                 array ( 'type'      => 'submit',
                                         'name'      => ts('Process Batch'),
                                         'isDefault' => true   ),
                                 array ( 'type'      => 'cancel',
                                         'name'      => ts('Cancel') ),
                                 )
                           );
        
        
        $this->assign( 'rowCount', $this->_batchInfo['item_count'] );
        
        $fileFieldExists = false;
 
        //load all campaigns.
        if ( array_key_exists( 'contribution_campaign_id', $this->_fields ) ) {
            $this->_componentCampaigns = array( );
            CRM_Core_PseudoConstant::populate( $this->_componentCampaigns,
                                               'CRM_Contribute_DAO_Contribution',
                                               true, 'campaign_id', 'id', 
                                               ' id IN ('. implode(' , ',array_values( $this->_contributionIds ) ) .' ) ');
        }
        
        require_once "CRM/Core/BAO/CustomField.php";
        $customFields = CRM_Core_BAO_CustomField::getFields( 'Contribution' );
        for ( $rowNumber = 1; $rowNumber<= $this->_batchInfo['item_count']; $rowNumber++  ) {
            require_once 'CRM/Contact/Form/NewContact.php';
            CRM_Contact_Form_NewContact::buildQuickForm( $this, $rowNumber, null, true );
 
            foreach ( $this->_fields as $name => $field ) {
                CRM_Core_BAO_UFGroup::buildProfile( $this, $field, null, $rowNumber );
            }
        }
        
        $this->assign( 'fields', $this->_fields );
        
        // don't set the status message when form is submitted.
        $buttonName = $this->controller->getButtonName('submit');

        if ( $suppressFields && $buttonName != '_qf_Batch_next' ) {
            CRM_Core_Session::setStatus( "FILE or Autocomplete Select type field(s) in the selected profile are not supported for Batch Update and have been excluded." );
        }

        $this->addDefaultButtons( ts( 'Process Batch' ) );
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
        /*
        foreach ($this->_contributionIds as $contributionId) {
            $details[$contributionId] = array( );
            CRM_Core_BAO_UFGroup::setProfileDefaults( null, $this->_fields, $defaults, false, $contributionId, 'Contribute' );
        }
         */
        return $defaults;
    }


    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {
        $params = $this->controller->exportValues( $this->_name );
        
        $dates = array( 'receive_date',
                        'receipt_date',
                        'thankyou_date',
                        'cancel_date'
                        );
        if ( isset( $params['field'] ) ) {
            foreach ( $params['field'] as $key => $value ) {
                // if contact is not selected we should skip the row
                if ( !CRM_Utils_Array::value( $key, $params['contact_select_id'] ) ) {
                    continue;
                }

                $value['contact_id'] = CRM_Utils_Array::value( $key, $params['contact_select_id'] );                          
                $value['custom'] = CRM_Core_BAO_CustomField::postProcess( $value,
                                                                          CRM_Core_DAO::$_nullObject,
                                                                          $key,
                                                                          'Contribution' );
                                
                foreach ( $dates as $val ) {
                    if ( isset( $value[$val] ) ) {
                        $value[$val] = CRM_Utils_Date::processDate( $value[$val] );
                    }
                }
                if ($value['contribution_type']) {
                    $value['contribution_type_id'] = $value['contribution_type'];
                }

                if ( CRM_Utils_Array::value( 'payment_instrument', $value ) ) {
                    $value['payment_instrument_id'] = $value['payment_instrument'];
                }
                
                if ( CRM_Utils_Array::value( 'contribution_source', $value ) ) {
                    $value['source'] = $value['contribution_source'];
                }
                
                unset($value['contribution_type']);
                unset($value['contribution_source']);
                
                $contribution = CRM_Contribute_BAO_Contribution::add( $value, CRM_Core_DAO::$_nullArray ); 
                
                // add custom field values           
                if ( CRM_Utils_Array::value( 'custom', $value ) &&
                     is_array( $value['custom'] ) ) {
                    require_once 'CRM/Core/BAO/CustomValueTable.php';
                    CRM_Core_BAO_CustomValueTable::store( $value['custom'], 'civicrm_contribution', $contribution->id );
                }            
            }
            CRM_Core_Session::setStatus("Your batch is processed."); 
        }

        // redirect to batch entry page.
        $session = CRM_Core_Session::singleton( );
        $session->replaceUserContext(CRM_Utils_System::url( 'civicrm/batch', "reset=1" ));
    }//end of function
} 
