<?php 

function contribution_create_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'receive_date' 		=> '20110316',
                  'total_amount' 		=> '100',
                  'contribution_type_id' 		=> '1',
                  'payment_instrument_id' 		=> '1',
                  'non_deductible_amount' 		=> '10',
                  'fee_amount' 		=> '50',
                  'net_amount' 		=> '90',
                  'trxn_id' 		=> '12345',
                  'invoice_id' 		=> '67890',
                  'source' 		=> 'SSF',
                  'contribution_status_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'contribution','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contribution_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'contact_id' => '1',
                      'contribution_type_id' => '1',
                      'contribution_page_id' => '',
                      'payment_instrument_id' => '1',
                      'receive_date' => '20110316',
                      'non_deductible_amount' => '10',
                      'total_amount' => '100',
                      'fee_amount' => '50',
                      'net_amount' => '90',
                      'trxn_id' => '12345',
                      'invoice_id' => '67890',
                      'currency' => 'USD',
                      'cancel_date' => '',
                      'cancel_reason' => '',
                      'receipt_date' => '',
                      'thankyou_date' => '',
                      'source' => 'SSF',
                      'amount_level' => '',
                      'contribution_recur_id' => '',
                      'honor_contact_id' => '',
                      'is_test' => '',
                      'is_pay_later' => '',
                      'contribution_status_id' => '1',
                      'honor_type_id' => '',
                      'address_id' => '',
                      'check_number' => 'null',
                      'campaign_id' => '',
           ),           ),
      );

  return $expectedResult  ;
}

