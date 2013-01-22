<?php

/*
 
 */
function contribution_recur_create_example(){
$params = array( 
  'version' => 3,
  'contact_id' => 3,
  'installments' => '12',
  'frequency_interval' => '1',
  'amount' => '500',
  'contribution_status_id' => 1,
  'start_date' => '2012-01-01 00:00:00',
  'currency' => 'USD',
  'frequency_unit' => 'day',
);

  $result = civicrm_api( 'contribution_recur','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contribution_recur_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 1,
  'error_message' => 'DB Error: 1364 ** Field 'create_date' doesn't have a default value [DB Error: unknown error]',
  'tip' => 'add debug=1 to your API call to have more info about the error',
);

  return $expectedResult  ;
}


/*
* This example has been generated from the API test suite. The test that created it is called
*
* testCreateContributionRecur and can be found in
* http://svn.civicrm.org/civicrm/trunk/tests/phpunit/CiviTest/api/v3/ContributionRecurTest.php
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