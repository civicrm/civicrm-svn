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
        }
        CRM_Utils_File::sourceSQLFile($config->dsn, "$civicrm_root/sql/logging_triggers.sql");
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
