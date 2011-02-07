<?php 

function group_contact_create_example(){
    $params = array(
    
                  'contact_id.1' 		=> '1',
                  'contact_id.2' 		=> '2',
                  'group_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_group_contact_create','GroupContact',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_contact_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Mandatory key(s) missing from params array: contact_id',

  );

  return $expectedResult  ;
}

