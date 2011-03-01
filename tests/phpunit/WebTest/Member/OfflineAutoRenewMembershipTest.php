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

class WebTest_Member_OfflineAutoRenewMembershipTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }

  function testOfflineAutoRenewMembership()
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin();

      // We need a payment processor
      $processorName = "Webtest AuthNet" . substr(sha1(rand()), 0, 7);
      $this->webtestAddPaymentProcessor($processorName, 'AuthNet');

      // -- start updating membership types 
      $this->open($this->sboxPath . "civicrm/admin/member/membershipType&action=update&id=1&reset=1");

      $this->waitForElementPresent("CIVICRM_QFID_1_10");
      $this->click("CIVICRM_QFID_1_10");

      $this->type("duration_interval", "1");
      $this->select("duration_unit", "label=year");

      $this->click("_qf_MembershipType_upload-bottom");
      $this->waitForPageToLoad("30000");

      $this->open($this->sboxPath . "civicrm/admin/member/membershipType&action=update&id=2&reset=1");

      $this->type("duration_interval", "6");
      $this->select("duration_unit", "label=month");

      $this->click("_qf_MembershipType_upload-bottom");
      $this->waitForPageToLoad("30000");

      // create a new contact for whom membership is to be created
      $firstName = 'Apt'.substr( sha1( rand( ) ), 0, 4 );
      $lastName  = 'Mem'.substr( sha1( rand( ) ), 0, 7 );
      $this->webtestAddContact($firstName, $lastName, "{$firstName}@example.com");
      $contactName = "$firstName $lastName";

      $this->click('css=li#tab_member a');

      $this->waitForElementPresent('link=Submit Credit Card Membership');
      $this->click('link=Submit Credit Card Membership');
      $this->waitForPageToLoad("30000");

      // since we don't have live credentials we will switch to test mode
      $url = $this->getLocation( );
      $url = str_replace('mode=live', 'mode=test', $url);
      $this->open($url);

      // start filling membership form
      $this->waitForElementPresent('payment_processor_id');
      $this->select("payment_processor_id",  "label={$processorName}");
      $this->select("membership_type_id[1]", "label=General");

      $this->click("auto_renew");

      $this->select("credit_card_type", "label=Visa");
      $this->type("credit_card_number", "4111111111111111");
      $this->type("cvv2", "123");
      $this->select("credit_card_exp_date[M]", "label=Feb");
      $this->select("credit_card_exp_date[Y]", "label=2014");

      $this->select("billing_country_id-5", "label=United States");
      $this->type("billing_first_name", "$firstName");
      $this->type("billing_last_name", $lastName);
      $this->type("billing_street_address-5", "99 ht");
      $this->type("billing_city-5", "SF");
      $this->select("billing_state_province_id-5", "label=California");
      $this->type("billing_postal_code-5", "919199");

      $this->click("_qf_Membership_upload-bottom");
      $this->waitForPageToLoad("30000");

      // Use Find Members to make sure membership exists
      $this->open($this->sboxPath . "civicrm/member/search&reset=1");
      $this->waitForElementPresent("member_end_date_high");

      $this->type("sort_name", "$firstName $lastName" );
      $this->click("member_test");
      $this->click("_qf_Search_refresh");

      $this->waitForPageToLoad('30000');

      $this->waitForElementPresent('css=#memberSearch table tbody tr td span a.action-item-first');
      $this->click('css=#memberSearch table tbody tr td span a.action-item-first');
      $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );

      // View Membership Record
      $this->webtestVerifyTabularData( array(
                                             'Member'          => "$firstName $lastName",
                                             'Membership Type' => 'General (test)',
                                             'Source'          => 'Online Membership: Admin Interface',
                                             'Status'          => 'Pending',
                                             'Auto-renew'      => 'Yes',
                                             )
                                       );
      $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );
  }
}
