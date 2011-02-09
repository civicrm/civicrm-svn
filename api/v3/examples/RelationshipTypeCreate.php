<?php 

function relationship_type_create_example(){
    $params = array(
    
                  'name_a_b' 		=> 'Relation 1 for relationship type create',
                  'name_b_a' 		=> 'Relation 2 for relationship type create',
                  'contact_type_a' 		=> 'Individual',
                  'contact_type_b' 		=> 'Organization',
                  'is_reserved' 		=> '1',
                  'is_active' 		=> '1',
                  'version' 		=> '3',
                  'sequential' 		=> '1',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'RelationshipType','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function relationship_type_create_expectedresult(){

  $expectedResult = 
            array(
                  'is_error' 		=> '1',
                  'error_message' 		=> 'Undefined variable: dao',

  );

  return $expectedResult  ;
}

