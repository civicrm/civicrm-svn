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


class CRM_Report_Form_Extended extends CRM_Report_Form {
    protected $_addressField = false;

    protected $_emailField   = false;

    protected $_summary      = null;

    protected $_customGroupExtends = array(  );
    protected $_baseTable = 'civicrm_contact';
    

    
    function __construct( ) {

        parent::__construct( );
    }

    function preProcess( ) {
        parent::preProcess( );
    }

    function select( ) {
        parent::select( ); }

    function from( ) {
      //@todo I think the first line here would make sense as the parent::from function
       $this->_from = "FROM " . $this->_baseTable  . " " . $this->_aliases[$this->_baseTable];
    }
    function groupBy( ) {
     parent::groupBy();
 //@todo - need to re-visit this - bad behaviour from pa
        if( $this->_groupBy  == 'GROUP BY'){
          $this->_groupBY = null;
        }
       // if a stat field has been selected the do a group by
       if(!empty($this->_statFields ) && empty( $this->_groupBy) ){
         $this->_groupBy[] = $this->_aliases[$this->_baseTable] . ".id";
       }
       //@todo - this should be in the parent function or at parent level - perhaps build query should do this?
       if(!empty( $this->_groupBy)){
         $this->_groupBy = 'GROUP BY ' . implode (',',$this->_groupBy);
  
       }
    }

    function orderBy( ) {
       parent::orderBy();
    }

    function statistics( &$rows ) {
        return parent::statistics( $rows );
    }

    function postProcess( ) {
      if(!empty($this->_aclTable) && CRM_Utils_Array::value($this->_aclTable, $this->_aliases)){
         $this->buildACLClause( $this->_aliases[$this->_aclTable] );
      }
      parent::postProcess( );
    }

    function alterDisplay( &$rows ) {
       parent::alterDisplay($rows);
       
       //THis is all generic functionality which can hopefully go into the parent class
       // it introduces the option of defining an alter display function as part of the column definition
       // @tod tidy up the iteration so it happens in this function
       list($firstRow)=  $rows;
       if(empty($firstRow))return; // no result to alter
       $selectedFields = array_keys($firstRow);

       $alterfunctions = array();
       foreach($this->_columns as $tablename => $table){
         if(array_key_exists('fields',$table)){
           foreach ($table['fields'] as $field => $specs) {
             
             if(in_array($tablename . '_' . $field, $selectedFields) && array_key_exists('alter_display',$specs)){
               $alterfunctions[$tablename . '_' . $field] = $specs['alter_display'];
               $fn = $specs['alter_display'];
               // at this point we are calling the function but also creating an array of the functions to call
               // which we will implement soon.
              // $this->$fn ( $rows);
             }
           }
         }
       }
       
       if(empty($alterfunctions)){
         return ;//- no manipulation to be done
       }
       
       foreach ( $rows as $index => $row) {
                foreach ( $row as $selectedfield  => $value) {
                  if(!array_key_exists($selectedfield,$alterfunctions)){
                    continue;
                  }
                  $rows[$index][$selectedfield] = $this->$alterfunctions[$selectedfield]($value);
                }

                
            
        }
       
    }
   
    
    
   function getLineItemColumns(){
     return  array( 'civicrm_line_item'      =>
                   array( 'dao'     => 'CRM_Price_BAO_LineItem',
                          'fields'  =>
                          array( 
                                 'qty' => 
                                 array( 'title' => ts( 'Quantity' ),
                                        'type'  => CRM_Utils_Type::T_INT, 
                                        'statistics'   => 
                                                array('sum' => ts( 'Total Quantity Selected' )),
                                        ), 
                                 'unit_price'           => 
                                 array( 'title' => ts( 'Unit Price' ),

                                        ),
                                 'line_total'           => 
                                 array( 'title' => ts( 'Line Total' ),
                                        'type'  => CRM_Utils_Type::T_MONEY, 
                                        'statistics'   => 
                                                array('sum' => ts( 'Total of Line Items' )),
                                                                       ),),

                                'participant_count'           => 
                                 array( 'title' => ts( 'Participant Count' ),
                                        'statistics'   => 
                                                array('sum' => ts( 'Total Participants' )),
                                        ),                                 

                         'filters' =>             
                           array(  'qty' => 
                                 array( 'title' => ts( 'Quantity' ),
                                       'type'       => CRM_Utils_Type::T_INT,
                                       'operator'   => CRM_Report_Form::OP_INT ),
                          ),
                          'group_bys' => 
                            array('price_field_id' => 
                                 array( 'title' => ts( 'Price Field' ),
                             ),
                             'price_field_value_id' => 
                                 array( 'title' => ts( 'Price Field Option' ),
                             ),)
 
 
                   ));
   }
   
