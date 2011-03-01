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

class WebTest_Member_OnlineAutoRenewMembershipTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp( );
  }

  function testOnlineAutoRenewMembership()
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin();
      
      //add payment processor.
      $processorName = "Webtest Auto Renew AuthNet" . substr(sha1(rand()), 0, 7);
      $this->open($this->sboxPath . "civicrm/admin/paymentProcessor&reset=1");
      $this->click("//a[@id='newPaymentProcessor']/span");
      $this->waitForPageToLoad("30000");
      $this->click("payment_processor_type");
      $this->select("payment_processor_type", "label=Authorize.Net");
      $this->waitForPageToLoad("30000");
      $this->click("name");
      $this->type("name", $processorName );
      $this->type("test_user_name", "89C2wUpk");
      $this->type("test_password",  "4c2T7XC95m8sP6x7");
      $this->type("test_signature", "shambho");
      $this->click("_qf_PaymentProcessor_next-bottom");
      $this->waitForPageToLoad("30000");
      
      // -- start updating membership types 
      $this->open($this->sboxPath . "civicrm/admin/member/membershipType&action=update&id=2&reset=1");
      
      $this->waitForElementPresent("CIVICRM_QFID_1_10");
      $this->click("CIVICRM_QFID_1_10");
      
      $this->type("duration_interval", "1");
      $this->select("duration_unit", "label=year");
      
      $this->click("_qf_MembershipType_upload-bottom");
      $this->waitForPageToLoad("30000");
      
      //now configure the membership signup page.
      $this->open($this->sboxPath . 'civicrm/admin/contribute/amount?reset=1&action=update&id=2');        
      $this->waitForPageToLoad( );
      
      //configure paymentr processor.
      $this->waitForElementPresent('payment_processor_id');
      $this->select("payment_processor_id",  "label={$processorName}");
      $this->click('_qf_Amount_next');
      
      $this->waitForElementPresent("_qf_Amount_next-bottom"); 
      $this->waitForPageToLoad("30000");
      $this->click("link=Memberships");
      $this->waitForElementPresent("_qf_MembershipBlock_next-bottom");
      
      $this->click("auto_renew_2");
      $this->select("auto_renew_2", "label=Give option");
      $this->click("_qf_MembershipBlock_next");
      $this->waitForPageToLoad("30000");
      
      //now do the test membership signup.
      $this->open($this->sboxPath . 'civicrm/contribute/transact?reset=1&action=preview&id=2' );        
      $this->waitForPageToLoad( );
      
      $this->click("CIVICRM_QFID_2_4");
      $this->click("auto_renew");
      $this->select("credit_card_type", "label=Visa");
      $this->type("credit_card_number", "4807731747657838");
      $this->type("cvv2", "000");
      $this->select("credit_card_exp_date[M]", "label=Feb");
      $this->select("credit_card_exp_date[Y]", "label=2019");
      $this->type("billing_street_address-5", "Street Address");
      $this->type("billing_city-5", "City");
      $this->select("billing_state_province_id-5", "label=California");
      $this->type("billing_postal_code-5", "12345");
      
      $this->click("_qf_Main_upload-bottom");
      $this->waitForPageToLoad("30000");
      $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
      
      $text = 'I want this membership to be renewed automatically every 1 year(s).';
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      
      $this->click("_qf_Confirm_next-bottom");
      $this->waitForPageToLoad("30000");
      
      $text = 'This membership will be renewed automatically every 1 year(s).';
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
  }
}
