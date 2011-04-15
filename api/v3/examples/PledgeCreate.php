<?php 

function pledge_create_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'pledge_create_date' 		=> '20110316',
                  'start_date' 		=> '20110316',
                  'scheduled_date' 		=> '20110318',
                  'pledge_amount' 		=> '100',
                  'pledge_status_id' 		=> '2',
                  'pledge_contribution_type_id' 		=> '1',
                  'pledge_original_installment_amount' 		=> '20',
                  'frequency_interval' 		=> '5',
                  'frequency_unit' 		=> 'year',
                  'frequency_day' 		=> '15',
                  'installments' 		=> '5',
                  'sequential' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'pledge','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '0' =>  array(
                      'id' => '1',
                      'contact_id' => '1',
                      'contribution_type_id' => '1',
                      'contribution_page_id' => '',
                      'amount' => '100',
                      'original_installment_amount' => '20',
                      'currency' => 'USD',
                      'frequency_unit' => 'year',
                      'frequency_interval' => '5',
                      'frequency_day' => '15',
                      'installments' => '5',
                      'start_date' => '20110316',
                      'create_date' => '20110316',
                      'acknowledge_date' => '',
                      'modified_date' => '',
                      'cancel_date' => '',
                      'end_date' => '',
                      'honor_contact_id' => '',
                      'honor_type_id' => '',
                      'max_reminders' => '',
                      'initial_reminder_day' => '',
                      'additional_reminder_day' => '',
                      'status_id' => '2',
                      'is_test' => '',
                      'campaign_id' => '',
           ),           ),
      );

  return $expectedResult  ;
}

