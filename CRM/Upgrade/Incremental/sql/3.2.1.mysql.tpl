-- CRM-6554
SELECT @domainID := min(id) FROM civicrm_domain;
SELECT @navid := id FROM civicrm_navigation WHERE name='Option Lists';
SELECT @wt := max(weight) FROM civicrm_navigation WHERE parent_id=@navid;
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
( @domainID, 'civicrm/admin/options/wordreplacements&reset=1',                                                              '{ts escape="sql"}Word Replacements{/ts}',       'Word Replacements',                         'administer CiviCRM', '',   @navid, '1', NULL, @wt + 1);

-- CRM-6532
UPDATE civicrm_state_province SET name = 'Bahia'     WHERE name = 'Baia';
UPDATE civicrm_state_province SET name = 'Tocantins' WHERE name = 'Tocatins';
