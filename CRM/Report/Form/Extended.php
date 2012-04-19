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
class CRM_Report_Form_Extended extends CRM_Report_Form {
  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array();
  protected $_baseTable = 'civicrm_contact'; function __construct() {

    parent::__construct();
  }

  function preProcess() {
    parent::preProcess();
  }

  function select() {
    parent::select();
  }
/*
 * From clause build where baseTable & fromClauses are defined
 */
  function from() {
    if(!empty($this->_baseTable)){
      $this->buildACLClause($this->_aliases['civicrm_contact']);
      $this->_from = "FROM {$this->_baseTable}   {$this->_aliases[$this->_baseTable]}";
      $availableClauses = $this->getAvailableJoins();
      foreach ( $this->fromClauses() as $fromClause ) {
        $fn = $availableClauses [$fromClause] ['callback'];
        $this->$fn();
      }
      if (strstr( $this->_from, 'civicrm_contact' )) {
        $this->_from .= $this->_aclFrom;
      }
    }
  }
  /*
   * Define any from clauses in use (child classes to override)
   */
  function fromClauses(){
   return array();
  }

  function groupBy() {
    parent::groupBy();
    //@todo - need to re-visit this - bad behaviour from pa
    if ($this->_groupBy == 'GROUP BY') {
      $this->_groupBY = NULL;
    }
    // if a stat field has been selected the do a group by
    if (!empty($this->_statFields) && empty($this->_groupBy)) {
      $this->_groupBy[] = $this->_aliases[$this->_baseTable] . ".id";
    }
    //@todo - this should be in the parent function or at parent level - perhaps build query should do this?
    if (!empty($this->_groupBy)) {
      $this->_groupBy = 'GROUP BY ' . implode(',', $this->_groupBy);
    }
  }

  function orderBy() {
    parent::orderBy();
  }

  function statistics(&$rows) {
    return parent::statistics($rows);
  }

  function postProcess() {
    if (!empty($this->_aclTable) && CRM_Utils_Array::value($this->_aclTable, $this->_aliases)) {
      $this->buildACLClause($this->_aliases[$this->_aclTable]);
    }
    parent::postProcess();
  }

