<?php 

function relationship_create_example(){
    $params = array(
    
                  'contact_id_a' 		=> '1',
                  'contact_id_b' 		=> '2',
                  'relationship_type_id' 		=> '10',
                  'start_date' 		=> '2010-10-30',
                  'end_date' 		=> '2010-12-30',
                  'is_active' 		=> '1',
                  'note' 		=> 'note',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'Relationship','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function relationship_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Undefined index: Ymd',

  );

  return $expectedResult  ;
}

