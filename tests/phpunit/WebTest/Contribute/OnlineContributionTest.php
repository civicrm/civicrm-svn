<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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


 
class WebTest_Contribute_OnlineContributionTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp();
  }
  
  function testOnlineContributionAdd()
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
      
      // We need a payment processor
      $processorName = "Webtest Dummy" . substr( sha1( rand( ) ), 0, 7 );  
      $processorType = 'Dummy';
      $pageTitle = substr( sha1( rand( ) ), 0, 7 );
      $rand = 2 * rand( 10, 50 );
      $hash = substr(sha1(rand()), 0, 7);
      $amountSection = true;
      $payLater =  false;
      $onBehalf = false;
      $pledges = false;
      $recurring = false;
      $memberships = false;
      $friend = true;
      $profilePreId  = 1;
      $profilePostId = null;
      $premiums = false;
      $widget = false;
      $pcp = false;
      $memPriceSetId = null;
      
      // create a new online contribution page
      // create contribution page with randomized title and default params
      $pageId = $this->webtestAddContributionPage( $hash, 
                                                   $rand, 
                                                   $pageTitle, 
                                                   array($processorName => $processorType), 
                                                   $amountSection, 
                                                   $payLater, 
                                                   $onBehalf,
                                                   $pledges, 
                                                   $recurring, 
                                                   $memberships, 
                                                   $memPriceSetId,
                                                   $friend, 
                                                   $profilePreId,
                                                   $profilePostId,
                                                   $premiums, 
                                                   $widget, 
                                                   $pcp );        
      
      //logout
      $this->open( $this->sboxPath . "civicrm/logout?reset=1" );
      $this->waitForPageToLoad( '30000' );
      
      //Open Live Contribution Page
      $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&id=". $pageId );
      $this->waitForElementPresent( "_qf_Main_upload-bottom" );
      
      
      $firstName = 'Ma'.substr( sha1( rand( ) ), 0, 4 );
      $lastName  = 'An'.substr( sha1( rand( ) ), 0, 7 );
      
      $this->type( "email-5", $firstName . "@example.com" );
      
      $this->type( "first_name", $firstName );
      $this->type( "last_name",$lastName );
      
      $this->click("amount_other");
      $this->type("amount_other",100);
      
      $streetAddress = "100 Main Street";
      $this->type( "street_address-1", $streetAddress );
      $this->type( "city-1", "San Francisco" );
      $this->type( "postal_code-1", "94117" );
      $this->select( "country-1", "value=1228" );
      $this->select( "state_province-1", "value=1001" );
      
      //Credit Card Info
      $this->select( "credit_card_type", "value=Visa" );
      $this->type( "credit_card_number", "4111111111111111" );
      $this->type( "cvv2", "000" );
      $this->select( "credit_card_exp_date[M]", "value=1" );
      $this->select( "credit_card_exp_date[Y]", "value=2020" );
      
      //Billing Info
      $this->type( "billing_first_name", $firstName."billing" );
      $this->type( "billing_last_name", $lastName."billing"  );
      $this->type( "billing_street_address-5", "15 Main St." );
      $this->type( " billing_city-5", "San Jose" );
      $this->select( "billing_country_id-5", "value=1228" );
      $this->select( "billing_state_province_id-5", "value=1004" );
      $this->type( "billing_postal_code-5", "94129" );  
      $this->click( "_qf_Main_upload-bottom" );
      
      $this->waitForPageToLoad( '30000' );
      $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
      
      $this->click( "_qf_Confirm_next-bottom" );
      $this->waitForPageToLoad( '30000' );
      
      //login to check contribution
      $this->open( $this->sboxPath );
      
      // Log in using webtestLogin() method
      $this->webtestLogin( );
      
      //Find Contribution
      $this->open( $this->sboxPath . "civicrm/contribute/search?reset=1" );
      
      $this->waitForElementPresent( "contribution_date_low" );
      
      $this->type( "sort_name", "$firstName $lastName" );
      $this->click( "_qf_Search_refresh" );
      
      $this->waitForPageToLoad( '30000' );
      
      $this->waitForElementPresent( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
      $this->click( "xpath=//div[@id='contributionSearch']//table//tbody/tr[1]/td[11]/span/a[text()='View']" );
      $this->waitForPageToLoad( '30000' );
      $this->waitForElementPresent( "_qf_ContributionView_cancel-bottom" );
      
      //View Contribution Record
      $expected = array( 3  => 'Donation',  
                         3  => '100.00', 
                         7  => 'Completed', 
                         1  => "{$firstName} {$lastName}" 
                         ); 
      foreach ( $expected as  $value => $label ) { 
          $this->verifyText("xpath=id('ContributionView')/div[2]/table[1]/tbody/tr[$value]/td[2]", preg_quote($label)); 
      }
  }
  
}
