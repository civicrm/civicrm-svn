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


 
class WebTest_Grant_StandaloneAddTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/tmp/';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }
  
  function testStandaloneGrantAdd()
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


      // Enable CiviCase module if necessary
      $this->open($this->sboxPath . "civicrm/admin/setting/component?reset=1");
      $this->waitForPageToLoad('30000');
      $this->waitForElementPresent("_qf_Component_next-bottom");
      $enabledComponents = $this->getSelectOptions("enableComponents-t");
      if (! array_search( "CiviGrant", $enabledComponents ) ) {
          $this->addSelection("enableComponents-f", "label=CiviGrant");
          $this->click("//option[@value='CiviGrant']");
          $this->click("add");
          $this->click("_qf_Component_next-bottom");
          $this->waitForPageToLoad("30000");          
          $this->assertTrue($this->isTextPresent("Your changes have been saved."));          
      }

        // let's give full CiviGrant permissions to demo user (registered user).
        $this->open( $this->sboxPath ."admin/user/permissions");
        $this->waitForElementPresent("edit-submit");
        $this->check("edit-2-access-CiviGrant");
        $this->check("edit-2-edit-grants");
        $this->check("edit-2-delete-in-CiviGrant");

        // save permissions
        $this->click("edit-submit");
        $this->waitForPageToLoad("30000");
        $this->assertTrue($this->isTextPresent("The changes have been saved."));        
       
      // Go directly to the URL of the screen that you will be testing (New Contribution-standalone).
      $this->open($this->sboxPath . "civicrm/grant/add&reset=1&context=standalone");
      
      // As mentioned before, waitForPageToLoad is not always reliable. Below, we're waiting for the submit
      // button at the end of this page to show up, to make sure it's fully loaded.
      $this->waitForElementPresent("_qf_Grant_upload");

      // Let's start filling the form with values.
      
      // create new contact using dialog
      $firstName = substr(sha1(rand()), 0, 7);
      $this->webtestNewDialogContact( $firstName, "Grantor", $firstName . "@example.com" );
      
      // select grant Status
      $this->select("status_id", "value=1");
      
      // select grant type
      $this->select("grant_type_id", "value=1");
      
      // total amount
      $this->type("amount_total", "100");
      
      // amount requested
      $this->type("amount_requested", "100");
      
      // amount granted
      $this->type("amount_granted", "90");
      
      // fill in application received Date
      $this->webtestFillDate('application_received_date');
      
      // fill in decision Date
      $this->webtestFillDate('decision_date');
      
      // fill in money transfered date
      $this->webtestFillDate('money_transfer_date');
      
      // fill in grant due Date
      $this->webtestFillDate('grant_due_date');
      
      // check  grant report recieved.
      $this->check("grant_report_received");
      
      // grant  note
      $this->type("note", "Grant Note");
      
      // Clicking save.
      $this->click("_qf_Grant_upload");
      $this->waitForPageToLoad("30000");
      
      // verify if Grant is created
      $this->waitForElementPresent( "xpath=//div[@id='Grants']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
      
      //click through to the Grant view screen
      $this->click( "xpath=//div[@id='Grants']//table/tbody/tr[1]/td[8]/span/a[text()='View']" );

      $this->waitForElementPresent("_qf_GrantView_cancel-bottom");

      $expected = array(
                        2   => 'Pending', 
                        3   => 'Emergency',
                        8   => '$ 100.00',
                        10  => '$ 90.00',
                        13  => 'Grant Note',
                        );

      foreach ( $expected as $label => $value ) {
          $this->verifyText("xpath=id('GrantView')/div[2]/table[1]/tbody/tr[$label]/td[2]", preg_quote($value));
      }

  }

}