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
    
    protected function setUp() {
        parent::setUp( );
    }

    function testAuthNetVerifySSL( ) {
        // make the payment processors and contribution page
        $pageId = $this->_configureContributionPage( );

        // turn on verify ssl
        $this->_enableVerifySSL();

        /*$this->open( $this->sboxPath );
        $this->webtestLogin( );
        $this->waitForPageToLoad("30000");

        // now do the test contribution
        $this->open($this->sboxPath . "civicrm/contribute/transact?reset=1&action=preview&id={$pageId}" );
        $this->waitForPageToLoad( "3000" );
        $this->waitForElementPresent("_qf_Main_upload-bottom");

        $this->click("CIVICRM_QFID_2_4");

        $this->webtestAddCreditCardDetails( );

        list( $firstName, $middleName, $lastName ) = $this->webtestAddBillingDetails( );

        $this->type( 'email-5', "{$lastName}@example.com" );

        $this->click("_qf_Main_upload-bottom");
        $this->waitForPageToLoad("30000");
        $this->waitForElementPresent( "_qf_Confirm_next-bottom" );

        $this->click("_qf_Confirm_next-bottom");
        $this->waitForPageToLoad("30000");*/
    }

    function _configureContributionPage( ) {
        static $pageId = null;
        if(!$pageId) {
            // log in
            $this->open($this->sboxPath);
            $this->webtestLogin();

            // add payment processors
            $hash = substr(sha1(rand()), 0, 7);
            $this->webtestAddPaymentProcessor( "WebTest Verify SSL AuthNet $hash", 'AuthNet' );
            $this->webtestAddPaymentProcessor( "WebTest Verify SSL Google Checkout $hash", 'Google_Checkout' );
            $this->webtestAddPaymentProcessor( "WebTest Verify SSL PayPal $hash", 'PayPal' );
            $this->webtestAddPaymentProcessor( "WebTest Verify SSL PayPal Standard $hash", 'PayPal_Standard' );

            // create new contribution page
            $this->open($this->sboxPath."civicrm/admin/contribute/add?action=add&reset=1");
            $this->waitForPageToLoad("30000");
            $this->type("id=title", "WebTest Verify SSL $hash");
            $this->click("id=_qf_Settings_next-bottom");
            $this->waitForPageToLoad("30000");
            $this->click("//label[text() = 'WebTest Verify SSL AuthNet $hash']");
            $this->check("xpath=//label[text() = 'WebTest Verify SSL Google Checkout $hash']/preceding-sibling::input[1]");
            $this->check("xpath=//label[text() = 'WebTest Verify SSL PayPal $hash']/preceding-sibling::input[1]");
            $this->check("xpath=//label[text() = 'WebTest Verify SSL PayPal Standard $hash']/preceding-sibling::input[1]");
            $this->click("id=is_allow_other_amount");
            $this->click("id=_qf_Amount_submit_savenext-bottom");
            $this->waitForPageToLoad("30000");
            $this->click("link=Receipt");
            $this->click("id=thankyou_title");
            $this->type("id=thankyou_title", "Thanks!");
            $this->click("id=_qf_ThankYou_submit_savenext-bottom");
            $this->waitForPageToLoad("30000");
            $this->click("id=_qf_Contribute_upload_done-bottom");
            $this->waitForPageToLoad("30000");

            // logout
            $this->open($this->sboxPath . "civicrm/logout?reset=1");
            $this->waitForPageToLoad('30000'); 
        }

        return $pageId;
    }

    function _enableVerifySSL( ) {
        // login
        $this->open( $this->sboxPath );
        $this->webtestLogin( );

        // load resource URLs
        $this->open($this->sboxPath . "civicrm/admin/setting/url?reset=1");
        $this->waitForPageToLoad('30000');
        $this->click("id=CIVICRM_QFID_1_6");
        $this->click("id=_qf_Url_next-bottom");
        $this->waitForPageToLoad("30000");
    }
}
