<?php 

function pledge_create_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'pledge_create_date' 		=> '20110209',
                  'start_date' 		=> '20110209',
                  'scheduled_date' 		=> '20110211',
                  'pledge_amount' 		=> '100',
                  'pledge_status_id' 		=> '2',
                  'contribution_type_id' 		=> '1',
                  'pledge_original_installment_amount' 		=> '20',
                  'frequency_interval' 		=> '5',
                  'frequency_unit' 		=> 'year',
                  'frequency_day' 		=> '15',
                  'installments' 		=> '5',
                  'sequential' 		=> '1',
                  'version' 		=> '3',
                  'amount' 		=> '100',

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
           'is_error' 		=> '1',
           'error_message' 		=> 'Undefined index: create_date',
      );

  return $expectedResult  ;
}

