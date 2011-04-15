<?php 

function contribution_get_example(){
    $params = array(
    
                  'contribution_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'contribution','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contribution_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'contact_id' => '1',
                      'contact_type' => 'Individual',
                      'sort_name' => 'Anderson, Anthony',
                      'display_name' => 'Mr. Anthony Anderson II',
                      'contribution_id' => '1',
                      'currency' => 'USD',
                      'receive_date' => '2011-03-16 00:00:00',
                      'non_deductible_amount' => '10.00',
                      'total_amount' => '100.00',
                      'fee_amount' => '51.00',
                      'net_amount' => '91.00',
                      'trxn_id' => '23456',
                      'invoice_id' => '78910',
                      'contribution_source' => 'SSF',
                      'is_test' => '0',
                      'is_pay_later' => '0',
                      'contribution_type_id' => '1',
                      'contribution_type' => 'Donation',
                      'contribution_status_id' => '1',
                      'contribution_status' => 'Completed',
           ),           ),
      );

  return $expectedResult  ;
}

