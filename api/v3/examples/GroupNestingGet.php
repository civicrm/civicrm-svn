<?php 

function group_nesting_get_example(){
    $params = array(
    
                  'parent_group_id' 		=> '1',
                  'child_group_id' 		=> '2',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'group_nesting','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_nesting_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '2',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'child_group_id' => '2',
                      'parent_group_id' => '1',
           ),                      'is_error' => '0',           ),
      );

  return $expectedResult  ;
}

