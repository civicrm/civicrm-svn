<?php

/*
 
 */
function membership_payment_create_example(){
$params = array( 
  'contribution_id' => '',
  'membership_id' => 1,
  'version' => 3,
);

  $result = civicrm_api( 'membership_payment','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_payment_create_expectedresult(){

  $expectedResult = array( 
  'fields' => array( 
      '0' => 'contribution_id',
    ),
  'error_code' => 'mandatory_missing',
  'is_error' => 1,
  'error_message' => 'Mandatory key(s) missing from params array: contribution_id',
);

  return $expectedResult  ;
}


/*
* This example has been generated from the API test suite. The test that created it is called
*
* testCreate and can be found in
* http://svn.civicrm.org/civicrm/trunk/tests/phpunit/CiviTest/api/v3/MembershipPaymentTest.php
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