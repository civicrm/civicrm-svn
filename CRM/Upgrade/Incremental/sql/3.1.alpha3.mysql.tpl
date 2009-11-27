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