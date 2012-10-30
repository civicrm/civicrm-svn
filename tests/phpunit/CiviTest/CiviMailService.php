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
 *  'Fake' mail service for use during unit testing to allow retrieval
 *  and examination of 'sent' emails.
 *
 *  @package CiviCRM
 */

require_once 'ezc/Base/src/ezc_bootstrap.php';
require_once 'ezc/autoload/mail_autoload.php';

class CiviMailService {

    protected $unitTest;

    /**
     * Constructor
     *
     * @param object ut A unit test object, e.g. the currently running test.
     */
    function __construct( &$ut ) {
        $this->unitTest = &$ut;

        if(!defined('CIVICRM_MAIL_LOG')) {
          define( 'CIVICRM_MAIL_LOG', CIVICRM_TEMPLATE_COMPILEDIR . 'mail.log' );
        }

        $this->unitTest->assertFalse(is_numeric(CIVICRM_MAIL_LOG) ,'we need to be able to log email to check receipt');

        $this->reset();
    }

    /**
     * reset()
     *
     * At the moment the "service" only really supports one email at a time,
     * so need to clear out the log each time.
     */
    function reset(){
        if ( defined( 'CIVICRM_MAIL_LOG' ) ) {
            file_put_contents(CIVICRM_MAIL_LOG, '');
        }
    }

    /**
     * stop()
     *
     * Can't actually stop because you can't undefine or redefine a constant.
     * This is probably ok within the context of unit testing for now.
     */
    function stop() {
        if ( defined( 'CIVICRM_MAIL_LOG' ) ) {
            unlink( CIVICRM_MAIL_LOG );
        }
    }

    /**
     * At the moment only one email at a time is supported, and the parameter
     * is ignored. The intention is to allow for e.g. raw message source or
     * non-EZC return formats, but for now we always return an ezcMailPart
     * object.
     */
    function &fetch( $params = null ) {
        if ( defined( 'CIVICRM_MAIL_LOG' ) ) {
            $set = new ezcMailFileSet( array( CIVICRM_MAIL_LOG ) );
            $parser = new ezcMailParser();
            $mail = $parser->parseMail( $set );
            return $mail[0];
        } else {
            return null;
        }
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
          $this->unitTest->assertContains($string, $mail, "$string .  not found in  $mail  $prefix");
        }
        foreach ($absentStrings as $string) {
          $this->unitTest->assertEmpty(strstr($mail,$string),"$string  incorrectly found in $mail $prefix");;
        }
        return $mail;
    }

    /*
     * Check that mail log is empty
     */
    function assertMailLogEmpty($prefix = ''){
        $mail = file_get_contents(CIVICRM_MAIL_LOG);
        $this->unitTest->assertEmpty($mail, 'mail sent when it should not have been ' . $prefix);
    }
}
