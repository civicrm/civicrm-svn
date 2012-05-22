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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */
class CRM_Upgrade_Incremental_php_FourTwo {
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
    if ($rev == '4.2.alpha1') {
      $postUpgradeMessage .= '<br />' . ts('Default versions of the following System Workflow Message Templates have been modified to handle new functionality: <ul><li>Events - Registration Confirmation and Receipt (on-line)</li><li>Pledges - Acknowledgement</li><li>Pledges - Payment Reminder</li></ul>. If you have modified these templates, please review the new default versions and implement updates as needed to your copies (Administer > Communications > Message Templates > System Workflow Messages).');
    }
  }

  function upgrade_4_2_alpha1($rev) {
    // Some steps take a long time, so we break them up into separate
    // tasks and enqueue them separately.
    $this->addTask(ts('Upgrade DB to 4.2.alpha1: Price Sets'), 'task_4_2_alpha1_createPriceSets');
    $this->addTask(ts('Upgrade DB to 4.2.alpha1: SQL'), 'task_4_2_alpha1_runSql', $rev);
    $minContributionId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxContributionId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minContributionId; $startId <= $maxContributionId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = ts('Upgrade DB to 4.2.alpha1: Contributions (%1 => %2)', array(1 => $startId, 2 => $endId));
      $this->addTask($title, 'task_4_2_alpha1_convertContributions', $startId, $endId);
    }
    $this->addTask(ts('Upgrade DB to 4.2.alpha1: Event Profile'), 'task_4_2_alpha1_eventProfile');
  }

  /**
   * (Queue Task Callback)
   *
   * Upgrade code to create priceset for contribution pages and events
   */
  static function task_4_2_alpha1_createPriceSets(CRM_Queue_TaskContext $ctx) {
    //CRM-9714
    CRM_Core_DAO::executeQuery("ALTER TABLE `civicrm_price_set` DROP INDEX `UI_title`");
    $daoName = array('civicrm_contribution_page' => array('CRM_Contribute_BAO_ContributionPage', CRM_Core_Component::getComponentID('CiviContribute')),
      'civicrm_event' => array('CRM_Event_BAO_Event', CRM_Core_Component::getComponentID('CiviEvent')),
    );

    $query = " SELECT `id`, `name` FROM `civicrm_option_group` 
WHERE `name` LIKE '%.amount.%' ";
    $dao = CRM_Core_DAO::executeQuery($query);
    while ($dao->fetch()) {
      $addTo = explode('.', $dao->name);

      $setParams['title'] = CRM_Core_DAO::getFieldValue($daoName[$addTo[0]][0], $addTo[2], 'title');
      $pageTitle = strtolower(CRM_Utils_String::munge($setParams['title'], '_', 245));

      if (!CRM_Core_DAO::getFieldValue('CRM_Price_BAO_Set', $pageTitle, 'id', 'name', true)) {
        $setParams['name'] = $pageTitle;
      }
      //FIXME: "_id" does not appear to be setup in either static or instance context
      //elseif (!CRM_Core_DAO::getFieldValue('CRM_Price_BAO_Set', $pageTitle . '_' . $this->_id, 'id', 'name')) {
      //  $setParams['name'] = $pageTitle . '_' . $this->_id;
      //}
      else {
        $setParams['name'] = $pageTitle . '_' . rand(1, 99);
      }
      $setParams['extends'] = $daoName[$addTo[0]][1];
      $priceSet = CRM_Price_BAO_Set::create($setParams);
      CRM_Price_BAO_Set::addTo($addTo[0], $addTo[2], $priceSet->id, 1);

      $fieldParams['html_type'] = 'Radio';
      $fieldParams['is_required'] = 1;
      if ($addTo[0] == 'civicrm_event') {
        $fieldParams['name'] = $fieldParams['label'] = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event', $addTo[2], 'fee_label');
      }
      else {
        $fieldParams['name'] = strtolower(CRM_Utils_String::munge("Contribution Amount", '_', 245));
        $fieldParams['label'] = "Contribution Amount";
        $otherAmount = CRM_Core_DAO::getFieldValue('CRM_Contribute_DAO_ContributionPage', $addTo[2], 'is_allow_other_amount');
        if ($otherAmount) {
          $fieldParams['is_required'] = 0;
        }
      }
      $fieldParams['price_set_id'] = $priceSet->id;
      $optionValue = array();
      CRM_Core_OptionGroup::getAssoc($dao->name, $optionValue);
      $fieldParams['option_label'] = $optionValue['label'];
      $fieldParams['option_amount'] = $optionValue['value'];
      $fieldParams['option_weight'] = $optionValue['weight'];
      $priceField = CRM_Price_BAO_Field::create($fieldParams);
      if ($otherAmount) {
        $fieldParams['label'] = "Other Amount";
        $fieldParams['name'] = strtolower(CRM_Utils_String::munge($fieldParams['label'], '_', 245));
        $fieldParams['price_set_id'] = $priceSet->id;
        $fieldParams['html_type'] = 'Text';
        $fieldParams['is_display_amounts'] = $fieldParams['is_required'] = 0;
        $fieldParams['weight'] = $fieldParams['option_weight'][1] = 2;
        $fieldParams['option_label'][1] = "Other Amount";
        $fieldParams['option_amount'][1] = 1;
        $priceField = CRM_Price_BAO_Field::create($fieldParams);
      }
    }

    return TRUE;
  }

  /**
   * (Queue Task Callback)
   */
  static function task_4_2_alpha1_runSql(CRM_Queue_TaskContext $ctx, $rev) {
      $upgrade = new CRM_Upgrade_Form();
      $upgrade->processSQL($rev);

      // now rebuild all the triggers
      // CRM-9716
      CRM_Core_DAO::triggerRebuild();

      return TRUE;
  }

  /**
   * (Queue Task Callback)
   *
   * Find any contribution records and create corresponding line-item
   * records.
   *
   * @param $startId int, the first/lowest contribution ID to convert
   * @param $endId int, the last/highest contribution ID to convert
   */
  static function task_4_2_alpha1_convertContributions(CRM_Queue_TaskContext $ctx, $startId, $endId) {

      // create lineitems for contribution done for membership
      $sql = " SELECT cc.id, cmp.membership_id, cpse.price_set_id, cc.total_amount
FROM `civicrm_contribution` cc
LEFT JOIN civicrm_line_item cli ON cc.id=cli.entity_id and cli.entity_table = 'civicrm_contribution'
LEFT JOIN civicrm_membership_payment cmp ON cc.id = cmp.contribution_id
LEFT JOIN civicrm_participant_payment cpp ON cc.id = cpp.contribution_id
LEFT JOIN civicrm_price_set_entity cpse on cpse.entity_table = 'civicrm_contribution_page' and cpse.entity_id = cc.contribution_page_id
WHERE (cc.id BETWEEN %1 AND %2)
AND cli.entity_id IS NULL AND cc.contribution_page_id IS NOT NULL AND cpp.contribution_id IS NULL
GROUP BY cc.id ";
      $sqlParams = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $result = CRM_Core_DAO::executeQuery($sql, $sqlParams);

      while ($result->fetch()) {
        $sql = " SELECT cpf.id, cpfv.id as price_field_value_id, cpfv.label, cpfv.amount, cpfv.count FROM civicrm_price_field cpf LEFT JOIN civicrm_price_field_value cpfv ON cpf.id = cpfv.price_field_id WHERE cpf.price_set_id = %1 ";
        $lineParams = array(
          'entity_table' => 'civicrm_contribution',
          'entity_id' => $result->id,
        );
        if ($result->membership_id) {
          $sql .= " AND cpf.name = %2 AND cpfv.membership_type_id = %3 ";
          $params = array('1' => array($result->price_set_id, 'Integer'),
            '2' => array('membership_amount', 'String'),
            '3' => array(CRM_Core_DAO::getFieldValue('CRM_Member_DAO_Membership', $result->membership_id, 'membership_type_id'), 'Integer'),
          );
          $res = CRM_Core_DAO::executeQuery($sql, $params);
          if ($res->fetch()) {
            $lineParams += array(
              'price_field_id' => $res->id,
              'label' => $res->label,
              'qty' => 1,
              'unit_price' => $res->amount,
              'line_total' => $res->amount,
              'participant_count' => $res->count ? $res->count : 0,
              'price_field_value_id' => $res->price_field_value_id,
            );
          }
          else {
            $lineParams['price_field_id'] = CRM_Core_DAO::getFieldValue('CRM_Price_DAO_Field', $result->price_set_id, 'id', 'price_set_id');
            $lineParams['label'] = 'Membership Amount';
            $lineParams['qty'] = 1;
            $lineParams['unit_price'] = $lineParams['line_total'] = $result->total_amount;
            $lineParams['participant_count'] = 0;
          }
        }
        else {
          $sql .= "AND cpfv.amount = %2";
          $params = array('1' => array($result->price_set_id, 'Integer'),
            '2' => array($result->total_amount, 'String'),
          );
          $res = CRM_Core_DAO::executeQuery($sql, $params);
          if ($res->fetch()) {
            $lineParams += array(
              'price_field_id' => $res->id,
              'label' => $res->label,
              'qty' => 1,
              'unit_price' => $res->amount,
              'line_total' => $res->amount,
              'participant_count' => $res->count ? $res->count : 0,
              'price_field_value_id' => $res->price_field_value_id,
            );
          }
          else {
            $params = array(
              'price_set_id' => $result->price_set_id,
              'name' => 'other_amount',
            );
            $defaults = array();
            CRM_Price_BAO_Field::retrieve($params, $defaults);
            if (!empty($defaults)) {
              $lineParams['price_field_id'] = $defaults['id'];
              $lineParams['label'] = $defaults['label'];
              $lineParams['qty'] = $result->total_amount;
              $lineParams['unit_price'] = $lineParams['line_total'] = 1;
              $lineParams['price_field_value_id'] = CRM_Core_DAO::getFieldValue('CRM_Price_DAO_FieldValue', $defaults['id'], 'id', 'price_field_id');
            }
            else {
              $lineParams['price_field_id'] = CRM_Core_DAO::getFieldValue('CRM_Price_DAO_Field', $result->price_set_id, 'id', 'price_set_id');
              $lineParams['label'] = 'Contribution Amount';
              $lineParams['qty'] = 1;
              $lineParams['unit_price'] = $lineParams['line_total'] = $result->total_amount;
            }
            $lineParams['participant_count'] = 0;
          }
        }
        CRM_Price_BAO_LineItem::create($lineParams);
      }
      //create entry in lineitems for participants
      $sql = " SELECT cc.id, cc.total_amount, cpse.price_set_id, cp.fee_level, cpf.id as price_field_id,cpfv.id as price_field_value_id
FROM `civicrm_contribution` cc
LEFT JOIN civicrm_line_item cli ON cc.id=cli.entity_id and cli.entity_table = 'civicrm_contribution'
LEFT JOIN civicrm_membership_payment cmp ON cc.id = cmp.contribution_id
LEFT JOIN civicrm_participant_payment cpp ON cc.id = cpp.contribution_id
LEFT JOIN civicrm_participant cp ON cp.id = cpp.participant_id
LEFT JOIN civicrm_price_set_entity cpse ON cp.event_id = cpse.entity_id and cpse.entity_table = 'civicrm_event'
LEFT JOIN civicrm_price_field cpf ON cpf.price_set_id = cpse.price_set_id
LEFT JOIN civicrm_price_field_value cpfv ON cpfv.price_field_id = cpf.id AND cpfv.label = cp.fee_level
WHERE (cc.id BETWEEN %1 AND %2)
AND cli.entity_id IS NULL AND cc.contribution_page_id IS NULL AND cmp.contribution_id IS NULL AND cpp.contribution_id IS NOT NULL 
GROUP BY cc.id;";

      $sqlParams = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $result = CRM_Core_DAO::executeQuery($sql, $sqlParams);
      while ($result->fetch()) {
        $lineParams = array(
          'entity_table' => 'civicrm_participant',
          'entity_id' => $result->id,
          'price_field_id' => $result->price_field_id,
          'label' => $result->fee_level,
          'qty' => 1,
          'unit_price' => $result->total_amount,
          'line_total' => $result->total_amount,
          'participant_count' => 1,
          'price_field_value_id' => $result->price_field_value_id,
        );
        CRM_Price_BAO_LineItem::create($lineParams);
      }
      
      return TRUE;
  }

  /**
   * (Queue Task Callback)
   *
   * Create an event registration profile with a single email field CRM-9587
   */
  static function task_4_2_alpha1_eventProfile(CRM_Queue_TaskContext $ctx) {
      $profileTitle = ts('Your Registration Info');
      $sql = "INSERT INTO `civicrm_uf_group` (`is_active`, `group_type`, `title`, `help_pre`, `help_post`, `limit_listings_group_id`, `post_URL`, `add_to_group_id`, `add_captcha`, `is_map`, `is_edit_link`, `is_uf_link`, `is_update_dupe`, `cancel_URL`, `is_cms_user`, `notify`, `is_reserved`, `name`, `created_id`, `created_date`, `is_proximity_search`)
              VALUES (1, 'Individual, Contact', '{$profileTitle}', NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, 'event_registration', NULL, NULL, 0);";
      CRM_Core_DAO::executeQuery($sql);
      $eventRegistrationId = CRM_Core_DAO::singleValueQuery('SELECT LAST_INSERT_ID()');
      $sql = "INSERT INTO `civicrm_uf_field` (`uf_group_id`, `field_name`, `is_active`, `is_view`, `is_required`, `weight`, `help_post`, `help_pre`, `visibility`, `in_selector`, `is_searchable`, `location_type_id`, `phone_type_id`, `label`, `field_type`, `is_reserved`)
              VALUES ({$eventRegistrationId}, 'email', 1, 0, 1, 1, NULL, NULL, 'User and User Admin Only', 0, 0, NULL, NULL, 'Email Address', 'Contact', 0);";
      CRM_Core_DAO::executeQuery($sql);

      $sql = "SELECT * FROM `civicrm_event` WHERE is_online_registration = 1;";
      $events = CRM_Core_DAO::executeQuery($sql);
      while ($events->fetch()) {
        // Get next weights for the event registration profile
        $nextMainWeight = $nextAdditionalWeight = 1;
        $sql            = "SELECT weight FROM `civicrm_uf_join` WHERE entity_id = {$events->id} AND module = 'CiviEvent' ORDER BY weight DESC LIMIT 1";
        $weights        = CRM_Core_DAO::executeQuery($sql);
        $weights->fetch();
        if (isset($weights->weight)) {
          $nextMainWeight += $weights->weight;
        }
        $sql = "SELECT weight FROM `civicrm_uf_join` WHERE entity_id = {$events->id} AND module = 'CiviEvent_Additional' ORDER BY weight DESC LIMIT 1";
        $weights = CRM_Core_DAO::executeQuery($sql);
        $weights->fetch();
        if (isset($weights->weight)) {
          $nextAdditionalWeight += $weights->weight;
        }
        // Add an event registration profile to the event
        $sql = "INSERT INTO `civicrm_uf_join` (`is_active`, `module`, `entity_table`, `entity_id`, `weight`, `uf_group_id`)
                    VALUES (1, 'CiviEvent', 'civicrm_event', {$events->id}, {$nextMainWeight}, {$eventRegistrationId});";
        CRM_Core_DAO::executeQuery($sql);
        $sql = "INSERT INTO `civicrm_uf_join` (`is_active`, `module`, `entity_table`, `entity_id`, `weight`, `uf_group_id`)
                    VALUES (1, 'CiviEvent_Additional', 'civicrm_event', {$events->id}, {$nextAdditionalWeight}, {$eventRegistrationId});";
        CRM_Core_DAO::executeQuery($sql);
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

