<?php 

function pledge_delete_example(){
    $params = array(
    
                  'pledge_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'pledge','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_delete_expectedresult(){

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

