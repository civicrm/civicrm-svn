<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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


 
class WebTest_Contribute_StandaloneAddTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }
  
  function testStandaloneContributeAdd()
  {
      // This is the path where our testing install resides. 
      // The rest of URL is defined in CiviSeleniumTestCase base class, in
      // class attributes.
      $this->open( $this->sboxPath );

      // Logging in. Remember to wait for page to load. In most cases,
      // you can rely on 30000 as the value that allows your test to pass, however,
      // sometimes your test might fail because of this. In such cases, it's better to pick one element
      // somewhere at the end of page and use waitForElementPresent on it - this assures you, that whole
      // page contents loaded and you can continue your test execution.
      $this->webtestLogin();

      // Go directly to the URL of the screen that you will be testing (New Contribution-standalone).
      $this->open($this->sboxPath . "civicrm/contribute/add&reset=1&context=standalone");

      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Contribution_upload");

      // Let's start filling the form with values.
      
      // create new contact using dialog
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestNewDialogContact( $firstName, "Contributor", $firstName . "@example.com" );
      
      // select contribution type
      $this->select("contribution_type_id", "value=1");
      
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
      $this->click('CIVICRM_QFID_3_4_6_yea');

      //Additional Detail section
      $this->click("AdditionalDetail");
      $this->waitForElementPresent("thankyou_date");

      $this->type("note", "This is a test note.");
      $this->type("non_deductible_amount", "10");
      $this->type("fee_amount", "0");
      $this->type("net_amount", "0");
      $this->type("invoice_id", time());
      $this->webtestFillDate('thankyou_date');
     
      //Honoree section
      $this->click("Honoree");
      $this->waitForElementPresent("honor_email");

      $this->click("CIVICRM_QFID_1_In_Hono");
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
      $this->click("_qf_Contribution_upload");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("The contribution record has been saved."), "Status message didn't show up after saving!");

      // click through to the contribution view screen
      $this->waitForElementPresent("link=View");

      $this->click('link=View');
      $this->waitForPageToLoad('30000');
      
      $this->webtestVerifyTabularData(
          array(
              'Contribution Type'               => 'Donation',
              'Contribution Status'             => 'Completed',
              'Paid By'                         => 'Check',
              'How long have you been a donor?' => '4-6 years',
              'Total Amount'                    => '100.00',
              'Check Number'      	            => 'check #1041'
          )
      );
  }
}