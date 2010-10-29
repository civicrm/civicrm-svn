<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
      $this->webtestLogin();

      // Adding Anderson, Anthony and Summerson, Samuel for testStandaloneActivityAdd test
      // We're using Quick Add block on the main page for this.
      $this->webtestAddContact( "Anthony", "Anderson" );
      $this->webtestAddContact( "Samuel", "Summerson" );

      // Go directly to the URL of the screen that you will be testing (New Activity-standalone).
      $this->open($this->sboxPath . "civicrm/activity&reset=1&action=add&context=standalone");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Activity_upload");

      // Let's start filling the form with values.

      // Select one of the options in Activity Type selector. Use option value, not label - since labels can be translated and test would fail
      $this->select("activity_type_id", "value=1");

      // We're filling in ajaxiefied  "With Contact" field:
      // We can not use id as selector for these input widgets. Use css selector, starting with the table row containing this field (which will have a unique class)
      // Typing contact's name into the field (using typeKeys(), not type()!)...
      $this->typeKeys("css=tr.crm-activity-form-block-target_contact_id input.token-input-box", 'Anthon');
      
      // ...waiting for drop down with results to show up...
      $this->waitForElementPresent("css=tr.crm-activity-form-block-target_contact_id td div ul li");
      
      //token-input-dropdown-facebook
      // ...clicking first result...
      $this->click("css=tr.crm-activity-form-block-target_contact_id td div ul li");

      // ...again, waiting for the box with contact name to show up (span with delete token class indicates that it's present)...
      $this->waitForElementPresent("css=tr.crm-activity-form-block-target_contact_id td ul li span.token-input-delete-token-facebook");
      
      // ...and verifying if the page contains properly formatted display name for chosen contact.
      $this->assertTrue($this->isTextPresent("Anderson, Anthony"), "Contact not found in line " . __LINE__ );

      // Now we're doing the same for "Assigned To" field.
      // Typing contact's name into the field (using typeKeys(), not type()!)...
      $this->typeKeys("css=tr.crm-activity-form-block-assignee_contact_id input.token-input-box", 'Summerson');
      
      // ...waiting for drop down with results to show up...
      $this->waitForElementPresent("css=tr.crm-activity-form-block-assignee_contact_id td div ul li");
      
      // ...clicking first result (which is an li element), selenium picks first matching element so we don't need to specify that...
      $this->click("css=tr.crm-activity-form-block-assignee_contact_id td div ul li");

      // ...again, waiting for the box with contact name to show up...
      $this->waitForElementPresent("css=tr.crm-activity-form-block-assignee_contact_id td ul li span.token-input-delete-token-facebook");
      
      // ...and verifying if the page contains properly formatted display name for chosen contact.
      $this->assertTrue($this->isTextPresent("Summerson, Samuel"), "Contact not found in line " . __LINE__ );
      
      // Since we're here, let's check of screen help is being displayed properly
      $this->assertTrue($this->isTextPresent("A copy of this activity will be emailed to each Assignee"));

      // Putting the contents into subject field - assigning the text to variable, it'll come in handy later
      $subject = "This is subject of test activity being added through standalone screen.";
      // For simple input fields we can use field id as selector
      $this->type("subject", $subject);
      $this->type("location", "Some location needs to be put in this field.");

      // Choosing the Date.
      // Please note that we don't want to put in fixed date, since
      // we want this test to work in the future and not fail because
      // of date being set in the past. Therefore, using helper webtestFillDate function.
      $this->webtestFillDateTime('activity_date_time','+1 month 11:10PM');

      // Setting duration.
      $this->type("duration", "30");

      // Putting in details.
      $this->type("details", "Really brief details information.");

      // Making sure that status is set to Scheduled (using value, not label).
      $this->select("status_id", "value=1");

      // Setting priority.
      $this->select("priority_id", "value=1");   

      // Adding attachment
      // TODO TBD
      
      // Scheduling follow-up.
      $this->click( "css=.crm-activity-form-block-schedule_followup div.crm-accordion-header" );
      $this->select( "followup_activity_type_id", "value=1" );
      $this->type( "interval", "1" );
      $this->select( "interval_unit","value=day" ); 
      $this->type( "followup_activity_subject","This is subject of schedule follow-up activity");

      // Clicking save.
      $this->click("_qf_Activity_upload");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("Activity '$subject' has been saved."), "Status message didn't show up after saving!");

      $this->click("css=#recently-viewed .crm-recently-viewed a");
      $this->waitForPageToLoad("30000");

      $expected =  array(
                         'Subject'               => $subject,
                         'Location'              => 'Some location needs to be put in this field.',
                         'Status'                => 'Scheduled',
                         'Duration'              => '30',
                         // Tough luck filling in WYSIWYG editor, so skipping verification for now.
                         //'Details'               => 'Really brief details information.',
                         'Priority'              => 'Urgent',
                         );
      foreach ($expected as $label => $value) {
          $this->verifyText("xpath=//table//tr/td/label[text()=\"$label\"]/../../td[2]", preg_quote($value));
      }
  }

}
?>
