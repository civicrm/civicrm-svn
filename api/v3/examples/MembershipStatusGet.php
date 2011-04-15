<?php 

function membership_status_get_example(){
    $params = array(
    
                  'name' 		=> 'test status',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'membership_status','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_status_get_expectedresult(){

  $expectedResult = 
     array(
           '1' 		=> array(           'id' => '1',                      'name' => 'New',                      'label' => 'New',                      'start_event' => 'join_date',                      'start_event_adjust_unit' => '',                      'start_event_adjust_interval' => '',                      'end_event' => 'join_date',                      'end_event_adjust_unit' => 'month',                      'end_event_adjust_interval' => '3',                      'is_current_member' => '1',                      'is_admin' => '0',                      'weight' => '1',                      'is_default' => '0',                      'is_active' => '1',                      'is_reserved' => '0',           ),
           '2' 		=> array(           'id' => '2',                      'name' => 'Current',                      'label' => 'Current',                      'start_event' => 'start_date',                      'start_event_adjust_unit' => '',                      'start_event_adjust_interval' => '',                      'end_event' => 'end_date',                      'end_event_adjust_unit' => '',                      'end_event_adjust_interval' => '',                      'is_current_member' => '1',                      'is_admin' => '0',                      'weight' => '2',                      'is_default' => '1',                      'is_active' => '1',                      'is_reserved' => '0',           ),
           '3' 		=> array(           'id' => '3',                      'name' => 'Grace',                      'label' => 'Grace',                      'start_event' => 'end_date',                      'start_event_adjust_unit' => '',                      'start_event_adjust_interval' => '',                      'end_event' => 'end_date',                      'end_event_adjust_unit' => 'month',                      'end_event_adjust_interval' => '1',                      'is_current_member' => '1',                      'is_admin' => '0',                      'weight' => '3',                      'is_default' => '0',                      'is_active' => '1',                      'is_reserved' => '0',           ),
           '4' 		=> array(           'id' => '4',                      'name' => 'Expired',                      'label' => 'Expired',                      'start_event' => 'end_date',                      'start_event_adjust_unit' => 'month',                      'start_event_adjust_interval' => '1',                      'end_event' => '',                      'end_event_adjust_unit' => '',                      'end_event_adjust_interval' => '',                      'is_current_member' => '0',                      'is_admin' => '0',                      'weight' => '4',                      'is_default' => '0',                      'is_active' => '1',                      'is_reserved' => '0',           ),
           '5' 		=> array(           'id' => '5',                      'name' => 'Pending',                      'label' => 'Pending',                      'start_event' => 'join_date',                      'start_event_adjust_unit' => '',                      'start_event_adjust_interval' => '',                      'end_event' => 'join_date',                      'end_event_adjust_unit' => '',                      'end_event_adjust_interval' => '',                      'is_current_member' => '0',                      'is_admin' => '0',                      'weight' => '5',                      'is_default' => '0',                      'is_active' => '1',                      'is_reserved' => '1',           ),
           '6' 		=> array(           'id' => '6',                      'name' => 'Cancelled',                      'label' => 'Cancelled',                      'start_event' => 'join_date',                      'start_event_adjust_unit' => '',                      'start_event_adjust_interval' => '',                      'end_event' => 'join_date',                      'end_event_adjust_unit' => '',                      'end_event_adjust_interval' => '',                      'is_current_member' => '0',                      'is_admin' => '0',                      'weight' => '6',                      'is_default' => '0',                      'is_active' => '1',                      'is_reserved' => '0',           ),
           '7' 		=> array(           'id' => '7',                      'name' => 'Deceased',                      'label' => 'Deceased',                      'start_event' => '',                      'start_event_adjust_unit' => '',                      'start_event_adjust_interval' => '',                      'end_event' => '',                      'end_event_adjust_unit' => '',                      'end_event_adjust_interval' => '',                      'is_current_member' => '0',                      'is_admin' => '1',                      'weight' => '7',                      'is_default' => '0',                      'is_active' => '1',                      'is_reserved' => '1',           ),
           '8' 		=> array(           'id' => '8',                      'name' => 'test status',                      'label' => 'test status',                      'start_event' => 'start_date',                      'start_event_adjust_unit' => '',                      'start_event_adjust_interval' => '',                      'end_event' => 'end_date',                      'end_event_adjust_unit' => '',                      'end_event_adjust_interval' => '',                      'is_current_member' => '1',                      'is_admin' => '0',                      'weight' => '',                      'is_default' => '0',                      'is_active' => '1',                      'is_reserved' => '0',           ),
      );

  return $expectedResult  ;
}

