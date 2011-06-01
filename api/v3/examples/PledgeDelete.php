<?php



/*
 
 */
function pledge_delete_example(){
$params = array( 
  'pledge_id' => 8,
  'version' => 3,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'pledge','delete',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function pledge_delete_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 8,
  'values' => array( 
      '8' => 8,
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* pledge_delete 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC40/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/