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

/**
 *  Mail utils for use during unit testing to allow retrieval
 *  and examination of 'sent' emails.
 *
 *  Basic usage:
 *
 *  $mut = new CiviUnitMailTest( $this );
 *  ... do stuff ...
 *  $msg = $mut->getMostRecentEmail( 'ezc' ); // or 'raw'
 *  ... assert stuff about $msg ...
 *  $mut->stop();
 *
 *
 *  @package CiviCRM
 */

require_once 'ezc/Base/src/ezc_bootstrap.php';
require_once 'ezc/autoload/mail_autoload.php';

class CiviMailUnitTest {

  /*
   * current outbound email option
   */
  protected $_outBound_option = null;

  /*
   * Constructor
   *
   * @param $unit_test object The currently running test
   * @param $startImmediately bool Start writing to db now or wait until start() is called
   */
  function __construct( &$unit_test, $startImmediately = true ) {
    $this->_ut = $unit_test;

    if ( $startImmediately ) {
      $this->start();
    }
  }

  /**
   * Start writing emails to db instead of current option
   */
  function start() {
    // Change outbound mail setting
    $this->_ut->open($this->_ut->sboxPath . "civicrm/admin/setting/smtp?reset=1");
    $this->_ut->waitForElementPresent("_qf_Smtp_next");

    // First remember the current setting
    $this->_outBound_option = $this->getSelectedOutboundOption();

    $this->_ut->click('xpath=//input[@name="outBound_option" and @value="' . CRM_Mailing_Config::OUTBOUND_OPTION_REDIRECT_TO_DB . '"]');
    $this->_ut->click("_qf_Smtp_next");
    $this->_ut->waitForPageToLoad("30000");

    // Is there supposed to be a status message displayed when outbound email settings are changed?
    // assert something?
  }

  function stop() {
    $this->_ut->open($this->_ut->sboxPath . "civicrm/admin/setting/smtp?reset=1");
    $this->_ut->waitForElementPresent("_qf_Smtp_next");
    $this->_ut->click('xpath=//input[@name="outBound_option" and @value="' . $this->_outBound_option . '"]');
    $this->_ut->click("_qf_Smtp_next");
    $this->_ut->waitForPageToLoad("30000");

    // Is there supposed to be a status message displayed when outbound email settings are changed?
    // assert something?
  }

  function getMostRecentEmail( $type = 'raw' ) {
    $this->_ut->open( $this->_ut->sboxPath . 'civicrm/mailing/browse/archived?reset=1' );
    // I don't understand but for some reason we have to load the page twice for a recent mailing to appear.
    $this->_ut->waitForPageToLoad("30000");
    $this->_ut->open( $this->_ut->sboxPath . 'civicrm/mailing/browse/archived?reset=1' );
    $this->_ut->waitForElementPresent( 'css=td.crm-mailing-name' );

    // This should select the first "Report" link in the table, which is sorted by Completion Date descending, so in theory is the most recent email. Not sure of a more robust way at the moment.
    $this->_ut->click( 'xpath=//tr[contains(@id, "crm-mailing_")]//a[text()="Report"]' );

    // Also not sure how robust this is, but there isn't a good
    // identifier for this link either.
    $this->_ut->waitForElementPresent( 'xpath=//a[contains(text(), "View complete message")]' );
    $this->_ut->click( 'xpath=//a[contains(text(), "View complete message")]' );

    $this->_ut->waitForPopUp( null, 30000 );
    $this->_ut->selectPopUp( null );
    /*
     * FIXME:
     *
     * Argh.
     * getBodyText() doesn't work because it's just one big long string without line breaks. getHtmlSource() doesn't work because it sees email addresses as html tags and inserts its own closing tags.
     */
    //$msg = $this->_ut->getHtmlSource();
    $msg = $this->_ut->getBodyText();
    switch ( $type ) {
    case 'raw':
      // nothing to do
      break;
    case 'ezc':
      $set = new ezcMailVariableSet( $msg );
      $parser = new ezcMailParser();
      $mail = $parser->parseMail( $set );
      $this->_ut->assertNotEmpty( $mail, 'Cannot parse mail' );
      $msg = $mail[0];
      break;
    }
    $this->_ut->close();
    $this->_ut->selectWindow( null );
    return $msg;
  }

  function getSelectedOutboundOption() {
    $selectedOption = CRM_Mailing_Config::OUTBOUND_OPTION_MAIL;
    // Is there a better way to do this?
    for( $i = 0; $i <= 5; $i++ ) {
      if ( $i != CRM_Mailing_Config::OUTBOUND_OPTION_MOCK ) {
        if ( $this->_ut->getValue( 'xpath=//input[@name="outBound_option" and @value="' . $i . '"]' ) == "on" ) {
          $selectedOption = $i;
          break;
        }
      }
    }
    return $selectedOption;
  }

  /*
   * Utility functions (previously part of CiviUnitTestCase)
   */

  /*
   * Check contents of mail log
   * @param array $strings strings that should be included
   * @param array $absentStrings strings that should not be included
   */
  function checkMailLog($strings, $absentStrings = array(), $prefix = ''){
    $mail = file_get_contents(CIVICRM_MAIL_LOG);
    foreach ($strings as $string) {
    $this->assertContains($string, $mail, "$string .  not found in  $mail  $prefix");
    }
    foreach ($absentStrings as $string) {
    $this->assertEmpty(strstr($mail,$string),"$string  incorrectly found in $mail $prefix");;
    }
    return $mail;
  }

  /*
   * Check that mail log is empty
   */
  function assertMailLogEmpty($prefix = ''){
    $mail = file_get_contents(CIVICRM_MAIL_LOG);
    $this->assertEmpty($mail, 'mail sent when it should not have been ' . $prefix);
  }
}
