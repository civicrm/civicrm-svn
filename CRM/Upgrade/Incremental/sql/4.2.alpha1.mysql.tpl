{include file='../CRM/Upgrade/4.2.alpha1.msg_template/civicrm_msg_template.tpl'}

-- CRM-9534
-- Add column 'is_selected' for 'civicrm_prevnext_cache'
ALTER TABLE `civicrm_prevnext_cache` ADD tinyint(4) DEFAULT 0;
