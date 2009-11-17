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
require_once 'CRM/Pledge/BAO/Payment.php';
require_once 'CRM/Pledge/BAO/Pledge.php';
/**
 * Test class for CRM_Pledge_BAO_Payment BAO
 *
 *  @package   CiviCRM
 */
class CRM_Pledge_BAO_PaymentTest extends CiviUnitTestCase 
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
	*  Add a string to scheduled amount
	*/
    function testAddStringScheduledAmount () {

		$payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_Pledge');
		$params = 	array('pledge_id' => 1,
						'scheduled_amount' => 'test',
						'scheduled_date' => date('Ymd')
						);
		$paymentid = CRM_Pledge_BAO_Payment::add($params);
		
		$this->assertEquals(count($paymentid),0,"Payment scheduled amount cannot be a string");	
    }

	/**
	*  Add a string to scheduled date
	*/
	function testAddStringScheduledDate () {
		$payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_Pledge');
		$params = 	array('pledge_id' => 1,
						'scheduled_amount' => '100',
						'scheduled_date' => 'Test'
						);
		$paymentid = CRM_Pledge_BAO_Payment::add($params);
		
		$this->assertEquals(count($paymentid),0,"Payment scheduled amount cannot be a string");	
    }

	/**
	*  Add a string to reminder date
	*/
    function testAddStringReminderDate () {
		$payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_Pledge');
		$params = 	array('pledge_id' => 1,
						'scheduled_amount' => '100',
						'scheduled_date' => date('Ymd'),
						'reminder_date' => 'Test',
						);
		$paymentid = CRM_Pledge_BAO_Payment::add($params);
		
		$this->assertEquals(count($paymentid),0,"Payment scheduled amount cannot be a string");	
    }

	/**
	*  Add a string to reminder count
	*/
	function testAddStringReminderCount () {
		$payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_Pledge');
		$params = 	array('pledge_id' => 1,
						'scheduled_amount' => '100',
						'scheduled_date' => date('Ymd'),
						'reminder_date' => date('Ymd'),
						'reminder_count' => 'Test'
						);
		$paymentid = CRM_Pledge_BAO_Payment::add($params);
		
		$this->assertEquals(count($paymentid),0,"Payment scheduled amount cannot be a string");	
    }

    /**
	*  Retrieve a payment based on a pledge id = 0
	*/
	function testRetrieveZeroPledeID () {
		$payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_Payment');
		$params = 	array('pledge_id' => 0
						);
		$defaults = array();
		$paymentid = CRM_Pledge_BAO_Payment::retrieve($params,$defaults);
		
		$this->assertEquals(count($paymentid),0,"Pledge Id must be greater than 0");	
    }

    /**
	*  Retrieve a payment based on a Null pledge id 
	*/
    function testRetrieveStringPledgeID () {
		$payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_Payment');
		$params = 	array('pledge_id' => 'Test'
						);
		$defaults = array();
		$paymentid = CRM_Pledge_BAO_Payment::retrieve($params,$defaults);
		
		$this->assertEquals(count($paymentid),0,"Pledge Id cannot be a string");	
    }

    /**
	*  Test that payment retrieve wrks based on known pledge id
	*/
    function testRetrieveKnownPledgeID () {
		$payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_Payment');
		$params = 	array('pledge_id' => 1,
						);
		$defaults = array();
		$paymentid = CRM_Pledge_BAO_Payment::retrieve($params,$defaults);
		
		$this->assertEquals(count($paymentid),1,"Pledge was retrieved");	
    }

    /**
	*  Delete Payments payments for one pledge
	*/
	function testDeletePaymentsNormal () {
		$payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_Payment');
		$paymentid = CRM_Pledge_BAO_Payment::deletePayments($payment->pledge_id);
		$this->assertEquals(count($paymentid),1,"Deleted one payment");
	}
	
    /**
	*  Pass Null Id for a payment deletion for one pledge
	*/
	function testDeletePaymentsNullId () {
		$payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_Payment');
		$paymentid = CRM_Pledge_BAO_Payment::deletePayments(Null);
		$this->assertEquals(count($paymentid),1,"No payments deleted");
	}
	
    /**
	*  Pass Zero Id for a payment deletion for one pledge
	*/	
	function testDeletePaymentsZeroId () {
		$payment = CRM_Core_DAO::createTestObject('CRM_Pledge_BAO_Payment');
		$paymentid = CRM_Pledge_BAO_Payment::deletePayments( 0 );
		$this->assertEquals(count($paymentid),1,"No payments deleted");
	}
}
