<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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


 
class WebTest_Contribute_OfflineContributionTest extends CiviSeleniumTestCase {

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

      // Create a contact to be used as soft creditor
      $softCreditFname = substr(sha1(rand()), 0, 7);
      $softCreditLname = substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $softCreditFname, $softCreditLname, false );
 
      // Add new Financial Account
        $orgName = 'Alberta '.substr(sha1(rand()), 0, 7);
        $financialAccountTitle = 'Financial Account '.substr(sha1(rand()), 0, 4);
        $financialAccountDescription = "{$financialAccountTitle} Description";
        $accountingCode = 1033;
      $financialAccountType = 'Asset';
      $parentFinancialAccount = 'Donation';
      $taxDeductible = FALSE;
      $isActive = FALSE;
      $headerAccount = TRUE;
      $isTax = TRUE;
      $taxRate = 10;
      $isDefault = FALSE;
      
        //Add new organisation
       if( $orgName )
            $this->webtestAddOrganization( $orgName );
        
       $this->_testAddFinancialAccount( $financialAccountTitle,
                                         $financialAccountDescription,
                                         $accountingCode,
                                         $orgName,
                                         $parentFinancialAccount,
                                         $financialAccountType,
                                         $taxDeductible,
                                         $isActive,
                                         $headerAccount,
                                         $isTax,
                                         $taxRate,
                                         $isDefault
                                         );

       $firstName = 'John'.substr(sha1(rand()), 0, 7);
       $lastName = 'Dsouza'.substr(sha1(rand()), 0, 7);
      $this->webtestAddContact( $firstName, $lastName );
      
      $this->waitForElementPresent("css=li#tab_contribute a");
      $this->click("css=li#tab_contribute a");
      $this->waitForElementPresent("link=Record Contribution (Check, Cash, EFT ...)");
      $this->click("link=Record Contribution (Check, Cash, EFT ...)");
      $this->waitForPageToLoad("30000");

      // select contribution type
      $this->select("financial_account_id", "value=1");
      
      // fill in Received Date
      $this->webtestFillDate('receive_date');
     
      // source
      $this->type("source", "Mailer 1");
      
      // total amount
      $this->type("total_amount", "100");

      //select Recieved Into
        $this->select("to_financial_account_id", "label={$financialAccountTitle}");
        //$this->select("to_financial_account_id", "value=5");

      // select payment instrument type = Check and enter chk number
      $this->select("payment_instrument_id", "value=4");
      $this->waitForElementPresent("check_number");
      $this->type("check_number", "check #1041");

      $this->type("trxn_id", "P20901X1" . rand(100, 10000));
      
      // soft credit
      $this->typeKeys("soft_credit_to", $softCreditFname);
      $this->fireEvent("soft_credit_to", "focus");
      $this->waitForElementPresent("css=div.ac_results-inner li");
      $this->click("css=div.ac_results-inner li");

      //Custom Data
      // $this->click('CIVICRM_QFID_3_6');

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
      $this->click("_qf_Contribution_upload");
      $this->waitForPageToLoad("30000");

      // Is status message correct?
      $this->assertTrue($this->isTextPresent("The contribution record has been saved."), "Status message didn't show up after saving!");
     
      // verify if Membership is created
      $this->waitForElementPresent( "xpath=//div[@id='Contributions']//table//tbody/tr[1]/td[8]/span/a[text()='View']" );
      
      //click through to the Membership view screen
      $this->click( "xpath=//div[@id='Contributions']//table/tbody/tr[1]/td[8]/span/a[text()='View']" );
      $this->waitForElementPresent("_qf_ContributionView_cancel-bottom");
      
      $expected = array(
                        'Contribution Type'   => 'Donation', 
                        'Total Amount'        => '100.00',
                        'Contribution Status' => 'Completed',
                        'Paid By'             => 'Check',
                        'Check Number'        => 'check #1041',
                        'Received Into'       => "{$financialAccountTitle}",
                        'Soft Credit To'      => "{$softCreditFname} {$softCreditLname}" );
      foreach ( $expected as $label => $value ) {
          $this->verifyText("xpath=id('ContributionView')/div[2]/table[1]/tbody//tr/td[1][text()='$label']/../td[2]", preg_quote($value));
      }

      // go to soft creditor contact view page
      $this->click( "xpath=id('ContributionView')/div[2]/table[1]/tbody//tr/td[1][text()='Soft Credit To']/../td[2]/a[text()='{$softCreditFname} {$softCreditLname}']" );

      // go to contribution tab
      $this->waitForElementPresent("css=li#tab_contribute a");
      $this->click("css=li#tab_contribute a");
      $this->waitForElementPresent("link=Record Contribution (Check, Cash, EFT ...)");

      // verify soft credit details
      $expected = array( 3  => 'Donation', 
                         2  => '100.00',
                         5  => 'Completed',
                         1  => "{$firstName} {$lastName}", );
      foreach ( $expected as  $value => $label ) {
          $this->verifyText("xpath=id('Search')/div[2]/table[2]/tbody/tr[2]/td[$value]", preg_quote($label));
      }
  }
}