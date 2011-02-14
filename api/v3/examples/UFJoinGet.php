<?php 

function uf_join_get_example(){
    $params = array(
    
                  'entity_table' 		=> 'civicrm_contribution_page',
                  'entity_id' 		=> '1',
                  'version' 		=> '3',
                  'sequential' 		=> '1',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'uf_join','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function uf_join_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '1',
           'error_message' 		=> 'Undefined variable: dao',
      );

  return $expectedResult  ;
}

