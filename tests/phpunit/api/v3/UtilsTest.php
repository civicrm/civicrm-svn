<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
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
require_once 'api/v3/utils.php';

/**
 * Test class for API utils
 *
 * @package   CiviCRM
 */
class api_v3_UtilsTest extends CiviUnitTestCase {
    protected $_apiversion;
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
        parent::setUp ();
        $this->_apiversion = 3;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
    }

    function testAddFormattedParam() {
        $values = array ('contact_type' => 'Individual' );
        $params = array ('something' => 1 );
        $result = _civicrm_api3_add_formatted_param ( $values, $params );
        $this->assertTrue ( $result );
    }

    function testCheckPermissionReturn() {
        $check = array ('check_permissions' => true );

        CRM_Core_Permission_UnitTests::$permissions = array ();
        $this->assertFalse(civicrm_api3_api_check_permission('contact', 'create', $check, false), 'empty permissions should not be enough');
        CRM_Core_Permission_UnitTests::$permissions = array ('access CiviCRM' );
        $this->assertFalse(civicrm_api3_api_check_permission('contact', 'create', $check, false), 'lacking permissions should not be enough');
        CRM_Core_Permission_UnitTests::$permissions = array ('add contacts' );
        $this->assertFalse(civicrm_api3_api_check_permission('contact', 'create', $check, false), 'lacking permissions should not be enough');

        CRM_Core_Permission_UnitTests::$permissions = array ('access CiviCRM', 'add contacts');
        $this->assertTrue(civicrm_api3_api_check_permission('contact', 'create', $check, false), 'exact permissions should be enough');

        CRM_Core_Permission_UnitTests::$permissions = array ('access CiviCRM', 'add contacts', 'import contacts' );
        $this->assertTrue(civicrm_api3_api_check_permission('contact', 'create', $check, false), 'overfluous permissions should be enough');
    }

    function testCheckPermissionThrow() {
        $check = array ('check_permissions' => true );

        try {
            CRM_Core_Permission_UnitTests::$permissions = array ('access CiviCRM' );
            civicrm_api3_api_check_permission('contact', 'create', $check);
        } catch ( Exception $e ) {
            $message = $e->getMessage ();
        }
        $this->assertEquals($message, 'API permission check failed for contact/create call; missing permission: add contacts.', 'lacking permissions should throw an exception');

        CRM_Core_Permission_UnitTests::$permissions = array ('access CiviCRM', 'add contacts', 'import contacts' );
        $this->assertTrue(civicrm_api3_api_check_permission('contact', 'create', $check), 'overfluous permissions should return true');
    }

    function testCheckPermissionSkip() {
        CRM_Core_Permission_UnitTests::$permissions = array ('access CiviCRM' );
        $params = array('check_permissions' => true);
        $this->assertFalse(civicrm_api3_api_check_permission('contact', 'create', $params, false), 'lacking permissions should not be enough');
        $params = array('check_permissions' => false);
        $this->assertTrue(civicrm_api3_api_check_permission('contact', 'create', $params, false), 'permission check should be skippable');
    }

    /*
     * Test verify mandatory - includes DAO & passed as well as empty & NULL fields
     */
    function testVerifyMandatory() {
        _civicrm_api3_initialize ( true );
        $params = array ('entity_table' => 'civicrm_contact',
                         'note' => '',
                         'contact_id' => $this->_contactID,
                         'modified_date' => '2011-01-31',
                         'subject' => NULL,
                         'version' => $this->_apiversion );
        try {
            $result = civicrm_api3_verify_mandatory ( $params, 'CRM_Core_BAO_Note', array ('note', 'subject' ) );
        }
        catch ( Exception $expected ) {
            $this->assertEquals ( 'Mandatory key(s) missing from params array: entity_id, note, subject', $expected->getMessage () );
            return;
        }

        $this->fail ( 'An expected exception has not been raised.' );
    }

    /*
     * Test verify one mandatory - includes DAO & passed as well as empty & NULL fields
     */
    function testVerifyOneMandatory() {
        _civicrm_api3_initialize ( true );
        $params = array ('entity_table' => 'civicrm_contact',
                         'note' => '',
                         'contact_id' => $this->_contactID,
                         'modified_date' => '2011-01-31',
                         'subject' => NULL,
                         'version' => $this->_apiversion );

        try {
            $result = civicrm_api3_verify_one_mandatory ( $params, 'CRM_Core_BAO_Note', array ('note', 'subject' ) );
        }
        catch ( Exception $expected ) {
            $this->assertEquals ( 'Mandatory key(s) missing from params array: entity_id, one of (note, subject)', $expected->getMessage () );
            return;
        }

        $this->fail ( 'An expected exception has not been raised.' );
    }

    /*
     * Test verify one mandatory - includes DAO & passed as well as empty & NULL fields
     */
    function testVerifyOneMandatoryOneSet() {
        _civicrm_api3_initialize ( true );
        $params = array ('entity_table' => 'civicrm_contact', 'note' => 'note', 'contact_id' => $this->_contactID, 'modified_date' => '2011-01-31', 'subject' => NULL, 'version' => $this->_apiversion );

        try {
            civicrm_api3_verify_one_mandatory ( $params, NULL, array ('note', 'subject' ) );
        }
        catch ( Exception $expected ) {
            $this->fail ( 'Exception raised when it shouldn\'t have been  in line ' . __LINE__ );
        }

    }


	/*
	 * Test GET DAO function returns DAO
	 */
	function testGetDAO(){
	 $DAO =  _civicrm_api3_get_DAO ('civicrm_api3_survey_get');
	 $this->assertEquals('CRM_Campaign_DAO_Survey', $DAO );
	 $DAO =  _civicrm_api3_get_DAO ('civicrm_api3_pledge_payment_get');
	 $this->assertEquals('CRM_Pledge_DAO_Payment', $DAO );
	}
	/*
	 * Test GET DAO function returns DAO
	 */
	function testGetBAO(){
	 $BAO =  _civicrm_api3_get_BAO ('civicrm_api3_survey_get');
	 $this->assertEquals('CRM_Campaign_BAO_Survey', $BAO );
	 $BAO =  _civicrm_api3_get_BAO ('civicrm_api3_pledge_payment_get');
	 $this->assertEquals('CRM_Pledge_BAO_Payment', $BAO );
	}
	
}
