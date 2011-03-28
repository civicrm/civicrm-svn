<?php 

function phone_delete_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'location_type_id' 		=> '6',
                  'phone' 		=> '021 512 755',
                  'is_primary' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'phone','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function phone_delete_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'values' 		=> '2',
      );

  return $expectedResult  ;
}

