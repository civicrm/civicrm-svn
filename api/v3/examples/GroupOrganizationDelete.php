<?php 

function group_organization_delete_example(){
    $params = array(
    
                  'id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'group_organization','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_organization_delete_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'values' 		=> 'Deleted Group Organization successfully',
      );

  return $expectedResult  ;
}

