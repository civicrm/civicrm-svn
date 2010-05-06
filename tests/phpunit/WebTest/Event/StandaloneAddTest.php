<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'CiviTest/CiviSeleniumTestCase.php';


 
class WebTest_Event_StandaloneAddTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testStandaloneEventAdd()
  {

      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();

      // Adding contact with randomized first name (so we can then select that contact when creating event registration)
      // We're using Quick Add block on the main page for this.
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Anderson", true );
      $contactName = "Anderson, $firstName";
      $displayName = "$firstName Anderson";

      // Go directly to the URL of the screen that you will be testing (New Activity-standalone).
      $this->open($this->sboxPath . "civicrm/participant/add?reset=1&action=add&context=standalone");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Participant_upload-bottom");

      // Let's start filling the form with values.
      // Type contact last name in contact auto-complete, wait for dropdown and click first result
      $this->webtestFillAutocomplete( $contactName );

      // Select first event. Use option value, not label - since labels can be translated and test would fail
      $this->select("event_id", "value=1");
      
      // Select role
      $this->select("role_id", "value=1");

      // Choosing the Date.
      // Please note that we don't want to put in fixed date, since
      // we want this test to work in the future and not fail because
      // of date being set in the past. Therefore, using helper webtestFillDate function.
      $this->webtestFillDate('register_date');

      // Setting time.
      // TODO TBD
      
      // Select participant status
      $this->select("status_id", "value=1");

      // Setting registration source
      $this->type("source", "Event StandaloneAddTest Webtest");

      // Since we're here, let's check of screen help is being displayed properly
      $this->assertTrue($this->isTextPresent("Source for this registration (if applicable)."));

      // Select an event fee
      $feeHelp = "Event Fee Level (if applicable).";
      $this->waitForTextPresent($feeHelp);
      $this->click("css=tr.crm-participant-form-block-fee_amount input");

      // Select 'Record Payment'
      $this->click("record_contribution");
      
      // Enter amount to be paid (note: this should default to selected fee level amount, s/b fixed during 3.2 cycle)
      $this->type("total_amount", "50.00");
      
      // Select payment method = Check and enter chk number
      $this->select("payment_instrument_id", "value=4");
      $this->waitForElementPresent("check_number");
      $this->type("check_number", "1044");
      
      // go for the chicken combo (obviously)
      $this->click("CIVICRM_QFID_chicken_Chicken");

      // Clicking save.
      $this->click("_qf_Participant_upload-bottom");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("Event registration for $displayName has been added"), "Status message didn't show up after saving!");

      // click through to the Participant View screen
      $this->waitForElementPresent("link=View");
      $this->click('link=View');
      $this->waitForPageToLoad('30000');

      // verify that the event registration values were properly saved by checking for label/value pairs on the view page 
      $this->webtestVerifyTabularData(
          array(
              'Participant Role' => 'Attendee',
              'Status'           => 'Registered',
              'Soup Selection'   => 'Chicken Combo',
          )
      );
      
      // check

  }

}
?>
