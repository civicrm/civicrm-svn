<?php



/*
 
 */
function activity_get_example(){
$params = array( 
  'activity_id' => 13,
  'version' => 3,
  'sequential' => 1,
  'return.assignee_contact_id' => 1,
  'api.contact.get' => array( 
      'id' => '$value.source_contact_id',
    ),
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
  'count' => 1,
  'id' => 13,
  'values' => array( 
      '0' => array( 
          'id' => '13',
          'source_contact_id' => '18',
          'activity_type_id' => '1',
          'subject' => 'test activity type id',
          'status_id' => '1',
          'priority_id' => '1',
          'assignee_contact_id' => array( 
              '0' => '19',
            ),
          'api.contact.get' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 1,
              'id' => 18,
              'values' => array( 
                  '0' => array( 
                      'contact_id' => '18',
                      'contact_type' => 'Individual',
                      'sort_name' => 'User 429722255, Logged In',
                      'display_name' => 'Logged In User 429722255',
                      'do_not_email' => 0,
                      'do_not_phone' => 0,
                      'do_not_mail' => 0,
                      'do_not_sms' => 0,
                      'do_not_trade' => 0,
                      'is_opt_out' => 0,
                      'preferred_mail_format' => 'Both',
                      'first_name' => 'Logged In',
                      'last_name' => 'User 429722255',
                      'is_deceased' => 0,
                      'contact_is_deleted' => 0,
                      'id' => '18',
                    ),
                ),
            ),
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testActivityGetGoodID1 and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/ActivityTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/