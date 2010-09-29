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

class CRM_Logging_Engine
{
    static function disableLogging()
    {
        if (!self::isEnabled()) return;

        $dao = new CRM_Core_DAO;

        require_once 'CRM/Logging/SchemaStructure.php';
        foreach (CRM_Logging_SchemaStructure::tables() as $table) {
            $dao->executeQuery("DROP TRIGGER IF EXISTS {$table}_after_insert");
            $dao->executeQuery("DROP TRIGGER IF EXISTS {$table}_after_update");
            $dao->executeQuery("DROP TRIGGER IF EXISTS {$table}_after_delete");
        }

        $dao->free();
    }

    static function enableLogging()
    {
        if (self::isEnabled()) return;

        require_once 'CRM/Core/Config.php';
        $config =& CRM_Core_Config::singleton();

        require_once 'CRM/Utils/File.php';
        global $civicrm_root;
        if (!self::tablesExist()) {
            CRM_Utils_File::sourceSQLFile($config->dsn, "$civicrm_root/sql/logging_tables.sql");
            self::createCustomTables();
        }
        CRM_Utils_File::sourceSQLFile($config->dsn, "$civicrm_root/sql/logging_triggers.sql");
    }

    private static function createCustomTables()
    {
        // fetch custom table names
        $tables = array();
        $dao = CRM_Core_DAO::executeQuery('SHOW TABLES LIKE "civicrm_value_%"');
        while ($dao->fetch()) {
            $tables[] = $dao->toValue('Tables_in_civicrm_(civicrm_value_%)');
        }

        // fetch CREATE TABLE queries
        $queries = array();
        foreach ($tables as $table) {
            $dao = CRM_Core_DAO::executeQuery("SHOW CREATE TABLE $table");
            $dao->fetch();
            $queries[$table] = $dao->Create_Table;
        }

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
        foreach ($queries as $table => $query) {
            $query = preg_replace("/^CREATE TABLE `$table`/", "CREATE TABLE `log_$table`", $query);
            $query = preg_replace("/ AUTO_INCREMENT/", '', $query);
            $query = preg_replace("/^  [^`].*$/m", '', $query);
            $query = preg_replace("/^\) ENGINE=[^ ]+ /m", ') ENGINE=ARCHIVE ', $query);
            $query = preg_replace("/^\) /m", "$cols\n) ", $query);

            CRM_Core_DAO::executeQuery("DROP TABLE IF EXISTS log_$table");
            CRM_Core_DAO::executeQuery($query);
        }
    }

    private static function isEnabled()
    {
        return self::tablesExist() and self::triggersExist();
    }

    private static function tablesExist()
    {
        return CRM_Core_DAO::checkTableExists('log_civicrm_contact');
    }

    private static function triggersExist()
    {
        return (bool) CRM_Core_DAO::singleValueQuery("SHOW TRIGGERS LIKE 'civicrm_contact'");
    }
}
