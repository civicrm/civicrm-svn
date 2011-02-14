<?php 

function relationship_get_example(){
    $params = array(
    
                  'contact_id' 		=> '2',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_relationship_get','Relationship',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function relationship_get_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Could not find contact_id in input parameters.',

  );

  return $expectedResult  ;
}

