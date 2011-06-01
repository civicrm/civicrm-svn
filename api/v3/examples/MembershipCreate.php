<?php



/*
 
 */
function membership_create_example(){
$params = array( 
  'contact_id' => 20,
  'membership_type_id' => 19,
  'join_date' => '2009-01-21',
  'start_date' => '2009-01-21',
  'end_date' => '2009-12-21',
  'source' => 'Payment',
  'is_override' => 1,
  'status_id' => 25,
  'version' => 3,
  'custom_2' => 'custom string',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'membership','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_create_expectedresult(){

  $expectedResult = array( 
  'id' => 11,
  'is_error' => 0,
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* membership_create 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/