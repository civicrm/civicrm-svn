<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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

class WebTest_Contribute_OnlineRecurContributionTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp( );
  }

  function testOnlineRecurContributino()
  {
      $this->open( $this->sboxPath );
      $this->webtestLogin();
      
      //add payment processor.
      $processorName = "Webtest Auto Renew AuthNet" . substr(sha1(rand()), 0, 7);
      $this->webtestAddPaymentProcessor( $processorName, 'AuthNet' );      
      
      //now configure the sample online contribution page to use our test processor.
      $this->open($this->sboxPath . 'civicrm/admin/contribute/amount?reset=1&action=update&id=1');          
      $this->waitForElementPresent('_qf_Amount_next-bottom');
      $this->select("payment_processor_id",  "label={$processorName}");
      $this->click('_qf_Amount_upload_done-top');
      $this->waitForPageToLoad("30000");
      $text = "information has been saved.";
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      
      //now do the test online recurring contribution as an anonymous user.
      $anonymous = true;
      $firstName = 'Jane'.substr( sha1( rand( ) ), 0, 7 );
      $middleName = 'Middle';
      $lastName  = 'Recuron_'.substr( sha1( rand( ) ), 0, 7 );
      $email = $firstName . '@example.com';
      $contactName = "$firstName $lastName";

      // logout
      $this->open($this->sboxPath . "civicrm/logout&reset=1");
      // Wait for Login button to indicate we've logged out.
      $this->waitForElementPresent( "edit-submit" );

      $this->open($this->sboxPath . "civicrm/contribute/transact?reset=1&action=preview&id=1");
      $this->waitForElementPresent( "_qf_Main_upload-bottom" );

      $this->click("amount_other");
      $this->type("amount_other", "10");
      $this->click("CIVICRM_QFID_1_14");
      $this->select("pledge_frequency_unit", "label=month");
      $this->type("pledge_installments", "12");
      $this->type("email-5", $email);

      $this->webtestAddCreditCardDetails( );
      $this->webtestAddBillingDetails( $firstName, $middleName, $lastName );
      $this->click("_qf_Main_upload-bottom");
      
      // Confirmation page
      $this->waitForElementPresent( "_qf_Confirm_next-bottom" );      
      $text = 'I pledge to contribute this amount every month for 12 installments.';
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      $text = '$ 10.00';
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );      
      $this->click("_qf_Confirm_next-bottom");

      // Thank-you page
      $this->waitForElementPresent( "thankyou_footer" );
      $this->assertTrue( $this->isElementPresent( 'tell-a-friend' ), 'Missing tell-a-friend div' );      
      $text = 'I pledge to contribute this amount every month for 12 installments.';
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      $text = '$ 10.00';
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );      
  }

}
