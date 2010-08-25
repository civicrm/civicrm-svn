SELECT @domainID        := min(id) FROM civicrm_domain;

-- CRM-6694, CRM-6716
SELECT @navid := id FROM civicrm_navigation WHERE name='Option Lists';
SELECT @wt := max(weight) FROM civicrm_navigation WHERE parent_id=@navid;
INSERT INTO civicrm_navigation
 ( domain_id, label, name, url, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
 ( @domainID, '{ts escape="sql"}Home{/ts}', 'Home', 'civicrm/dashboard&reset=1', NULL, '', NULL, 1, NULL, 0),
 ( @domainID, '{ts escape="sql"}Website Types{/ts}', 'Website Types', 'civicrm/admin/options/website_type&group=website_type&reset=1', 'administer CiviCRM', '', @navid, 1, NULL, @wt + 1);
 
 -- CRM-6726
 UPDATE  civicrm_option_value SET  filter =  0 WHERE  civicrm_option_value.name = 'Print PDF Letter';