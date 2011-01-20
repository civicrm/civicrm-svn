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
        $where = array('log_conn_id = %1', 'log_date < DATE_ADD(%2, INTERVAL 10 SECOND)');
        if ($id) {
            $params[3] = array($id, 'Integer');
            $where[]   = 'id = %3';
        }
        $changedSQL = "SELECT * FROM `{$this->db}`.`$table` WHERE " . implode(' AND ', $where) . ' ORDER BY log_date DESC LIMIT 1';
        $changed    = $this->sqlToArray($changedSQL, $params);

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

    function titlesAndValuesForTable($table)
    {
        $titles = array();
        $values = array();

        $daos = array(
            'log_civicrm_address' => 'CRM_Core_DAO_Address',
            'log_civicrm_contact' => 'CRM_Contact_DAO_Contact',
            'log_civicrm_email'   => 'CRM_Core_DAO_Email',
            'log_civicrm_im'      => 'CRM_Core_DAO_IM',
            'log_civicrm_openid'  => 'CRM_Core_DAO_OpenID',
            'log_civicrm_phone'   => 'CRM_Core_DAO_Phone',
            'log_civicrm_website' => 'CRM_Core_DAO_Website',
        );

        if (in_array($table, array_keys($daos))) {
            // FIXME: these should be populated with pseudo constants as they
            // were at the time of logging rather than their current values
            $values = array(
                'country_id'                     => CRM_Core_PseudoConstant::country(),
                'gender_id'                      => CRM_Core_PseudoConstant::gender(),
                'location_type_id'               => CRM_Core_PseudoConstant::locationType(),
                'phone_type_id'                  => CRM_Core_PseudoConstant::phoneType(),
                'preferred_communication_method' => CRM_Core_PseudoConstant::pcm(),
                'preferred_language'             => CRM_Core_PseudoConstant::languages(),
                'prefix_id'                      => CRM_Core_PseudoConstant::individualPrefix(),
                'provider_id'                    => CRM_Core_PseudoConstant::IMProvider(),
                'state_province_id'              => CRM_Core_PseudoConstant::stateProvince(),
                'suffix_id'                      => CRM_Core_PseudoConstant::individualSuffix(),
                'website_type_id'                => CRM_Core_PseudoConstant::websiteType(),
            );

            require_once str_replace('_', DIRECTORY_SEPARATOR, $daos[$table]) . '.php';
            eval("\$dao = new $daos[$table];");
            foreach ($dao->fields() as $field) {
                $titles[$field['name']] = $field['title'];

                if ($field['type'] == CRM_Utils_Type::T_BOOLEAN) {
                    $values[$field['name']] = array('0' => ts('false'), '1' => ts('true'));
                }
            }
        } elseif (substr($table, 0, 18) == 'log_civicrm_value_') {
            list($titles, $values) = $this->titlesAndValuesForCustomDataTable($table);
        }

        return array($titles, $values);
    }

    private function sqlToArray($sql, $params)
    {
        $dao =& CRM_Core_DAO::executeQuery($sql, $params);
        $dao->fetch();
        return $dao->toArray();
    }

    private function titlesAndValuesForCustomDataTable($table)
    {
        $titles = array();
        $values = array();

        $params = array(
            1 => array($this->log_conn_id, 'Integer'),
            2 => array($this->log_date,    'String'),
            3 => array(substr($table, 4),  'String'),
        );

        $sql = "SELECT id, title FROM `{$this->db}`.log_civicrm_custom_group WHERE log_date <= %2 AND table_name = %3 ORDER BY log_date DESC LIMIT 1";
        $cgDao =& CRM_Core_DAO::executeQuery($sql, $params);
        $cgDao->fetch();

        $params[3] = array($cgDao->id, 'Integer');
        $sql = "SELECT column_name, data_type, label, name FROM `{$this->db}`.log_civicrm_custom_field WHERE log_date <= %2 AND custom_group_id = %3 ORDER BY log_date";
        $cfDao =& CRM_Core_DAO::executeQuery($sql, $params);

        while ($cfDao->fetch()) {
            $titles[$cfDao->column_name] = "{$cgDao->title}: {$cfDao->label}";

            switch ($cfDao->data_type) {
            case 'Boolean':
                $values[$cfDao->column_name] = array('0' => ts('false'), '1' => ts('true'));
                break;
            case 'String':
                $values[$cfDao->column_name] = array();
                $params[3] = array("custom_{$cfDao->name}", 'String');
                $sql = "SELECT id FROM `{$this->db}`.log_civicrm_option_group WHERE log_date <= %2 AND name = %3 ORDER BY log_date DESC LIMIT 1";
                $ogId = CRM_Core_DAO::singleValueQuery($sql, $params);

                $params[3] = array($ogId, 'Integer');
                $sql = "SELECT label, value FROM `{$this->db}`.log_civicrm_option_value WHERE log_date <= %2 AND option_group_id = %3 ORDER BY log_date";
                $ovDao =& CRM_Core_DAO::executeQuery($sql, $params);
                while ($ovDao->fetch()) {
                    $values[$cfDao->column_name][$ovDao->value] = $ovDao->label;
                }
                break;
            }
        }

        return array($titles, $values);
    }
}
