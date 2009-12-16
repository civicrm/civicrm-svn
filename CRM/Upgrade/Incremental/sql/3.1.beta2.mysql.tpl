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

-- CRM-5450
 
SELECT @option_group_id_address_options := max(id) from civicrm_option_group where name = 'address_options';
SELECT @adOpt_max_val := MAX(ROUND(op.value)) FROM civicrm_option_value op WHERE op.option_group_id = @option_group_id_address_options;
SELECT @adOpt_max_wt := MAX(ROUND(val.weight)) FROM civicrm_option_value val where val.option_group_id = @option_group_id_address_options;

INSERT INTO 
   civicrm_option_value(`option_group_id`, {localize field='label'}`label`{/localize}, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `visibility_id`) 
VALUES(@option_group_id_address_options, {localize}'Street Address Parsing'{/localize}, (SELECT @adOpt_max_val := @adOpt_max_val+1), 'street_address_parsing', NULL, 0, NULL, (SELECT @adOpt_max_wt := @adOpt_max_wt + 1 ), 0, 0, 1, NULL, NULL);

--fix broken default address options.
SELECT  @domain_id := min(id) FROM civicrm_domain;

UPDATE  `civicrm_preferences`
   SET  `address_options` = REPLACE( `address_options`, '1314', '' )
 WHERE  `domain_id` = @domain_id 
   AND  `contact_id` IS NULL;

-- CRM-5528

SELECT @option_group_id_cdt := max(id) from civicrm_option_group where name = 'custom_data_type';

INSERT INTO 
   `civicrm_option_value` (`option_group_id`, {localize field='label'}`label`{/localize}, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `visibility_id`) 
VALUES(@option_group_id_cdt, {localize}'Participant Event Type'{/localize}, '3', 'ParticipantEventType', NULL, 0, NULL, 3, NULL, 0, 0, 1, NULL, NULL );

-- CRM-5549
ALTER TABLE `civicrm_report_instance`
    ADD `domain_id` INT(10) UNSIGNED NOT NULL COMMENT 'Which Domain is this instance for' AFTER `id`;

UPDATE `civicrm_report_instance` SET domain_id = @domain_id;

ALTER TABLE `civicrm_report_instance`
    ADD CONSTRAINT `FK_civicrm_report_instance_domain_id` FOREIGN KEY (`domain_id`) REFERENCES `civicrm_domain` (`id`);
