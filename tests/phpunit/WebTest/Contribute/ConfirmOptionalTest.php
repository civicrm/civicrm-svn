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

class WebTest_Contribute_ConfirmOptionalTest extends CiviSeleniumTestCase {
    protected $pageId = 0;

    protected function setUp() {
        parent::setUp();
    }

    function testWithConfirm() {
        $this->_addContributionPage(true);
        $this->_fillOutContributionPage();
        
        // confirm contribution
        $this->assertFalse($this->isTextPresent("Your transaction has been processed successfully"), "Loaded thank you page");
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );
        $this->assertTrue($this->isTextPresent("Your contribution will not be completed until"), "Should load confirmation page");
        $this->click("_qf_Confirm_next-bottom");
        $this->waitForPageToLoad("30000");

        // thank you page
        $this->assertTrue($this->isTextPresent("Your transaction has been processed successfully"), "Should load thank you page");
    }

    function testWithoutConfirm() {
        $this->_addContributionPage(false);
        $this->_fillOutContributionPage();

        // thank you page
        $this->assertTrue($this->isTextPresent("Your transaction has been processed successfully"), "Didn't load thank you page after main page");
        $this->assertFalse($this->isTextPresent("Your contribution will not be completed until"), "Loaded confirmation page");
    }

    protected function _addContributionPage($isConfirmEnabled) {
        // log in
        $this->open($this->sboxPath);
        $this->webtestLogin();

        // create new contribution page
        $hash = substr(sha1(rand()), 0, 7);
        $this->pageId = $this->webtestAddContributionPage(
            $hash,
            $rand = null,
            $pageTitle = "Test Confirm ($hash)",
            $processor = array("Dummy ($hash)" => 'Dummy'),
            $amountSection = true,
            $payLater      = false,
            $onBehalf      = false,
            $pledges       = false,
            $recurring     = false,
            $membershipTypes = false,
            $memPriceSetId = null,
            $friend        = false,
            $profilePreId  = null,
            $profilePostId = null,
            $premiums      = false,
            $widget        = false,
            $pcp           = false ,
            $isAddPaymentProcessor = true,
            $isPcpApprovalNeeded = false,
            $isSeparatePayment = false,
            $honoreeSection = false,
            $allowOtherAmmount = true,
            $isConfirmEnabled = $isConfirmEnabled
        );
    }

    protected function _fillOutContributionPage() {
        // load contribution page
        $this->open($this->sboxPath . "civicrm/contribute/transact?reset=1&id={$this->pageId}" );
        $this->waitForPageToLoad( "3000" );
        $this->waitForElementPresent("_qf_Main_upload-bottom");

        // fill out info
        $this->type("id=amount_other", "30");
        $this->webtestAddCreditCardDetails();
        list( $firstName, $middleName, $lastName ) = $this->webtestAddBillingDetails();
        $this->type( 'email-5', "$lastName@example.com" );

        // submit contribution
        $this->click("_qf_Main_upload-bottom");
        $this->waitForPageToLoad("30000");
    }
}
