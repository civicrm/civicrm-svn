<?php 

function membership_create_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'membership_type_id' 		=> '1',
                  'join_date' 		=> '2006-01-21',
                  'start_date' 		=> '2006-01-21',
                  'end_date' 		=> '2006-12-21',
                  'source' 		=> 'Payment',
                  'is_override' 		=> '1',
                  'status_id' 		=> '8',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'membership','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_create_expectedresult(){

  $expectedResult = 
     array(
           'id' 		=> '2',
           'is_error' 		=> '0',
      );

  return $expectedResult  ;
}

