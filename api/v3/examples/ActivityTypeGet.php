<?php 

function activity_type_get_example(){
    $params = array(
    
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_activity_type_get','ActivityType',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_type_get_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Input variable `params` is not an array',

  );

  return $expectedResult  ;
}

