<?php 

function relationship_get_example(){
    $params = array(
    
                  'contact_id' 		=> '2',
                  'version' 		=> '3',

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'relationship','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function relationship_get_expectedresult(){

  $expectedResult = 
     array(
           'is_error' 		=> '0',
           'version' 		=> '3',
           'count' 		=> '1',
           'id' 		=> '1',
           'values' 		=> array(           '1' =>  array(
                      'id' => '1',
                      'cid' => '1',
                      'contact_id_a' => '1',
                      'contact_id_b' => '2',
                      'relationship_type_id' => '10',
                      'relation' => 'Relation 2 for delete',
                      'name' => 'Anderson, Anthony',
                      'display_name' => 'Mr. Anthony Anderson II',
                      'job_title' => '',
                      'email' => 'anthony_anderson@civicrm.org',
                      'phone' => '',
                      'employer_id' => '',
                      'organization_name' => '',
                      'country' => '',
                      'city' => '',
                      'state' => '',
                      'start_date' => '',
                      'end_date' => '',
                      'description' => '',
                      'is_active' => '1',
                      'is_permission_a_b' => '0',
                      'is_permission_b_a' => '0',
                      'case_id' => '',
                      'civicrm_relationship_type_id' => '10',
                      'rtype' => 'b_a',
           ),           ),
      );

  return $expectedResult  ;
}

