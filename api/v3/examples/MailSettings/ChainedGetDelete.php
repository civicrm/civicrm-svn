<?php

/*
 demonstrates get + delete in the same call
 */
function mail_settings_get_example(){
$params = array( 
  'version' => 3,
  'title' => 'MailSettings title',
  'api.MailSettings.delete' => 1,
);

  $result = civicrm_api( 'mail_settings','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function mail_settings_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 2,
  'values' => array( 
      '1' => array( 
          'id' => '1',
          'domain_id' => '1',
          'name' => 'default',
          'is_default' => '1',
          'domain' => 'EXAMPLE.ORG',
          'api.MailSettings.delete' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 1,
              'values' => 1,
            ),
        ),
      '3' => array( 
          'id' => '3',
          'domain_id' => '1',
          'name' => 'my mail setting',
          'is_default' => 0,
          'domain' => 'setting.com',
          'server' => 'localhost',
          'username' => 'sue',
          'password' => 'pass',
          'is_ssl' => 0,
          'api.MailSettings.delete' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 1,
              'values' => 1,
            ),
        ),
    ),
);

  return $expectedResult  ;
}


/*
* This example has been generated from the API test suite. The test that created it is called
*
* testGetMailSettingsChainDelete and can be found in
* http://svn.civicrm.org/civicrm/trunk/tests/phpunit/CiviTest/api/v3/MailSettingsTest.php
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