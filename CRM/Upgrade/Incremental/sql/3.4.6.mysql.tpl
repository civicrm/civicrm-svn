-- CRM-8125

SELECT @option_group_id_languages := MAX( id ) FROM civicrm_option_group WHERE name = 'languages';

DELETE FROM civicrm_option_value WHERE option_group_id = @option_group_id_languages AND name = 'de_CH';
DELETE FROM civicrm_option_value WHERE option_group_id = @option_group_id_languages AND name = 'es_PR';

SELECT @languages_max_weight := MAX( weight ) FROM civicrm_option_value WHERE option_group_id = @option_group_id_languages;

INSERT INTO civicrm_option_value
  (option_group_id, is_default, is_active, name, value, {localize field='label'}label{/localize}, weight)
VALUES
(@option_group_id_languages, 0, 1, 'de_CH', 'de', {localize}'{ts escape="sql"}German (Swiss){/ts}'{/localize},                   @weight := @languages_max_weight + 1),
(@option_group_id_languages, 0, 1, 'es_PR', 'es', {localize}'{ts escape="sql"}Spanish; Castilian (Puerto Rico){/ts}'{/localize}, @weight := @languages_max_weight + 2);

-- CRM-8653
UPDATE civicrm_dashboard SET url = 'civicrm/report/instance/3&reset=1&section=2&snippet=4&context=dashlet' WHERE url = 'civicrm/report/instance/3&reset=1&section=2&snippet=4';

-- CRM-8654
ALTER TABLE `civicrm_dashboard_contact` CHANGE `content` `content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'dashlet content';
