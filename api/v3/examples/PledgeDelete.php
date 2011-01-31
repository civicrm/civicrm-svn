<?php 

function pledge_delete_example(){
    $params = array(
    
                  'pledge_id' 		=> '',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_pledge_delete','Pledge',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_delete_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Could not find pledge_id in input parameters',

  );

  return $expectedResult  ;
}

