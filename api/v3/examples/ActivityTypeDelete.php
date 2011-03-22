<?php 

function activity_type_delete_example(){
    $params = array(
    
                  'activity_type_id' 		=> '562',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'activity_type','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_type_delete_expectedresult(){

  $expectedResult = 
     array(
           '0' 		=> '1',
      );

  return $expectedResult  ;
}

