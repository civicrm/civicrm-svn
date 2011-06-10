<?php



/*
 
 */
function membership_get_example(){
$params = array( 
  'contact_id' => 9,
  'membership_type_id' => 9,
  'join_date' => '2009-01-21',
  'start_date' => '2009-01-21',
  'end_date' => '2009-12-21',
  'source' => 'Payment',
  'is_override' => 1,
  'status_id' => 16,
  'version' => 3,
  'custom_1' => 'custom string',
);

  require_once 'api/api.php';
  $result = civicrm_api( 'membership','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function membership_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 6,
  'values' => array( 
      '6' => array( 
          'id' => 6,
          'contact_id' => 9,
          'membership_type_id' => 9,
          'join_date' => '20090121',
          'start_date' => '20090121',
          'end_date' => '20091221',
          'source' => 'Payment',
          'status_id' => 16,
          'is_override' => 1,
          'reminder_date' => 'null',
          'owner_membership_id' => '',
          'is_test' => '',
          'is_pay_later' => '',
          'contribution_recur_id' => '',
          'campaign_id' => '',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* membership_get 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/