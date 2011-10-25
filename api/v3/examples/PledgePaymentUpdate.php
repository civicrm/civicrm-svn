<?php



/*
 
 */
function pledge_payment_update_example(){
$params = array( 
  'contact_id' => 1,
  'pledge_id' => '',
  'contribution_id' => 1,
  'version' => 3,
  'status_id' => 2,
  'actual_amount' => 20,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'pledge_payment','update',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_payment_update_expectedresult(){

  $expectedResult = array( 
  'is_error' => 1,
  'error_message' => 'No pledge_payment with id ',
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testUpdatePledgePayment and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/PledgePaymentTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/