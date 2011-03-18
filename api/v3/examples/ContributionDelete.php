<?php 

function contribution_delete_example(){
    $params = array(
    
                  'contribution_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'contribution','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contribution_delete_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' => '1',           ),
      );

  return $expectedResult  ;
}

