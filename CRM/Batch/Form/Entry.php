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

  public $_action;
  public $_mode;

  /**
   * when not to reset sort_name
   */
  protected $_preserveDefault = true;

  /**
   * Contact fields
   */
  protected $_contactFields = array();

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
      CRM_Core_BAO_Batch::retrieve( $params, $this->_batchInfo );

      $this->assign( 'batchTotal', $this->_batchInfo['total'] );
      // get the profile id associted with this batch type
      $this->_profileId = CRM_Core_BAO_Batch::getProfileId( $this->_batchInfo['type_id'] );
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

    $this->addElement('hidden', 'batch_id', $this->_batchId );

    // get the profile information
    if ( $this->_batchInfo['type_id'] == 1) {
      CRM_Utils_System::setTitle( ts('Batch Entry for Contributions') );
      $customFields = CRM_Core_BAO_CustomField::getFields( 'Contribution' );
    } else {
      CRM_Utils_System::setTitle( ts('Batch Entry for Memberships') );
      $customFields = CRM_Core_BAO_CustomField::getFields( 'Membership' );
    }

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

    $this->addFormRule( array( 'CRM_Batch_Form_Entry', 'formRule' ), $this );

    //should we restrict number of fields for batch entry
    //$this->_fields  = array_slice($this->_fields, 0, $this->_maxFields);

    // add the force save button
    $forceSave = $this->getButtonName( 'upload', 'force' );
    
    $this->addElement('submit',
      $forceSave,
      ts( 'Ignore Mismatch & Process the Batch?' ) );

    $this->addButtons( array(
      array ( 
        'type'      => 'upload',
        'name'      => ts('Validate & Process the Batch'),
        'isDefault' => true   ),
      array ( 
        'type'      => 'cancel',
        'name'      => ts('Save & Continue Later') )
      )
    );

    $this->assign( 'rowCount', $this->_batchInfo['item_count'] + 1 );

    $fileFieldExists = false;
    $preserveDefaultsArray = array( 
      'first_name', 'last_name', 'middle_name',
      'organization_name',
      'household_name');

    $contactTypes  = array( 'Contact', 'Individual', 'Household', 'Organization' );
    for ( $rowNumber = 1; $rowNumber<= $this->_batchInfo['item_count']; $rowNumber++  ) {
      CRM_Contact_Form_NewContact::buildQuickForm( $this, $rowNumber, null, true, 'primary_' );

      foreach ( $this->_fields as $name => $field ) {
        if ( in_array( $field['field_type'], $contactTypes ) ) {
          $this->_contactFields[$field['name']] = 1;
        }
        CRM_Core_BAO_UFGroup::buildProfile( $this, $field, null, null, false, false, $rowNumber );

        if ( in_array($field['name'], $preserveDefaultsArray ) ) {
          $this->_preserveDefault = false;
        }
      }
    }

    $this->assign( 'fields', $this->_fields );
    $this->assign( 'contactFields', $this->_contactFields );

    // don't set the status message when form is submitted.
    $buttonName = $this->controller->getButtonName('submit');

    if ( $suppressFields && $buttonName != '_qf_Entry_next' ) {
      CRM_Core_Session::setStatus( "FILE or Autocomplete Select type field(s) in the selected profile are not supported for Batch Update and have been excluded." );
    }
  }

  /**
   * form validations
   *
   * @param array $params   posted values of the form
   * @param array $files    list of errors to be posted back to the form
   * @param array $self     form object
   *
   * @return array list of errors to be posted back to the form
   * @static
   * @access public
   */
  static function formRule( $params, $files, $self ) {
    $errors = array();
    
    if ( CRM_Utils_Array::value( '_qf_Entry_upload_force', $params ) ) {
      return true;
    } 
     
    $batchTotal = 0;
    foreach ( $params['field'] as $key => $value ) {
      $batchTotal += $value['total_amount'];
    }
    
    if ( $batchTotal != $self->_batchInfo['total'] ) {
      $self->assign('batchAmountMismatch', true );
      $errors['_qf_defaults'] = ts('Total for amounts entered below does not match the expected batch total.');
      return $errors;
    }
    
    $self->assign('batchAmountMismatch', false );
    return true;
  }

  /**
   * Override default cancel action
   */  
  function cancelAction() {
    // redirect to batch listing
    CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/batch', 'reset=1' ) );
    CRM_Utils_System::civiExit( );    
  }

  /**
   * This function sets the default values for the form.
   * 
   * @access public
   * @return None
   */
  function setDefaultValues( ) {
    if (empty($this->_fields)) {
      return;
    }

    // get the existing batch values from cache table
    $cacheKeyString = CRM_Core_BAO_Batch::getCacheKeyForBatch( $this->_batchId );
    $defaults = CRM_Core_BAO_Cache::getItem( 'batch entry', $cacheKeyString );

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

    $params['actualBatchTotal'] = 0;
    
    // get the profile information
    if ( $this->_batchInfo['type_id'] == 1) {
      $this->processContribution( $params );
    }
    else {
      $this->processMembership( $params );
    }

    // update batch to close status
    $paramValues = array( 
      'id'        => $this->_batchId,
      'status_id' => 2, // close status
      'total'     => $params['actualBatchTotal']   
    );

    CRM_Core_BAO_Batch::create( $paramValues ); 

    // delete from cache table
    $cacheKeyString = CRM_Core_BAO_Batch::getCacheKeyForBatch( $this->_batchId );
    CRM_Core_BAO_Cache::deleteGroup( 'batch entry', $cacheKeyString, false );

    // set success status
    CRM_Core_Session::setStatus("Your batch has been processed.");
 
    CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/batch', 'reset=1&status=2' ) );
  }

  /**
   * process contribution records
   *
   * @param array $params associated array of submitted values
   *
   * @access public
   * @return None
   */
  private function processContribution( &$params ) {
    $dates = array( 
      'receive_date',
      'receipt_date',
      'thankyou_date',
      'cancel_date'
    );

    if ( isset( $params['field'] ) ) {
      foreach ( $params['field'] as $key => $value ) {
        // if contact is not selected we should skip the row
        if ( !CRM_Utils_Array::value( $key, $params['primary_contact_select_id'] ) ) {
          continue;
        }

        $value['contact_id'] = CRM_Utils_Array::value( $key, $params['primary_contact_select_id'] );                          

        // update contact information
        $this->updateContactInfo( $value );

        // handle soft credit
        if ( CRM_Utils_Array::value( $key, $params['soft_credit_contact_select_id'] ) ) {
          $value['soft_credit_to'] = $params['soft_credit_contact_select_id'][$key];                          
        }

        $value['custom'] = CRM_Core_BAO_CustomField::postProcess( $value,
          CRM_Core_DAO::$_nullObject,
          $key,
          'Contribution' );

        if ( CRM_Utils_Array::value( 'send_receipt', $value ) ) {
          $value['receipt_date'] = date("Y-m-d");
        }

        foreach ( $dates as $val ) {
          if ( isset( $value[$val] ) ) {
            $value[$val] = CRM_Utils_Date::processDate( $value[$val] );
          }
        }

        if ( $value['contribution_type'] ) {
          $value['contribution_type_id'] = $value['contribution_type'];
        }

        if ( CRM_Utils_Array::value( 'payment_instrument', $value ) ) {
          $value['payment_instrument_id'] = $value['payment_instrument'];
        }

        if ( CRM_Utils_Array::value( 'contribution_source', $value ) ) {
          $value['source'] = $value['contribution_source'];
        }

        if ( CRM_Utils_Array::value( 'contribution_note', $value ) ) {
          $value['note'] = $value['contribution_note'];
        }

        $params['actualBatchTotal'] += $value['total_amount'];

        unset($value['contribution_note']);
        unset($value['contribution_type']);
        unset($value['contribution_source']);
        
        $value['batch_id'] = $this->_batchId;
        $value['skipRecentView'] = true;
        $contribution = CRM_Contribute_BAO_Contribution::create( $value, CRM_Core_DAO::$_nullArray );

        //process premiums
        if ( CRM_Utils_Array::value( 'product_name', $value ) ) {
          if ( $value['product_name'][0] > 0 ) {
            list( $products, $options ) = CRM_Contribute_BAO_Premium::getPremiumProductInfo();
            
            $value['hidden_Premium'] = 1;
            $value['product_option'] = CRM_Utils_Array::value( 
              $value['product_name'][1], 
              $options[$value['product_name'][0]] );


            $premiumParams = array(
              'product_id'      => $value['product_name'][0],
              'contribution_id' => $contribution->id,
              'product_option'  => $value['product_option'],
              'quantity'        => 1
            );
            CRM_Contribute_BAO_Contribution::addPremium( $premiumParams );
          }    
        } // end of premium

        //send receipt mail.
        if ( $contribution->id && 
          CRM_Utils_Array::value( 'send_receipt', $value ) ) {
          
          // add the domain email id
          $domainEmail = CRM_Core_BAO_Domain::getNameAndEmail();
          $domainEmail = "$domainEmail[0] <$domainEmail[1]>";
          
          $value['from_email_address'] = $domainEmail; 
          $value['contribution_id'] = $contribution->id;
          CRM_Contribute_Form_AdditionalInfo::emailReceipt( $this, $value );
        }
      }
    }
  }//end of function

  /**
   * process membership records
   *
   * @param array $params associated array of submitted values
   *
   * @access public
   * @return None
   */
  private function processMembership( &$params ) {
    $dates = array( 
      'join_date',
      'membership_start_date',
      'membership_end_date',
      'receive_date'
    );
 
    if ( isset( $params['field'] ) ) {
      $customFields = array( );
      foreach ( $params['field'] as $key => $value ) {               
        // if contact is not selected we should skip the row
        if ( !CRM_Utils_Array::value( $key, $params['primary_contact_select_id'] ) ) {
          continue;
        }

        $value['contact_id'] = CRM_Utils_Array::value( $key, $params['primary_contact_select_id'] );                          

        // update contact information
        $this->updateContactInfo( $value );

        foreach ( $dates as $val ) {
          if ( isset( $value[$val] ) ) {
            $value[$val] = CRM_Utils_Date::processDate( $value[$val] );
          }
        }

        if ( CRM_Utils_Array::value( 'membership_source', $value ) ) {
          $value['source'] = $value['membership_source'];
        }

        if ( CRM_Utils_Array::value( 'membership_type', $value ) ) {
          $membershipTypeId = $value['membership_type_id'] = $value['membership_type'];
        }

        unset($value['membership_source']);
        unset($value['membership_type']);

        //Get the membership status
        $value['status_id'] = ( CRM_Utils_Array::value( 'membership_status', $value ) ) ? $value['membership_status'] : CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_Membership', $key, 'status_id' );
        unset( $value['membership_status'] );

        if ( empty( $customFields ) ) {
          if ( !CRM_Utils_Array::value( 'membership_type_id', $value ) ) {
            $membershipTypeId = CRM_Core_DAO::getFieldValue( 'CRM_Member_DAO_Membership', $key, 'membership_type_id' );
          }

          // membership type custom data
          $customFields = CRM_Core_BAO_CustomField::getFields( 'Membership', false, false, $membershipTypeId );

          $customFields = CRM_Utils_Array::crmArrayMerge( $customFields, 
            CRM_Core_BAO_CustomField::getFields( 'Membership',
            false, false, null, null, true ) );
        }

        //check for custom data
        $value['custom'] = CRM_Core_BAO_CustomField::postProcess( $params['field'][$key],
          $customFields,
          $key,
          'Membership',
          $membershipTypeId );

        if ( CRM_Utils_Array::value('contribution_type', $value) ) {
          $value['contribution_type_id'] = $value['contribution_type'];
        }

        if ( CRM_Utils_Array::value( 'payment_instrument', $value ) ) {
          $value['payment_instrument_id'] = $value['payment_instrument'];
        }

        // handle soft credit
        if ( CRM_Utils_Array::value( $key, $params['soft_credit_contact_select_id'] ) ) {
          $value['soft_credit_to'] = $params['soft_credit_contact_select_id'][$key];                          
        }
        
        $params['actualBatchTotal'] += $value['total_amount'];

        unset($value['contribution_type']);
        unset($value['payment_instrument']);

        $value['batch_id'] = $this->_batchId;
        $value['skipRecentView'] = true;

        // end of contribution related section

        $membership = CRM_Member_BAO_Membership::create( $value, CRM_Core_DAO::$_nullArray );

        // add custom field values           
        if ( CRM_Utils_Array::value( 'custom', $value ) &&
          is_array( $value['custom'] ) ) {
            CRM_Core_BAO_CustomValueTable::store( $value['custom'], 'civicrm_membership', $membership->id );
        }

        //process premiums
        if ( CRM_Utils_Array::value( 'product_name', $value ) ) {
          if ( $value['product_name'][0] > 0 ) {
            list( $products, $options ) = CRM_Contribute_BAO_Premium::getPremiumProductInfo();
            
            $value['hidden_Premium'] = 1;
            $value['product_option'] = CRM_Utils_Array::value( 
              $value['product_name'][1], 
              $options[$value['product_name'][0]] );

            $premiumParams = array(
              'product_id'      => $value['product_name'][0],
              'contribution_id' => $value['contribution_id'],
              'product_option'  => $value['product_option'],
              'quantity'        => 1
            );
            CRM_Contribute_BAO_Contribution::addPremium( $premiumParams );
          }    
        } // end of premium
      }
    }
  }

  /**
   * update contact information 
   *
   * @param array $value associated array of submitted values
   *
   * @access public
   * @return None
   */
  private function updateContactInfo( &$value ) {
    $value['preserveDBName'] = $this->_preserveDefault;

    //parse street address, CRM-7768
    CRM_Contact_Form_Task_Batch::parseStreetAddress( $value, $this );

    CRM_Contact_BAO_Contact::createProfileContact( $value, $this->_fields,
      $value['contact_id'] );
  }

} 
