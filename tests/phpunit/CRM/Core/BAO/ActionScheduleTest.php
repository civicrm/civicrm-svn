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
class CRM_Core_BAO_ActionScheduleTest extends CiviUnitTestCase {
  function get_info() {
    return array(
      'name' => 'Action-Schedule BAO',
      'description' => 'Test sending of scheduled notifications.',
      'group' => 'CiviCRM BAO Tests',
    );
  }

  function setUp() {
    parent::setUp();

    global $civicrm_setting;
    $civicrm_setting[CRM_Core_BAO_Setting::MAILING_PREFERENCES_NAME]['mailing_backend'] = array( 'outBound_option' => '4');
    $this->mailer = CRM_Core_Config::getMailer();
    //$this->mailer->sentMessages = array();

    $this->fixtures['rolling_membership'] = array( // createTestObject
      'membership_type_id' => array(
        'period_type' => 'rolling',
        'duration_unit' => 'month',
        'duration_interval' => '3',
        'is_active' => 1,
      ),
      'join_date' => '20120315',
      'start_date' => '20120315',
      'end_date' => '20120615',
    );
    $this->fixtures['phonecall'] = array( // createTestObject
      'status_id' => 1,
      'activity_type_id' => 2,
      'activity_date_time' => '20120615100000',
      'is_current_revision' => 1,
      'is_deleted' => 0,
    );
    $this->fixtures['contact'] = array( // API
      'version' => 3,
      'contact_type' => 'Individual',
      'first_name' => 'Woot',
      'last_name' => 'Tang is for the children',
      'email' => 'test@example.com',
    );
    $this->fixtures['sched_membership_2week'] = array( // create()
      'name' => 'sched_membership_2week',
      'title' => 'sched_membership_2week',
      'absolute_date' => '',
      'body_html' => 'Hello',
      'body_text' => 'Hello',
      'end_action' => '',
      'end_date' => '',
      'end_frequency_interval' => '',
      'end_frequency_unit' => '',
      'entity_status' => '',
      'entity_value' => '',
      'group_id' => '',
      'is_active' => 1,
      'is_repeat' => '0',
      'mapping_id' => 4,
      'msg_template_id' => '',
      'recipient' => '',
      'recipient_listing' => '',
      'recipient_manual' => '',
      'record_activity' => 1,
      'repetition_frequency_interval' => '',
      'repetition_frequency_unit' => '',
      'start_action_condition' => 'before',
      'start_action_date' => 'membership_end_date',
      'start_action_offset' => '2',
      'start_action_unit' => 'week',
      'subject' => 'Hello',

    );
    $this->fixtures['sched_activity_1day'] = array( // create()
      'name' => 'One_Day_Phone_Call_Notice',
      'title' => 'One Day Phone Call Notice',
      'absolute_date' => NULL,
      'body_html' => '<p>1-Day (non-repeating)</p>',
      'body_text' => '1-Day (non-repeating)',
      'end_action' => NULL,
      'end_date' => NULL,
      'end_frequency_interval' => NULL,
      'end_frequency_unit' => NULL,
      'entity_status' => '1',
      'entity_value' => '2',
      'group_id' => NULL,
      'is_active' => '1',
      'is_repeat' => '0',
      'mapping_id' => '1',
      'msg_template_id' => NULL,
      'recipient' => '2',
      'recipient_listing' => NULL,
      'recipient_manual' => NULL,
      'record_activity' => NULL,
      'repetition_frequency_interval' => NULL,
      'repetition_frequency_unit' => NULL,
      'start_action_condition' => 'before',
      'start_action_date' => 'activity_date_time',
      'start_action_offset' => '1',
      'start_action_unit' => 'day',
      'subject' => '1-Day (non-repeating)',
    );
    $this->fixtures['sched_activity_1day_r'] = array(
      'name' => 'One_Day_Phone_Call_Notice_R',
      'title' => 'One Day Phone Call Notice R',
      'absolute_date' => NULL,
      'body_html' => '<p>1-Day (repeating)</p>',
      'body_text' => '1-Day (repeating)',
      'end_action' => 'after',
      'end_date' => 'activity_date_time',
      'end_frequency_interval' => '2',
      'end_frequency_unit' => 'day',
      'entity_status' => '1',
      'entity_value' => '2',
      'group_id' => NULL,
      'is_active' => '1',
      'is_repeat' => '1',
      'mapping_id' => '1',
      'msg_template_id' => NULL,
      'recipient' => '2',
      'recipient_listing' => NULL,
      'recipient_manual' => NULL,
      'record_activity' => NULL,
      'repetition_frequency_interval' => '6',
      'repetition_frequency_unit' => 'hour',
      'start_action_condition' => 'before',
      'start_action_date' => 'activity_date_time',
      'start_action_offset' => '1',
      'start_action_unit' => 'day',
      'subject' => '1-Day (repeating)',

    );
    $this->_setUp();
    $this->quickCleanup(array('civicrm_action_log', 'civicrm_action_schedule'));
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   *
   * @access protected
   */
  function tearDown() {
    parent::tearDown();

    global $civicrm_setting;
    unset($civicrm_setting[CRM_Core_BAO_Setting::MAILING_PREFERENCES_NAME]['mailing_backend']);
    unset($this->mailer);
    
    $this->_tearDown();
  }

  function testNonRepeatableSchedule() {
    $actionScheduleDao = CRM_Core_BAO_ActionSchedule::add($this->fixtures['sched_activity_1day'], $ids);
    $this->assertTrue(is_numeric($actionScheduleDao->id));
    
    $activity = $this->createTestObject('CRM_Activity_DAO_Activity', $this->fixtures['phonecall']);
    $this->assertTrue(is_numeric($activity->id));
    $contact = civicrm_api('contact', 'create', $this->fixtures['contact']);
    $activity->source_contact_id = $contact['id'];
    $activity->save();

    // Before the 24-hour mark, no email
    CRM_Utils_Time::setTime('2012-06-14 04:00:00');
    $result = civicrm_api('job', 'send_reminder', array(
      'version' => 3,
    ));
    $this->assertAPISuccess($result);
    $this->assertRecipients(array(), $this->mailer);
    
    // After the 24-hour mark, an email
    CRM_Utils_Time::setTime('2012-06-14 15:00:00');
    $result = civicrm_api('job', 'send_reminder', array(
      'version' => 3,
    ));
    $this->assertAPISuccess($result);
    $this->assertRecipients(array(array('test@example.com')), $this->mailer);
    $this->mailer->sentMessages = array();
    
    // Run cron again; message already sent
    CRM_Utils_Time::setTime('2012-06-14 16:00:00');
    $result = civicrm_api('job', 'send_reminder', array(
      'version' => 3,
    ));
    $this->assertAPISuccess($result);
    $this->assertRecipients(array(), $this->mailer);
    $this->mailer->sentMessages = array();
  }
  
  function testRepeatableSchedule() {
    $actionScheduleDao = CRM_Core_BAO_ActionSchedule::add($this->fixtures['sched_activity_1day_r'], $ids);
    $this->assertTrue(is_numeric($actionScheduleDao->id));
    
    $activity = $this->createTestObject('CRM_Activity_DAO_Activity', $this->fixtures['phonecall']);
    $this->assertTrue(is_numeric($activity->id));
    $contact = civicrm_api('contact', 'create', $this->fixtures['contact']);
    $activity->source_contact_id = $contact['id'];
    $activity->save();

    /// Before the 24-hour mark, no email
    CRM_Utils_Time::setTime('2012-06-14 04:00:00');
    $result = civicrm_api('job', 'send_reminder', array(
      'version' => 3,
    ));
    $this->assertAPISuccess($result);
    $this->assertRecipients(array(), $this->mailer);
    $this->mailer->sentMessages = array();

    /// After the 24-hour mark, an email
    CRM_Utils_Time::setTime('2012-06-14 15:00:00');
    $result = civicrm_api('job', 'send_reminder', array(
      'version' => 3,
    ));
    $this->assertAPISuccess($result);
    $this->assertRecipients(array(array('test@example.com')), $this->mailer);
    $this->mailer->sentMessages = array();
    
    /// Run cron 4 hours later; first message already sent
    CRM_Utils_Time::setTime('2012-06-14 20:00:00');
    $result = civicrm_api('job', 'send_reminder', array(
      'version' => 3,
    ));
    $this->assertAPISuccess($result);
    $this->assertRecipients(array(), $this->mailer);
    $this->mailer->sentMessages = array();

    // Run cron 6 hours later; send second message
    CRM_Utils_Time::setTime('2012-06-15 21:00:01');
    $result = civicrm_api('job', 'send_reminder', array(
      'version' => 3,
    ));
    $this->assertAPISuccess($result);
    $this->assertRecipients(array(array('test@example.com')), $this->mailer);
    $this->mailer->sentMessages = array();
  }
  
  /**
   *
   *
  function testMembershipEndDate() {
    $actionScheduleDao = CRM_Core_BAO_ActionSchedule::add($this->fixtures['sched_membership_2week'], $ids);
    $this->assertTrue(is_numeric($actionScheduleDao->id));
    $membership = $this->createTestObject('CRM_Member_DAO_Membership', $this->fixtures['rolling_membership']);
  }
  */

  function assertRecipients($expectedRecipients, $mailer) {      
    $recipients = array();
    foreach($mailer->sentMessages as $message) {
      $recipients[] = $message['recipients'];
    }
    sort($recipients);
    sort($expectedRecipients);
    $this->assertEquals(
      $expectedRecipients,
      $recipients,
      "Incorrect recipients: " . print_r(array('expected'=>$expectedRecipients, 'actual'=>$recipients), TRUE)
    );
  }
  
  ////////////////////////////////
  ////////////////////////////////
  ////////////////////////////////
  ////////////////////////////////
  
  
  /**
   * @var array(DAO_Name => array(int)) List of items to garbage-collect during tearDown
   */
  private $_testObjects;

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   *
   * @access protected
   */
  protected function _setUp() {
    $this->_testObjects = array();
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   *
   * @access protected
   */
  protected function _tearDown() {
    parent::tearDown();
    $this->deleteTestObjects();
  }

  /**
   * This is a wrapper for CRM_Core_DAO::createTestObject which tracks
   * created entities and provides for brainless clenaup.
   *
   * @see CRM_Core_DAO::createTestObject
   */
  function createTestObject($daoName, $params = array(
    ), $numObjects = 1, $createOnly = FALSE) {
    $objects = CRM_Core_DAO::createTestObject($daoName, $params, $numObjects, $createOnly);
    if (is_array($objects)) {
      $this->registerTestObjects($objects);
    } else {
      $this->registerTestObjects(array($objects));
    }
    return $objects;
  }

  /**
   * @param $objects array(object) DAO or BAO objects
   */
  function registerTestObjects($objects) {
    //if (is_object($objects)) {
    //  $objects = array($objects);
    //}
    foreach ($objects as $object) {
      $daoName = preg_replace('/_BAO_/', '_DAO_', get_class($object));
      $this->_testObjects[$daoName][] = $object->id;
    }
  }

  function deleteTestObjects() {
    // Note: You might argue that the FK relations between test
    // objects could make this problematic; however, it should
    // behave intuitively as long as we mentally split our
    // test-objects between the "manual/primary records"
    // and the "automatic/secondary records"
    foreach ($this->_testObjects as $daoName => $daoIds) {
      foreach ($daoIds as $daoId) {
        CRM_Core_DAO::deleteTestObjects($daoName, array('id' => $daoId));
      }
    }
    $this->_testObjects = array();
  }

}
