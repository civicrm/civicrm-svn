<?php 

function group_contact_delete_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'group_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'group_contact','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_contact_delete_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '3',
           'values' 		=> array(           'not_removed' => '0',                      'removed' => '1',                      'total_count' => '1',           ),
      );

  return $expectedResult  ;
}

