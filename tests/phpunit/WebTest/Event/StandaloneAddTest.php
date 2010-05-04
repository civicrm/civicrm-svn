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

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
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

      // Adding Anderson, Adam
      // We're using Quick Add block on the main page for this.
      $this->webtestAddContact( "Adam", "Anderson" );
      $contactName = "Anderson, Adam";

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
      $this->select("role_id", "value=1");

      // Choosing the Date.
      // Please note that we don't want to put in fixed date, since
      // we want this test to work in the future and not fail because
      // of date being set in the past. Therefore, using helper _lastDay function.
      $this->click("register_date");
      $dayId = $this->_lastDay();
      $this->click("link=$dayId");

      // Setting time.
      // TODO TBD
      
      $this->select("status_id", "value=1");

      // Setting registration source
      $this->type("source", "Event StandaloneAddTest Webtest");

      // Since we're here, let's check of screen help is being displayed properly
      $this->assertTrue($this->isTextPresent("Source for this registration (if applicable)."));

      // Clicking save.
      $this->click("_qf_Participant_upload-bottom");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("Event registration for has been added"), "Status message didn't show up after saving!");

  }

}
?>
