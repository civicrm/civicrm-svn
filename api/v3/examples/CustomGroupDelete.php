<?php 

function custom_group_delete_example(){
    $params = array(
    
                  'id' 		=> '1',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_custom_group_delete','CustomGroup',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function custom_group_delete_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Mandatory key(s) missing from params array: version',

  );

  return $expectedResult  ;
}

