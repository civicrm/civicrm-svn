-- CRM-10969
SELECT @mailingsID := MAX(id) FROM civicrm_navigation WHERE name = 'Mailings';
SELECT @navWeight := MAX(id) FROM civicrm_navigation WHERE name = 'New SMS' AND parent_id = @mailingsID;

UPDATE civicrm_navigation SET has_separator = NULL
WHERE name = 'New SMS' AND parent_id = @mailingsID AND has_separator = 1;

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, 'civicrm/mailing/browse?reset=1&sms=1', '{ts escape="sql" skip="true"}Find Mass SMS{/ts}', 'Find Mass SMS', 'administer CiviCRM', NULL, @mailingsID, '1', 1, @navWeight+1 );