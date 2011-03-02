<?php 

function group_organization_create_example(){
    $params = array(
    
                  'organization_id' 		=> '1',
                  'group_id' 		=> '',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'group_organization','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_organization_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '1',
           'error_message' 		=> 'group organization not created',
      );

  return $expectedResult  ;
}

