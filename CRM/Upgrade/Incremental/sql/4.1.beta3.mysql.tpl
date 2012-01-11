-- Fix invalid api action in Job table
UPDATE `civicrm_job`
SET api_action = 'process_membership_reminder_date' WHERE api_action = 'process_process_membership_reminder_date';

-- Insert Schedule Jobs admin menu item
