-- Fix invalid api action in Job table
UPDATE `civicrm_job`
SET api_action = 'process_membership_reminder_date' WHERE api_action = 'process_process_membership_reminder_date';

-- Insert Schedule Jobs admin menu item
SELECT @systemSettingsID := id     FROM civicrm_navigation where name = 'System Settings';
SELECT @domainID := min(id) FROM civicrm_domain;

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( @domainID, 'civicrm/admin/job&reset=1', '{ts escape="sql" skip="true"}Scheduled Jobs{/ts}', 'Scheduled Jobs', 'administer CiviCRM', '', @systemSettingsID, '1', NULL, 15 );
