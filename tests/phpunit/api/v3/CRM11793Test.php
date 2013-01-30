<?php
/**
 *  File for the CRM11793 issue
 *  Include class definitions
 */
require_once 'CiviTest/CiviUnitTestCase.php';


/**
 *  Test APIv3 civicrm_activity_* functions
 *
 *  @package   CiviCRM
 */
class api_v3_CRM11793Test extends CiviUnitTestCase {
  /**
   *  Constructor
   *
   *  Initialize configuration
   */
  function __construct() {
    parent::__construct();
  }

  /**
   *  Test setup for every test
   *
   *  Connect to the database, truncate the tables that will be used
   *  and redirect stdin to a temporary file
   */
  public function setUp() {
    //  Connect to the database
    parent::setUp();
  }

  function tearDown() {
  }

  /**
   *  Test civicrm_contact_create
   *
   *  Verify that attempt to create individual contact with only
   *  first and last names succeeds
   */
  function testCRM11793() {
    $result = civicrm_api(
      'contact',
      'get',
      array(
        'version' => 3,
        'contact_type' => 'Organization'
      )
    );

    $this->assertEquals($result['is_error'], 0, "In line " . __LINE__);
    foreach ($result['values'] as $idx => $contact) {
      $this->assertEquals($contact['contact_type'], 'Organization', "In line " . __LINE__);
    }
  }
}