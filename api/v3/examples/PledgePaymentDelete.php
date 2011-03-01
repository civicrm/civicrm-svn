<?php 

function pledge_payment_delete_example(){
    $params = array(
    
                  '0' 		=> 'Array',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'pledge_payment','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_payment_delete_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'values' 		=> '1',
      );

  return $expectedResult  ;
}

