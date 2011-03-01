<?php 

function group_organization_delete_example(){
    $params = array(
    
                  'id' 		=> '',
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
           'is_error' 		=> '1',
           'error_message' 		=> 'DB_DataObject Error: delete: No condition specifed for query',
      );

  return $expectedResult  ;
}

