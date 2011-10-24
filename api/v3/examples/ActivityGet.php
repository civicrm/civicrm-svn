<?php



/*
 
 */
function activity_get_example(){
$params = array( 
  'contact_id' => 17,
  'activity_type_id' => 1,
  'version' => 3,
  'sequential' => 1,
  'return.custom_1' => 1,
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
  'is_error' => 1,
  'error_message' => 'Mandatory key(s) missing from params array: source_contact_id',
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testActivityGetContact_idCustom and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/ActivityTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/