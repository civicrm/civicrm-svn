<?php 

function email_delete_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'location_type_id' 		=> '6',
                  'email' 		=> 'api@a-team.com',
                  'is_primary' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'email','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function email_delete_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'values' 		=> '1',
      );

  return $expectedResult  ;
}

