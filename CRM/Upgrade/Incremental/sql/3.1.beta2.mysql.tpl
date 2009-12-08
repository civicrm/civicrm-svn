-- CRM-3507: upgrade message templates (if changed)
{include file='../CRM/Upgrade/3.1.beta2.msg_template/civicrm_msg_template.tpl'}

-- CRM-5496
    SELECT @option_group_id_report         := max(id) from civicrm_option_group where name = 'report_template';
    SELECT @caseCompId       := max(id) FROM civicrm_component where name = 'CiviCase';
    INSERT INTO 
        `civicrm_option_value` (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `visibility_id`) 
    VALUES
        (@option_group_id_report , '{ts escape="sql"}Case Summary Report{/ts}',                     'case/summary',                   'CRM_Report_Form_Case_Summary',                   NULL, 0, NULL, 24, '{ts escape="sql"}Provides a summary of cases and their duration by date range, status, staff member and / or case role.{/ts}', 0, 0, 1, @caseCompId, NULL),
        (@option_group_id_report , '{ts escape="sql"}Case Time Spent Report{/ts}',                  'case/timespent',                 'CRM_Report_Form_Case_TimeSpent',                 NULL, 0, NULL, 25, '{ts escape="sql"}Aggregates time spent on case and / or or non-case activities by activity type and contact.{/ts}', 0, 0, 1, @caseCompId, NULL),
        (@option_group_id_report , '{ts escape="sql"}Contact Demographics Report{/ts}',             'case/demographics',              'CRM_Report_Form_Case_Demographics',              NULL, 0, NULL, 26, '{ts escape="sql"}Demographic breakdown for case clients (and or non-case contacts) in your database. Includes custom contact fields.{/ts}', 0, 0, 1, @caseCompId, NULL),
        (@option_group_id_report , '{ts escape="sql"}Database Log Report{/ts}',                     'contact/log',                    'CRM_Report_Form_Contact_Log',                    NULL, 0, NULL, 27, '{ts escape="sql"}Log of contact and activity records created or updated in a given date range.{/ts}', 0, 0, 1, NULL, NULL);
-- CRM-5438
UPDATE civicrm_navigation SET permission ='access CiviCRM', permission_operator ='' WHERE civicrm_navigation.name= 'Manage Groups';
