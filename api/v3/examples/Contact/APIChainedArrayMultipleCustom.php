<?php



/*
 /*this demonstrates the usage of chained api functions. A variety of techniques are used
 */
function contact_get_example(){
$params = array( 
  'id' => 1,
  'version' => 3,
  'api.website.getValue' => array( 
      'return' => 'url',
    ),
  'api.Contribution.getCount' => array(),
  'api.CustomValue.get' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'contact','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contact_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'contact_id' => '1',
          'contact_type' => 'Individual',
          'sort_name' => 'xyz3, abc3',
          'display_name' => 'abc3 xyz3',
          'do_not_email' => 0,
          'do_not_phone' => 0,
          'do_not_mail' => 0,
          'do_not_sms' => 0,
          'do_not_trade' => 0,
          'is_opt_out' => 0,
          'preferred_mail_format' => 'Both',
          'first_name' => 'abc3',
          'last_name' => 'xyz3',
          'is_deceased' => 0,
          'contact_is_deleted' => 0,
          'email_id' => '2',
          'email' => 'man3@yahoo.com',
          'on_hold' => 0,
          'id' => '1',
          'api.website.getValue' => 'http://civicrm.org',
          'api.Contribution.getCount' => 2,
          'api.CustomValue.get' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 10,
              'values' => array( 
                  '1-0' => array( 
                      'entity_id' => '1',
                      'entity_table' => 'Contact',
                      'testGetIndividualWithChainedArraysAndMultipleCustom' => 'value 4',
                    ),
                  '2-1' => array( 
                      'entity_id' => '1',
                      'entity_table' => 'Contact',
                      'Cust_Field' => 'value 2',
                    ),
                  '3-1' => array( 
                      'entity_id' => '1',
                      'entity_table' => 'Contact',
                      'field_2' => 'warm beer',
                    ),
                  '4-1' => array( 
                      'entity_id' => '1',
                      'entity_table' => 'Contact',
                      'field_3' => '',
                    ),
                  '2-2' => array( 
                      'entity_id' => '1',
                      'entity_table' => 'Contact',
                      'Cust_Field' => 'value 3',
                    ),
                  '3-2' => array( 
                      'entity_id' => '1',
                      'entity_table' => 'Contact',
                      'field_2' => '',
                    ),
                  '4-2' => array( 
                      'entity_id' => '1',
                      'entity_table' => 'Contact',
                      'field_3' => '',
                    ),
                  '5-1' => array( 
                      'entity_id' => '1',
                      'entity_table' => 'Contact',
                      'Cust_Field' => '',
                    ),
                  '6-1' => array( 
                      'entity_id' => '1',
                      'entity_table' => 'Contact',
                      'field_2' => 'vegemite',
                    ),
                  '7-1' => array( 
                      'entity_id' => '1',
                      'entity_table' => 'Contact',
                      'field_3' => '',
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
* testGetIndividualWithChainedArraysAndMultipleCustom and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/ContactTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/