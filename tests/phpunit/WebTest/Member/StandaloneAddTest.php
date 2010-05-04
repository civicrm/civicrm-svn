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


 
class WebTest_Member_StandaloneAddTest extends CiviSeleniumTestCase {

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

      $this->open( $this->sboxPath );
      $this->webtestLogin();
      $this->webtestAddContact( "Memberino", "Memberson", "memberino@memberson.name" );

      $this->open($this->sboxPath . "civicrm/member/add&reset=1&action=add&context=standalone");

      $this->waitForElementPresent("_qf_Membership_upload");


      // select contact
      // fill in Membership Organization and Type
      // fill in Source
      // fill in Join Date
      // fill in Start Date
      // fill in End Date
      // fill in Status Override?
      // fill in Record Membership Payment?



      //---      
      
      // Select one of the options in Activity Type selector
      $this->select("activity_type_id", "value=1");

      // We're filling in ajaxiefied  "With Contact" field:
      // Typing contact's name into the field (using typeKeys(), not type()!)...
      $this->typeKeys("css=tr.crm-activity-form-block-target_contact_id input.token-input-box", 'Anthony');
      
      // ...waiting for drop down with results to show up...
      //$this->waitForElementPresent("//form[@id='Activity']/div[2]/table/tbody/tr[3]/td[2]/div/ul/li[1]");
      $this->waitForElementPresent("css=tr.crm-activity-form-block-target_contact_id td div ul li");
      
      //token-input-dropdown-facebook
      // ...clicking first result...
      $this->click("css=tr.crm-activity-form-block-target_contact_id td div ul li");

      // ...again, waiting for the box with contact name to show up...
      $this->waitForElementPresent("css=tr.crm-activity-form-block-target_contact_id td ul li span.token-input-delete-token-facebook");
      
      // ...and verifying if the page contains properly formatted display name for chosen contact.
      $this->assertTrue($this->isTextPresent("Anderson, Anthony"), "Contact not found in line " . __LINE__ );

      // Now we're doing the same for "Assigned To" field.
      // Typing contact's name into the field (using typeKeys(), not type()!)...
      $this->typeKeys("css=tr.crm-activity-form-block-assignee_contact_id input.token-input-box", 'Summerson');
      
      // ...waiting for drop down with results to show up...
      //$this->waitForElementPresent("//form[@id='Activity']/div[2]/table/tbody/tr[3]/td[2]/div/ul/li[1]");
      $this->waitForElementPresent("css=tr.crm-activity-form-block-assignee_contact_id td div ul li");
      
      // ...clicking first result...
      $this->click("css=tr.crm-activity-form-block-assignee_contact_id td div ul li");

      // ...again, waiting for the box with contact name to show up...
      $this->waitForElementPresent("css=tr.crm-activity-form-block-assignee_contact_id td ul li span.token-input-delete-token-facebook");
      
      // ...and verifying if the page contains properly formatted display name for chosen contact.
      $this->assertTrue($this->isTextPresent("Summerson, Samuel"), "Contact not found in line " . __LINE__ );
      
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
