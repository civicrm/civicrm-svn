<?php 

function group_nesting_create_example(){
    $params = array(
    
                  'parent_group_id' 		=> '1',
                  'child_group_id' 		=> '3',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'group_nesting','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_nesting_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> 'is_error',
           'values' 		=> array(           'is_error' => '0',           ),
      );

  return $expectedResult  ;
}

