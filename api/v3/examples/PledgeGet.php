<?php 

function pledge_get_example(){
    $params = array(
    
                  'pledge_id' 		=> '1',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_pledge_get','Pledge',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_get_expectedresult(){

  $expectedResult = 
            array(
                  '1' 		=>                   array(                  'contact_id' => '1',                                    'contact_type' => 'Individual',                                    'sort_name' => 'Anderson, Anthony',                                    'display_name' => 'Mr. Anthony Anderson II',                                    'pledge_id' => '1',                                    'pledge_amount' => '100.00',                                    'pledge_create_date' => '2011-02-06 00:00:00',                                    'pledge_status' => 'Pending',                                    'pledge_next_pay_date' => '2011-02-08 00:00:00',                                    'pledge_next_pay_amount' => '20.00',                                    'pledge_frequency_interval' => '1',                                    'pledge_frequency_unit' => 'month',                                    'pledge_is_test' => '0',                  ),

  );

  return $expectedResult  ;
}

