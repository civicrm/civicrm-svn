<?php 

function group_nesting_delete_example(){
    $params = array(
    
                  'parent_group_id' 		=> '1',
                  'child_group_id' 		=> '2',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'group_nesting','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_nesting_delete_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
      );

  return $expectedResult  ;
}

