<?php 

function group_contact_get_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api_legacy( 'civicrm_group_contact_get','GroupContact',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_contact_get_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '0',
                  'values' 		=>                   array(),

  );

  return $expectedResult  ;
}

