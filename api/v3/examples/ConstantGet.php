<?php 

function constant_get_example(){
    $params = array(
    

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'constant','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function constant_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '4',
           'values' 		=> array(           '5' => 'Billing',                      '1' => 'Home',                      '3' => 'Main',                      '2' => 'Work',           ),
      );

  return $expectedResult  ;
}

