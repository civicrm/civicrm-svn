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

class CRM_Logging_Differ
{
    private $db;
    private $log_conn_id;
    private $log_date;

    function __construct($log_conn_id, $log_date)
    {
        $dsn = defined('CIVICRM_LOGGING_DSN') ? DB::parseDSN(CIVICRM_LOGGING_DSN) : DB::parseDSN(CIVICRM_DSN);
        $this->db          = $dsn['database'];
        $this->log_conn_id = $log_conn_id;
        $this->log_date    = $log_date;
    }

    function diffsInTable($table, $id = null)
    {
        $params = array(
            1 => array($this->log_conn_id, 'Integer'),
            2 => array($this->log_date,    'String'),
        );

        // we look for the last change in the given connection that happended less than 10 seconds later than log_date to catch multi-query changes
        if ($id) {
            $params[3]  = array($id, 'Integer');
            $changedSQL = "SELECT * FROM `{$this->db}`.`$table` WHERE log_conn_id = %1 AND log_date < DATE_ADD(%2, INTERVAL 10 SECOND) AND id = %3 ORDER BY log_date DESC LIMIT 1";
        } else {
            $changedSQL = "SELECT * FROM `{$this->db}`.`$table` WHERE log_conn_id = %1 AND log_date < DATE_ADD(%2, INTERVAL 10 SECOND) ORDER BY log_date DESC LIMIT 1";
        }
        $changed = $this->sqlToArray($changedSQL, $params);

        // return early if nothing found
        if (empty($changed)) return array();

        // we look for the previous state (different log_conn_id) of the found id
        $params[3]   = array($changed['id'], 'Integer');
        $originalSQL = "SELECT * FROM `{$this->db}`.`$table` WHERE log_conn_id != %1 AND log_date < %2 AND id = %3 ORDER BY log_date DESC LIMIT 1";
        $original    = $this->sqlToArray($originalSQL, $params);

        $rows = array();

        // populate $rows with only the differences between $changed and $original
        $skipped = array('log_action', 'log_conn_id', 'log_date', 'log_user_id');
        foreach (array_keys(array_diff_assoc($changed, $original)) as $diff) {
            if (in_array($diff, $skipped))            continue;
            if ($original[$diff] === $changed[$diff]) continue;
            $rows[] = array(
                'field' => $diff,
                'from'  => $original[$diff],
                'to'    => $changed[$diff],
            );
        }

        return $rows;
    }

    private function sqlToArray($sql, $params)
    {
        $dao =& CRM_Core_DAO::executeQuery($sql, $params);
        $dao->fetch();
        return $dao->toArray();
    }
}
