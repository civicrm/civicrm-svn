<?php 

function option_value_get_example(){
    $params = array(
    
                  'option_group_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'option_value','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function option_value_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '1',
           'error_message' 		=> 'array_key_exists() expects parameter 2 to be array, null given',
      );

  return $expectedResult  ;
}

