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


 
class WebTest_Event_AddFreeEventFromTemplateTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function _checkStrings( &$strings ) {
      // search for elements
      foreach ( $strings as $string ) {
          $this->assertTrue($this->isTextPresent($string), "Could not find $string on page");
      }

  }

  function testNewEventForm( )
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );
    
      $this->webtestLogin( );

      // visit New Event form
      $this->open($this->sboxPath . "civicrm/event/add&reset=1&action=add");
      $this->waitForElementPresent("_qf_EventInfo_upload-bottom");

      $stringsToCheck = 
          array( 'Event Type',
                 'Participant Role',
                 'Participant Listing',
                 'Event Title',
                 'Event Summary',
                 'Complete Description',
                 'Start Date',
                 'End Date / Time',
                 'Max Number of Participants',
                 'Include Map to Event Location?',
                 'Public Event?',
                 'Is this Event Active?' );

      $this->_checkStrings( $stringsToCheck );
  }

  function testAddFreeEventFromTemplate()
  {

      $this->open( $this->sboxPath );

      $this->webtestLogin();

      // Go directly to the URL of the screen that you will be testing (New Event).
      $this->open($this->sboxPath . "civicrm/event/add&reset=1&action=add");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_EventInfo_upload-bottom");

      // Let's start filling the form with values.
      // Select "Free Meeting with Online Registration" template. Use option value, not label - since labels can be translated and test would fail
      $this->select("template_id", "value=5");
      
      // Wait for event type to be filled in (since page reloads)
      $this->waitForPageToLoad('30000');
      $this->verifySelectedValue("event_type_id", "4");

      // Attendee role s/b selected now.
      $this->verifySelectedValue("default_role_id", "1");
      
      // Enter Event Title, Summary and Description
      $eventTitle = 'My Free Meeting - '.substr(sha1(rand()), 0, 7);
      $this->type("title", $eventTitle);
      $this->type("summary", "This is a free meeting. Sign up now!");

      // Enter description in ckEditor
      // not working?? $this->type("css=td#cke_contents_description body", "Here is a description for this meeting.");

      // Choose Start and End dates.
      // Using helper webtestFillDate function.
      $this->webtestFillDateTime("start_date", "+1 week");
      $this->webtestFillDateTime("end_date", "+1 week 1 day 8 hours ");

      $this->type("max_participants", "50");
      $this->click("is_map");
      $this->click("_qf_EventInfo_upload-bottom");      

      // Wait for Location tab form to load
      $this->waitForElementPresent("_qf_Location_upload-bottom");

      // Fill in address fields
      $streetAddress = "100 Main Street";
      $this->type("address_1_street_address", $streetAddress);
      $this->type("address_1_city", "San Francisco");
      $this->type("address_1_postal_code", "94117");
      $this->select("address_1_state_province_id", "value=1004");
      $this->type("email_1_email", "info@civicrm.org");

      $this->click("_qf_Location_upload-bottom");      

      // Wait for "saved" status msg
      $this->waitForPageToLoad('30000');
      $this->waitForTextPresent("'Event Location' information has been saved.");

      // Go to Fees tab and check that Paid Event is false (No)
      $this->click("link=Fees");
      $this->waitForElementPresent("_qf_Fee_upload-bottom");
      $this->verifyChecked("CIVICRM_QFID_0_No");
      $this->click("_qf_Fee_upload-bottom");      


      // Wait for "saved" status msg
      $this->waitForPageToLoad('30000');
      $this->waitForTextPresent("'Fees' information has been saved.");
      
      // Go to Online Registration tab
      $this->click("link=Online Registration");
      $this->waitForElementPresent("_qf_Registration_upload-bottom");

      // Enter intro text for registration page
      $registerIntro = "Fill in all the fields below and click Continue.";
      $this->type("intro_text", $registerIntro);
      $this->click("_qf_Registration_upload-bottom");
      $this->waitForPageToLoad('30000');
      $this->waitForTextPresent("'Online Registration' information has been saved.");

      // verify event input on info page
      // start at Manage Events listing
      $this->open($this->sboxPath . "civicrm/event/manage&reset=1");
      $this->click("link=$eventTitle");
      
      $this->waitForPageToLoad('30000');
      // Look for Register button
      $this->waitForElementPresent("link=Register Now");
      
      // Check that event fees section is not present
      $this->verifyElementNotPresent("css=div.event_fees-section");

      // Go to Register page and check for intro text and NO fee section
      $this->click("link=Register Now");
      $this->waitForElementPresent("_qf_Register_upload-bottom");
      $this->verifyTextPresent($registerIntro);
      $this->verifyElementNotPresent("css=div.paid_event-section");
      
  }

}
