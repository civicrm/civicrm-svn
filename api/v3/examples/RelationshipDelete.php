<?php 

function relationship_delete_example(){
    $params = array(
    
                  'contact_id_a' 		=> '1',
                  'contact_id_b' 		=> '2',
                  'relationship_type_id' 		=> '10',
                  'start_date' 		=> '2008-12-20',
                  'is_active' 		=> '1',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_relationship_delete','Relationship',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function relationship_delete_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '0',
                  'version' 		=> '3',
                  'count' 		=> '1',
                  'id' 		=> 'id',
                  'values' 		=>                   array(                  'id' => '1',                  ),

  );

  return $expectedResult  ;
}

