<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CRM/Core/Permission.php';
require_once 'CRM/Core/Permission/UnitTests.php';
require_once 'api/v2/utils.php';

/**
 * Test class for API utils
 *
 *  @package   CiviCRM
 */
class api_v2_UtilsTest extends CiviUnitTestCase
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        parent::setUp();
        CRM_Core_Permission_UnitTests::$permissions = null; // reset check() stub
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

    function testAddFormattedParam() {
      $values = array( 'contact_type' => 'Individual' );
      $params = array( 'something' => 1 );
      $result = _civicrm_add_formatted_param( $values, $params );
      $this->assertTrue( $result );
    }

    function testCheckPermissionReturn()
    {
        CRM_Core_Permission_UnitTests::$permissions = array();
        $this->assertFalse(civicrm_api_check_permission('civicrm_contact_create', array()), 'empty permissions should not be enough');
        CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM');
        $this->assertFalse(civicrm_api_check_permission('civicrm_contact_create', array()), 'lacking permissions should not be enough');
        CRM_Core_Permission_UnitTests::$permissions = array('add contacts');
        $this->assertFalse(civicrm_api_check_permission('civicrm_contact_create', array()), 'lacking permissions should not be enough');

        CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM', 'add contacts');
        $this->assertTrue(civicrm_api_check_permission('civicrm_contact_create', array()), 'exact permissions should be enough');

        CRM_Core_Permission_UnitTests::$permissions = array('access CiviCRM', 'add contacts', 'import contacts');
        $this->assertTrue(civicrm_api_check_permission('civicrm_contact_create', array()), 'overfluous permissions should be enough');
    }
}
