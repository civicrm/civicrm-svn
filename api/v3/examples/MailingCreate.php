<?php



/*
 
 */
function mailing_create_example(){
$params = array( 
  'subject' => 'maild',
  'body_text' => 'bdkfhdskfhduew',
  'version' => 3,
  'name' => 'mailing name',
  'created_id' => 1,
);


  $result = civicrm_api( 'mailing','create',$params );

  return $result;
}

/*
 * Function returns array of result expected from previous function
 */
function mailing_create_expectedresult(){

  $expectedResult = array( 
  'is_error' => 0,
  'version' => 3,
  'count' => 1,
  'id' => 1,
  'values' => array( 
      '1' => array( 
          'id' => 1,
          'domain_id' => 1,
          'header_id' => 'null',
          'footer_id' => 'null',
          'reply_id' => '',
          'unsubscribe_id' => '',
          'resubscribe_id' => '',
          'optout_id' => '',
          'name' => 'mailing name',
          'from_name' => 'null',
          'from_email' => 'info@EXAMPLE.ORG',
          'replyto_email' => 'info@EXAMPLE.ORG',
          'subject' => 'maild',
          'body_text' => 'bdkfhdskfhduew',
          'body_html' => '',
          'url_tracking' => true,
          'forward_replies' => '',
          'auto_responder' => 0,
          'open_tracking' => true,
          'is_completed' => '',
          'msg_template_id' => '',
          'override_verp' => true,
          'created_id' => '',
          'created_date' => '20130109235924',
          'scheduled_id' => '',
          'scheduled_date' => '20130109235924',
          'approver_id' => '',
          'approval_date' => '20130109235924',
          'approval_status_id' => '',
          'approval_note' => '',
          'is_archived' => '',
          'visibility' => 'User and User Admin Only',
          'campaign_id' => '',
          'dedupe_email' => '',
          'sms_provider_id' => '',
          'api.mailing_job.create' => array( 
              'is_error' => 0,
              'version' => 3,
              'count' => 1,
              'id' => 1,
              'values' => array( 
                  '0' => array( 
                      'id' => 1,
                      'mailing_id' => 1,
                      'scheduled_date' => '20130109235924',
                      'start_date' => '',
                      'end_date' => '',
                      'status' => 'Scheduled',
                      'is_test' => 0,
                      'job_type' => '',
                      'parent_id' => '',
                      'job_offset' => '',
                      'job_limit' => '',
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
* testMailerCreateSuccess and can be found in
* http://svn.civicrm.org/civicrm/trunk/tests/phpunit/CiviTest/api/v3/MailingTest.php
*
* You can see the outcome of the API tests at
* http://tests.dev.civicrm.org/trunk/results-api_v3
* and review the wiki at
* http://wiki.civicrm.org/confluence/display/CRMDOC/CiviCRM+Public+APIs
* Read more about testing here
* http://wiki.civicrm.org/confluence/display/CRM/Testing
*
* API Standards documentation:
* http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
*/