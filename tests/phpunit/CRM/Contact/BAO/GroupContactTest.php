<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
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

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CiviTest/Contact.php';

/**
 * Test class for CRM_Contact_BAO_GroupContact BAO
 *
 *  @package   CiviCRM
 */
class CRM_Contact_BAO_GroupContactTest extends CiviUnitTestCase 
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

    /**
     * test case for add( )
     */
    function testAdd( )
    {
        require_once 'CRM/Contact/BAO/GroupContact.php';

        //creates a test group contact by recursively creation
        //lets create 10 groupContacts for fun
        $groupContacts = CRM_Core_DAO::createTestObject( 'CRM_Contact_DAO_GroupContact',null,10);

        //check the group contact id is not null for each of them
        foreach ($groupContacts as $gc) $this->assertNotNull( $gc->id );

        //cleanup
        foreach ($groupContacts as $gc) $gc->deleteTestObjects('CRM_Contact_DAO_GroupContact');
    }

    /**
     * test case for getGroupId( )
     */
    function testGetGroupId()
    {

        require_once 'CRM/Contact/BAO/GroupContact.php';

        //creates a test groupContact object
	//force group_id to 1 so we can compare
        $groupContact = CRM_Core_DAO::createTestObject( 'CRM_Contact_DAO_GroupContact');

	//check the group contact id is not null
        $this->assertNotNull( $groupContact->id );

        $this->assertEquals( $groupContact->group_id, 1, 'Check for group_id' );

        //cleanup
        $groupContact->deleteTestObjects('CRM_Contact_DAO_GroupContact');
    }
}
