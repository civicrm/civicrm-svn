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

class WebTest_Contribute_ContactContextAddTest extends CiviSeleniumTestCase {

  protected function setUp()
  {
      parent::setUp();
  }

  function testContactContextAdd()
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );
      
      // Log in using webtestLogin() method
      $this->webtestLogin();
      
      // Adding contact with randomized first name (so we can then select that contact when creating contribution.)
      // We're using Quick Add block on the main page for this.
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, "Anderson", true );
      
      // go to contribution tab and add contribution.
      $this->click("css=li#tab_contribute a");
      
      // wait for Record Contribution elenment.
      $this->waitForElementPresent("link=Record Contribution (Check, Cash, EFT ...)");
      $this->click("link=Record Contribution (Check, Cash, EFT ...)");
      
      $this->waitForElementPresent("_qf_Contribution_cancel-bottom");
      // fill contribution type.
      $this->select("contribution_type_id", "Donation");
      
      // fill in Received Date
      $this->webtestFillDate('receive_date');
     
      // source
      $this->type("source", "Mailer 1");
      
      // total amount
      $this->type("total_amount", "100");

      // select payment instrument type = Check and enter chk number
      $this->select("payment_instrument_id", "value=4");
      $this->waitForElementPresent("check_number");
      $this->type("check_number", "check #1041");

      $this->type("trxn_id", "P20901X1" . rand(100, 10000));
      
      //Custom Data
      $this->click('CIVICRM_QFID_3_6');

      //Additional Detail section
      $this->click("AdditionalDetail");
      $this->waitForElementPresent("thankyou_date");

      $this->type("note", "Test note for {$firstName}.");
      $this->type("non_deductible_amount", "10");
      $this->type("fee_amount", "0");
      $this->type("net_amount", "0");
      $this->type("invoice_id", time());
      $this->webtestFillDate('thankyou_date');
     
      //Honoree section
      $this->click("Honoree");
      $this->waitForElementPresent("honor_email");

      $this->click("CIVICRM_QFID_1_2");
      $this->select("honor_prefix_id", "label=Ms.");
      $this->type("honor_first_name", "Foo");
      $this->type("honor_last_name", "Bar");
      $this->type("honor_email", "foo@bar.com");

      //Premium section
      $this->click("Premium");
      $this->waitForElementPresent("fulfilled_date");
      $this->select("product_name[0]", "label=Coffee Mug ( MUG-101 )");
      $this->select("product_name[1]", "label=Black");
      $this->webtestFillDate('fulfilled_date');
      
      // Clicking save.
      $this->click("_qf_Contribution_upload-bottom");
      $this->waitForPageToLoad("30000");
      
      // Is status message correct?
      $this->assertTrue($this->isTextPresent("The contribution record has been saved"));
      
      $this->waitForElementPresent("xpath=//div[@id='Contributions']//table/tbody/tr/td[8]/span/a[text()='View']");

      // click through to the Contribution view screen
      $this->click("xpath=//div[@id='Contributions']//table/tbody/tr/td[8]/span/a[text()='View']");
      $this->waitForElementPresent('_qf_ContributionView_cancel-bottom');
      
      // verify Contribution created
      $this->assertTrue($this->isTextPresent("Test note for {$firstName}."), "Contribution Note did not match");
  }
}