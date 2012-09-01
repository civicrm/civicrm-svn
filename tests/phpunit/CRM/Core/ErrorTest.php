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
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'CiviTest/CiviUnitTestCase.php';

/**
 * Tests for linking to resource files
 */
class CRM_Core_ErrorTest extends CiviUnitTestCase {
  function get_info() {
    return array(
      'name'    => 'Errors',
      'description' => 'Tests for error handling',
      'group'     => 'Core',
    );
  }

  function setUp() {
    parent::setUp();
  }

  /**
   * Make sure that formatBacktrace() accepts values from debug_backtrace()
   */
  function testFormatBacktrace_debug() {
    $bt = debug_backtrace();
    $msg = CRM_Core_Error::formatBacktrace($bt);
    $this->assertRegexp('/CRM_Core_ErrorTest->testFormatBacktrace_debug/', $msg);
  }

  /**
   * Make sure that formatBacktrace() accepts values from Exception::getTrace()
   */
  function testFormatBacktrace_exception() {
    $e = new Exception('foo');
    $msg = CRM_Core_Error::formatBacktrace($e->getTrace());
    $this->assertRegexp('/CRM_Core_ErrorTest->testFormatBacktrace_exception/', $msg);
  }
}
