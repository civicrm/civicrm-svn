-- +--------------------------------------------------------------------+
-- | CiviCRM version 3.2                                                |
-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC (c) 2004-2010                                |
-- +--------------------------------------------------------------------+
-- | This file is a part of CiviCRM.                                    |
-- |                                                                    |
-- | CiviCRM is free software; you can copy, modify, and distribute it  |
-- | under the terms of the GNU Affero General Public License           |
-- | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
-- |                                                                    |
-- | CiviCRM is distributed in the hope that it will be useful, but     |
-- | WITHOUT ANY WARRANTY; without even the implied warranty of         |
-- | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
-- | See the GNU Affero General Public License for more details.        |
-- |                                                                    |
-- | You should have received a copy of the GNU Affero General Public   |
-- | License and the CiviCRM Licensing Exception along                  |
-- | with this program; if not, contact CiviCRM LLC                     |
-- | at info[AT]civicrm[DOT]org. If you have questions about the        |
-- | GNU Affero General Public License or the licensing of CiviCRM,     |
-- | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
-- +--------------------------------------------------------------------+

{foreach from=$logtables key=table item=types}

  DROP TABLE IF EXISTS {$table}_log;

  CREATE TABLE {$table}_log (
    {foreach from=$types key=column item=type}
      {$column} {$type},
    {/foreach}
    log_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    log_conn_id INTEGER,
    log_user_id INTEGER,
    log_action  ENUM('Initialization', 'Insert', 'Update', 'Delete')
  ) ENGINE ARCHIVE;

  CREATE TRIGGER {$table}_after_insert AFTER INSERT ON {$table}
    FOR EACH ROW INSERT INTO {$table}_log
      ({foreach from=$types key=column item=type}{$column},     {/foreach} log_conn_id,     log_user_id,      log_action) VALUES
      ({foreach from=$types key=column item=type}NEW.{$column}, {/foreach} CONNECTION_ID(), @civicrm_user_id, 'Insert');

  CREATE TRIGGER {$table}_after_update AFTER UPDATE ON {$table}
    FOR EACH ROW INSERT INTO {$table}_log
      ({foreach from=$types key=column item=type}{$column},     {/foreach} log_conn_id,     log_user_id,      log_action) VALUES
      ({foreach from=$types key=column item=type}NEW.{$column}, {/foreach} CONNECTION_ID(), @civicrm_user_id, 'Update');

  CREATE TRIGGER {$table}_after_delete AFTER DELETE ON {$table}
    FOR EACH ROW INSERT INTO {$table}_log
      ({foreach from=$types key=column item=type}{$column},     {/foreach} log_conn_id,     log_user_id,      log_action) VALUES
      ({foreach from=$types key=column item=type}OLD.{$column}, {/foreach} CONNECTION_ID(), @civicrm_user_id, 'Delete');

{/foreach}
