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
      $this->webtestNewDialogContact( );
            
      // select contribution type
      $this->select("contribution_type_id", "value=1");
      
      // source
      $this->type("source", "Mailer 1");
      
      // total amount
      $this->type("total_amount", "100");

      // select payment instrument type
      $this->select("payment_instrument_id", "value=4");

      $this->type("check_number", "check #1041");

      $this->type("trxn_id", "P20901X1" . rand(100, 10000));
       
      $this->click("Honoree");
      $this->waitForElementPresent("honor_email");

      $this->click("CIVICRM_QFID_1_In_Hono");
      $this->select("honor_prefix_id", "label=Ms.");
      $this->type("honor_first_name", "Foo");
      $this->type("honor_last_name", "Bar");
      $this->type("honor_email", "foo@bar.com");

      // Clicking save.
      $this->click("_qf_Contribution_upload");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("The contribution record has been saved."), "Status message didn't show up after saving!");
  }
}