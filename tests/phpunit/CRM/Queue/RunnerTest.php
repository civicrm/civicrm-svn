<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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

/**
 * Ensure that various queue implementations comply with the interface
 */
class CRM_Queue_RunnerTest extends CiviUnitTestCase {
  function get_info() {
    return array(
      'name' => 'SQL Queue',
      'description' => 'Test SQL-backed queue items',
      'group' => 'Queue',
    );
  }

  function setUp() {
    parent::setUp();
    require_once 'CRM/Queue/Service.php';
    $this->queueService = CRM_Queue_Service::singleton(TRUE);
    $this->queue = $this->queueService->create(array(
        'type' => 'Memory',
        'name' => 'test-queue',
      ));
    self::$_recordedValues = array();
  }

  function tearDown() {
    unset($this->queue);
    unset($this->queueService);
  }

  function testRunAllNormal() {
    // prepare a list of tasks with an error in the middle
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_recordValue'),
        array('a'),
        'Add "a"'
      ));
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_recordValue'),
        array('b'),
        'Add "b"'
      ));
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_recordValue'),
        array('c'),
        'Add "c"'
      ));

    // run the list of tasks
    $runner = new CRM_Queue_Runner(array(
        'queue' => $this->queue,
        'errorMode' => CRM_Queue_Runner::ERROR_ABORT,
      ));
    $this->assertEquals(self::$_recordedValues, array());
    $this->assertEquals(3, $this->queue->numberOfItems());
    $result = $runner->runAll();
    $this->assertEquals(TRUE, $result);
    $this->assertEquals(self::$_recordedValues, array('a', 'b', 'c'));
    $this->assertEquals(0, $this->queue->numberOfItems());
  }

  /**
   * Run a series of tasks; when one throws an
   * exception, ignore it and continue
   */
  function testRunAll_Continue_Exception() {
    // prepare a list of tasks with an error in the middle
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_recordValue'),
        array('a'),
        'Add "a"'
      ));
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_throwException'),
        array('b'),
        'Throw exception'
      ));
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_recordValue'),
        array('c'),
        'Add "c"'
      ));

    // run the list of tasks
    $runner = new CRM_Queue_Runner(array(
        'queue' => $this->queue,
        'errorMode' => CRM_Queue_Runner::ERROR_CONTINUE,
      ));
    $this->assertEquals(self::$_recordedValues, array());
    $this->assertEquals(3, $this->queue->numberOfItems());
    $result = $runner->runAll();
    // FIXME useless return
    $this->assertEquals(TRUE, $result);
    $this->assertEquals(self::$_recordedValues, array('a', 'c'));
    $this->assertEquals(0, $this->queue->numberOfItems());
  }

  /**
   * Run a series of tasks; when one throws an exception,
   * abort processing and return it to the queue.
   */
  function testRunAll_Abort_Exception() {
    // prepare a list of tasks with an error in the middle
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_recordValue'),
        array('a'),
        'Add "a"'
      ));
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_throwException'),
        array('b'),
        'Throw exception'
      ));
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_recordValue'),
        array('c'),
        'Add "c"'
      ));

    // run the list of tasks
    $runner = new CRM_Queue_Runner(array(
        'queue' => $this->queue,
        'errorMode' => CRM_Queue_Runner::ERROR_ABORT,
      ));
    $this->assertEquals(self::$_recordedValues, array());
    $this->assertEquals(3, $this->queue->numberOfItems());
    $result = $runner->runAll();
    $this->assertEquals(1, $result['is_error']);
    // nothing from 'c'
    $this->assertEquals(self::$_recordedValues, array('a'));
    // 'b' and 'c' remain
    $this->assertEquals(2, $this->queue->numberOfItems());
  }

  /**
   * Run a series of tasks; when one returns false,
   * abort processing and return it to the queue.
   */
  function testRunAll_Abort_False() {
    // prepare a list of tasks with an error in the middle
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_recordValue'),
        array('a'),
        'Add "a"'
      ));
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_returnFalse'),
        array(),
        'Return false'
      ));
    $this->queue->createItem(new CRM_Queue_Task(
        array('CRM_Queue_RunnerTest', '_recordValue'),
        array('c'),
        'Add "c"'
      ));

    // run the list of tasks
    $runner = new CRM_Queue_Runner(array(
        'queue' => $this->queue,
        'errorMode' => CRM_Queue_Runner::ERROR_ABORT,
      ));
    $this->assertEquals(self::$_recordedValues, array());
    $this->assertEquals(3, $this->queue->numberOfItems());
    $result = $runner->runAll();
    $this->assertEquals(1, $result['is_error']);
    // nothing from 'c'
    $this->assertEquals(self::$_recordedValues, array('a'));
    // 'b' and 'c' remain
    $this->assertEquals(2, $this->queue->numberOfItems());
  }

  /* **** Queue tasks **** */


  static $_recordedValues;

  static
  function _recordValue($taskCtx, $value) {
    self::$_recordedValues[] = $value;
    return TRUE;
  }

  static
  function _returnFalse($taskCtx) {
    return FALSE;
  }

  static
  function _throwException($taskCtx, $value) {
    throw new Exception("Manufactured error: $value");
  }
}

