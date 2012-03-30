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
 
class WebTest_Contribute_OnlineMultiplePaymentProcessorTest extends CiviSeleniumTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }


    function testOnlineMultpiplePaymentProcessor() 
    {
        $this->open( $this->sboxPath );

        // Log in using webtestLogin() method
        $this->webtestLogin();


        $proProcessorName = "Pro " . substr(sha1(rand()), 0, 7);
        $standardProcessorName = "Standard " . substr(sha1(rand()), 0, 7);
        $donationPageTitle = "Donation" . substr(sha1(rand()), 0, 7);
        $pageId = $this->webtestAddContributionPage( $hash = null,
                                         $rand = null,
                                         $pageTitle = $donationPageTitle,
                                         $processor = array($proProcessorName => 'Dummy', $standardProcessorName => 'PayPal_Standard'),
                                         $amountSection = true,
                                         $payLater      = true,
                                         $onBehalf      = false,
                                         $pledges       = true,
                                         $recurring     = false,
                                         $membershipTypes = false,
                                         $memPriceSetId = null,
                                         $friend        = false,
                                         $profilePreId  = 1,
                                         $profilePostId = null,
                                         $premiums      = false,
                                         $widget        = false,
                                         $pcp           = false ,
                                         $isAddPaymentProcessor = true,
                                         $isPcpApprovalNeeded = false,
                                         $isSeparatePayment = false,
                                         $honoreeSection = false,
                                         $allowOtherAmmount = true);

        

        $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&action=preview&id=$pageId" );
        $this->waitForElementPresent('page-title');
        $this->assertTrue( $this->isTextPresent( $donationPageTitle ));

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

        $this->assertTrue( $this->isTextPresent( "Payment Method" ));
        $xpath = "xpath=//label[text() = '{$proProcessorName}']/preceding-sibling::input[1]";
        $this->check($xpath);

        $this->waitForElementPresent( "credit_card_type" );

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

    }     

    function testOnlineMultpiplePaymentProcessorWithPayLater() 
    {
        $this->open( $this->sboxPath );

        // Log in using webtestLogin() method
        $this->webtestLogin();


        $proProcessorName = "Pro " . substr(sha1(rand()), 0, 7);
        $standardProcessorName = "Standard " . substr(sha1(rand()), 0, 7);
        $donationPageTitle = "Donation" . substr(sha1(rand()), 0, 7);
        $hash = substr(sha1(rand()), 0, 7);
        $pageId = $this->webtestAddContributionPage( $hash,
                                         $rand = null,
                                         $pageTitle = $donationPageTitle,
                                         $processor = array($proProcessorName => 'Dummy'),
                                         $amountSection = true,
                                         $payLater      = true,
                                         $onBehalf      = false,
                                         $pledges       = true,
                                         $recurring     = false,
                                         $membershipTypes = false,
                                         $memPriceSetId = null,
                                         $friend        = false,
                                         $profilePreId  = 1,
                                         $profilePostId = null,
                                         $premiums      = false,
                                         $widget        = false,
                                         $pcp           = false ,
                                         $isAddPaymentProcessor = true,
                                         $isPcpApprovalNeeded = false,
                                         $isSeparatePayment = false,
                                         $honoreeSection = false,
                                         $allowOtherAmmount = true);

        

        $this->open( $this->sboxPath . "civicrm/contribute/transact?reset=1&action=preview&id=$pageId" );
        $this->waitForElementPresent('page-title');
        $this->assertTrue( $this->isTextPresent( $donationPageTitle ));

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

        $this->assertTrue( $this->isTextPresent( "Payment Method" ));
        $payLaterText = "Pay later label $hash";
        $xpath = "xpath=//label[text() = '{$payLaterText}']/preceding-sibling::input[1]";
        $this->check($xpath);
        $this->waitForPageToLoad( '30000' );

        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        $this->assertTrue( $this->isTextPresent( $payLaterInstructionsText ));
        
        $this->click( "_qf_Confirm_next-bottom" );
        $this->waitForPageToLoad( '30000' );

        $payLaterInstructionsText = "Pay later instructions $hash";
        $this->assertTrue( $this->isTextPresent( $payLaterInstructionsText ));
        
        //login to check contribution
        $this->open( $this->sboxPath );

    }
}