   function getPriceFieldValueColumns(){
     return  array( 'civicrm_price_field_value'      =>
                   array( 'dao'     => 'CRM_Price_BAO_FieldValue',
                          'fields' => array( 'price_field_value_label'           => 
                                 array( 'title' => ts( 'Price Field Value Label' ),
                                        'name' => 'label'),
                               ),
                         'filters' =>             
                          array( 'price_field_value_label'           => 
                                 array( 'title' => ts( 'Price Fields Value Label' ),
                                        'type'       => CRM_Utils_Type::T_STRING,
                                        'operator'   => 'like',
                                        'name' => 'label' ), 
                              ),
                        'order_bys'  =>
                         array( 'label' =>
                                array( 'title' => ts( 'Price Field Value Label'), ),
                                ),
                         'group_bys' =>  
                          array( 'label' => 
                                 array( 'title' => ts('Price Field Value Label') ),
                                 ),
 
 
                   ));
   }
   function getPriceFieldColumns(){
     return  array( 'civicrm_price_field'      =>
                   array( 'dao'     => 'CRM_Price_BAO_Field',
                          'fields'  =>
                          array( 'label'           => 
                                 array( 'title' => ts( 'Price Field Label' ),
                                        ),
                              ),
                         'filters' =>             
                          array( 'label'           => 
                                 array( 'title' => ts( 'Price Field Label' ),
                                       'type'       => CRM_Utils_Type::T_STRING,
                                       'operator'   => 'like' ), 
                              ),
 
 
                   ));
   }
   function getParticipantColumns(){
     static $_events;
     if ( !isset($_events['all']) ) {
            CRM_Core_PseudoConstant::populate( $_events['all'], 'CRM_Event_DAO_Event', false, 'title', 'is_active', "is_template IS NULL OR is_template = 0", 'end_date DESC' );
     }
     return  array('civicrm_participant' =>
                  array( 'dao'     => 'CRM_Event_DAO_Participant',
                         'fields'  =>
                         array( 'participant_id'            => array( 'title' => 'Participant ID' ),
                                'participant_record'        => array( 'name'       => 'id' ,
                                                                      'title'  => 'Participant Id'
                                   ),

                                'event_id'                  => array( 'title' => ts('Event ID'),
                                                                      'type'    =>  CRM_Utils_Type::T_STRING ,
                                                                       'alter_display'  => 'alterEventID'),
                                'status_id'                 => array( 'title'   => ts('Status'),
                                                                      'alter_display'  => 'alterParticipantStatus',
                                                                       ),
                                'role_id'                   => array( 'title'   => ts('Role'),
                                                                      'alter_display'  => 'alterParticipantRole' ),
                                'participant_fee_level'     => null,
                                
                                'participant_fee_amount'    => null,
                               
                                'participant_register_date' => array( 'title'   => ts('Registration Date') ),
                                ), 
                         'grouping' => 'event-fields',
                         'filters'  =>             
                         array( 'event_id'                  => array( 'name'         => 'event_id',
                                                                      'title'        => ts( 'Event' ),
                                                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                                      'options'      => $_events['all'] ),
                                
                                'sid'                       => array( 'name'         => 'status_id',
                                                                      'title'        => ts( 'Participant Status' ),
                                                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                                      'options'      => CRM_Event_PseudoConstant::participantStatus( null, null, 'label' ) ),
                                'rid'                       => array( 'name'         => 'role_id',
                                                                      'title'        => ts( 'Participant Role' ),
                                                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                                      'options'      => CRM_Event_PseudoConstant::participantRole( ) ),
                                'participant_register_date' => array( 'title'        => ' Registration Date',
                                                                      'operatorType' => CRM_Report_Form::OP_DATE ),
                                ),
                         
                         'order_bys'  =>
                         array( 'event_id' =>
                                array( 'title' => ts( 'Event'), 'default_weight' => '1', 'default_order' => 'ASC'),
                                ),
                         'group_bys' =>  
                          array( 'event_id' => 
                                 array( 'title' => ts('Event') ),
                                 ),
                         ),);
                  
   }
     function getEventColumns(){
       return array ('civicrm_event' =>
                  array( 'dao'        => 'CRM_Event_DAO_Event',
                         'fields'     =>
                         array( 
                               'event_type_id' => array( 'title' => ts('Event Type') ,
                                                         'alter_display' => 'alterEventType'),
                              ), 
                         'grouping'  => 'event-fields', 
                         'filters'   =>             
                         array(                      
                               'eid' =>  array( 'name'         => 'event_type_id',
                                                'title'        => ts( 'Event Type' ),
                                                'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                'options'      => CRM_Core_OptionGroup::values('event_type'),
                                ),
                                'event_title' =>  array( 'name'         => 'title',
                                                'title'        => ts( 'Event Title' ),
                                                'operatorType' => CRM_Report_Form::OP_STRING,
                                ), 
                               ),
                         'order_bys'  =>
                         array( 'event_type_id' =>
                                array( 'title' => ts( 'Event Type'), 
                                       'default_weight' => '2', 
                                       'default_order' => 'ASC',
                                ),
                                ),
                         ), );
     }
   
