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


 
class WebTest_Mailing_MailingTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testAddMailing( ) {
      
      $this->open( $this->sboxPath );
      $this->webtestLogin();      
   
      //----do create test mailing group 
      
      // Go directly to the URL of the screen that you will be testing (New Group).
      $this->open($this->sboxPath . "civicrm/group/add&reset=1");
      $this->waitForElementPresent("_qf_Edit_upload");

      // make group name
      $groupName = 'group_'.substr(sha1(rand()), 0, 7);

      // fill group name
      $this->type("title", $groupName);
      
      // fill description
      $this->type("description", "New mailing group for Webtest");

      // enable Mailing List
      $this->click("group_type[2]");

      // select Visibility as Public Pages
      $this->select("visibility", "value=Public Pages");
      
      // Clicking save.
      $this->click("_qf_Edit_upload");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("The Group '$groupName' has been saved."));

      //---- create mailing contact and add to mailing Group
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Mailson", "mailino@mailson.co.in" );
      
      // go to group tab and add to mailing group
      $this->click("css=li#tab_group a");
      $this->waitForElementPresent("_qf_GroupContact_next");
      $this->select("group_id", "$groupName");
      $this->click("_qf_GroupContact_next");
      
      // Go directly to Schedule and Send Mailing form
      $this->open($this->sboxPath . "civicrm/mailing/send&reset=1");
      $this->waitForElementPresent("_qf_Group_cancel");
      
      //-------select recipients----------
      
      // fill mailing name
      $mailingName = substr(sha1(rand()), 0, 7);
      $this->type("name", "Mailing $mailingName Webtest");
      
      // Add the test mailing group
      $this->select("includeGroups-f", "$groupName");
      $this->click("add");
      
      // click next
      $this->click("_qf_Group_next");
      $this->waitForElementPresent("_qf_Settings_cancel");
      
      //--------track and respond----------
      
      // check for default settings options
      $this->assertChecked("url_tracking");
      $this->assertChecked("open_tracking");
      
      // do check count for Recipient
      $this->assertTrue($this->isTextPresent("Total Recipients: 1"));
      
      // no need tracking for this test      
      
      // click next with default settings
      $this->click("_qf_Settings_next");
      $this->waitForElementPresent("_qf_Upload_cancel");
      
      
      //--------Mailing content------------
      // let from email address be default
      
      // fill subject for mailing
      $this->type("subject", "Test subject for Webtest");
      
      // check for default option enabled
      $this->assertChecked("CIVICRM_QFID_1_Compose");
      
      // fill message (presently using script for simple text area)
      $this->click("//fieldset[@id='compose_id']/div[2]/div[1]");
      $this->type("text_message", "this is test content for Mailing $mailingName Webtest");
      
      // add attachment?
      
      // check for default header and footer ( with label ) 
      $this->assertSelectedLabel("header_id", "Mailing Header");
      $this->assertSelectedLabel("footer_id", "Mailing Footer");
      
      // do check count for Recipient
      $this->assertTrue($this->isTextPresent("Total Recipients: 1"));
      
      // click next with nominal content
      $this->click("_qf_Upload_upload");
      $this->waitForElementPresent("_qf_Test_cancel");
      
      //---------------Test------------------

      ////////--Commenting test mailing and mailing preview (test mailing and preview not presently working).
      
      // send test mailing
      //$this->type("test_email", "mailino@mailson.co.in");
      //$this->click("sendtest");
      
      // verify status message 
      //$this->assertTrue($this->isTextPresent("Your test message has been sent. Click 'Next' when you are ready to Schedule or Send your live mailing (you will still have a chance to confirm or cancel sending this mailing on the next page)."));
      
      // check mailing preview 
      //$this->click("//form[@id='Test']/div[2]/div[4]/div[1]");
      //$this->assertTrue($this->isTextPresent("this is test content for Mailing $mailingName Webtest"));
      
      ////////

      // do check count for Recipient
      $this->assertTrue($this->isTextPresent("Total Recipients: 1"));
      
      // click next
      $this->click("_qf_Test_next");
      $this->waitForElementPresent("_qf_Schedule_cancel");      
      
      //----------Schedule or Send------------
      
      // do check for default option enabled
      $this->assertChecked("now");
      
      // uncheck now option and schedule with date and time 
      $this->uncheck("now");
      $this->webtestFillDateTime("start_date");

      // do check count for Recipient
      $this->assertTrue($this->isTextPresent("Total Recipients: 1"));

      // finally schedule the mail by clicking submit
      $this->click("_qf_Schedule_next");
      $this->waitForPageToLoad("30000");
      
      //----------end New Mailing-------------

      //check redirected page to Scheduled and Sent Mailings and  verify for mailing name
      $this->assertTrue($this->isTextPresent("Scheduled and Sent Mailings"));
      $this->assertTrue($this->isTextPresent("Mailing $mailingName Webtest"));

  }
  
  
}
?>