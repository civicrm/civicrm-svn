<?php 

function entity_tag_create_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'tag_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'entity_tag','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function entity_tag_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'not_added' 		=> '0',
           'added' 		=> '1',
           'total_count' 		=> '1',
      );

  return $expectedResult  ;
}