     function getContributionColumns(){
       return array(                   'civicrm_contribution' =>
                   array( 'dao'     => 'CRM_Contribute_DAO_Contribution',
                          'fields'  =>
                          array(
                                 'contribution_id' => array( 
                                                            'name' => 'id',

                                                ),
                                 'contribution_type_id' => array( 'title'   => ts('Contribution Type'),
                                                                  'default' => true,
                                                                  'alter_display' => 'alterContributionType'
                                                                ),
                                'payment_instrument_id' => array( 'title'   => ts('Payment Type'),
                                                                  'alter_display' => 'alterPaymentType'
                                                                            ),
                                 'trxn_id'              => null,
                                 'receive_date'         => array( 'default' => true ),
                                 'receipt_date'         => null,
                                 'fee_amount'           => null,
                                 'net_amount'           => null,
                                 'total_amount'         => array( 'title'        => ts( 'Amount' ),                                                                  
                                                                  'statistics'   => 
                                                                          array('sum' => ts( 'Total Amount' )),
                                                                  'type'  => CRM_Utils_Type::T_MONEY ,
                                                                  ),
                                 ),
                          'filters' =>             
                          array( 'receive_date'           => 
                                    array( 'operatorType' => CRM_Report_Form::OP_DATE ),
                                 'contribution_type_id'   =>
                                    array( 'title'        => ts( 'Contribution Type' ), 
                                           'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                           'options'      => CRM_Contribute_PseudoConstant::contributionType( )
                                         ),
                                 'payment_instrument_id'   =>
                                    array( 'title'        => ts( 'Payment Type' ), 
                                           'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                           'options'      => CRM_Contribute_PseudoConstant::paymentInstrument( )
                                         ),
                                'contribution_status_id' => 
                                    array( 'title'        => ts( 'Contribution Status' ), 
                                        'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                        'options'      => CRM_Contribute_PseudoConstant::contributionStatus( ),
                                        
                                        ),
                                 'total_amount'           => 
                                    array( 'title'        => ts( 'Contribution Amount' ) ),
                                 ),
                          'group_bys' =>  
                          array( 'contribution_type_id' => 
                                 array( 'title' => ts('Contribution Type') ),
                                 ),
                          'grouping'=> 'contribution-fields',
                          ),);
     }
     
