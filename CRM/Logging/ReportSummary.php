<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */
class CRM_Logging_ReportSummary extends CRM_Report_Form {
  protected $cid;

  protected $_logTables =
    array(
      'log_civicrm_contact' =>
      array( 'fk'  => 'id',
      ),
      'log_civicrm_email' =>
      array( 'fk'  => 'contact_id',
        'log_type' => 'Contact',
      ),
      'log_civicrm_phone' =>
      array( 'fk'  => 'contact_id',
        'log_type' => 'Contact',
      ),
      'log_civicrm_address' =>
      array( 'fk'  => 'contact_id',
        'log_type' => 'Contact',
      ),
      'log_civicrm_note' =>
      array( 'fk'  => 'entity_id',
        'entity_table' => true,
        'dao_log_table' => 'log_civicrm_note',
        'dao_column'  => 'subject',
      ),
      'log_civicrm_note_comment' =>
      array( 'fk'  => 'entity_id',
        'table_name'  => 'log_civicrm_note',
        'entity_table' => true,
        'dao_log_table' => 'log_civicrm_note',
        'dao_column'  => 'subject',
      ),
      'log_civicrm_group_contact' =>
      array( 'fk'  => 'contact_id',
        'dao_log_table' => 'log_civicrm_group',
        'dao_column'    => 'title',
        'entity_column' => 'group_id',
        'action_column' => 'status',
        'log_type'      => 'Group',
      ),
      'log_civicrm_entity_tag' =>
      array( 'fk'  => 'entity_id',
        'dao_log_table' => 'log_civicrm_tag',
        'dao_column'    => 'name',
        'entity_column' => 'tag_id',
        'entity_table'  => true
      ),
      'log_civicrm_relationship' =>
      array( 'fk'  => 'contact_id_a',
        'entity_column' => 'relationship_type_id',
        'dao_log_table' => 'log_civicrm_relationship_type',
        'dao_column' => 'label_a_b',
      ),
    );

  protected $loggingDB;

  function __construct() {
    // don’t display the ‘Add these Contacts to Group’ button
    $this->_add2groupSupported = FALSE;

    $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
    $this->loggingDB = $dsn['database'];

    // used for redirect back to contact summary
    $this->cid = CRM_Utils_Request::retrieve('cid', 'Integer', CRM_Core_DAO::$_nullObject);

    $logging = new CRM_Logging_Schema;
    $customTables = $logging->customDataLogTables();
    foreach ($customTables as $table) {
      $this->_logTables[$table] = array('fk' => 'entity_id', 'log_type' => 'Contact');
    }

    parent::__construct();
  }

  function groupBy() {
    $this->_groupBy = 'GROUP BY entity_log_civireport.log_conn_id, entity_log_civireport.log_user_id, EXTRACT(DAY_MICROSECOND FROM entity_log_civireport.log_date), entity_log_civireport.id';
  }

