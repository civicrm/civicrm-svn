<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
class WebTest_Mailing_SpoolTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testSpooledMailing() {

    $this->open($this->sboxPath);
    $this->webtestLogin();

    // Change outbound mail setting
    $this->open($this->sboxPath . "civicrm/admin/setting/smtp?reset=1");
    $this->waitForElementPresent("_qf_Smtp_next");
    $this->click("xpath=//input[@name='outBound_option' and @value='4']");
    $this->click("_qf_Smtp_next");
    $this->waitForPageToLoad("30000");
    
    // Is there supposed to be a status message displayed when outbound email settings are changed?
    // assert something?

    $fname = substr(sha1(rand()), 0, 6);
    $lname = substr(sha1(rand()), 0, 6);
    $email = $this->webtestAddContact($fname, $lname, TRUE);

    $urlElements = $this->parseURL();
    $cid = $urlElements['queryString']['cid'];
    $this->assertNotEmpty( $cid, 'Could not find cid after adding contact' );

    // Create an email to the added contact
    $this->open( $this->sboxPath . 'civicrm/activity/email/add?action=add&reset=1&cid=' . $cid . '&selectedChild=activity&atype=3' );
    $this->waitForPageToLoad("30000");
    $this->type( 'subject', 'test spool' );
    $this->fillRichTextField( 'html_message', 'Unit tests keep children safe.' );
    $this->click( "_qf_Email_upload" );

    $this->open( $this->sboxPath . 'civicrm/mailing/browse/archived?reset=1' );
// I don't understand but for some reason we have to load the page twice for our mailing to appear.
    $this->waitForPageToLoad("30000");
    $this->open( $this->sboxPath . 'civicrm/mailing/browse/archived?reset=1' );
    $this->waitForElementPresent( 'css=td.crm-mailing-name' );

    $this->assertText( 'css=td.crm-mailing-name', 'test spool' );

    // should always be mid=1 if starting with a blank sandbox
    // alternatively could click the Report link but not sure how to select it since it wouldn't be unique if there was more than one
    $this->open( $this->sboxPath . 'civicrm/mailing/report?mid=1&reset=1' );

    // Not sure how robust this is if text changes, but there isn't a good
    // identifier for this link either.
    $this->waitForElementPresent( '//a[contains(text(), "View complete message")]' );
    $this->click( '//a[contains(text(), "View complete message")]' );

    $this->waitForPopUp( null, 30000 );
    $this->selectPopUp( null );
    $msg = $this->getBodyText();
    $this->assertNotEmpty( $msg, 'Mail message empty or not found.' );
    echo $msg;
//TODO: parse msg (using EZC?) and check it matches. Consider providing reusable class to do common parsing for unit testing emails.
  }
}
