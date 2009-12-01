-- CRM-3507: upgrade message templates (if changed)
{include file='../CRM/Upgrade/3.1.alpha3.msg_template/civicrm_msg_template.tpl'}

--  CRM-5388
SELECT @option_group_id_prefix := max(id) from civicrm_option_group where name = 'individual_prefix';
SELECT @option_group_id_suffix := max(id) from civicrm_option_group where name = 'individual_suffix';
{if $multilingual}    
    -- prefix
    UPDATE civicrm_option_value
        SET {foreach from=$locales item=locale}label_{$locale} = CONCAT( label_{$locale}, '.') ,{/foreach}
            name = CONCAT( `name`, '.')
        WHERE name IN ('Mrs','Ms','Mr','Dr') AND option_group_id = @option_group_id_prefix;
    -- suffix
    UPDATE civicrm_option_value
        SET {foreach from=$locales item=locale}label_{$locale} = CONCAT( label_{$locale}, '.') ,{/foreach}
            name = CONCAT( `name`, '.')
        WHERE name IN ('Jr','Sr') AND option_group_id = @option_group_id_suffix;    
{else}
    -- prefix
    UPDATE civicrm_option_value SET label = CONCAT( label, '.') , name = CONCAT( `name`, '.')
            WHERE name IN ('Mrs','Ms','Mr','Dr') AND option_group_id = @option_group_id_prefix;
    -- suffix    
    UPDATE civicrm_option_value SET label = CONCAT( label, '.') , name = CONCAT( `name`, '.')
            WHERE name IN ('Jr','Sr') AND option_group_id = @option_group_id_suffix;
{/if}

--  CRM-5435
ALTER TABLE `civicrm_contribution_soft` 
    ADD CONSTRAINT `FK_civicrm_contribution_soft_pcp_id` FOREIGN KEY (`pcp_id`) REFERENCES `civicrm_pcp` (`id`) ON DELETE SET NULL;

ALTER TABLE `civicrm_contribution_soft` 
    CHANGE `pcp_id` `pcp_id` int(10) unsigned default NULL COMMENT 'FK to civicrm_pcp.id';

-- CRM-5322

  SELECT @option_group_id_sfe := max(id) from civicrm_option_group where name = 'safe_file_extension';
  SELECT @max_val             := MAX(ROUND(op.value)) FROM civicrm_option_value op WHERE op.option_group_id  = @option_group_id_sfe;
  SELECT @max_wt              := max(weight) from civicrm_option_value where option_group_id= @option_group_id_sfe;

  INSERT INTO civicrm_option_value
    (option_group_id,      {localize field='label'}label{/localize}, value,                           filter, weight) VALUES
    (@option_group_id_sfe, {localize}'docx'{/localize},              (SELECT @max_val := @max_val+1), 0,      (SELECT @max_wt := @max_wt+1)),
    (@option_group_id_sfe, {localize}'xlsx'{/localize},              (SELECT @max_val := @max_val+1), 0,      (SELECT @max_wt := @max_wt+1));

