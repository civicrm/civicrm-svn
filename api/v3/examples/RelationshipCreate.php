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
  $result = civicrm_api( 'relationship','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function relationship_create_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'moreIDs' => '1',
           ),           ),
      );

  return $expectedResult  ;
}

