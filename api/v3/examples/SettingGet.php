<?php



/*
 
 */
function setting_get_example(){
$params = array( 
  'version' => 3,
  'domain_id' => '',
  'uniq_email_per_site' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'setting','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function setting_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => '',
  'values' => array( 
      '' => array( 
          'uniq_email_per_site' => 0,
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetSetting and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/SettingTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/