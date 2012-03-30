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

class WebTest_Member_OnlineAutoRenewMembershipGCTest extends CiviSeleniumTestCase {

  protected $captureScreenshotOnFailure = TRUE;
  protected $screenshotPath = '/var/www/api.dev.civicrm.org/public/sc';
  protected $screenshotUrl = 'http://api.dev.civicrm.org/sc/';
    
  protected function setUp()
  {
      parent::setUp( );
  }
  
  function testOnlineAutoRenewMembershipAnonymous()
  {
      //configure membership signup page.
      $pageId = $this->_configureMembershipPage( );
      
      //now do the test membership signup.
      $this->open($this->sboxPath . "civicrm/contribute/transact?reset=1&action=preview&id={$pageId}" );        
      $this->waitForPageToLoad( "3000" );
      $this->waitForElementPresent("_qf_Main_upload-bottom");
      
      $this->click("CIVICRM_QFID_2_4");

      $this->click("auto_renew");
      
      $firstName = 'John';
      $lastName  = 'Smith_' . substr(sha1(rand()), 0, 7);
      $this->type( 'email-5', "{$lastName}@example.com" );
      
      $this->click("_qf_Main_upload-bottom");
      $this->waitForPageToLoad("30000");
      $this->waitForElementPresent( "_qf_Confirm_next_checkout" );
      
      $text = 'I want this membership to be renewed automatically every 1 year(s).';
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      
      $this->click("_qf_Confirm_next_checkout");
      $this->waitForPageToLoad("30000");

      $this->waitForElementPresent( "Email" );
      $this->type("Email","custdeepak");
      $this->type("Passwd","deepak123");

      $this->click("xpath=//input[@id='signIn']");
      $this->waitForElementPresent( "bottomBuyButton" );

      $this->click("bottomBuyButton");
      $this->waitForPageToLoad("30000");
  }
  
  function testOnlineAutoRenewMembershipAuthenticated( )
  {
      //configure membership signup page.
      $pageId = $this->_configureMembershipPage( );
      
      $this->open( $this->sboxPath );
      $this->webtestLogin( );
      $this->waitForPageToLoad("30000");
      
      //now do the test membership signup.
      $this->open($this->sboxPath . "civicrm/contribute/transact?reset=1&action=preview&id={$pageId}" );
      $this->waitForPageToLoad( "3000" );
      $this->waitForElementPresent("_qf_Main_upload-bottom");
      
      $this->click("CIVICRM_QFID_2_4");
      
      $this->click("auto_renew");

      $firstName = 'John';
      $lastName  = 'Smith_' . substr(sha1(rand()), 0, 7);
      $this->type( 'email-5', "{$lastName}@example.com" );
      
      $this->click("_qf_Main_upload-bottom");
      $this->waitForPageToLoad("30000");
      $this->waitForElementPresent( "_qf_Confirm_next_checkout" );
      
      $text = 'I want this membership to be renewed automatically every 1 year(s).';
      $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
      
      $this->click("_qf_Confirm_next_checkout");
      $this->waitForPageToLoad("30000");
      
      $this->waitForElementPresent( "Email" );
      $this->type("Email","custdeepak");
      $this->type("Passwd","deepak123");

      $this->click("xpath=//input[@id='signIn']");
      $this->waitForElementPresent( "bottomBuyButton" );

      $this->click("bottomBuyButton");
      $this->waitForPageToLoad("30000");
  }
  
  function _configureMembershipPage( ) {
      static $pageId = null;

      if ( !$pageId ) {
          $this->open( $this->sboxPath );
          $this->webtestLogin( );
          
          //add payment processor.
          $hash = substr(sha1(rand()), 0, 7);
          $rand = 2 * rand(2, 50);
          $processorName = "Webtest Auto Renew Google Checkout" . $hash;
          $this->webtestAddPaymentProcessor( $processorName, 'Google_Checkout' );
          
          // -- start updating membership types 
          $this->open($this->sboxPath . "civicrm/admin/member/membershipType?action=update&id=1&reset=1");
          $this->waitForPageToLoad("30000");
          
          $this->waitForElementPresent("CIVICRM_QFID_1_10");
          $this->click("CIVICRM_QFID_1_10");
          
          $this->type("duration_interval", "1");
          $this->select("duration_unit", "label=year");
          
          $this->click("_qf_MembershipType_upload-bottom");
          sleep(5);
          
          $this->open($this->sboxPath . "civicrm/admin/member/membershipType?action=update&id=2&reset=1");
          $this->waitForPageToLoad("30000");
          
          $this->waitForElementPresent("CIVICRM_QFID_1_10");
          $this->click("CIVICRM_QFID_1_10");
          
          $this->type("duration_interval", "1");
          $this->select("duration_unit", "label=year");
          
          $this->click("_qf_MembershipType_upload-bottom");
          sleep(5);
          
          // create contribution page with randomized title and default params
          $amountSection = false;
          $payLater      = true; 
          $onBehalf      = false;
          $pledges       = false; 
          $recurring     = true;
          $membershipTypes = array( array( 'id' => 1, 'auto_renew' => 1 ),
                                    array( 'id' => 2, 'auto_renew' => 1 ) );
          $memPriceSetId = null;
          $friend        = true; 
          $profilePreId  = null;
          $profilePostId = null;
          $premiums      = true;
          $widget        = true;
          $pcp           = true;
          
          $contributionTitle = "Title $hash";
          $pageId = $this->webtestAddContributionPage( $hash, 
                                                       $rand, 
                                                       $contributionTitle, 
                                                       array($processorName => 'Google_Checkout'), 
                                                       $amountSection,
                                                       $payLater     , 
                                                       $onBehalf     ,
                                                       $pledges      , 
                                                       $recurring    ,
                                                       $membershipTypes,
                                                       $memPriceSetId,
                                                       $friend       , 
                                                       $profilePreId ,
                                                       $profilePostId,
                                                       $premiums     ,
                                                       $widget       ,
                                                       $pcp          ,
                                                       false 
                                                       );
        
          // now logout and login with admin credentials
          $this->open($this->sboxPath . "civicrm/logout?reset=1");
          $this->waitForPageToLoad('30000'); 

          //make sure we do have required permissions.
          $permissions = array( "edit-1-make-online-contributions" );
          $this->changePermissions( $permissions );

          // now logout and do membership test that way
          $this->open($this->sboxPath . "civicrm/logout?reset=1");
          $this->waitForPageToLoad('30000'); 
      }

      return $pageId;
  }
  
}
