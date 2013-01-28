<?php
// $Id$

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

/**
 * File for the CiviCRM APIv3 job functions
 *
 * @package CiviCRM_APIv3
 * @subpackage API_Job
 *
 * @copyright CiviCRM LLC (c) 2004-2012
 * @version $Id: Job.php 30879 2010-11-22 15:45:55Z shot $
 *
 */
require_once 'CiviTest/CiviUnitTestCase.php';
class api_v3_JobTest extends CiviUnitTestCase {
  protected $_apiversion;

  public $_eNoticeCompliant = TRUE;
  public $DBResetRequired = FALSE;
  public $_entity = 'Job';
  public $_apiVersion = 3;

  function setUp() {
    parent::setUp();
  }

  function tearDown() {
  }

  public function testCallUpdateGreetingMissingParams() {
    $result = civicrm_api($this->_entity, 'update_greeting', array('gt' => 1, 'version' => $this->_apiVersion));
    $this->assertEquals('Mandatory key(s) missing from params array: ct', $result['error_message']);
  }

  public function testCallUpdateGreetingIncorrectParams() {
    $result = civicrm_api($this->_entity, 'update_greeting', array('gt' => 1, 'ct' => 'djkfhdskjfhds', 'version' => $this->_apiVersion));
    $this->assertEquals('ct `djkfhdskjfhds` is not valid.', $result['error_message']);
  }
/*
 * Note that this test is about tesing the metadata / calling of the function & doesn't test the success of the called function
 */
  public function testCallUpdateGreetingSuccess() {
    $result = civicrm_api($this->_entity, 'update_greeting', array('gt' => 'postal_greeting', 'ct' => 'Individual', 'version' => $this->_apiVersion));
    $this->assertAPISuccess($result);
   }

  public function testCallUpdateGreetingCommaSeparatedParamsSuccess() {
    $gt = 'postal_greeting,email_greeting,addressee';
    $ct = 'Individual,Household';
    $result = civicrm_api($this->_entity, 'update_greeting', array('gt' => $gt, 'ct' => $ct, 'version' => $this->_apiVersion));
    $this->assertAPISuccess($result);
  }
}

