<?php 

function contribution_get_example(){
    $params = array(
    
                  'contribution_id' 		=> '',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_contribution_get','Contribution',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contribution_get_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'DB Error: no such field',

  );

  return $expectedResult  ;
}

