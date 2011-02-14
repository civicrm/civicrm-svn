<?php 

function membership_delete_example(){
    $params = array(
    
                  'id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_membership_delete','Membership',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_delete_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Input parameter should be numeric',

  );

  return $expectedResult  ;
}

