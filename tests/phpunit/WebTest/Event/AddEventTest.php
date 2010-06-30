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


 
class WebTest_Event_AddPaidEventTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testAddPaidEventNoTemplate()
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();

      // Go directly to the URL of the screen that you will be testing (New Event).
      $this->open($this->sboxPath . "civicrm/event/add&reset=1&action=add");

      $eventTitle = 'My Conference - '.substr(sha1(rand()), 0, 7);
      $eventDescription = "Here is a description for this conference.";
      $this->_testAddEventInfo( $eventTitle, $eventDescription );

      $streetAddress = "100 Main Street";
      $this->_testAddLocation( $streetAddress );
      
      $this->_testAddFees();
      
      // intro text for registration page
      $registerIntro = "Fill in all the fields below and click Continue.";
      $this->_testAddOnlineRegistration( $registerIntro );

      $eventInfoStrings = array( $eventTitle, $eventDescription, $streetAddress );
      $this->_testVerifyEventInfo( $eventTitle, $eventInfoStrings );
      
      $registerStrings = array("250.00 Member", "325.00 Non-member", $registerIntro );
      $this->_testVerifyRegisterPage( $registerStrings );
      
  }

  function testAddPaidEventWithTemplate()
  {
      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();

      // Go directly to the URL of the screen that you will be testing (New Event).
      $this->open($this->sboxPath . "civicrm/event/add&reset=1&action=add");

      $eventTitle = 'My Conference - '.substr(sha1(rand()), 0, 7);
      $eventDescription = "Here is a description for this conference.";
      // Select paid online registration template.
      $templateID = 6;
      $eventTypeID = 1;
      $this->_testAddEventInfoFromTemplate( $eventTitle, $eventDescription, $templateID, $eventTypeID );

      $streetAddress = "100 Main Street";
      $this->_testAddLocation( $streetAddress );
      
      $this->_testAddFees();
            
      // intro text for registration page
      $registerIntro = "Fill in all the fields below and click Continue.";
      $this->_testAddOnlineRegistration( $registerIntro );

      // $eventInfoStrings = array( $eventTitle, $eventDescription, $streetAddress );
      $eventInfoStrings = array( $eventTitle, $streetAddress );
      $this->_testVerifyEventInfo( $eventTitle, $eventInfoStrings );
      
      $registerStrings = array("250.00 Member", "325.00 Non-member", $registerIntro );
      $this->_testVerifyRegisterPage( $registerStrings );
      
  }
  
  function testAddFreeEventWithTemplate()
  {
      $this->open( $this->sboxPath );

      // Log in using webtestLogin() method
      $this->webtestLogin();

      // Go directly to the URL of the screen that you will be testing (New Event).
      $this->open($this->sboxPath . "civicrm/event/add&reset=1&action=add");

      $eventTitle = 'My Free Meeting - '.substr(sha1(rand()), 0, 7);
      $eventDescription = "Here is a description for this free meeting.";
      // Select "Free Meeting with Online Registration" template (id = 5).
      $templateID = 5;
      $eventTypeID = 4;
      $this->_testAddEventInfoFromTemplate( $eventTitle, $eventDescription, $templateID, $eventTypeID );

      $streetAddress = "100 Main Street";
      $this->_testAddLocation( $streetAddress );
      
      // Go to Fees tab and check that Paid Event is false (No)
      $this->click("link=Fees");
      $this->waitForElementPresent("_qf_Fee_upload-bottom");
      $this->verifyChecked("CIVICRM_QFID_0_No");
      
      // intro text for registration page
      $registerIntro = "Fill in all the fields below and click Continue.";
      $this->_testAddOnlineRegistration( $registerIntro );

      // $eventInfoStrings = array( $eventTitle, $eventDescription, $streetAddress );
      $eventInfoStrings = array( $eventTitle, $streetAddress );
      $this->_testVerifyEventInfo( $eventTitle, $eventInfoStrings );
      
      $registerStrings = array( $registerIntro );
      $this->_testVerifyRegisterPage( $registerStrings );
      // make sure paid_event div is NOT present since this is a free event
      $this->verifyElementNotPresent("css=div.paid_event-section");
      
  }
  
  function _testAddEventInfo( $eventTitle, $eventDescription ) {
      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_EventInfo_upload-bottom");

      // Let's start filling the form with values.
      $this->select("event_type_id", "value=1");
      
      // Attendee role s/b selected now.
      $this->select("default_role_id", "value=1");
      
      // Enter Event Title, Summary and Description
      $this->type("title", $eventTitle);
      $this->type("summary", "This is a great conference. Sign up now!");

      // Type description in ckEditor (fieldname, text to type, editor)
      $this->fillRichTextField( "description", $eventDescription,'CKEditor' );

      // Choose Start and End dates.
      // Using helper webtestFillDate function.
      $this->webtestFillDateTime("start_date", "+1 week");
      $this->webtestFillDateTime("end_date", "+1 week 1 day 8 hours ");

      $this->type("max_participants", "50");
      $this->click("is_map");
      $this->click("_qf_EventInfo_upload-bottom");      
  }
  
  function _testAddEventInfoFromTemplate( $eventTitle, $eventDescription, $templateID, $eventTypeID ) {
      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_EventInfo_upload-bottom");

      // Let's start filling the form with values.
      // Select event template. Use option value, not label - since labels can be translated and test would fail
      $this->select("template_id", "value={$templateID}");
      
      // Wait for event type to be filled in (since page reloads)
      $this->waitForPageToLoad('30000');
      $this->verifySelectedValue("event_type_id", $eventTypeID);

      // Attendee role s/b selected now.
      $this->verifySelectedValue("default_role_id", "1");
      
      // Enter Event Title, Summary and Description
      $this->type("title", $eventTitle);
      $this->type("summary", "This is a great conference. Sign up now!");

      // Type description in ckEditor (fieldname, text to type, editor)
      $this->fillRichTextField( "description", $eventDescription,'CKEditor' );

      // Choose Start and End dates.
      // Using helper webtestFillDate function.
      $this->webtestFillDateTime("start_date", "+1 week");
      $this->webtestFillDateTime("end_date", "+1 week 1 day 8 hours ");

      $this->type("max_participants", "50");
      $this->click("is_map");
      $this->click("_qf_EventInfo_upload-bottom");      
  }
  
  function _testAddLocation( $streetAddress ) {
      // Wait for Location tab form to load
      $this->waitForPageToLoad("30000");
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
      $this->waitForTextPresent("'Location' information has been saved.");
      
  }
  
  function _testAddFees( $discount=false, $priceSet=false ){
      // Go to Fees tab
      $this->click("link=Fees");
      $this->waitForElementPresent("_qf_Fee_upload-bottom");
      $this->click("CIVICRM_QFID_1_Yes");
      $this->select("payment_processor_id", "value=3");
      $this->select("contribution_type_id", "value=4");
      if ( $priceSet) {
          // get one - TBD
      } else {
          $this->type("label_1", "Member");
          $this->type("value_1", "250.00");
          $this->type("label_2", "Non-member");
          $this->type("value_2", "325.00");          
      }

      if ( $discount ) {
          // enter early bird discounts
      }
      
      $this->click("_qf_Fee_upload-bottom");      

      // Wait for "saved" status msg
      $this->waitForPageToLoad('30000');
      $this->waitForTextPresent("'Fee' information has been saved.");      
  }
  
  function _testAddOnlineRegistration($registerIntro){
      // Go to Online Registration tab
      $this->click("link=Online Registration");
      $this->waitForElementPresent("_qf_Registration_upload-bottom");

      $this->check("is_online_registration");
      $this->assertChecked("is_online_registration");
      
      $this->type("intro_text", $registerIntro);
      
      // enable confirmation email
      $this->click("CIVICRM_QFID_1_Yes");
      $this->type("confirm_from_name", "Jane Doe");
      $this->type("confirm_from_email", "jane.doe@example.org");

      $this->click("_qf_Registration_upload-bottom");
      $this->waitForPageToLoad("30000");
      $this->waitForTextPresent("'Registration' information has been saved.");
  }
  
  function _testVerifyEventInfo( $eventTitle, $eventInfoStrings ){
      // verify event input on info page
      // start at Manage Events listing
      $this->open($this->sboxPath . "civicrm/event/manage&reset=1");
      $this->click("link=$eventTitle");
      
      $this->waitForPageToLoad('30000');
      // Look for Register button
      $this->waitForElementPresent("link=Register Now");
      
      // Check for correct event info strings
      $this->assertStringsPresent( $eventInfoStrings );
  }

  function _testVerifyRegisterPage( $registerStrings ){
      // Go to Register page and check for intro text and fee levels
      $this->click("link=Register Now");
      $this->waitForElementPresent("_qf_Register_upload-bottom");
      $this->assertStringsPresent( $registerStrings );
  }
}
