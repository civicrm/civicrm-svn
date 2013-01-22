<?php

/*
 shows setting a variable for all domains
 */
function setting_create_example(){
$params = array( 
  'version' => 3,
  'domain_id' => 'all',
  'uniq_email_per_site' => 1,
);

  $result = civicrm_api( 'setting','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function setting_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 1,
  'error_message' => 'All domains not retrieved - problem with Domain Get api call Undefined index: contact_id',
);

  return $expectedResult  ;
}


/*
* This example has been generated from the API test suite. The test that created it is called
*
* testCreateSettingMultipleDomains and can be found in
* http://svn.civicrm.org/civicrm/trunk/tests/phpunit/CiviTest/api/v3/SettingTest.php
*
* You can see the outcome of the API tests at
* http://tests.dev.civicrm.org/trunk/results-api_v3
*
* To Learn about the API read
* http://book.civicrm.org/developer/current/techniques/api/
*
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
*
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*
* API Standards documentation:
* http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
*/