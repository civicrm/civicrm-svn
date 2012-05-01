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

    // redirect to batch entry page.
    $session = CRM_Core_Session::singleton( );
    $session->replaceUserContext(CRM_Utils_System::url( 'civicrm/batch', "reset=1" ));
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

    //should we restrict number of fields for batch entry
    //$this->_fields  = array_slice($this->_fields, 0, $this->_maxFields);

    $this->addButtons( array(
      array ( 'type'      => 'submit',
      'name'      => ts('Validate & Process the Batch'),
      'isDefault' => true   ),
      array ( 'type'      => 'cancel',
      'name'      => ts('Save & Continue Later') )
      )
    );

    $this->assign( 'rowCount', $this->_batchInfo['item_count'] + 1 );

    $fileFieldExists = false;

    for ( $rowNumber = 1; $rowNumber<= $this->_batchInfo['item_count']; $rowNumber++  ) {
      CRM_Contact_Form_NewContact::buildQuickForm( $this, $rowNumber, null, true, 'primary_' );

      foreach ( $this->_fields as $name => $field ) {
        CRM_Core_BAO_UFGroup::buildProfile( $this, $field, null, null, false, false, $rowNumber );
      }
    }

    $this->assign( 'fields', $this->_fields );

    // don't set the status message when form is submitted.
    $buttonName = $this->controller->getButtonName('submit');

    if ( $suppressFields && $buttonName != '_qf_Entry_next' ) {
      CRM_Core_Session::setStatus( "FILE or Autocomplete Select type field(s) in the selected profile are not supported for Batch Update and have been excluded." );
    }
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
    
    // get the profile information
    if ( $this->_batchInfo['type_id'] == 1) {
      $this->processContribution( $params );
    } else {
      $this->processMembership( $params );
    }

    // update batch to close status
    $paramValues = array( 
      'id'  => $this->_batchId,
      'status_id' => 2 );

    CRM_Core_BAO_Batch::create( $paramValues ); 

    // delete from cache table
    $cacheKeyString = CRM_Core_BAO_Batch::getCacheKeyForBatch( $this->_batchId );
    CRM_Core_BAO_Cache::deleteGroup( 'batch entry', $cacheKeyString, false );

    CRM_Core_Session::setStatus("Your batch is processed.");
    CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/batch', "reset=1" ) );
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
    $dates = array( 'receive_date',
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

        // handle soft credit
        if ( !CRM_Utils_Array::value( $key, $params['soft_credit_contact_select_id'] ) ) {
          $value['soft_credit_to'] = CRM_Utils_Array::value( $key, $params['soft_credit_contact_select_id'] );                          
        }

        $value['custom'] = CRM_Core_BAO_CustomField::postProcess( $value,
          CRM_Core_DAO::$_nullObject,
          $key,
          'Contribution' );

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

        unset($value['contribution_note']);
        unset($value['contribution_type']);
        unset($value['contribution_source']);

        $value['batch_id'] = $this->_batchId;
        $contribution = CRM_Contribute_BAO_Contribution::create( $value, CRM_Core_DAO::$_nullArray ); 
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
    if ( isset( $params['field'] ) ) {
      $customFields = array( );
      foreach ( $params['field'] as $key => $value ) {               
        // if contact is not selected we should skip the row
        if ( !CRM_Utils_Array::value( $key, $params['primary_contact_select_id'] ) ) {
          continue;
        }

        $value['contact_id'] = CRM_Utils_Array::value( $key, $params['primary_contact_select_id'] );                          
 
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

        $membership = CRM_Member_BAO_Membership::add( $value, CRM_Core_DAO::$_nullArray );

        // add custom field values           
        if ( CRM_Utils_Array::value( 'custom', $value ) &&
          is_array( $value['custom'] ) ) {
            CRM_Core_BAO_CustomValueTable::store( $value['custom'], 'civicrm_membership', $membership->id );
          }            
      }
    }
  }
} 
