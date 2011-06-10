<?php



/*
 
 */
function relationship_get_example(){
$params = array( 
  'contact_id' => 40,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'relationship','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function relationship_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 6,
  'values' => array( 
      '6' => array( 
          'id' => '6',
          'cid' => '39',
          'contact_id_a' => '39',
          'contact_id_b' => '40',
          'relationship_type_id' => '29',
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
          'is_permission_a_b' => '',
          'is_permission_b_a' => '',
          'case_id' => '',
          'civicrm_relationship_type_id' => '29',
          'rtype' => 'b_a',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* relationship_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/