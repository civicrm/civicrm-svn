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


 
class WebTest_Activity_StandaloneAddTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  /**
   * Helper function for filling in date selector, 
   * provides the number of last day in current month.
   */
  private function _lastDay() {
      $y = date('Y');
      $m = date('m');
      $r = strtotime("{$y}-{$m}-01");
      $r = strtotime('-1 second', strtotime('+1 month', $r));
      return date('d', $r);
  }

  function testStandaloneActivityAdd()
  {

      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );

      // Logging in. Remember to wait for page to load. In most cases,
      // you can rely on 30000 as the value that allows your test to pass, however,
      // sometimes your test might fail because of this. In such cases, it's better to pick one element
      // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
      // page contents loaded and you can continue your test execution.
      $this->type("edit-name", $this->settings->username);
      $this->type("edit-pass", $this->settings->password);
      $this->click("edit-submit");
      $this->waitForPageToLoad("30000");

      // Adding Anderson, Anthony and Summerson, Samuel for testStandaloneActivityAdd test
      // We're using Quick Add block on the main page for this.
      $this->open($this->sboxPath . "civicrm/dashboard?reset=1");
      $this->type("qa_first_name", "Anthony");
      $this->type("qa_last_name", "Anderson");
      $this->click("_qf_Contact_next");
      $this->waitForPageToLoad("30000");

      $this->open($this->sboxPath . "civicrm/dashboard?reset=1");
      $this->type("qa_first_name", "Samuel");
      $this->type("qa_last_name", "Summerson");
      $this->click("_qf_Contact_next");
      $this->waitForPageToLoad("30000");

      // Go directly to the URL of the screen that you wiwll be testing.
      $this->open($this->sboxPath . "civicrm/activity&reset=1&action=add&context=standalone");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Activity_upload");

      // Let's start filling the form with values.

      // Select one of the options in Activity Type selector
      $this->select("activity_type_id", "value=1");

      // We're filling in ajaxiefied  "With Contact" field:
      // Typing contact's name into the field (using typeKeys(), not type()!)...
      $this->typeKeys("css=tr.crm-activity-form-block-target_contact_id input.token-input-box", 'Anthony');
      
      // ...waiting for drop down with results to show up...
      $this->waitForElementPresent("//form[@id='Activity']/div[2]/table/tbody/tr[3]/td[2]/div/ul/li[1]");
      //$this->waitForElementPresent("css=tr.crm-activity-form-block-target_contact_id td ul li:contains('Anderson, Anthony')");
      
      //token-input-dropdown-facebook
      // ...clicking first result...
      $this->click("//form[@id='Activity']/div[2]/table/tbody/tr[3]/td[2]/div/ul/li[1]");
      // ...again, waiting for the box with contact name to show up...

      //$this->waitForElementPresent("//form[@id='Activity']/div[2]/table/tbody/tr[3]/td[2]/div/ul/li[1]/p");
      // ...and verifying if the page contains properly formatted display name for chosen contact.
      $this->assertTrue($this->isTextPresent("Anderson, Anthony"), "Contact not found in line " . __LINE__ );

      // Now we're doing the same for "Assigned To" field.
      // FIXME Which - unfortunately - doesn't work at the moment.
//      $this->click("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/ul/li/input");
//      $this->type("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/ul/li/input", "Samuel");
//      $this->click("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/ul/li/input");      
//      $this->waitForElementPresent("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/div");
//      $this->click("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/div/ul/li");
//      $this->waitForElementPresent("//form[@id='Activity']/fieldset/table/tbody/tr[4]/td[2]/ul/li");
//      $this->assertTrue($this->isTextPresent("Summerson, Samuel"), "Contact not found in line " . __LINE__ );  

      // Since we're here, let's check of screen help is being displayed properly
      $this->assertTrue($this->isTextPresent("A copy of this activity will be emailed to each Assignee"));

      // Putting the contents into subject field - assigning the text to variable, it'll come in handy later
      $subject = "This is subject of test activity being added through standalone screen.";
      $this->type("subject", $subject);
      $this->type("location", "Some location needs to be put in this field.");

      // Choosing the Date.
      // Please note that we don't want to put in fixed date, since
      // we want this test to work in the future and not fail because
      // of date being set in the past. Therefore, using helper _lastDay function.
      $this->click("activity_date_time");
      $dayId = $this->_lastDay();
      $this->click("link=$dayId");

      // Setting time.
      // TODO TBD
      
      // Setting duration.
      $this->type("duration", "30");

      // Putting in details.
      $this->type("details", "Really brief details information.");

      // Making sure that status is set to Scheduled.
      $this->select("status_id", "label=Scheduled");

      // Setting priority.
      $this->select("priority_id", "label=Urgent");                

      // Adding attachment
      // TODO TBD

      // Scheduling follow-up.
      // TODO TBD

      // Clicking save.
      $this->click("_qf_Activity_upload");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("Activity '$subject' has been saved."), "Status message didn't show up after saving!");

  }

}
?>
