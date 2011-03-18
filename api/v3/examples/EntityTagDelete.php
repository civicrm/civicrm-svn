<?php 

function entity_tag_delete_example(){
    $params = array(
    
                  'contact_id_h' 		=> '2',
                  'tag_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'entity_tag','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function entity_tag_delete_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'not_removed' 		=> '0',
           'removed' 		=> '1',
           'total_count' 		=> '1',
      );

  return $expectedResult  ;
}