     function getContactColumns(){
       return             array( 'civicrm_contact' =>
                   array( 'dao'       => 'CRM_Contact_DAO_Contact',
                          'fields'    =>
                          array( 'sort_name' => 
                                 array( 'title' => ts( 'Contact Name' ),
                                       ),
                                 'id'           => 
                                 array( 'title'      => ts( 'Contact ID' ),
                                         ),
                                      ),
                          'filters'   =>             
                          array( 'id'           => 
                                 array( 'title'      => ts( 'Contact ID' ),
                                        'alter_display' => 'alterContactID' ),
                                 'sort_name' =>
                                 array( 'title'      => ts( 'Contact Name' ),),),
                          'grouping'  => 'contact-fields',
                          'order_bys'  =>
                          array( 'sort_name' =>
                                 array( 'title' => ts( 'Last Name, First Name'), 'default' => '1', 'default_weight' => '0',  'default_order' => 'ASC' )
                          ),
                          ),);
     }
      function joinPriceFieldValueFromLineItem(){
        $this->_from .= " LEFT JOIN civicrm_price_field_value {$this->_aliases['civicrm_price_field_value']} 
                          ON {$this->_aliases['civicrm_line_item']}.price_field_value_id = {$this->_aliases['civicrm_price_field_value']}.id";
     }
      function joinPriceFieldFromLineItem(){
        $this->_from .= " LEFT JOIN civicrm_price_field {$this->_aliases['civicrm_price_field']} 
                          ON {$this->_aliases['civicrm_line_item']}.price_field_id = {$this->_aliases['civicrm_price_field']}.id";
     }
      function joinParticipantFromLineItem(){
        $this->_from .= " LEFT JOIN civicrm_participant {$this->_aliases['civicrm_participant']} 
                          ON ( {$this->_aliases['civicrm_line_item']}.entity_id = {$this->_aliases['civicrm_participant']}.id AND {$this->_aliases['civicrm_line_item']}.entity_table = 'civicrm_participant')
                          ";
     }
      function joinContributionFromParticipant(){
        $this->_from .= " LEFT JOIN civicrm_participant_payment pp 
                          ON {$this->_aliases['civicrm_participant']}.id = pp.participant_id
        LEFT JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']} 
                          ON pp.contribution_id = {$this->_aliases['civicrm_contribution']}.id";
     }
      function joinParticipantFromContribution(){
        $this->_from .= " LEFT JOIN civicrm_participant_payment pp 
                          ON {$this->_aliases['civicrm_contribution']}.id = pp.contribution_id
        LEFT JOIN civicrm_participant {$this->_aliases['civicrm_participant']} 
                          ON pp.participant_id = {$this->_aliases['civicrm_participant']}.id";
     }
     
