<?php



/*
 
 */
function activity_get_example(){
$params = array( 
  'activity_id' => 4,
  'activity_type_id' => 1,
  'version' => 3,
  'sequential' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'activity','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function activity_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 0,
  'values' => array(),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* activity_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/