<?php 

function pledge_payment_get_example(){
    $params = array(
    
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'pledge_payment','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_payment_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '5',
           'values' 		=> array(           '6' =>  array(
                      'id' => '6',
                      'pledge_id' => '2',
                      'contribution_id' => '',
                      'scheduled_amount' => '20.00',
                      'actual_amount' => '',
                      'currency' => 'USD',
                      'scheduled_date' => '2011-04-21 00:00:00',
                      'reminder_date' => '',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '7' =>  array(
                      'id' => '7',
                      'pledge_id' => '2',
                      'contribution_id' => '',
                      'scheduled_amount' => '20.00',
                      'actual_amount' => '',
                      'currency' => 'USD',
                      'scheduled_date' => '2016-04-21 00:00:00',
                      'reminder_date' => '',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '8' =>  array(
                      'id' => '8',
                      'pledge_id' => '2',
                      'contribution_id' => '',
                      'scheduled_amount' => '20.00',
                      'actual_amount' => '',
                      'currency' => 'USD',
                      'scheduled_date' => '2021-04-21 00:00:00',
                      'reminder_date' => '',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '9' =>  array(
                      'id' => '9',
                      'pledge_id' => '2',
                      'contribution_id' => '',
                      'scheduled_amount' => '20.00',
                      'actual_amount' => '',
                      'currency' => 'USD',
                      'scheduled_date' => '2026-04-21 00:00:00',
                      'reminder_date' => '',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '10' =>  array(
                      'id' => '10',
                      'pledge_id' => '2',
                      'contribution_id' => '',
                      'scheduled_amount' => '20.00',
                      'actual_amount' => '',
                      'currency' => 'USD',
                      'scheduled_date' => '2031-04-21 00:00:00',
                      'reminder_date' => '',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),           ),
      );

  return $expectedResult  ;
}