     function joinContributionFromLineItem(){
       
       // this can be stored as a temp table & indexed for more speed. Not done at this state.
       // another option is to cache it but I haven't tried to put that code in yet (have used it before for one hour caching
       $this->_from .= "  LEFT JOIN (SELECT line_item_civireport.id as lid, contribution_civireport_direct.* 
FROM civicrm_line_item line_item_civireport
LEFT JOIN civicrm_contribution contribution_civireport_direct 
                       ON (line_item_civireport.line_total <> 0 AND line_item_civireport.entity_id = contribution_civireport_direct.id AND line_item_civireport.entity_table = 'civicrm_contribution')

			
WHERE 	contribution_civireport_direct.id IS NOT NULL
			
UNION SELECT line_item_civireport.id as lid, contribution_civireport.*
			FROM civicrm_line_item line_item_civireport
			LEFT JOIN civicrm_participant participant_civireport 
                          ON (line_item_civireport.line_total <> 0 AND line_item_civireport.entity_id = participant_civireport.id AND line_item_civireport.entity_table = 'civicrm_participant')

LEFT JOIN civicrm_participant_payment pp 
                          ON participant_civireport.id = pp.participant_id
        LEFT JOIN civicrm_contribution contribution_civireport 
                          ON pp.contribution_id = contribution_civireport.id 				

UNION SELECT line_item_civireport.id as lid,contribution_civireport.*
			FROM civicrm_line_item line_item_civireport
			LEFT JOIN civicrm_membership membership_civireport 
                          ON (line_item_civireport.line_total <> 0 AND line_item_civireport.entity_id =membership_civireport.id AND line_item_civireport.entity_table = 'civicrm_membership')

LEFT JOIN civicrm_membership_payment pp 
                          ON membership_civireport.id = pp.membership_id
        LEFT JOIN civicrm_contribution contribution_civireport 
                          ON pp.contribution_id = contribution_civireport.id 				
) as {$this->_aliases['civicrm_contribution']} 
  ON {$this->_aliases['civicrm_contribution']}.lid = {$this->_aliases['civicrm_line_item']}.id
 ";
     }
     
     function joinLineItemFromContribution(){
       
       // this can be stored as a temp table & indexed for more speed. Not done at this stage.
       // another option is to cache it but I haven't tried to put that code in yet (have used it before for one hour caching
       $this->_from .= "  
       LEFT JOIN (
SELECT contribution_civireport_direct.id AS contid, line_item_civireport.*
FROM civicrm_contribution contribution_civireport_direct  
LEFT JOIN civicrm_line_item line_item_civireport ON (line_item_civireport.line_total <> 0 AND line_item_civireport.entity_id = contribution_civireport_direct.id AND line_item_civireport.entity_table = 'civicrm_contribution')
WHERE 	line_item_civireport.id IS NOT NULL 

UNION 
SELECT contribution_civireport_direct.id AS contid, line_item_civireport.*
FROM civicrm_contribution contribution_civireport_direct  
LEFT JOIN civicrm_participant_payment pp ON contribution_civireport_direct.id = pp.contribution_id
LEFT JOIN civicrm_participant p ON pp.participant_id = p.id
LEFT JOIN civicrm_line_item line_item_civireport ON (line_item_civireport.line_total <> 0 AND line_item_civireport.entity_id = p.id AND line_item_civireport.entity_table = 'civicrm_participant')
WHERE 	line_item_civireport.id IS NOT NULL 

UNION

SELECT contribution_civireport_direct.id AS contid, line_item_civireport.*
FROM civicrm_contribution contribution_civireport_direct  
LEFT JOIN civicrm_membership_payment pp ON contribution_civireport_direct.id = pp.contribution_id
LEFT JOIN civicrm_membership p ON pp.membership_id = p.id
LEFT JOIN civicrm_line_item line_item_civireport ON (line_item_civireport.line_total <> 0 AND line_item_civireport.entity_id = p.id AND line_item_civireport.entity_table = 'civicrm_membership')
WHERE 	line_item_civireport.id IS NOT NULL 
) as {$this->_aliases['civicrm_line_item']} 
  ON {$this->_aliases['civicrm_line_item']}.contid = {$this->_aliases['civicrm_contribution']}.id
 
  
  ";
     }
     
      function joinContactFromParticipant(){
        $this->_from .= " LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']} 
                          ON {$this->_aliases[civicrm_participant]}.contact_id = {$this->_aliases['civicrm_contact']}.id";
     }

      function joinContactFromContribution(){
        $this->_from .= " LEFT JOIN civicrm_contact {$this->_aliases['civicrm_contact']} 
                          ON {$this->_aliases['civicrm_contribution']}.contact_id = {$this->_aliases['civicrm_contact']}.id";
     }    
      function joinEventFromParticipant(){
        $this->_from .= "  LEFT JOIN civicrm_event {$this->_aliases['civicrm_event']} 
                    ON ({$this->_aliases['civicrm_event']}.id = {$this->_aliases['civicrm_participant']}.event_id ) AND 
                       ({$this->_aliases['civicrm_event']}.is_template IS NULL OR  
                        {$this->_aliases['civicrm_event']}.is_template = 0)";
      }
  
    /*
    * Retrieve text for contribution type from pseudoconstant
    */ 
    function alterContributionType( $value ) {
        return is_string(CRM_Contribute_PseudoConstant::contributionType( $value, false ))?CRM_Contribute_PseudoConstant::contributionType( $value, false ):'';
    } 
    /*
    * Retrieve text for contribution status from pseudoconstant
    */
   function alterContributionStatus( $value ) {
      return CRM_Contribute_PseudoConstant::contributionStatus( $value );
   }
   /*
    * Retrieve text for payment instrument from pseudoconstant
    */
   function alterEventType( $value ) {
     return CRM_Event_PseudoConstant::eventType($value);
   }
   function alterEventID( $value ) {
     return is_string(CRM_Event_PseudoConstant::event( $value, false ))?CRM_Event_PseudoConstant::event( $value, false ):'';
   }
   function alterContactID( $value ) {
     return $value;
   }   
   function alterParticipantStatus($value){
     if(empty($value))return;
     return CRM_Event_PseudoConstant::participantStatus( $value, false , 'label');
   }
   
   function alterParticipantRole($value){
      if(empty($value))return;
      $roles = explode( CRM_Core_DAO::VALUE_SEPARATOR, $value ); 
      $value = array( );
      foreach( $roles as $role) {
          $value[$role] = CRM_Event_PseudoConstant::participantRole( $role, false );
     }
     return implode( ', ', $value );
   }
   function alterPaymentType($value){
     $paymentInstruments = CRM_Contribute_PseudoConstant::paymentInstrument( );
     return $paymentInstruments[$value];
   }
}