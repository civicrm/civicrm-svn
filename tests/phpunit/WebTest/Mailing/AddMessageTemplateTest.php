<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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

class WebTest_Mailing_AddMessageTemplateTest extends CiviSeleniumTestCase {
    
    protected function setUp()
    {
        parent::setUp();
    }
    
    function testTemplateAdd ( )
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
        $this->webtestLogin( );
        
        // Go directly to the URL of the screen that you will be testing (Add Message Template).
        $this->open($this->sboxPath . "civicrm/admin/messageTemplates&reset=1");
        
        $this->click("newMessageTemplates");
        $this->waitForPageToLoad("30000");
        
        // Fill message title.
        $msgTitle = 'msg_'.substr(sha1(rand()), 0, 7);
        $this->type("msg_title", $msgTitle);
        
        // Fill message subject.
        $msgSubject = "This is subject for message";
        $this->type("msg_subject", $msgSubject);
        
        // Fill text message.
        $txtMsg = "This is text message";
        $this->type("msg_text", $txtMsg);
        
        // Fill html message.
        $htmlMsg = "This is HTML message";
        $this->type("msg_html", $htmlMsg);
        
        // Clicking save.
        $this->click("_qf_MessageTemplates_next");
        $this->waitForPageToLoad("30000");
        
        // Is status message correct
        $this->assertTrue($this->isTextPresent("The Message Template '$msgTitle' has been saved."));
        
        // Verify text.
        $this->assertTrue( $this->isTextPresent( $msgTitle ) );
        $this->assertTrue( $this->isTextPresent( $msgSubject ) );
    }
    
}
?>