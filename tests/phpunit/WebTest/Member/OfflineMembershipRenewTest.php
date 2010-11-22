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

class WebTest_Member_OfflineMembershipRenewTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testOfflineMembershipRenew()
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin();

      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact($firstName, "Memberson", "{$firstName}@memberson.com");
      $contactName = "$firstName Memberson";

      // click through to the membership tab
      $this->click('css=li#tab_member a');

      $this->waitForElementPresent('link=Add Membership');
      $this->click('link=Add Membership');

      $this->waitForElementPresent('_qf_Membership_cancel-bottom');

      // fill in Membership Organization and Type
      $this->select('membership_type_id[1]', 'value=1');

      // fill in Source
      $sourceText = 'Offline Membership Renewal Webtest';
      $this->type('source', $sourceText);

      // Fill Join Date
      $this->webtestFillDate('join_date', '-2 year');

      // Let Start Date and End Date be auto computed

      // Clicking save.
      $this->click('_qf_Membership_upload');
      $this->waitForPageToLoad('30000');

      // page was loaded
      $this->waitForTextPresent( $sourceText );
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent("General membership for $firstName Memberson has been added."), "Status message didn't show up after saving!");

      $this->waitForElementPresent("xpath=//div[@id='Memberships']//table/tbody/tr/td[6]/span[2][text()='more ']/ul/li/a[text()='Renew']");

      // click through to the Membership Renewal Link
      $this->click("xpath=//div[@id='Memberships']//table/tbody/tr/td[6]/span[2][text()='more ']/ul/li/a[text()='Renew']");

      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');

      // save the renewed membership
      $this->click('_qf_MembershipRenewal_upload-bottom');

      $this->waitForPageToLoad('30000');

      // page was loaded
      $this->waitForTextPresent( $sourceText );

      $this->waitForElementPresent("xpath=//div[@id='Memberships']//table/tbody/tr/td[6]/span/a[text()='View']");

      // click through to the membership view screen
      $this->click("xpath=//div[@id='Memberships']//table/tbody/tr/td[6]/span/a[text()='View']");

      $this->waitForElementPresent('_qf_MembershipView_cancel-bottom');

      $joinDate = $startDate = date('F jS, Y', strtotime("-2 year"));
      $endDate  = date('F jS, Y', strtotime("+2 year -1 day"));

      // verify membership renewed
      $this->webtestVerifyTabularData( array(
                                             'Member'          => $contactName,
                                             'Membership Type' => 'General',
                                             'Status'          => 'Current',
                                             'Source'          => $sourceText,
                                             'Join date'       => $joinDate,
                                             'Start date'      => $startDate,
                                             'End date'        => $endDate,
                                             )
                                       );
  }

  function testOfflineMemberRenewOverride( ) 
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin();

      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact($firstName, "Memberson", "{$firstName}@memberson.com");
      $contactName = "$firstName Memberson";

      // click through to the membership tab
      $this->click('css=li#tab_member a');

      $this->waitForElementPresent('link=Add Membership');
      $this->click('link=Add Membership');

      $this->waitForElementPresent('_qf_Membership_cancel-bottom');

      // fill in Membership Organization and Type
      $this->select('membership_type_id[1]', 'value=1');

      // fill in Source
      $sourceText = 'Offline Membership Renewal Webtest';
      $this->type('source', $sourceText);

      // Let Join Date stay default
      
      // fill in Start Date
      $this->webtestFillDate('start_date');
      
      // Let End Date be auto computed

      // fill in Status Override?
      $this->click('is_override', 'value=1');
      $this->waitForElementPresent('status_id');
      $this->select('status_id', 'value=3');
      
      // fill in Record Membership Payment?
      $this->click('record_contribution', 'value=1');
      $this->waitForElementPresent('contribution_status_id');
      
      // select the contribution type for the selected membership type
      $this->select('contribution_type_id', 'value=2');
      
      // the amount for the selected membership type
      $this->type('total_amount', '100.00');
      
      // select payment instrument type = Check and enter chk number
      $this->select("payment_instrument_id", "value=4");
      $this->waitForElementPresent("check_number");
      $this->type("check_number", "check #12345");
      $this->type("trxn_id", "P5476785" . rand(100, 10000));

      // fill  the payment status be default
      $this->select("contribution_status_id", "value=2");

      // Clicking save.
      $this->click('_qf_Membership_upload');
      $this->waitForPageToLoad('30000');

      // page was loaded
      $this->waitForTextPresent( $sourceText );
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent("General membership for $firstName Memberson has been added."),
                        "Status message didn't show up after saving!");

      $this->waitForElementPresent("xpath=//div[@id='Memberships']//table/tbody/tr/td[6]/span[2][text()='more ']/ul/li/a[text()='Renew']");
      
      // click through to the Membership Renewal Link
      $this->click("xpath=//div[@id='Memberships']//table/tbody/tr/td[6]/span[2][text()='more ']/ul/li/a[text()='Renew']");
      
      $this->waitForElementPresent('_qf_MembershipRenewal_cancel-bottom');
      
      // save the renewed membership
      $this->click('_qf_MembershipRenewal_upload-bottom');
      
      $this->waitForPageToLoad('30000');

      // page was loaded
      $this->waitForTextPresent( $sourceText );
      
      $this->waitForElementPresent("xpath=//div[@id='Memberships']//table/tbody/tr/td[6]/span/a[text()='View']");

      // click through to the membership view screen
      $this->click("xpath=//div[@id='Memberships']//table/tbody/tr/td[6]/span/a[text()='View']");
      
      $this->waitForElementPresent('_qf_MembershipView_cancel-bottom');
      
      $joinDate  = date('F jS, Y');
      $startDate = date('F jS, Y', strtotime("+1 month"));
      $endDate   = date('F jS, Y', strtotime("+4 year 1 month -1 day"));

      // verify membership renewed
      $this->webtestVerifyTabularData( array(
                                             'Member'          => $contactName,
                                             'Membership Type' => 'General',
                                             'Status'          => 'New',
                                             'Source'          => $sourceText,
                                             'Join date'       => $joinDate,
                                             'Start date'      => $startDate,
                                             'End date'        => $endDate,
                                             )
                                       );
  }
}