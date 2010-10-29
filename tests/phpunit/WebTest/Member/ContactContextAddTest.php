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


 
class WebTest_Member_ContactContextAddTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testContactMemberAdd()
  {

      $this->open( $this->sboxPath );
      $this->webtestLogin();

      // Go directly to the URL of the screen that you will be testing (New Individual).
      $this->open($this->sboxPath . "civicrm/contact/add&reset=1&ct=Individual");

      $firstName = "John_" . substr(sha1(rand()), 0, 7);

      //fill in first name
      $this->type("first_name", $firstName );
      
      //fill in last name
      $lastName = "Smith_" . substr(sha1(rand()), 0, 7);;
      $this->type("last_name", $lastName);
      
      //fill in email
      $email = substr(sha1(rand()), 0, 7) . "john@gmail.com";
      $this->type("email_1_email", $email);
      
      // Clicking save.
      $this->click("_qf_Contact_upload_view");
      $this->waitForPageToLoad("30000");
      
      $this->assertTrue($this->isTextPresent("Your Individual contact record has been saved."));

      // click through to the membership view screen
      $this->click("css=li#tab_member a");

      $this->waitForElementPresent("link=Add Membership");
      $this->click("link=Add Membership");

      $this->waitForElementPresent("_qf_Membership_cancel-bottom");

      // fill in Membership Organization and Type
      $this->select("membership_type_id[1]", "value=1");

      $sourceText = "Membership ContactAddTest Webtest";
      // fill in Source
      $this->type("source", $sourceText );

      // Let Join Date stay default

      // fill in Start Date
      $this->webtestFillDate('start_date');

      // Clicking save.
      $this->click("_qf_Membership_upload");
      $this->waitForPageToLoad("30000");

      // page was loaded
      $this->waitForTextPresent( $sourceText );
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent("membership for $firstName $lastName has been added."),
                        "Status message didn't show up after saving!");
      
      // click through to the membership view screen
      $this->click("xpath=//x:tr[td/text()='$sourceText']/x:td/x:span/x:a[text()='View']");
      $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");

      $this->webtestVerifyTabularData( array(
                                             'Membership Type' => 'General',
                                             'Status'          => 'New',
                                             'Source'          => $sourceText,
                                             )
                                       );

      $this->click("_qf_MembershipView_cancel-bottom");
      $this->waitForPageToLoad("30000");
      // page was loaded
      $this->waitForTextPresent( $sourceText );
      
      // click through to the activities screen
      $this->click("css=li#tab_activity a");
      // page was loaded
      $this->waitForTextPresent( 'Membership Signup' );

      // click through to the activiy view screen (which is the membership view
      $this->click("xpath=//x:tr[td/text()='Membership Signup']/x:td/x:span/x:a[text()='View']");
      $this->waitForElementPresent("_qf_MembershipView_cancel-bottom");
      
      $this->webtestVerifyTabularData( array(
                                             'Membership Type' => 'General',
                                             'Status'          => 'New',
                                             'Source'          => $sourceText,
                                             )
                                       );
  }
  

}
?>
