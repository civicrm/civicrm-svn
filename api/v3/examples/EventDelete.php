<?php 

function event_delete_example(){
    $params = array(
    
                  'id' 		=> '18',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'event','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function event_delete_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'values' 		=> '1',
      );

  return $expectedResult  ;
}

