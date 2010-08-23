-- CRM-6694
INSERT INTO civicrm_navigation
 ( domain_id, label, name, url, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
 ( @domainID, '{ts escape="sql"}Home{/ts}', 'Home', 'civicrm/dashboard&reset=1', NULL, '', NULL, 1, NULL, 0);