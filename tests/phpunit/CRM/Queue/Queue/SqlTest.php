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

class CRM_Queue_Queue_SQLTest extends CiviUnitTestCase 
{
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
      'type' => 'Sql',
      'name' => 'test-queue',
    ));
  }

  function tearDown() {
    require_once 'CRM/Utils/Time.php';
    CRM_Utils_Time::resetTime();
      
    $tablesToTruncate = array('civicrm_queue_item');
    $this->quickCleanup( $tablesToTruncate );
  }
  
  /**
   * Create a few queue items; alternately enqueue and dequeue various 
   */
  function testBasicUsage() {
    $this->assertTrue($this->queue instanceof CRM_Queue_Queue);
    
    $this->queue->createItem(array(
      'test-key' => 'a',
    ));
    $this->queue->createItem(array(
      'test-key' => 'b',
    ));
    $this->queue->createItem(array(
      'test-key' => 'c',
    ));
    
    $this->assertEquals(3, $this->queue->numberOfItems());
    $item = $this->queue->claimItem();
    $this->assertEquals('a', $item->data['test-key']);
    $this->queue->deleteItem($item);
    
    $this->assertEquals(2, $this->queue->numberOfItems());
    $item = $this->queue->claimItem();
    $this->assertEquals('b', $item->data['test-key']);
    $this->queue->deleteItem($item);
    
    $this->queue->createItem(array(
      'test-key' => 'd',
    ));
    
    $this->assertEquals(2, $this->queue->numberOfItems());
    $item = $this->queue->claimItem();
    $this->assertEquals('c', $item->data['test-key']);
    $this->queue->deleteItem($item);
    
    $this->assertEquals(1, $this->queue->numberOfItems());
    $item = $this->queue->claimItem();
    $this->assertEquals('d', $item->data['test-key']);
    $this->queue->deleteItem($item);
    
    $this->assertEquals(0, $this->queue->numberOfItems());
  }
  
  /**
   * Claim an item from the queue and release it back for subsequent processing
   */
  function testManualRelease() {
    $this->assertTrue($this->queue instanceof CRM_Queue_Queue);
    
    $this->queue->createItem(array(
      'test-key' => 'a',
    ));
    
    $item = $this->queue->claimItem();
    $this->assertEquals('a', $item->data['test-key']);
    $this->assertEquals(1, $this->queue->numberOfItems());
    $this->queue->releaseItem($item);
    
    $this->assertEquals(1, $this->queue->numberOfItems());
    $item = $this->queue->claimItem();
    $this->assertEquals('a', $item->data['test-key']);
    $this->queue->deleteItem($item);
    
    $this->assertEquals(0, $this->queue->numberOfItems());
  }
  
  /**
   * Test that item leases expire at the expected time
   */
  function testTimeoutRelease() {
    $this->assertTrue($this->queue instanceof CRM_Queue_Queue);
    
    require_once 'CRM/Utils/Time.php';
    CRM_Utils_Time::setTime('2012-04-01 1:00:00');
    $this->queue->createItem(array(
      'test-key' => 'a',
    ));
    
    $item = $this->queue->claimItem();
    $this->assertEquals('a', $item->data['test-key']);
    $this->assertEquals(1, $this->queue->numberOfItems());
    // forget to release
    
    // haven't reach expiration yet
    CRM_Utils_Time::setTime('2012-04-01 1:59:00');
    $item2 = $this->queue->claimItem();
    $this->assertEquals(FALSE, $item2);

    // pass expiration mark
    CRM_Utils_Time::setTime('2012-04-01 2:00:01');    
    $item3 = $this->queue->claimItem();
    $this->assertEquals('a', $item3->data['test-key']);
    $this->assertEquals(1, $this->queue->numberOfItems());
    $this->queue->deleteItem($item3);
        
    $this->assertEquals(0, $this->queue->numberOfItems());
  }
  
  /**
   * Test that queue content is reset when reset=>TRUE
   */
  function testCreateResetTrue() {
    $this->queue->createItem(array(
      'test-key' => 'a',
    ));
    $this->queue->createItem(array(
      'test-key' => 'b',
    ));
    $this->assertEquals(2, $this->queue->numberOfItems());
    unset($this->queue);
    
    $queue2 = $this->queueService->create(array(
      'type' => 'Sql',
      'name' => 'test-queue',
      'reset' => TRUE,
    ));
    $this->assertEquals(0, $queue2->numberOfItems());
  }
  
  /**
   * Test that queue content is not reset when reset is omitted
   */
  function testCreateResetFalse() {
    $this->queue->createItem(array(
      'test-key' => 'a',
    ));
    $this->queue->createItem(array(
      'test-key' => 'b',
    ));
    $this->assertEquals(2, $this->queue->numberOfItems());
    unset($this->queue);
    
    $queue2 = $this->queueService->create(array(
      'type' => 'Sql',
      'name' => 'test-queue',
      //default// 'reset' => FALSE,
    ));
    $this->assertEquals(2, $queue2->numberOfItems());
    
    $item = $queue2->claimItem();
    $this->assertEquals('a', $item->data['test-key']);
    $queue2->releaseItem($item);
  }
  
  /**
   * Test that queue content is not reset when using load()
   */
  function testLoad() {
    $this->queue->createItem(array(
      'test-key' => 'a',
    ));
    $this->queue->createItem(array(
      'test-key' => 'b',
    ));
    $this->assertEquals(2, $this->queue->numberOfItems());
    unset($this->queue);
    
    $queue2 = $this->queueService->create(array(
      'type' => 'Sql',
      'name' => 'test-queue',
      //default// 'reset' => FALSE,
    ));
    $this->assertEquals(2, $queue2->numberOfItems());
    
    $item = $queue2->claimItem();
    $this->assertEquals('a', $item->data['test-key']);
    $queue2->releaseItem($item);
  }
}
