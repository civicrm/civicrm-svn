<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
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

require_once 'CRM/Core/DAO.php';

class CRM_Logging_Schema
{
    private $tables = array();

    function __construct()
    {
        $dao = CRM_Core_DAO::executeQuery('SHOW TABLES LIKE "civicrm_%"');
        while ($dao->fetch()) {
            $this->tables[] = $dao->toValue('Tables_in_civicrm_(civicrm_%)');
        }
    }

    function disableLogging()
    {
        if (!$this->isEnabled()) return;

        $this->dropTriggers();
    }

    function enableLogging()
    {
        if ($this->isEnabled()) return;

        if (!$this->tablesExist()) {
            $this->createLogTables();
        }
        $this->createTriggers();
    }

    private function columnsOf($table)
    {
        $dao = CRM_Core_DAO::executeQuery("SHOW COLUMNS FROM $table");
        $columns = array();
        while ($dao->fetch()) {
            $columns[] = $dao->Field;
        }
        return $columns;
    }

    private function createLogTableFor($table)
    {
        $dao = CRM_Core_DAO::executeQuery("SHOW CREATE TABLE $table");
        $dao->fetch();
        $query = $dao->Create_Table;

        // rewrite the queries into CREATE TABLE queries for log tables:
        // - prepend the name with log_
        // - drop AUTO_INCREMENT columns
        // - drop non-column rows of the query (keys, constraints, etc.)
        // - set the ENGINE to ARCHIVE
        // - add log-specific columns (at the end of the table)
        $cols = <<<COLS
            log_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            log_conn_id INTEGER,
            log_user_id INTEGER,
            log_action  ENUM('Initialization', 'Insert', 'Update', 'Delete')
COLS;
        $query = preg_replace("/^CREATE TABLE `$table`/", "CREATE TABLE `log_$table`", $query);
        $query = preg_replace("/ AUTO_INCREMENT/", '', $query);
        $query = preg_replace("/^  [^`].*$/m", '', $query);
        $query = preg_replace("/^\) ENGINE=[^ ]+ /m", ') ENGINE=ARCHIVE ', $query);
        $query = preg_replace("/^\) /m", "$cols\n) ", $query);

        CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS log_$table");
        CRM_Core_DAO::executeQuery($query);

        $columns = implode(', ', $this->columnsOf($table));
        CRM_Core_DAO::executeQuery("INSERT INTO log_$table ($columns, log_conn_id, log_user_id, log_action) SELECT $columns, CONNECTION_ID(), @civicrm_user_id, 'Initialization' FROM $table");
    }

    private function createLogTables()
    {
        foreach ($this->tables as $table) {
            $this->createLogTableFor($table);
        }
    }

    private function createTriggersFor($table)
    {
        $columns = $this->columnsOf($table);

        $queries = array();
        foreach (array('Insert', 'Update', 'Delete') as $action) {
            $trigger = "{$table}_after_" . strtolower($action);
            $queries[] = "DROP TRIGGER IF EXISTS $trigger";
            $query = "CREATE TRIGGER $trigger AFTER $action ON $table FOR EACH ROW INSERT INTO log_$table (";
            foreach ($columns as $column) {
                $query .= "$column, ";
            }
            $query .= "log_conn_id, log_user_id, log_action) VALUES (";
            foreach ($columns as $column) {
                $query .= $action == 'Delete' ? "OLD.$column, " : "NEW.$column, ";
            }
            $query .= "CONNECTION_ID(), @civicrm_user_id, '$action')";
            $queries[] = $query;
        }

        $dao = new CRM_Core_DAO;
        foreach ($queries as $query) {
            $dao->executeQuery($query);
        }
    }

    private function createTriggers()
    {
        foreach ($this->tables as $table) {
            $this->createTriggersFor($table);
        }
    }

    private function dropTriggers()
    {
        foreach ($this->tables as $table) {
            $dao->executeQuery("DROP TRIGGER IF EXISTS {$table}_after_insert");
            $dao->executeQuery("DROP TRIGGER IF EXISTS {$table}_after_update");
            $dao->executeQuery("DROP TRIGGER IF EXISTS {$table}_after_delete");
        }
    }

    private function isEnabled()
    {
        return $this->tablesExist() and $this->triggersExist();
    }

    private function tablesExist()
    {
        // FIXME: probably should be a bit more thorough…
        return CRM_Core_DAO::checkTableExists('log_civicrm_contact');
    }

    private function triggersExist()
    {
        // FIXME: probably should be a bit more thorough…
        return (bool) CRM_Core_DAO::singleValueQuery("SHOW TRIGGERS LIKE 'civicrm_contact'");
    }
}