  function select() {
    $select = array();
    $this->_columnHeaders = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) or CRM_Utils_Array::value($fieldName, $this->_params['fields'])) {
            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['no_display'] = CRM_Utils_Array::value('no_display', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title', $field);
          }
        }
      }
    }
    $this->_select = 'SELECT ' . implode(', ', $select) . ' ';
  }

  function where() {
    parent::where();
    $this->_where .= " AND (entity_log_civireport.log_action != 'Initialization')";
  }

  function postProcess() {
    $this->beginPostProcess();
    $rows = array();
    // temp table to hold all altered contact-ids
    $sql = "
CREATE TEMPORARY TABLE
       civicrm_temp_civireport_logsummary ( id int(10), 
                                            log_action varchar(64), 
                                            log_type varchar(64), 
                                            log_user_id int(10), 
                                            log_date timestamp, 
                                            altered_contact varchar(128), 
                                            altered_contact_id int(10), 
                                            log_conn_id int(11), 
                                            is_deleted tinyint(4), 
                                            display_name varchar(128) ) ENGINE=HEAP";
    CRM_Core_DAO::executeQuery($sql);

    $logDateClause = $this->dateClause('log_date',
      CRM_Utils_Array::value("log_date_relative",  $this->_params),
      CRM_Utils_Array::value("log_date_from",      $this->_params),
      CRM_Utils_Array::value("log_date_to",        $this->_params),
      CRM_Utils_Type::T_DATE,
      CRM_Utils_Array::value("log_date_from_time", $this->_params),
      CRM_Utils_Array::value("log_date_to_time",   $this->_params));
    $logDateClause = $logDateClause ? "AND {$logDateClause}" : null;

    $logTypes = CRM_Utils_Array::value('log_type_value', $this->_params);
    unset($this->_params['log_type_value']);
    if ( empty($logTypes) ) {
      foreach ( array_keys($this->_logTables) as  $table ) {
        $type = $this->getLogType($table);
        $logTypes[$type] = $type;
      }
    }

    foreach ( $this->_logTables as $entity => $detail ) {
      if ((in_array($this->getLogType($entity), $logTypes) &&
        CRM_Utils_Array::value('log_type_op', $this->_params) == 'in') ||
        (!in_array($this->getLogType($entity), $logTypes) &&
          CRM_Utils_Array::value('log_type_op', $this->_params) == 'notin')) {
        $this->from( $entity );
        $sql = $this->buildQuery(false);
        $sql = str_replace("entity_log_civireport.log_type as", "'{$entity}' as", $sql);
        $sql = "INSERT IGNORE INTO civicrm_temp_civireport_logsummary {$sql}";
        CRM_Core_DAO::executeQuery($sql);
      }
    }

    $this->limit();
    $sql = "{$this->_select}
FROM civicrm_temp_civireport_logsummary entity_log_civireport
ORDER BY entity_log_civireport.log_date DESC {$this->_limit}";
    $sql = str_replace('modified_contact_civireport.display_name', 'entity_log_civireport.altered_contact',   $sql);
    $sql = str_replace('modified_contact_civireport.id',           'entity_log_civireport.altered_contact_id', $sql);
    $sql = str_replace(array('modified_contact_civireport.', 'altered_by_contact_civireport.'), 'entity_log_civireport.', $sql);
    $this->buildRows($sql, $rows);

    // format result set.
    $this->formatDisplay($rows);

    // assign variables to templates
    $this->doTemplateAssignment($rows);

    // do print / pdf / instance stuff if needed
    $this->endPostProcess($rows);
  }

  function getLogType( $entity ) {
    if (CRM_Utils_Array::value('log_type', $this->_logTables[$entity])) {
      return $this->_logTables[$entity]['log_type'];
    }
    $logType = ucfirst(substr($entity, strrpos($entity, '_') + 1));
    return $logType;
  }

  function getEntityValue( $id, $entity, $logDate ) {
    if (CRM_Utils_Array::value('dao_log_table', $this->_logTables[$entity])) {
      if (CRM_Utils_Array::value('entity_column', $this->_logTables[$entity])) {
        $sql = "select {$this->_logTables[$entity]['entity_column']} from `{$this->loggingDB}`.{$entity} where id = %1";
        $entityID = CRM_Core_DAO::singleValueQuery($sql, array(1 => array($id, 'Integer')));
      } else {
        $entityID = $id;
      }

      if ($entityID && $logDate) {
        $sql = "
SELECT {$this->_logTables[$entity]['dao_column']} 
FROM  `{$this->loggingDB}`.{$this->_logTables[$entity]['dao_log_table']} 
WHERE  log_date <= %1 AND id = %2 ORDER BY log_date DESC LIMIT 1";
        return CRM_Core_DAO::singleValueQuery($sql, array(1 => array(CRM_Utils_Date::isoToMysql($logDate), 'Timestamp'), 2 => array ($entityID, 'Integer')));
      }
    }
    return null;
  }

  function getEntityAction( $id, $connId, $entity, $oldAction ) {
    if (CRM_Utils_Array::value('action_column', $this->_logTables[$entity])) {
      $sql = "select {$this->_logTables[$entity]['action_column']} from `{$this->loggingDB}`.{$entity} where id = %1 AND log_conn_id = %2";
      $newAction = CRM_Core_DAO::singleValueQuery($sql, array(1 => array($id, 'Integer'), 2 => array($connId, 'Integer')));

      switch ($entity) {
      case 'log_civicrm_group_contact':
        if ($oldAction !== 'Update')
          $newAction = $oldAction;
        if ($oldAction == 'Insert')
          $newAction = 'Added';
        break;
      }
      return $newAction;
    }
    return null;
  }
}
