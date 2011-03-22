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

class WebTest_Member_OnlineMembershipCreateTest extends CiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    function testOnlineMembershipCreate()
    {
        // a random 7-char string and an even number to make this pass unique
        $hash = substr(sha1(rand()), 0, 7);
        $rand = 2 * rand(2, 50);
        // This is the path where our testing install resides. 
        // The rest of URL is defined in CiviSeleniumTestCase base class, in
        // class attributes.
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        // We need a payment processor
        $processorName = "Webtest Dummy" . substr(sha1(rand()), 0, 7);
        //$this->webtestAddPaymentProcessor($processorName);
        
        // create contribution page with randomized title and default params
        $amountSection = false;
        $payLater      = false; 
        $onBehalf      = false;
        $pledges       = false; 
        $recurring     = false;
        $memberships   = true;
        $friend        = true; 
        $profilePreId  = 1;
        $profilePostId = null;
        $premiums      = true;
        $widget        = true;
        $pcp           = true;

        $contributionTitle = "Title $hash";
        $pageId = $this->webtestAddContributionPage( $hash, 
                                                     $rand, 
                                                     $contributionTitle, 
                                                     'Dummy'       , 
                                                     $processorName, 
                                                     $amountSection,
                                                     $payLater     , 
                                                     $onBehalf     ,
                                                     $pledges      , 
                                                     $recurring    ,
                                                     $memberships  ,
                                                     $friend       , 
                                                     $profilePreId ,
                                                     $profilePostId,
                                                     $premiums     ,
                                                     $widget       ,
                                                     $pcp          
                                                     );

        //get Url for Live Contribution Page
        $registerUrl = "{$this->sboxPath}civicrm/contribute/transact?reset=1&id=$pageId";
        
        //logout
        $this->open($this->sboxPath . "civicrm/logout&reset=1");
        $this->waitForPageToLoad('30000');
        
        //Open Live Contribution Page
        $this->open($registerUrl);
        $this->waitForElementPresent("_qf_Main_upload-bottom");
        
        $firstName = 'Ma'.substr( sha1( rand( ) ), 0, 4 );
        $lastName  = 'An'.substr( sha1( rand( ) ), 0, 7 );
        
        $this->type("email-5", $firstName . "@example.com");
        
        $this->type("first_name", $firstName);
        $this->type("last_name",$lastName );
        
        $streetAddress = "100 Main Street";
        $this->type("street_address-1", $streetAddress);
        $this->type("city-1", "San Francisco");
        $this->type("postal_code-1", "94117");
        $this->select("country-1", "value=1228");
        $this->select("state_province-1", "value=1001");
        
        //Credit Card Info
        $this->select("credit_card_type", "value=Visa");
        $this->type("credit_card_number", "4111111111111111");
        $this->type("cvv2", "000");
        $this->select("credit_card_exp_date[M]", "value=1");
        $this->select("credit_card_exp_date[Y]", "value=2020");
        
        //Billing Info
        $this->type("billing_first_name", $firstName."billing");
        $this->type("billing_last_name", $lastName."billing" );
        $this->type("billing_street_address-5", "15 Main St.");
        $this->type(" billing_city-5", "San Jose");
        $this->select("billing_country_id-5", "value=1228");
        $this->select("billing_state_province_id-5", "value=1004");
        $this->type("billing_postal_code-5", "94129");  
        $this->click("_qf_Main_upload-bottom");
        
        $this->waitForPageToLoad('30000');
        $this->waitForElementPresent("_qf_Confirm_next-bottom");
        
        $this->click("_qf_Confirm_next-bottom");
        $this->waitForPageToLoad('30000');
        
        //login to check membership
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        //Find Member
        $this->open($this->sboxPath . "civicrm/member/search&reset=1");
        $this->waitForElementPresent("member_end_date_high");
        
        $this->type("sort_name", "$firstName $lastName" );
        $this->click("_qf_Search_refresh");
        
        $this->waitForPageToLoad('30000');
        
        $this->waitForElementPresent('css=#memberSearch table tbody tr td span a.action-item-first');
        $this->click('css=#memberSearch table tbody tr td span a.action-item-first');
        $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );
        
        //View Membership Record
        $verifyData = array(
                            'Member' => $firstName.' '.$lastName,
                            'Membership Type'=> 'Student',
                            'Source' => 'Online Contribution:'.' '.$contributionTitle,
                            );
        foreach ( $verifyData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
        $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );
        $this->waitForElementPresent( "xpath=id('MembershipView')/div[2]/div/table[2]/tbody/tr[1]/td[8]/span/a[text()='View']" );
        $this->click("xpath=id('MembershipView')/div[2]/div/table[2]/tbody/tr[1]/td[8]/span/a[text()='View']");
        $this->waitForElementPresent( "_qf_ContributionView_cancel-bottom" ); 
        //View Contribution Record
        $verifyData = array(
                            'From'=> $firstName.' '.$lastName,
                            'Contribution Type' => 'Donation',
                            'Total Amount'=> '$ 50.00',
                            );
        foreach ( $verifyData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='ContributionView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
    }  
}
