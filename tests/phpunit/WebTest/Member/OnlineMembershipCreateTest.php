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
        
        //check for online contribution and profile listings permissions
        $this->changeAdminLinks( );
        if ( !$this->isChecked( "edit-1-make-online-contributions" ) ) {
            $this->click( "edit-1-make-online-contributions" );
        }
        if ( !$this->isChecked( "edit-1-profile-listings-and-forms" ) ) {
            $this->click( "edit-1-profile-listings-and-forms" ); 
        }
        $this->click( "edit-submit" );
        $this->waitForPageToLoad( "30000" );
        
        // create contribution page with randomized title and default params
        
        $amountSection     = true;
        $payLater          = true;
        $allowOtherAmmount = false;
        $onBehalf          = false;
        $pledges           = false; 
        $recurring         = false;
        $memberships       = true;
        $memPriceSetId     = null;
        $friend            = true; 
        $profilePreId      = 1;
        $profilePostId     = null;
        $premiums          = true;
        $widget            = false;
        $pcp               = true;
        $isSeparatePayment = true;
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
                                                     $memPriceSetId,
                                                     $friend       , 
                                                     $profilePreId ,
                                                     $profilePostId,
                                                     $premiums     ,
                                                     $widget       ,
                                                     $pcp          ,
                                                     true          ,
                                                     false         ,
                                                     $isSeparatePayment,
                                                     true          ,
                                                     false     
                                                     );
        
        
        // create two new membership types
        $memTypeParams1 = $this->webtestAddMembershipType( );
        $memTypeTitle1  = $memTypeParams1['membership_type'];
        $memTypeId1     = explode( '&id=', $this->getAttribute( "xpath=//div[@id='membership_type']/div[2]/table/tbody//tr/td[text()='{$memTypeTitle1}']/../td[10]/span/a[3]@href" ) );
        $memTypeId1     = $memTypeId1[1];
        
        $memTypeParams2 = $this->webtestAddMembershipType( );
        $memTypeTitle2  = $memTypeParams2['membership_type'];
        $memTypeId2     = explode( '&id=', $this->getAttribute( "xpath=//div[@id='membership_type']/div[2]/table/tbody//tr/td[text()='{$memTypeTitle2}']/../td[10]/span/a[3]@href" ) );
        $memTypeId2     = $memTypeId2[1];
        
        // edit contribution page memberships tab to add two new membership types
        $this->open($this->sboxPath . "civicrm/admin/contribute/membership?reset=1&action=update&id={$pageId}");
        $this->waitForElementPresent('_qf_MembershipBlock_next-bottom');   
        $this->click("membership_type[$memTypeId1]");
        $this->click("membership_type[$memTypeId2]");
        $this->click('_qf_MembershipBlock_next');
        $this->waitForPageToLoad('30000');
        $this->waitForElementPresent('_qf_MembershipBlock_next-bottom');
        $text = "'MembershipBlock' information has been saved.";
        $this->assertTrue( $this->isTextPresent( $text ), 'Missing text: ' . $text );
        
        //logout
        $this->open($this->sboxPath . "civicrm/logout?reset=1");
        $this->waitForPageToLoad('30000');
        
        // signup for membership 1
        $firstName = 'Ma'.substr( sha1( rand( ) ), 0, 4 );
        $lastName  = 'An'.substr( sha1( rand( ) ), 0, 7 );
        
        //$this->_testOnlineMembershipSignup( $pageId, $memTypeId1, $firstName, $lastName );
        $this->_testOnlineMembershipSignup( $pageId, $memTypeId1, $firstName, $lastName, $payLater );
        //login to check membership
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        //Find Member
        $this->open($this->sboxPath . "civicrm/member/search?reset=1");
        $this->waitForElementPresent("member_end_date_high");
        
        $this->type("sort_name", "$firstName $lastName" );
        $this->click("_qf_Search_refresh");
        
        $this->waitForPageToLoad('30000');
        
        $this->waitForElementPresent( "xpath=//div[@id='memberSearch']/table/tbody/tr" );
        $this->click( "xpath=//div[@id='memberSearch']/table/tbody/tr/td[11]/span/a[text()='View']" );
        $this->waitForElementPresent( "_qf_MembershipView_cancel-bottom" );
        
        //View Membership Record
        $verifyData = array(
                            'Member' => $firstName.' '.$lastName,
                            'Membership Type'=> $memTypeTitle1,
                            'Source' => 'Online Contribution:'.' '.$contributionTitle,
                            );
        if( $payLater )
            $verifyData['Status'] = 'Pending';
        else
            $verifyData['Status'] = 'New';
        foreach ( $verifyData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='MembershipView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
        
        // Click View action link on associated contribution record
        $this->waitForElementPresent("xpath=//form[@id='MembershipView']/div[2]/div/table[2]/tbody/tr[1]/td[8]/span/a[text()='View']");
        $this->click("xpath=//form[@id='MembershipView']/div[2]/div/table[2]/tbody/tr[1]/td[8]/span/a[text()='View']");
        $this->waitForElementPresent( "_qf_ContributionView_cancel-bottom" ); 
        //View Contribution Record
        $verifyData = array(
                            'From'=> $firstName.' '.$lastName,
                            'Total Amount'=> '$ 100.00',
                            );
        if( $payLater )
            $verifyData['Contribution Status'] = 'Pending : Pay Later';
        else
            $verifyData['Contribution Status'] = 'Completed';
        foreach ( $verifyData as $label => $value ) {
            $this->verifyText( "xpath=//form[@id='ContributionView']//table/tbody/tr/td[text()='{$label}']/following-sibling::td", 
                               preg_quote( $value ) );   
        }
        
        // CRM-8141 signup for membership 2 with same anonymous user info (should create 2 separate membership records because membership orgs are different)
        //logout
        $this->open($this->sboxPath . "civicrm/logout?reset=1");
        $this->waitForPageToLoad('30000');
        
        //$this->_testOnlineMembershipSignup( $pageId, $memTypeId2, $firstName, $lastName );
        $this->_testOnlineMembershipSignup( $pageId, $memTypeId2, $firstName, $lastName, $payLater);
        //login to check membership
        $this->open( $this->sboxPath );
        
        // Log in using webtestLogin() method
        $this->webtestLogin();
        
        //Find Member
        $this->open($this->sboxPath . "civicrm/member/search?reset=1");
        $this->waitForElementPresent("member_end_date_high");
        
        $this->type("sort_name", "$firstName $lastName" );
        $this->click("_qf_Search_refresh");
        
        $this->waitForPageToLoad('30000');
        $this->assertTrue( $this->isTextPresent( '2 Results' ), 'Missing text: ' . '2 Results' ); 
        
    }  
    
    //function _testOnlineMembershipSignup( $pageId, $memTypeId, $firstName, $lastName )
    function _testOnlineMembershipSignup( $pageId, $memTypeId, $firstName, $lastName, $payLater )
    {
        //Open Live Contribution Page
        $makeContribUrl = "{$this->sboxPath}civicrm/contribute/transact?reset=1&id=$pageId";
        $this->open($makeContribUrl);
        $this->waitForElementPresent("_qf_Main_upload-bottom");
        
        // Select membership type 1
        $this->check( "name=selectMembership value={$memTypeId}" );
        $this->check( "name=amount value=no_thanks" );
        if( $payLater )
            $this->check( "name=is_pay_later value=1" );
        $this->type("email-5", $firstName . "@example.com");
        
        $this->type("first_name", $firstName);
        $this->type("last_name",$lastName );
        
        $streetAddress = "100 Main Street";
        $this->type("street_address-1", $streetAddress);
        $this->type("city-1", "San Francisco");
        $this->type("postal_code-1", "94117");
        $this->select("country-1", "value=1228");
        $this->select("state_province-1", "value=1001");
        if( !$payLater ){
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
        } 
        $this->click("_qf_Main_upload-bottom");
        
        $this->waitForPageToLoad('30000');
        $this->waitForElementPresent("_qf_Confirm_next-bottom");
        
        $this->click("_qf_Confirm_next-bottom");
        $this->waitForPageToLoad('30000');
    }
}
