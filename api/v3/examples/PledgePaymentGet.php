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
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'pledge_id' => '1',
                      'scheduled_amount' => '20.00',
                      'currency' => 'USD',
                      'scheduled_date' => '2011-04-29 00:00:00',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '2' =>  array(
                      'id' => '2',
                      'pledge_id' => '1',
                      'scheduled_amount' => '20.00',
                      'currency' => 'USD',
                      'scheduled_date' => '2016-04-29 00:00:00',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '3' =>  array(
                      'id' => '3',
                      'pledge_id' => '1',
                      'scheduled_amount' => '20.00',
                      'currency' => 'USD',
                      'scheduled_date' => '2021-04-29 00:00:00',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '4' =>  array(
                      'id' => '4',
                      'pledge_id' => '1',
                      'scheduled_amount' => '20.00',
                      'currency' => 'USD',
                      'scheduled_date' => '2026-04-29 00:00:00',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '5' =>  array(
                      'id' => '5',
                      'pledge_id' => '1',
                      'scheduled_amount' => '20.00',
                      'currency' => 'USD',
                      'scheduled_date' => '2031-04-29 00:00:00',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),           ),
      );

  return $expectedResult  ;
}

