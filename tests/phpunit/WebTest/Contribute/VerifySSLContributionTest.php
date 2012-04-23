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

class WebTest_Contribute_VerifySSLContributionTest extends CiviSeleniumTestCase {

    protected $initialized = false;
    protected $names = array();
    protected $pageId = 0;
    
    protected function setUp() {
        parent::setUp( );
    }

    function testPaymentProcessorsSSL() {
        $this->_initialize();
        $this->_tryPaymentProcessor($this->names['AuthNet']);

        // todo: write code to check other payment processors
        /*$this->_tryPaymentProcessor($this->names['Google_Checkout']);
        $this->_tryPaymentProcessor($this->names['PayPal']);
        $this->_tryPaymentProcessor($this->names['PayPal_Standard']);*/
    }

    function _initialize() {
        if(!$this->initialized) {
            // log in
            $this->open($this->sboxPath);
            $this->webtestLogin();

            // build names
            $hash = substr(sha1(rand()), 0, 7);
            $contributionPageTitle = "Verify SSL ($hash)";
            $this->names['AuthNet'] = "AuthNet ($hash)";
            $this->names['PayPal'] = "PayPal Pro ($hash)";
            //$this->names['Google_Checkout'] = "Google Checkout ($hash)";
            //$this->names['PayPal_Standard'] = "PayPal Standard ($hash)";

            $processors = array();
            foreach($this->names as $key => $val) {
                $processors[$val] = $key;
            }

            // create new contribution page
            $this->pageId = $this->webtestAddContributionPage(
                $hash,
                $rand = null,
                $pageTitle = $contributionPageTitle,
                $processor = $processors,
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
                $allowOtherAmmount = true
            );
            
            // enable verify ssl
            $this->open($this->sboxPath . "civicrm/admin/setting/url?reset=1");
            $this->waitForPageToLoad('30000');
            $this->click("id=CIVICRM_QFID_1_6");
            $this->click("id=_qf_Url_next-bottom");
            $this->waitForPageToLoad("30000");

            $this->initialized = true;
        }
    }

    function _tryPaymentProcessor($name) {
        // load contribution page
        $this->open($this->sboxPath . "civicrm/contribute/transact?reset=1&action=preview&id={$this->pageId}" );
        $this->waitForPageToLoad( "3000" );
        $this->waitForElementPresent("_qf_Main_upload-bottom");

        // fill out info
        $this->type("xpath=//div[@class='crm-section other_amount-section']//div[2]/input", "30");
        $this->type( 'email-5', "smith@example.com" );

        // choose the payment processor
        $this->click("xpath=//label[text() = '{$name}']/preceding-sibling::input[1]");

        // do we need to add credit card details?
        if(strpos($name, "AuthNet") !== false || strpos($name, "PayPal Pro") !== false) {
            $this->webtestAddCreditCardDetails();
            list( $firstName, $middleName, $lastName ) = $this->webtestAddBillingDetails();
        }

        // submit contribution
        $this->click("_qf_Main_upload-bottom");
        $this->waitForPageToLoad("30000");
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );

        // confirm contribution
        $this->click("_qf_Confirm_next-bottom");
        $this->waitForPageToLoad("30000");
        $this->assertFalse($this->isTextPresent("Payment Processor Error message"), "Payment processor returned error message");
    }
}
