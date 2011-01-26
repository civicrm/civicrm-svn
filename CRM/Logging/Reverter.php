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

class CRM_Logging_Reverter
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

    function revert($tables)
    {
        // FIXME: split off the table → DAO mapping to a GenCode-generated class
        $daos = array(
            'civicrm_address' => 'CRM_Core_DAO_Address',
            'civicrm_contact' => 'CRM_Contact_DAO_Contact',
            'civicrm_email'   => 'CRM_Core_DAO_Email',
            'civicrm_im'      => 'CRM_Core_DAO_IM',
            'civicrm_openid'  => 'CRM_Core_DAO_OpenID',
            'civicrm_phone'   => 'CRM_Core_DAO_Phone',
            'civicrm_website' => 'CRM_Core_DAO_Website',
        );

        // get custom data tables, columns and types
        $ctypes = array();
        $dao =& CRM_Core_DAO::executeQuery('SELECT table_name, column_name, data_type FROM civicrm_custom_group cg JOIN civicrm_custom_field cf ON (cf.custom_group_id = cg.id)');
        while ($dao->fetch()) {
            if (!isset($ctypes[$dao->table_name])) $ctypes[$dao->table_name] = array();
            $ctypes[$dao->table_name][$dao->column_name] = $dao->data_type;
        }

        $differ = new CRM_Logging_Differ($this->log_conn_id, $this->log_date);
        $diffs  = $differ->diffsInTables($tables);

        $deletes = array();
        $reverts = array();
        foreach ($diffs as $table => $changes) {
            $table = substr($table, 4);   // drop the ‘log_’ prefix
            foreach ($changes as $change) {
                switch ($change['action']) {
                case 'Delete':
                    // FIXME: handle Delete actions
                    break;
                case 'Insert':
                    if (!isset($deletes[$table])) $deletes[$table] = array();
                    $deletes[$table][] = $change['id'];
                    break;
                case 'Update':
                    if (!isset($reverts[$table]))                $reverts[$table] = array();
                    if (!isset($reverts[$table][$change['id']])) $reverts[$table][$change['id']] = array();
                    $reverts[$table][$change['id']][$change['field']] = $change['from'];
                    break;
                }
            }
        }

        // revert inserts by deleting
        foreach ($deletes as $table => $ids) {
            CRM_Core_DAO::executeQuery("DELETE FROM `$table` WHERE id IN (" . implode(', ', array_unique($ids)) . ')');
        }

        // revert updates by updating to previous values
        foreach ($reverts as $table => $row) {
            switch (true) {
            // DAO-based tables
            case in_array($table, array_keys($daos)):
                require_once str_replace('_', DIRECTORY_SEPARATOR, $daos[$table]) . '.php';
                eval("\$dao = new {$daos[$table]};");
                foreach ($row as $id => $changes) {
                    $dao->id = $id;
                    foreach ($changes as $field => $value) {
                        $dao->$field = $value;
                    }
                    $dao->save();
                    $dao->reset();
                }
                break;
            // custom data tables
            case in_array($table, array_keys($ctypes)):
                foreach ($row as $id => $changes) {
                    $sets    = array();
                    $params  = array(1 => array($id, 'Integer'));
                    $counter = 2;
                    foreach ($changes as $field => $value) {
                        if (!isset($ctypes[$table][$field])) continue;   // don’t try reverting a field that’s no longer there
                        $sets[] = "$field = %$counter";
                        $params[$counter] = array($value, $ctypes[$table][$field]);
                        $counter++;
                    }
                    $sql = "UPDATE `$table` SET " . implode(', ', $sets) . ' WHERE id = %1';
                    CRM_Core_DAO::executeQuery($sql, $params);
                }
                break;
            }
        }
    }
}
