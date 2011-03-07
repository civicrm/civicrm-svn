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
                      'contribution_id' => '',
                      'scheduled_amount' => '20.00',
                      'actual_amount' => '',
                      'currency' => 'USD',
                      'scheduled_date' => '2011-03-04 00:00:00',
                      'reminder_date' => '',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '2' =>  array(
                      'id' => '2',
                      'pledge_id' => '1',
                      'contribution_id' => '',
                      'scheduled_amount' => '20.00',
                      'actual_amount' => '',
                      'currency' => 'USD',
                      'scheduled_date' => '2016-03-04 00:00:00',
                      'reminder_date' => '',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '3' =>  array(
                      'id' => '3',
                      'pledge_id' => '1',
                      'contribution_id' => '',
                      'scheduled_amount' => '20.00',
                      'actual_amount' => '',
                      'currency' => 'USD',
                      'scheduled_date' => '2021-03-04 00:00:00',
                      'reminder_date' => '',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '4' =>  array(
                      'id' => '4',
                      'pledge_id' => '1',
                      'contribution_id' => '',
                      'scheduled_amount' => '20.00',
                      'actual_amount' => '',
                      'currency' => 'USD',
                      'scheduled_date' => '2026-03-04 00:00:00',
                      'reminder_date' => '',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),                      '5' =>  array(
                      'id' => '5',
                      'pledge_id' => '1',
                      'contribution_id' => '',
                      'scheduled_amount' => '20.00',
                      'actual_amount' => '',
                      'currency' => 'USD',
                      'scheduled_date' => '2031-03-04 00:00:00',
                      'reminder_date' => '',
                      'reminder_count' => '0',
                      'status_id' => '2',
           ),           ),
      );

  return $expectedResult  ;
}

