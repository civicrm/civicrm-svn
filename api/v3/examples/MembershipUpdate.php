<?php



/*
 
 */
function membership_update_example(){
$params = array( 
  'contact_id' => 26,
  'membership_type_id' => 25,
  'join_date' => '2009-01-21',
  'start_date' => '2009-01-21',
  'end_date' => '2009-12-21',
  'source' => 'Payment',
  'is_override' => 1,
  'status_id' => 31,
  'version' => 3,
  'custom_3' => 'custom string',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'membership','update',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_update_expectedresult(){

  $expectedResult = array( 
  'id' => 17,
  'is_error' => 0,
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* membership_update 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/