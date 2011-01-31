<?php 

function pledge_get_example(){
    $params = array(
    
                  'pledge_id' 		=> '1',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_pledge_get','Pledge',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_get_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'DB Error: no such field',

  );

  return $expectedResult  ;
}

