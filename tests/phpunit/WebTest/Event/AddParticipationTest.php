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


 
class WebTest_Event_AddParticipationTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testEventParticipationAdd()
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

      // Go directly to the URL of the screen that you will be testing (Register Participant for Event-standalone).
      $this->open($this->sboxPath . "civicrm/participant/add?reset=1&action=add&context=standalone");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Participant_upload-bottom");

      // Let's start filling the form with values.
      // Type contact last name in contact auto-complete, wait for dropdown and click first result
      $this->webtestFillAutocomplete( $firstName );

      // Select event. Based on label for now.
      $this->select("event_id", "label=regexp:Rain-forest Cup Youth Soccer Tournament.");
      
      // Select role
      $this->click("role_id[2]");

      // Choose Registration Date.
      // Using helper webtestFillDate function.
      $this->webtestFillDate('register_date', 'now');
      $today = date('F jS, Y', strtotime('now'));
      // May 5th, 2010

      // Select participant status
      $this->select("status_id", "value=1");

      // Setting registration source
      $this->type("source", "Event StandaloneAddTest Webtest");

      // Since we're here, let's check of screen help is being displayed properly
      $this->assertTrue($this->isTextPresent("Source for this registration (if applicable)."));

      // Select an event fee
      $feeHelp = "Event Fee Level (if applicable).";
      $this->waitForTextPresent($feeHelp);
      $this->click("CIVICRM_QFID_552_10");

      // Select 'Record Payment'
      $this->click("record_contribution");
      
      // Enter amount to be paid (note: this should default to selected fee level amount, s/b fixed during 3.2 cycle)
      $this->type("total_amount", "800");
      
      // Select payment method = Check and enter chk number
      $this->select("payment_instrument_id", "value=4");
      $this->waitForElementPresent("check_number");
      $this->type("check_number", "1044");
      
      // go for the chicken combo (obviously)
//      $this->click("CIVICRM_QFID_chicken_Chicken");
      
      // Clicking save.
      $this->click("_qf_Participant_upload-bottom");
      $this->waitForPageToLoad("30000");
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent("Event registration for $displayName has been added"), "Status message didn't show up after saving!");
      
      $this->waitForElementPresent( "xpath=//div[@id='Events']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
      //click through to the participant view screen
      $this->click( "xpath=//div[@id='Events']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
      $this->waitForElementPresent( "_qf_ParticipantView_cancel-bottom" );
      
      $expected = array(
                        2 => 'Rain-forest Cup Youth Soccer Tournament', 
                        3 => 'Attendee',
                        5 => 'Registered',
                        6 => 'Event StandaloneAddTest Webtest', 
                        7 => 'Tiny-tots (ages 5-8) - $ 800.00',
                        );
      foreach ( $expected as $label => $value ) {
          $this->verifyText("xpath=id('ParticipantView')/div[2]/table[1]/tbody/tr[$label]/td[2]", preg_quote($value));
      }
      
      // check contribution record as well
      //click through to the contribution view screen
      $this->waitForElementPresent( "xpath=id('ParticipantView')/div[2]/table[2]/tbody/tr[1]/td[8]/span/a[text()='View']" );
      $this->click( "xpath=id('ParticipantView')/div[2]/table[2]/tbody/tr[1]/td[8]/span/a[text()='View']" );
      $this->waitForElementPresent( "_qf_ContributionView_cancel-bottom" );
      
      $expected = array(
                        1 => $displayName,
                        2 => 'Event Fee', 
                        3 => '$ 800.00',
                        4 => $today, 
                        5 => 'Completed',
                        6 => 'Check',
                        7 => '1044',
                        );      
      
      foreach ( $expected as $label => $value ) {
          $this->verifyText("xpath=id('ContributionView')/div[2]/table[1]/tbody/tr[$label]/td[2]", preg_quote($value));
      }
  }
  
}
