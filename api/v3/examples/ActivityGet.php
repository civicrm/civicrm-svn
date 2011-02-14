<?php 

function activity_get_example(){
    $params = array(
    
                  'activity_id' 		=> '4',
                  'activity_type_id' 		=> '5',
                  'version' 		=> '3',
                  'sequential' 		=> '1',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_activity_get','Activity',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_get_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Undefined index: activity_type_id',

  );

  return $expectedResult  ;
}

