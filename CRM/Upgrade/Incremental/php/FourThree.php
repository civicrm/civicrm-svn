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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */
class CRM_Upgrade_Incremental_php_FourThree {
  const BATCH_SIZE = 5000;

  function verifyPreDBstate(&$errors) {
    return TRUE;
  }

  /**
   * Compute any messages which should be displayed after upgrade
   *
   * @param $postUpgradeMessage string, alterable
   * @param $rev string, an intermediate version; note that setPostUpgradeMessage is called repeatedly with different $revs
   * @return void
   */
  function setPostUpgradeMessage(&$postUpgradeMessage, $rev) {
    if ($rev == '4.3.alpha1') {
      // check if CiviMember component is enabled
      $config = CRM_Core_Config::singleton();
      if (in_array('CiviMember', $config->enableComponents)) {
        $postUpgradeMessage .= '<br />' . ts('Membership renewal reminders must now be configured using the Schedule Reminders feature, which supports multiple renewal reminders  (Administer > Communications > Schedule Reminders). The Update Membership Statuses scheduled job will no longer send membershp renewal reminders. You can use your existing renewal reminder message template(s) with the Schedule Reminders feature.');
        $postUpgradeMessage .= '<br />' . ts('The Set Membership Reminder Dates scheduled job has been deleted since membership reminder dates stored in the membership table are no longer in use.');
      }
    }
  }

  function upgrade_4_3_alpha1($rev) {
    $upgrade = new CRM_Upgrade_Form();
    $upgrade->processSQL($rev);

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contact');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contact');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade timestamps (%1 => %2)', array(
                 1 => $startId,
                 2 => $endId,
               ));
      $this->addTask($title, 'convertTimestamps', $startId, $endId);
    }

    // CRM-10893
    // fix WP access control
    $config = CRM_Core_Config::singleton( );
    if ($config->userFramework == 'WordPress') {
      civicrm_wp_set_capabilities( );
    }

    // now rebuild all the triggers
    // CRM-9716
    // FIXME // CRM_Core_DAO::triggerRebuild();

    return TRUE;
  }

  /**
   * Read creation and modification times from civicrm_log; add
   * them to civicrm_contact.
   */
  function convertTimestamps(CRM_Queue_TaskContext $ctx, $startId, $endId) {
    $sql = "
      SELECT entity_id, min(modified_date) AS created, max(modified_date) AS modified
      FROM civicrm_log
      WHERE entity_table = 'civicrm_contact'
      AND entity_id BETWEEN %1 AND %2
      GROUP BY entity_id
    ";
    $params = array(
      1 => array($startId, 'Integer'),
      2 => array($endId, 'Integer'),
    );
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while ($dao->fetch()) {
      // FIXME civicrm_log.modified_date is DATETIME; civicrm_contact.modified_date is TIMESTAMP
      CRM_Core_DAO::executeQuery(
        'UPDATE civicrm_contact SET created_date = %1, modified_date = %2 WHERE id = %3',
        array(
          1 => array($dao->created, 'String'),
          2 => array($dao->modified, 'String'),
          3 => array($dao->entity_id, 'Integer'),
        )
      );
    }

    return TRUE;
  }

  /**
   * Syntatic sugar for adding a task which (a) is in this class and (b) has
   * a high priority.
   *
   * After passing the $funcName, you can also pass parameters that will go to
   * the function. Note that all params must be serializable.
   */
  protected function addTask($title, $funcName) {
    $queue = CRM_Queue_Service::singleton()->load(array(
      'type' => 'Sql',
      'name' => CRM_Upgrade_Form::QUEUE_NAME,
    ));

    $args = func_get_args();
    $title = array_shift($args);
    $funcName = array_shift($args);
    $task = new CRM_Queue_Task(
      array(get_class($this), $funcName),
      $args,
      $title
    );
    $queue->createItem($task, array('weight' => -1));
  }
}