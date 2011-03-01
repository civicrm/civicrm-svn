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
      $this->webtestAddPaymentProcessor( $processorName, 'AuthNet' );
      
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

      $this->webtestAddCreditCardDetails( );

      list( $firstName, $middleName, $lastName ) = $this->webtestAddBillingDetails( );

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
