<?php



/*
 
 */
function contribution_page_get_example(){
$params = array( 
  'version' => 3,
  'amount' => '34567',
  'currency' => 'NZD',
  'financial_account_id' => 1,
);

  require_once 'api/api.php';
  $result = civicrm_api( 'contribution_page','get',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function contribution_page_get_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 3,
  'values' => array( 
      '3' => array( 
          'id' => '3',
          'financial_account_id' => '1',
          'is_credit_card_only' => 0,
          'is_monetary' => '1',
          'is_recur' => 0,
          'is_confirm_enabled' => '1',
          'is_recur_interval' => 0,
          'is_recur_installments' => 0,
          'is_pay_later' => 0,
          'is_allow_other_amount' => 0,
          'goal_amount' => '34567.00',
          'is_for_organization' => 0,
          'is_email_receipt' => 0,
          'amount_block_is_active' => '1',
          'currency' => 'NZD',
          'is_share' => '1',
        ),
    ),
);

  return $expectedResult  ;
}




/*
* This example has been generated from the API test suite. The test that created it is called
* 
* testGetContributionPageByAmount and can be found in 
* http://svn.civicrm.org/civicrm/branches/v3.4/tests/phpunit/CiviTest/api/v3/ContributionPageTest.php
* 
* You can see the outcome of the API tests at 
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*/