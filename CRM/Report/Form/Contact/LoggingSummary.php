<?php
// $Id$

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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */
class CRM_Report_Form_Contact_LoggingSummary extends CRM_Logging_ReportSummary {
  function __construct() {
    $logTypes = array_keys($this->_logTables);
    $logTypes = array_flip($logTypes);
    foreach ( $logTypes as $table => &$type ) {
      $type = $this->getLogType($table);
    }

    $this->_columns = array(
      'log_civicrm_entity' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'alias' => 'entity_log',
        'fields' => array(
          'id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'log_type' => array(
            'required' => TRUE,
            'title' => ts('Log Type'),
          ),
          'log_user_id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'log_date' => array(
            'default' => TRUE,
            'required' => TRUE,
            'type' => CRM_Utils_Type::T_TIME,
            'title' => ts('When'),
          ),
          'altered_contact' => array(
            'default' => TRUE,
            'name' => 'display_name',
            'title' => ts('Altered Contact'),
            'alias' => 'modified_contact_civireport',
          ),
          'altered_contact_id' => array(
            'name' => 'id',
            'no_display' => TRUE,
            'required' => TRUE,
            'alias'    => 'modified_contact_civireport',
          ),
          'log_conn_id' => array(
            'no_display' => TRUE,
            'required' => TRUE,
          ),
          'log_action' => array(
            'default' => TRUE,
            'title' => ts('Action'),
          ),
          'is_deleted' => array(
            'no_display' => TRUE,
            'required' => TRUE,
            'alias' => 'modified_contact_civireport',
          ),
        ),
        'filters' => array(
          'log_date' => array(
            'title' => ts('When'),
            'operatorType' => CRM_Report_Form::OP_DATE,
            'type' => CRM_Utils_Type::T_DATE,
          ),
          'altered_contact' => array(
            'name' => 'display_name',
            'title' => ts('Altered Contact'),
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'altered_contact_id' => array(
            'name' => 'id',
            'type' => CRM_Utils_Type::T_INT,
            'alias' => 'modified_contact_civireport',
            'no_display' => TRUE,
          ),
          'log_type' => array(
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $logTypes,
            'title' => ts('Log Type'),
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'log_action' => array(
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => array('Insert' => ts('Insert'), 'Update' => ts('Update'), 'Delete' => ts('Delete')),
            'title' => ts('Action'),
            'type' => CRM_Utils_Type::T_STRING,
          ),
          'id' => array(
            'no_display' => TRUE,
            'type' => CRM_Utils_Type::T_INT,
          ),
        ),
      ),
      'altered_by_contact' => array(
        'dao'   => 'CRM_Contact_DAO_Contact',
        'alias' => 'altered_by_contact',
        'fields' => array(
          'display_name' => array(
            'default' => TRUE,
            'name' => 'display_name',
            'title' => ts('Altered By'),
          ),
        ),
        'filters' => array(
          'display_name' => array(
            'name' => 'display_name',
            'title' => ts('Altered By'),
            'type' => CRM_Utils_Type::T_STRING,
          ),
        ),
      ),
    );
    parent::__construct();
  }

  function alterDisplay(&$rows) {
    // cache for id → is_deleted mapping
    $isDeleted = array();
    $newRows   = array();

    foreach ($rows as $key => &$row) {
      if (!isset($isDeleted[$row['log_civicrm_entity_altered_contact_id']])) {
        $isDeleted[$row['log_civicrm_entity_altered_contact_id']] = 
          CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $row['log_civicrm_entity_altered_contact_id'], 'is_deleted') !== '0';
      }

      if (!$isDeleted[$row['log_civicrm_entity_altered_contact_id']]) {
        $row['log_civicrm_entity_altered_contact_link'] = 
          CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $row['log_civicrm_entity_altered_contact_id']);
        $row['log_civicrm_entity_altered_contact_hover'] = ts("Go to contact summary");
        $entity = $this->getEntityValue($row['log_civicrm_entity_id'], $row['log_civicrm_entity_log_type']);
        if ($entity)
          $row['log_civicrm_entity_altered_contact'] = $row['log_civicrm_entity_altered_contact'] . " [{$entity}]";
      }
      $row['altered_by_contact_display_name_link'] = CRM_Utils_System::url('civicrm/contact/view', 'reset=1&cid=' . $row['log_civicrm_entity_log_user_id']);
      $row['altered_by_contact_display_name_hover'] = ts("Go to contact summary");

      if ($row['log_civicrm_entity_is_deleted'] and $row['log_civicrm_entity_log_action'] == 'Update') {
        $row['log_civicrm_entity_log_action'] = ts('Delete (to trash)');
      }

      if ($newAction = $this->getEntityAction($row['log_civicrm_entity_id'], $row['log_civicrm_entity_log_conn_id'], $row['log_civicrm_entity_log_type']))
        $row['log_civicrm_entity_log_action'] = $newAction;

      $row['log_civicrm_entity_log_type'] = $this->getLogType($row['log_civicrm_entity_log_type']);

      if ($row['log_civicrm_entity_log_action'] == 'Update') {
        $q = "reset=1&log_conn_id={$row['log_civicrm_entity_log_conn_id']}&log_date={$row['log_civicrm_entity_log_date']}";
        if ($this->cid) {
          $q .= '&cid=' . $this->cid;
        }

        $url = CRM_Report_Utils_Report::getNextUrl('logging/contact/detail', $q, FALSE, TRUE);
        $row['log_civicrm_entity_log_action_link'] = $url;
        $row['log_civicrm_entity_log_action_hover'] = ts("View details for this update");
        $row['log_civicrm_entity_log_action'] = '<div class="icon details-icon"></div> ' . ts('Update');
      }

      unset($row['log_civicrm_entity_log_user_id']);
      unset($row['log_civicrm_entity_log_conn_id']);

      $date = CRM_Utils_Date::isoToMysql($row['log_civicrm_entity_log_date']);
      $newRows[$date] = $row;
    }

    krsort($newRows);
    $rows = $newRows;
  }

  function from( $logTable = null ) {
    static $entity = null;
    if ( $logTable ) {
      $entity = $logTable;
    }

    $detail = $this->_logTables[$entity];
    $clause = CRM_Utils_Array::value('entity_table', $detail);
    $clause = $clause ? "AND entity_log_civireport.entity_table = 'civicrm_contact'" : null;

    $this->_from = "
FROM `{$this->loggingDB}`.$entity entity_log_civireport
INNER JOIN civicrm_temp_civireport_logsummary temp 
        ON (entity_log_civireport.{$detail['fk']} = temp.contact_id)
INNER JOIN civicrm_contact modified_contact_civireport 
        ON (entity_log_civireport.{$detail['fk']} = modified_contact_civireport.id {$clause})
INNER JOIN civicrm_contact altered_by_contact_civireport 
        ON (entity_log_civireport.log_user_id = altered_by_contact_civireport.id)";
  }
}

