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

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */
class CRM_Logging_ReportSummary extends CRM_Report_Form {
  protected $cid;

  protected $_logTables = 
    array( 'log_civicrm_note' => 
           array( 'fk'     => 'entity_id',
                  'entity_table' => true ),
           'log_civicrm_email' => 
           array( 'fk'     => 'contact_id' ),
           'log_civicrm_phone' => 
           array( 'fk'     => 'contact_id' ),
           'log_civicrm_group_contact' => 
           array( 'fk'     => 'contact_id' ),
           );

  protected $loggingDB; function __construct() {
    // don’t display the ‘Add these Contacts to Group’ button
    $this->_add2groupSupported = FALSE;

    $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
    $this->loggingDB = $dsn['database'];

    // used for redirect back to contact summary
    $this->cid = CRM_Utils_Request::retrieve('cid', 'Integer', CRM_Core_DAO::$_nullObject);

    parent::__construct();
  }

  function groupBy() {
    $this->_groupBy = 'GROUP BY log_conn_id, log_user_id, EXTRACT(DAY_MICROSECOND FROM log_date)';
  }

  function orderBy() {
    $this->_orderBy = 'ORDER BY log_date DESC';
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

    list($offset, $rowCount) = $this->limit();
    $this->_where .= " AND (log_action != 'Initialization') AND temp.id BETWEEN $offset AND $rowCount";

    unset($this->_limit);
  }

  function postProcess() {
    $this->beginPostProcess();

    // temp table to hold all altered contact-ids
    $sql = "
CREATE TEMPORARY TABLE 
       civicrm_temp_civireport_logsummary ( id int PRIMARY KEY AUTO_INCREMENT, 
                                            contact_id int, UNIQUE UI_id (contact_id) ) ENGINE=HEAP";
    CRM_Core_DAO::executeQuery($sql);

    foreach ( $this->_logTables as $entity => $detail ) {
      $clause = CRM_Utils_Array::value('entity_table', $detail);
      $clause = $clause ? "entity_table = 'civicrm_contact' AND" : null;
      $sql    = "
INSERT IGNORE INTO civicrm_temp_civireport_logsummary ( contact_id ) 
SELECT DISTINCT {$detail['fk']} FROM {$entity}
WHERE {$clause} log_action != 'Initialization'";
      CRM_Core_DAO::executeQuery($sql);
    }

    foreach ( $this->_logTables as $entity => $detail ) {
      $this->from( $entity );
      $sql = $this->buildQuery(false);
      $sql = str_replace("entity_log_civireport.log_type as", "'Note' as", $sql);
      $this->buildRows($sql, $rows);
    }

    // format result set.
    $this->formatDisplay($rows);
    
    // assign variables to templates
    $this->doTemplateAssignment($rows);
    
    // do print / pdf / instance stuff if needed
    $this->endPostProcess($rows);
  }
}