  function alterDisplay(&$rows) {
    parent::alterDisplay($rows);

    //THis is all generic functionality which can hopefully go into the parent class
    // it introduces the option of defining an alter display function as part of the column definition
    // @tod tidy up the iteration so it happens in this function
    list($firstRow) = $rows;
    // no result to alter
    if (empty($firstRow)) {
      return;
    }
    $selectedFields = array_keys($firstRow);

    $alterfunctions = array();
    foreach ($this->_columns as $tablename => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $field => $specs) {

          if (in_array($tablename . '_' . $field, $selectedFields) && array_key_exists('alter_display', $specs)) {
            $alterfunctions[$tablename . '_' . $field] = $specs['alter_display'];
          }
        }
      }
    }
    if (empty( $alterfunctions ) ) {
      // - no manipulation to be done
      return;
    }
    
    foreach ( $rows as $index => &$row ) {
      foreach ( $row as $selectedfield => $value ) {
        if (array_key_exists( $selectedfield, $alterfunctions )) {
          $rows [$index] [$selectedfield] = $this->$alterfunctions [$selectedfield]( $value, $row, $selectedfield );
        }     
      }
    }
  }
  
  function getLineItemColumns() {
    return array('civicrm_line_item' =>
      array('dao' => 'CRM_Price_BAO_LineItem',
        'fields' =>
        array(
          'qty' =>
          array('title' => ts('Quantity'),
            'type' => CRM_Utils_Type::T_INT,
            'statistics' =>
            array('sum' => ts('Total Quantity Selected')),
          ),
          'unit_price' =>
          array('title' => ts('Unit Price'),
          ),
          'line_total' =>
          array('title' => ts('Line Total'),
            'type' => CRM_Utils_Type::T_MONEY,
            'statistics' =>
            array('sum' => ts('Total of Line Items')),
          ),
        ),
        'participant_count' =>
        array('title' => ts('Participant Count'),
          'statistics' =>
          array('sum' => ts('Total Participants')),
        ),
        'filters' =>
        array('qty' =>
          array('title' => ts('Quantity'),
            'type' => CRM_Utils_Type::T_INT,
            'operator' => CRM_Report_Form::OP_INT,
          ),
        ),
        'group_bys' =>
        array('price_field_id' =>
          array('title' => ts('Price Field'),
          ),
          'price_field_value_id' =>
          array('title' => ts('Price Field Option'),
          ),
        ),
      ),
    );
  }
  
  function getPriceFieldValueColumns() {
    return array('civicrm_price_field_value' =>
      array('dao' => 'CRM_Price_BAO_FieldValue',
        'fields' => array('price_field_value_label' =>
          array('title' => ts('Price Field Value Label'),
            'name' => 'label',
          ),
        ),
        'filters' =>
        array('price_field_value_label' =>
          array('title' => ts('Price Fields Value Label'),
            'type' => CRM_Utils_Type::T_STRING,
            'operator' => 'like',
            'name' => 'label',
          ),
        ),
        'order_bys' =>
        array('label' =>
          array('title' => ts('Price Field Value Label'),
          ),
        ),
        'group_bys' =>
        //note that we have a requirement to group by label such that all 'Promo book' lines
        // are grouped together across price sets but there may be a separate need to group
        // by id so that entries in one price set are distinct from others. Not quite sure what
        // to call the distinction for end users benefit
        array('price_field_value_label' =>
          array('title' => ts('Price Field Value Label'),
            'name' => 'label',
          ),
        ),
      ),
    );
  }

  function getPriceFieldColumns() {
    return array('civicrm_price_field' =>
      array('dao' => 'CRM_Price_BAO_Field',
        'fields' =>
        array('price_field_label' =>
          array('title' => ts('Price Field Label'),
            'name' => 'label',
          ),
        ),
        'filters' =>
        array('price_field_label' =>
          array('title' => ts('Price Field Label'),
            'type' => CRM_Utils_Type::T_STRING,
            'operator' => 'like',
            'name' => 'label',
          ),
        ),
        'group_bys' =>
        array('price_field_label' =>
          array('title' => ts('Price Field Label'),
            'name' => 'label',
          ),
        ),
      ),
    );
  }

  function getParticipantColumns() {
    static $_events;
    if (!isset($_events['all'])) {
      CRM_Core_PseudoConstant::populate($_events['all'], 'CRM_Event_DAO_Event', FALSE, 'title', 'is_active', "is_template IS NULL OR is_template = 0", 'end_date DESC');
    }
    return array('civicrm_participant' =>
      array('dao' => 'CRM_Event_DAO_Participant',
        'fields' =>
        array('participant_id' => array('title' => 'Participant ID'),
          'participant_record' => array('name' => 'id',
            'title' => 'Participant Id',
          ),
          'event_id' => array('title' => ts('Event ID'),
            'type' => CRM_Utils_Type::T_STRING,
            'alter_display' => 'alterEventID',
          ),
          'status_id' => array('title' => ts('Status'),
            'alter_display' => 'alterParticipantStatus',
          ),
          'role_id' => array('title' => ts('Role'),
            'alter_display' => 'alterParticipantRole',
          ),
          'participant_fee_level' => NULL,
          'participant_fee_amount' => NULL,
          'participant_register_date' => array('title' => ts('Registration Date')),
        ),
        'grouping' => 'event-fields',
        'filters' =>
        array('event_id' => array('name' => 'event_id',
            'title' => ts('Event'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $_events['all'],
          ),
          'sid' => array('name' => 'status_id',
            'title' => ts('Participant Status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Event_PseudoConstant::participantStatus(NULL, NULL, 'label'),
          ),
          'rid' => array('name' => 'role_id',
            'title' => ts('Participant Role'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Event_PseudoConstant::participantRole(),
          ),
          'participant_register_date' => array('title' => ' Registration Date',
            'operatorType' => CRM_Report_Form::OP_DATE,
          ),
        ),
        'order_bys' =>
        array('event_id' =>
          array('title' => ts('Event'), 'default_weight' => '1', 'default_order' => 'ASC'),
        ),
        'group_bys' =>
        array('event_id' =>
          array('title' => ts('Event')),
        ),
      ),
    );
  }
  
  function getMembershipColumns() {
    return array(
      'civicrm_membership' => array(
        'dao' => 'CRM_Member_DAO_Membership', 
        'grouping' => 'member-fields', 
        'fields' => array(
          'membership_type_id' => array(
            'title' => 'Membership Type', 
            'required' => TRUE, 
            'alter_display' => 'alterMembershipTypeID' 
          ), 
          'status_id' => array(
            'title' => 'Membership Status', 
            'required' => TRUE, 
            'alter_display' => 'alterMembershipStatusID' 
          ), 
          'join_date' => NULL, 
          'start_date' => array(
            'title' => ts( 'Current Cycle Start Date' ) 
          ), 
          'end_date' => array(
            'title' => ts( 'Current Membership Cycle End Date' ) 
          ) 
        ), 
        'group_bys' => array(
          'membership_type_id' => array(
            'title' => ts( 'Membership Type' ) 
          ) 
        ), 
        'filters' => array(
          'join_date' => array(
            'type' => CRM_Utils_Type::T_DATE, 
            'operatorType' => CRM_Report_Form::OP_DATE 
          ) 
        ) 
      ) 
    );
  }
  function getMembershipTypeColumns() {
    require_once 'CRM/Member/PseudoConstant.php';
    return array(
      'civicrm_membership_type' => array(
        'dao' => 'CRM_Member_DAO_MembershipType', 
        'grouping' => 'member-fields', 
        'filters' => array(
          'gid' => array(
            'name' => 'id', 
            'title' => ts( 'Membership Types' ), 
            'operatorType' => CRM_Report_Form::OP_MULTISELECT, 
            'type' => CRM_Utils_Type::T_INT + CRM_Utils_Type::T_ENUM, 
            'options' => CRM_Member_PseudoConstant::membershipType() 
          ) 
        ) 
      ) 
    );
  }
  function getEventColumns() {
    return array(
      'civicrm_event' => array(
        'dao' => 'CRM_Event_DAO_Event', 
        'fields' => array(
          'event_type_id' => array(
            'title' => ts( 'Event Type' ), 
            'alter_display' => 'alterEventType' 
          ) 
        ), 
        'grouping' => 'event-fields', 
        'filters' => array(
          'eid' => array(
            'name' => 'event_type_id', 
            'title' => ts( 'Event Type' ), 
            'operatorType' => CRM_Report_Form::OP_MULTISELECT, 
            'options' => CRM_Core_OptionGroup::values( 'event_type' ) 
          ), 
          'event_title' => array(
            'name' => 'title', 
            'title' => ts( 'Event Title' ), 
            'operatorType' => CRM_Report_Form::OP_STRING 
          ) 
        ), 
        'order_bys' => array(
          'event_type_id' => array(
            'title' => ts( 'Event Type' ), 
            'default_weight' => '2', 
            'default_order' => 'ASC' 
          ) 
        ) 
      ) 
    );
  }
  
  function getContributionColumns() {
    return array('civicrm_contribution' =>
      array('dao' => 'CRM_Contribute_DAO_Contribution',
        'fields' =>
        array(
          'contribution_id' => array(
            'name' => 'id',
          ),
          'contribution_type_id' => array('title' => ts('Contribution Type'),
            'default' => TRUE,
            'alter_display' => 'alterContributionType',
          ),
          'payment_instrument_id' => array('title' => ts('Payment Instrument'),
            'alter_display' => 'alterPaymentType',
          ),
          'trxn_id' => NULL,
          'receive_date' => array('default' => TRUE),
          'receipt_date' => NULL,
          'fee_amount' => NULL,
          'net_amount' => NULL,
          'total_amount' => array('title' => ts('Amount'),
            'statistics' =>
            array('sum' => ts('Total Amount')),
            'type' => CRM_Utils_Type::T_MONEY,
          ),
        ),
        'filters' =>
        array('receive_date' =>
          array('operatorType' => CRM_Report_Form::OP_DATE),
          'contribution_type_id' =>
          array('title' => ts('Contribution Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Contribute_PseudoConstant::contributionType(),
          ),
          'payment_instrument_id' =>
          array('title' => ts('Payment Type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Contribute_PseudoConstant::paymentInstrument(),
          ),
          'contribution_status_id' =>
          array('title' => ts('Contribution Status'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Contribute_PseudoConstant::contributionStatus(),
          ),
          'total_amount' =>
          array('title' => ts('Contribution Amount')),
        ),
        'group_bys' =>
        array('contribution_type_id' =>
          array('title' => ts('Contribution Type')),
          'payment_instrument_id' =>
          array('title' => ts('Payment Instrument')),
        ),
        'grouping' => 'contribution-fields',
      ),
    );
  }
  
  function getContactColumns() {
    return array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact', 
        'fields' => array(
          'display_name' => array(
            'title' => ts( 'Contact Name' ) 
          ), 
          'id' => array(
            'title' => ts( 'Contact ID' ), 
            'alter_display' => 'alterContactID' 
          ), 
          'first_name' => array(
            'title' => ts( 'First Name' ) 
          ), 
          'last_name' => array(
            'title' => ts( 'Last Name' ) 
          ), 
          'nick_name' => array(
            'title' => ts( 'Nick Name' ) 
          ) 
        ), 
        'filters' => array(
          'id' => array(
            'title' => ts( 'Contact ID' ) 
          )
          , 
          'sort_name' => array(
            'title' => ts( 'Contact Name' ) 
          ) 
        ), 
        'grouping' => 'contact-fields', 
        'order_bys' => array(
          'sort_name' => array(
            'title' => ts( 'Last Name, First Name' ), 
            'default' => '1', 
            'default_weight' => '0', 
            'default_order' => 'ASC' 
          ) 
        ) 
      ) 
    );
  }
  
  /*
     * function for adding address fields to construct function in reports
     * @param bool $groupBy Add GroupBy? Not appropriate for detail report
     * @param bool $orderBy Add GroupBy? Not appropriate for detail report
     * @return array address fields for construct clause
     */
  function getAddressColumns($groupBy = TRUE, $orderBy = FALSE, $filters = TRUE, $defaults = array('country_id' => TRUE)) {
    $addressFields = array(
      'civicrm_address' => array(
        'dao' => 'CRM_Core_DAO_Address', 
        'fields' => array(
          'name' => array(
            'title' => ts( 'Address Name' ), 
            'default' => CRM_Utils_Array::value( 'name', $defaults, FALSE ) 
          ), 
          'street_address' => array(
            'title' => ts( 'Street Address' ), 
            'default' => CRM_Utils_Array::value( 'street_address', $defaults, FALSE ) 
          ), 
          'supplemental_address_1' => array(
            'title' => ts( 'Supplementary Address Field 1' ), 
            'default' => CRM_Utils_Array::value( 'supplemental_address_1', $defaults, FALSE ) 
          ), 
          'supplemental_address_2' => array(
            'title' => ts( 'Supplmentary Address Field 2' ), 
            'default' => CRM_Utils_Array::value( 'supplemental_address_2', $defaults, FALSE ) 
          ), 
          'street_number' => array(
            'name' => 'street_number', 
            'title' => ts( 'Street Number' ), 
            'type' => 1, 
            'default' => CRM_Utils_Array::value( 'street_number', $defaults, FALSE ) 
          ), 
          'street_name' => array(
            'name' => 'street_name', 
            'title' => ts( 'Street Name' ), 
            'type' => 1, 
            'default' => CRM_Utils_Array::value( 'street_name', $defaults, FALSE ) 
          ), 
          'street_unit' => array(
            'name' => 'street_unit', 
            'title' => ts( 'Street Unit' ), 
            'type' => 1, 
            'default' => CRM_Utils_Array::value( 'street_unit', $defaults, FALSE ) 
          ), 
          
          'city' => array(
            'title' => ts( 'City' ), 
            'default' => CRM_Utils_Array::value( 'city', $defaults, FALSE ) 
          ), 
          'postal_code' => array(
            'title' => ts( 'Postal Code' ), 
            'default' => CRM_Utils_Array::value( 'postal_code', $defaults, FALSE ) 
          ), 
          'county_id' => array(
            'title' => ts( 'County' ), 
            'default' => CRM_Utils_Array::value( 'county_id', $defaults, FALSE ), 
            'alter_display' => 'alterCountyID' 
          ), 
          'state_province_id' => array(
            'title' => ts( 'State/Province' ), 
            'default' => CRM_Utils_Array::value( 'state_province_id', $defaults, FALSE ), 
            'alter_display' => 'alterStateProvinceID' 
          ), 
          'country_id' => array(
            'title' => ts( 'Country' ), 
            'default' => CRM_Utils_Array::value( 'country_id', $defaults, FALSE ), 
            'alter_display' => 'alterCountryID' 
          ) 
        ), 
        'grouping' => 'location-fields' 
      ) 
    );
    
    if ($filters) {
      $addressFields ['civicrm_address'] ['filters'] = array(
        'street_number' => array(
          'title' => ts( 'Street Number' ), 
          'type' => 1, 
          'name' => 'street_number' 
        ), 
        'street_name' => array(
          'title' => ts( 'Street Name' ), 
          'name' => 'street_name', 
          'operator' => 'like' 
        ), 
        'postal_code' => array(
          'title' => ts( 'Postal Code' ), 
          'type' => 1, 
          'name' => 'postal_code' 
        ), 
        'city' => array(
          'title' => ts( 'City' ), 
          'operator' => 'like', 
          'name' => 'city' 
        ), 
        'county_id' => array(
          'name' => 'county_id', 
          'title' => ts( 'County' ), 
          'type' => CRM_Utils_Type::T_INT, 
          'operatorType' => CRM_Report_Form::OP_MULTISELECT, 
          'options' => CRM_Core_PseudoConstant::county() 
        ), 
        'state_province_id' => array(
          'name' => 'state_province_id', 
          'title' => ts( 'State/Province' ), 
          'type' => CRM_Utils_Type::T_INT, 
          'operatorType' => CRM_Report_Form::OP_MULTISELECT, 
          'options' => CRM_Core_PseudoConstant::stateProvince() 
        ), 
        'country_id' => array(
          'name' => 'country_id', 
          'title' => ts( 'Country' ), 
          'type' => CRM_Utils_Type::T_INT, 
          'operatorType' => CRM_Report_Form::OP_MULTISELECT, 
          'options' => CRM_Core_PseudoConstant::country() 
        ) 
      );
    }
    
    if ($orderBy) {
      $addressFields ['civicrm_address'] ['order_bys'] = array(
        'street_name' => array(
          'title' => ts( 'Street Name' ) 
        ), 
        'street_number' => array(
          'title' => 'Odd / Even Street Number' 
        ), 
        'street_address' => NULL, 
        'city' => NULL, 
        'postal_code' => NULL 
      );
    }
    
    if ($groupBy) {
      $addressFields ['civicrm_address'] ['group_bys'] = array(
        'street_address' => NULL, 
        'city' => NULL, 
        'postal_code' => NULL, 
        'state_province_id' => array(
          'title' => ts( 'State/Province' ) 
        ), 
        'country_id' => array(
          'title' => ts( 'Country' ) 
        ), 
        'county_id' => array(
          'title' => ts( 'County' ) 
        ) 
      );
    }
    return $addressFields;
  
  }
  
  /*
   * Get Information about advertised Joins
   */
  function getAvailableJoins() {
    return array(
      'priceFieldValue_from_lineItem' => array(
        'leftTable' => 'civicrm_line_item', 
        'rightTable' => 'civicrm_price_field_value', 
        'callback' => 'joinPriceFieldValueFromLineItem' 
      ), 
      'priceField_from_lineItem' => array(
        'leftTable' => 'civicrm_line_item', 
        'rightTable' => 'civicrm_price_field', 
        'callback' => 'joinPriceFieldFromLineItem' 
      ), 
      'participant_from_lineItem' => array(
        'leftTable' => 'civicrm_line_item', 
        'rightTable' => 'civicrm_participant', 
        'callback' => 'joinParticipantFromLineItem' 
      ), 
      'contribution_from_lineItem' => array(
        'leftTable' => 'civicrm_line_item', 
        'rightTable' => 'civicrm_contribution', 
        'callback' => 'joinContributionFromLineItem' 
      ), 
      'membership_from_lineItem' => array(
        'leftTable' => 'civicrm_line_item', 
        'rightTable' => 'civicrm_membership', 
        'callback' => 'joinMembershipFromLineItem' 
      ), 
      'contribution_from_participant' => array(
        'leftTable' => 'civicrm_participant', 
        'rightTable' => 'civicrm_contribution', 
        'callback' => 'joinParticipantFromContribution' 
      ), 
      'contribution_from_membership' => array(
        'leftTable' => 'civicrm_membership', 
        'rightTable' => 'civicrm_contribution', 
        'callback' => 'joinContributionFromMembership' 
      ), 
      'membership_from_contribution' => array(
        'leftTable' => 'civicrm_contribution', 
        'rightTable' => 'civicrm_membership', 
        'callback' => 'joinMembershipFromContribution' 
      ), 
      'membershipType_from_membership' => array(
        'leftTable' => 'civicrm_membership', 
        'rightTable' => 'civicrm_membership_type', 
        'callback' => 'joinMembershipTypeFromMembership' 
      ), 
      'lineItem_from_contribution' => array(
        'leftTable' => 'civicrm_contribution', 
        'rightTable' => 'civicrm_line_item', 
        'callback' => 'joinLineItemFromContribution' 
      ), 
      'lineItem_from_membership' => array(
        'leftTable' => 'civicrm_membership', 
        'rightTable' => 'civicrm_line_item', 
        'callback' => 'joinLineItemFromMembership' 
      ), 
      'contact_from_participant' => array(
        'leftTable' => 'civicrm_participant', 
        'rightTable' => 'civicrm_contact', 
        'callback' => 'joinContactFromParticipant' 
      ), 
      'contact_from_membership' => array(
        'leftTable' => 'civicrm_membership', 
        'rightTable' => 'civicrm_contact', 
        'callback' => 'joinContactFromMembership' 
      ), 
      'contact_from_contribution' => array(
        'leftTable' => 'civicrm_contribution', 
        'rightTable' => 'civicrm_contact', 
        'callback' => 'joinContactFromContribution' 
      ), 
      'event_from_participant' => array(
        'leftTable' => 'civicrm_participant', 
        'rightTable' => 'civicrm_event', 
        'callback' => 'joinEventFromParticipant' 
      ), 
      'address_from_contact' => array(
        'leftTable' => 'civicrm_contact', 
        'rightTable' => 'civicrm_address', 
        'callback' => 'joinAddressFromContact' 
      ) 
    );
  }
  
  function joinAddressFromContact() {
    $this->_from .= " LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
      ON {$this->_aliases['civicrm_address']}.contact_id = {$this->_aliases['civicrm_contact']}.id";
  }
  
  function joinPriceFieldValueFromLineItem() {
    $this->_from .= " LEFT JOIN civicrm_price_field_value {$this->_aliases['civicrm_price_field_value']}
                          ON {$this->_aliases['civicrm_line_item']}.price_field_value_id = {$this->_aliases['civicrm_price_field_value']}.id";
  }
  
  function joinPriceFieldFromLineItem() {
    $this->_from .= "
       LEFT JOIN civicrm_price_field {$this->_aliases['civicrm_price_field']}
      ON {$this->_aliases['civicrm_line_item']}.price_field_id = {$this->_aliases['civicrm_price_field']}.id
     ";
  }
  /*
   * Define join from line item table to participant table
   */
  function joinParticipantFromLineItem() {
    $this->_from .= " LEFT JOIN civicrm_participant {$this->_aliases['civicrm_participant']}
      ON ( {$this->_aliases['civicrm_line_item']}.entity_id = {$this->_aliases['civicrm_participant']}.id
      AND {$this->_aliases['civicrm_line_item']}.entity_table = 'civicrm_participant')
    ";
  }
  
  /*
   * Define join from line item table to Membership table. Seems to be still via contribution
   * as the entity. Have made 'inner' to restrict does that make sense?
   */
  function joinMembershipFromLineItem() {
    $this->_from .= " INNER JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']}
      ON ( {$this->_aliases['civicrm_line_item']}.entity_id = {$this->_aliases['civicrm_contribution']}.id
      AND {$this->_aliases['civicrm_line_item']}.entity_table = 'civicrm_contribution')
      LEFT JOIN civicrm_membership_payment pp
      ON {$this->_aliases['civicrm_contribution']}.id = pp.contribution_id
      LEFT JOIN civicrm_membership {$this->_aliases['civicrm_membership']}
      ON pp.membership_id = {$this->_aliases['civicrm_membership']}.id
    ";
  }
  /*
   * Define join from Participant to Contribution table
   */
  function joinContributionFromParticipant() {
    $this->_from .= " LEFT JOIN civicrm_participant_payment pp
        ON {$this->_aliases['civicrm_participant']}.id = pp.participant_id
        LEFT JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']}
        ON pp.contribution_id = {$this->_aliases['civicrm_contribution']}.id
      ";
  }
  
  /*
   * Define join from Membership to Contribution table
   */
  function joinContributionFromMembership() {
    $this->_from .= " LEFT JOIN civicrm_membership_payment pp
        ON {$this->_aliases['civicrm_membership']}.id = pp.membership_id
        LEFT JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']}
        ON pp.contribution_id = {$this->_aliases['civicrm_contribution']}.id
      ";
  }
  
  function joinParticipantFromContribution() {
    $this->_from .= " LEFT JOIN civicrm_participant_payment pp
                          ON {$this->_aliases['civicrm_contribution']}.id = pp.contribution_id
        LEFT JOIN civicrm_participant {$this->_aliases['civicrm_participant']}
                          ON pp.participant_id = {$this->_aliases['civicrm_participant']}.id";
  }
  
  function joinMembershipFromContribution() {
    $this->_from .= "
       LEFT JOIN civicrm_membership_payment pp
      ON {$this->_aliases['civicrm_contribution']}.id = pp.contribution_id
      LEFT JOIN civicrm_membership {$this->_aliases['civicrm_membership']}
      ON pp.membership_id = {$this->_aliases['civicrm_membership']}.id";
  }
  
  function joinMembershipTypeFromMembership() {
    $this->_from .= "
       LEFT JOIN civicrm_membership_type {$this->_aliases['civicrm_membership_type']}
      ON {$this->_aliases['civicrm_membership']}.membership_type_id = {$this->_aliases['civicrm_membership_type']}.id
      ";
  }
  
  function joinContributionFromLineItem() {
    
    // this can be stored as a temp table & indexed for more speed. Not done at this state.
    // another option is to cache it but I haven't tried to put that code in yet (have used it before for one hour caching
    $this->_from .= "  LEFT JOIN (SELECT line_item_civireport.id as lid, contribution_civireport_direct.*
FROM civicrm_line_item line_item_civireport
LEFT JOIN civicrm_contribution contribution_civireport_direct
                       ON (line_item_civireport.line_total > 0 AND line_item_civireport.entity_id = contribution_civireport_direct.id AND line_item_civireport.entity_table = 'civicrm_contribution')


WHERE 	contribution_civireport_direct.id IS NOT NULL

UNION SELECT line_item_civireport.id as lid, contribution_civireport.*
			FROM civicrm_line_item line_item_civireport
			LEFT JOIN civicrm_participant participant_civireport
                          ON (line_item_civireport.line_total > 0 AND line_item_civireport.entity_id = participant_civireport.id AND line_item_civireport.entity_table = 'civicrm_participant')

LEFT JOIN civicrm_participant_payment pp
                          ON participant_civireport.id = pp.participant_id
        LEFT JOIN civicrm_contribution contribution_civireport
                          ON pp.contribution_id = contribution_civireport.id

UNION SELECT line_item_civireport.id as lid,contribution_civireport.*
			FROM civicrm_line_item line_item_civireport
			LEFT JOIN civicrm_membership membership_civireport
                          ON (line_item_civireport.line_total > 0 AND line_item_civireport.entity_id =membership_civireport.id AND line_item_civireport.entity_table = 'civicrm_membership')

LEFT JOIN civicrm_membership_payment pp
                          ON membership_civireport.id = pp.membership_id
        LEFT JOIN civicrm_contribution contribution_civireport
                          ON pp.contribution_id = contribution_civireport.id
) as {$this->_aliases['civicrm_contribution']}
  ON {$this->_aliases['civicrm_contribution']}.lid = {$this->_aliases['civicrm_line_item']}.id
 ";
  }
  
  function joinLineItemFromContribution() {
    
    // this can be stored as a temp table & indexed for more speed. Not done at this stage.
    // another option is to cache it but I haven't tried to put that code in yet (have used it before for one hour caching
    $this->_from .= "
       LEFT JOIN (
SELECT contribution_civireport_direct.id AS contid, line_item_civireport.*
FROM civicrm_contribution contribution_civireport_direct
LEFT JOIN civicrm_line_item line_item_civireport ON (line_item_civireport.line_total > 0 AND line_item_civireport.entity_id = contribution_civireport_direct.id AND line_item_civireport.entity_table = 'civicrm_contribution')
WHERE line_item_civireport.id IS NOT NULL

UNION
SELECT contribution_civireport_direct.id AS contid, line_item_civireport.*
FROM civicrm_contribution contribution_civireport_direct
LEFT JOIN civicrm_participant_payment pp ON contribution_civireport_direct.id = pp.contribution_id
LEFT JOIN civicrm_participant p ON pp.participant_id = p.id
LEFT JOIN civicrm_line_item line_item_civireport ON (line_item_civireport.line_total > 0 AND line_item_civireport.entity_id = p.id AND line_item_civireport.entity_table = 'civicrm_participant')
WHERE line_item_civireport.id IS NOT NULL

UNION

SELECT contribution_civireport_direct.id AS contid, line_item_civireport.*
FROM civicrm_contribution contribution_civireport_direct
LEFT JOIN civicrm_membership_payment pp ON contribution_civireport_direct.id = pp.contribution_id
LEFT JOIN civicrm_membership p ON pp.membership_id = p.id
LEFT JOIN civicrm_line_item line_item_civireport ON (line_item_civireport.line_total > 0 AND line_item_civireport.entity_id = p.id AND line_item_civireport.entity_table = 'civicrm_membership')
WHERE 	line_item_civireport.id IS NOT NULL
) as {$this->_aliases['civicrm_line_item']}
  ON {$this->_aliases['civicrm_line_item']}.contid = {$this->_aliases['civicrm_contribution']}.id


  ";
  }
  function joinLineItemFromMembership() {
    
    // this can be stored as a temp table & indexed for more speed. Not done at this stage.
    // another option is to cache it but I haven't tried to put that code in yet (have used it before for one hour caching
    $this->_from .= "
       LEFT JOIN (
SELECT contribution_civireport_direct.id AS contid, line_item_civireport.*
FROM civicrm_contribution contribution_civireport_direct
LEFT JOIN civicrm_line_item line_item_civireport
ON (line_item_civireport.line_total > 0 AND line_item_civireport.entity_id = contribution_civireport_direct.id AND line_item_civireport.entity_table = 'civicrm_contribution')

WHERE 	line_item_civireport.id IS NOT NULL

UNION

SELECT contribution_civireport_direct.id AS contid, line_item_civireport.*
FROM civicrm_contribution contribution_civireport_direct
LEFT JOIN civicrm_membership_payment pp ON contribution_civireport_direct.id = pp.contribution_id
LEFT JOIN civicrm_membership p ON pp.membership_id = p.id
LEFT JOIN civicrm_line_item line_item_civireport ON (line_item_civireport.line_total > 0 AND line_item_civireport.entity_id = p.id AND line_item_civireport.entity_table = 'civicrm_membership')
WHERE 	line_item_civireport.id IS NOT NULL
) as {$this->_aliases['civicrm_line_item']}
  ON {$this->_aliases['civicrm_line_item']}.contid = {$this->_aliases['civicrm_contribution']}.id
  ";
  }
  
  function joinContactFromParticipant() {
    $this->_from .= " LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']}
                          ON {$this->_aliases[civicrm_participant]}.contact_id = {$this->_aliases['civicrm_contact']}.id";
  }
  
  function joinContactFromMembership() {
    $this->_from .= " LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']}
                          ON {$this->_aliases[civicrm_membership]}.contact_id = {$this->_aliases['civicrm_contact']}.id";
  }
  
  function joinContactFromContribution() {
    $this->_from .= " LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']}
                          ON {$this->_aliases['civicrm_contribution']}.contact_id = {$this->_aliases['civicrm_contact']}.id";
  }
  
  function joinEventFromParticipant() {
    $this->_from .= "  LEFT JOIN civicrm_event {$this->_aliases['civicrm_event']}
                    ON ({$this->_aliases['civicrm_event']}.id = {$this->_aliases['civicrm_participant']}.event_id ) AND
                       ({$this->_aliases['civicrm_event']}.is_template IS NULL OR
                        {$this->_aliases['civicrm_event']}.is_template = 0)";
  }
  
  /*
    * Retrieve text for contribution type from pseudoconstant
    */
  function alterContributionType($value, &$row) {
    return is_string( CRM_Contribute_PseudoConstant::contributionType( $value, FALSE ) ) ? CRM_Contribute_PseudoConstant::contributionType( $value, FALSE ) : '';
  }
  /*
    * Retrieve text for contribution status from pseudoconstant
    */
  function alterContributionStatus($value, &$row) {
    return CRM_Contribute_PseudoConstant::contributionStatus( $value );
  }
  /*
    * Retrieve text for payment instrument from pseudoconstant
    */
  function alterEventType($value, &$row) {
    return CRM_Event_PseudoConstant::eventType( $value );
  }
  
  function alterEventID($value, &$row) {
    return is_string( CRM_Event_PseudoConstant::event( $value, FALSE ) ) ? CRM_Event_PseudoConstant::event( $value, FALSE ) : '';
  }
  
  function alterMembershipTypeID($value, &$row) {
    require_once 'CRM/Member/PseudoConstant.php';
    return is_string( CRM_Member_PseudoConstant::membershipType( $value, FALSE ) ) ? CRM_Member_PseudoConstant::membershipType( $value, FALSE ) : '';
  }
  
  function alterMembershipStatusID($value, &$row) {
    require_once 'CRM/Member/PseudoConstant.php';
    return is_string( CRM_Member_PseudoConstant::membershipStatus( $value, FALSE ) ) ? CRM_Member_PseudoConstant::membershipStatus( $value, FALSE ) : '';
  }
  function alterCountryID($value, &$row) {
    return CRM_Core_PseudoConstant::country( $value, FALSE );
  }
  function alterCountyID($value, &$row) {
    return CRM_Core_PseudoConstant::county( $value, FALSE );
  }
  function alterStateProvinceID($value, &$row) {
    require_once 'CRM/Core/PseudoConstant.php';
    $url = CRM_Utils_System::url( CRM_Utils_System::currentPath(), "reset=1&force=1&state_province_id_op=in&state_province_id_value={$value}", $this->_absoluteUrl );
    $row ['civicrm_address_state_province_id_link'] = $url;
    $row ['civicrm_address_state_province_id_hover'] = ts( "%1 for this state.", array(
      1 => $urltxt 
    ) );
    
    return CRM_Core_PseudoConstant::stateProvince( $value, FALSE );
  }
  function alterContactID($value, &$row, $fieldname) {
    $row [$fieldname . '_link'] = CRM_Utils_System::url( "civicrm/contact/view", 'reset=1&cid=' . $value, $this->_absoluteUrl );
    return $value;
  }
  
  function alterParticipantStatus($value) {
    if (empty( $value )) {
      return;
    }
    return CRM_Event_PseudoConstant::participantStatus( $value, FALSE, 'label' );
  }
  
  function alterParticipantRole($value) {
    if (empty( $value )) {
      return;
    }
    $roles = explode( CRM_Core_DAO::VALUE_SEPARATOR, $value );
    $value = array();
    foreach ( $roles as $role ) {
      $value [$role] = CRM_Event_PseudoConstant::participantRole( $role, FALSE );
    }
    return implode( ', ', $value );
  }
  
  function alterPaymentType($value) {
    $paymentInstruments = CRM_Contribute_PseudoConstant::paymentInstrument();
    return $paymentInstruments [$value];
  }
}

