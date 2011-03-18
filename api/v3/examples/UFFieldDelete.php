<?php 

function uf_field_delete_example(){
    $params = array(
    
                  'version' 		=> '3',
                  'field_id' 		=> '35',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'uf_field','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function uf_field_delete_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'values' 		=> '1',
      );

  return $expectedResult  ;
}

