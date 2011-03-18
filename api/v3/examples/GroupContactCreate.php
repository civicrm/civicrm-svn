<?php 

function group_contact_create_example(){
    $params = array(
    
                  'contact_id' 		=> '1',
                  'contact_id.2' 		=> '2',
                  'group_id' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'group_contact','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function group_contact_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '3',
           'values' 		=> array(           'not_added' => '1',                      'added' => '1',                      'total_count' => '2',           ),
      );

  return $expectedResult  ;
}

