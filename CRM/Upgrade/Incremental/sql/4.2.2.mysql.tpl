-- CRM-10810
SELECT @max_strict := max(id), @cnt_strict := count(*) FROM `civicrm_dedupe_rule_group` WHERE `contact_type` = 'Individual' AND `level` = 'Strict';
UPDATE `civicrm_dedupe_rule_group` SET  `is_default` = 0 WHERE @cnt_strict > 1 AND id = @max_strict;

SELECT @max_fuzzy := max(id), @cnt_fuzzy := count(*) FROM `civicrm_dedupe_rule_group` WHERE `contact_type` = 'Individual' AND `level` = 'Fuzzy';
UPDATE `civicrm_dedupe_rule_group` SET  `is_default` = 0 WHERE @cnt_fuzzy > 1 AND id = @max_fuzzy;

