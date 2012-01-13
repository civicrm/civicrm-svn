-- Fix invalid api action in Job table and insert missing job
UPDATE `civicrm_job`
SET api_action = 'process_membership_reminder_date' WHERE api_action = 'process_process_membership_reminder_date';

SELECT @domainID := min(id) FROM civicrm_domain;
INSERT INTO `civicrm_job`
    ( domain_id, run_frequency, last_run, name, description, api_prefix, api_entity, api_action, parameters, is_active ) 
VALUES 
    ( @domainID, 'Always' , NULL, '{ts escape="sql" skip="true"}Process Survey Respondents{/ts}',   '{ts escape="sql" skip="true"}Releases reserved survey respondents when they have been reserved for longer than the Release Frequency days specified for that survey.{/ts}','civicrm_api3', 'job', 'process_respondent','version=3\r\n', 0);

-- Insert Schedule Jobs admin menu item
SELECT @systemSettingsID := id     FROM civicrm_navigation where name = 'System Settings';

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/admin/job&reset=1', '{ts escape="sql" skip="true"}Scheduled Jobs{/ts}', 'Scheduled Jobs', 'administer CiviCRM', '', @systemSettingsID, '1', NULL, 15 );
