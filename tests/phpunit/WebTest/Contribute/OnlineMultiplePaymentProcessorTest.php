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

        $processorName = "Pro " . substr(sha1(rand()), 0, 7);
        $this->webtestAddPaymentProcessor($processorName, 'PayPal');

        $processorName = "Standard " . substr(sha1(rand()), 0, 7);
        $this->webtestAddPaymentProcessor($processorName, 'PayPal_Standard');

    } 
}
