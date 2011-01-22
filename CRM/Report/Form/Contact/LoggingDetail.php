<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Logging/Differ.php';
require_once 'CRM/Logging/Schema.php';
require_once 'CRM/Report/Form.php';

class CRM_Report_Form_Contact_LoggingDetail extends CRM_Report_Form
{
    private $cid;
    private $db;
    private $log_conn_id;
    private $log_date;
    private $raw;
    private $tables = array();

    function __construct()
    {
        $this->_add2groupSupported = false; // don’t display the ‘Add these Contacts to Group’ button

        $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
        $this->db = $dsn['database'];

        $this->log_conn_id = CRM_Utils_Request::retrieve('log_conn_id', 'Integer', CRM_Core_DAO::$_nullObject);
        $this->log_date    = CRM_Utils_Request::retrieve('log_date',    'String',  CRM_Core_DAO::$_nullObject);
        $this->cid         = CRM_Utils_Request::retrieve('cid',         'Integer', CRM_Core_DAO::$_nullObject);
        $this->raw         = CRM_Utils_Request::retrieve('raw',         'Boolean', CRM_Core_DAO::$_nullObject);

        // set the tables concerning this report: contact, custom data and contact-related
        $logging = new CRM_Logging_Schema;
        $this->tables[] = 'log_civicrm_contact';
        $this->tables   = array_merge($this->tables, $logging->customDataLogTables());
        $this->tables[] = 'log_civicrm_email';
        $this->tables[] = 'log_civicrm_phone';
        $this->tables[] = 'log_civicrm_im';
        $this->tables[] = 'log_civicrm_openid';
        $this->tables[] = 'log_civicrm_website';
        $this->tables[] = 'log_civicrm_address';

        if (CRM_Utils_Request::retrieve('revert', 'Boolean', CRM_Core_DAO::$_nullObject)) {
            require_once 'CRM/Logging/Reverter.php';
            $reverter = new CRM_Logging_Reverter($this->log_conn_id, $this->log_date);
            $reverter->revert($this->tables);
            CRM_Core_Session::setStatus(ts('The changes have been reverted.'));
            CRM_Utils_System::redirect(CRM_Report_Utils_Report::getNextUrl('logging/contact/summary', 'reset=1', false, true));
        }

        // make sure the report works even without the params
        if (!$this->log_conn_id or !$this->log_date) {
            $dao = new CRM_Core_DAO;
            $dao->query("SELECT log_conn_id, log_date FROM `{$this->db}`.log_civicrm_contact WHERE log_action = 'Update' ORDER BY log_date DESC LIMIT 1");
            $dao->fetch();
            $this->log_conn_id = $dao->log_conn_id;
            $this->log_date    = $dao->log_date;
        }

        $this->_columnHeaders = array(
            'field' => array('title' => ts('Field')),
            'from'  => array('title' => ts('Changed From')),
            'to'    => array('title' => ts('Changed To')),
        );

        parent::__construct();
    }

    function buildQuery()
    {
    }

    function buildQuickForm()
    {
        parent::buildQuickForm();

        $params = array(
            1 => array($this->log_conn_id, 'Integer'),
            2 => array($this->log_date,    'String'),
        );

        // let the template know who updated whom when
        $sql = "
            SELECT who.id who_id, who.display_name who_name, whom.id whom_id, whom.display_name whom_name, l.is_deleted
            FROM `{$this->db}`.log_civicrm_contact l
            JOIN civicrm_contact who ON (l.log_user_id = who.id)
            JOIN civicrm_contact whom ON (l.id = whom.id)
            WHERE log_action = 'Update' AND log_conn_id = %1 AND log_date = %2 ORDER BY log_date DESC LIMIT 1
        ";
        $dao =& CRM_Core_DAO::executeQuery($sql, $params);
        $dao->fetch();
        $this->assign('who_url',   CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$dao->who_id}"));
        $this->assign('whom_url',  CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$dao->whom_id}"));
        $this->assign('who_name',  $dao->who_name);
        $this->assign('whom_name', $dao->whom_name);
        $this->assign('log_date',  $this->log_date);

        if ($this->cid) {
            // link back to contact summary
            $this->assign('backURL', CRM_Utils_System::url('civicrm/contact/view', "reset=1&selectedChild=log&cid={$this->cid}", false, null, false));
        } else {
            // link back to summary report
            require_once 'CRM/Report/Utils/Report.php';
            $this->assign('backURL', CRM_Report_Utils_Report::getNextUrl('logging/contact/summary', 'reset=1', false, true));
        }

        $q = "reset=1&log_conn_id={$this->log_conn_id}&log_date={$this->log_date}";
        if ($this->raw) {
            $this->assign('revertURL', CRM_Report_Utils_Report::getNextUrl('logging/contact/detail', "$q&revert=1", false, true));
        } else {
            $this->assign('revertURL', CRM_Report_Utils_Report::getNextUrl('logging/contact/detail', "$q&raw=1", false, true));
        }
    }

    function buildRows($sql, &$rows)
    {
        // safeguard for when there aren’t any log entries yet
        if (!$this->log_conn_id or !$this->log_date) return;

        if (empty($rows)) $rows = array();

        foreach ($this->tables as $table) {
            $rows = array_merge($rows, $this->diffsInTable($table));
        }
    }

    private function diffsInTable($table)
    {
        $rows = array();

        $differ = new CRM_Logging_Differ($this->log_conn_id, $this->log_date);
        $diffs  = $differ->diffsInTable($table);

        // return early if nothing found
        if (empty($diffs)) return $rows;

        list($titles, $values) = $differ->titlesAndValuesForTable($table);

        // populate $rows with only the differences between $changed and $original (skipping certain columns and NULL ↔ empty changes unless raw requested)
        // FIXME: explode preferred_communication_method on CRM_Core_DAO::VALUE_SEPARATOR and handle properly somehow
        $skipped = array('contact_id', 'entity_id', 'id');
        foreach ($diffs as $diff) {
            $field = $diff['field'];
            $from  = $diff['from'];
            $to    = $diff['to'];

            if ($this->raw) {
                $field = substr($table, 4) . '.' . $field;
            } else {
                if (in_array($field, $skipped))      continue;
                if ($from == $to)                    continue; // $differ filters out === values; for presentation hide changes like 42 → '42'
                if ($from == false and $to == false) continue; // only in PHP: '0' == false and null == false but '0' != null

                if (isset($values[$field][$from])) $from  = $values[$field][$from];
                if (isset($values[$field][$to]))   $to    = $values[$field][$to];
                if (isset($titles[$field]))        $field = $titles[$field];
                if ($diff['action'] == 'Insert')   $from  = ts('[NONEXISTENT]');
            }

            $rows[] = array('field' => $field . " (id: {$diff['id']})", 'from' => $from, 'to' => $to);
        }

        return $rows;
    }
}
