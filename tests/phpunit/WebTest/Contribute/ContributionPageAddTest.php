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

class WebTest_Contribute_ContributionPageAddTest extends CiviSeleniumTestCase {

    function testContributionPageAdd()
    {
        // open browser, login
        $this->open($this->sboxPath);
        $this->webtestLogin();

        // a random 7-char string and an even number to make this pass unique
        $hash = substr(sha1(rand()), 0, 7);
        $rand = 2 * rand(2, 50);
        $pageTitle = 'Donate Online ' . $hash;
        // create contribution page with randomized title and default params
        $pageId = $this->webtestAddContributionPage( $hash, $rand, $pageTitle );

        $this->open($this->sboxPath . 'civicrm/admin/contribute&reset=1');
        $this->waitForPageToLoad();        

        // search for the new contrib page and go to its test version
        $this->type('title', $pageTitle);
        $this->click('_qf_SearchContribution_refresh');
        $this->waitForPageToLoad("30000");
        $this->waitForElementPresent("links_{$pageId}"); 

        $this->click("links_{$pageId}");
        $this->click("link=Test-drive");
        $this->waitForPageToLoad();

        // verify whatever’s possible to verify
        // FIXME: ideally should be expanded
        $texts = array(
            "Title - New Membership $hash",
            "This is introductory message for $pageTitle",
            "Student  (contribute at least $ 50.00 to be eligible for this membership)",
            "$ $rand.00 Label $hash",
            "Pay later label $hash",
            "Organization Details",
            "Other Amount",
            "I pledge to contribute this amount every",
            "Honoree Section Title $hash",
            "Honoree Introductory Message $hash",
            "In Honor of",
            "Name and Address",
            "Summary Overlay"
        );
        foreach ($texts as $text) {
            $this->assertTrue( $this->isTextPresent($text), 'Missing text: ' . $text );
        }
    }
    
}
