--CRM-6455
SELECT @domainID               := MIN(id) FROM civicrm_domain;
SELECT @option_group_id_editor := MAX(id) from civicrm_option_group where name = 'wysiwyg_editor';
SELECT @max_value              := MAX(ROUND(value)) from civicrm_option_value where option_group_id = @option_group_id_editor;
SELECT @max_weight             := MAX(ROUND(weight)) from civicrm_option_value where option_group_id = @option_group_id_editor;

INSERT INTO civicrm_option_value
        ( option_group_id, {localize field='label'}label{/localize}, value, name, grouping, filter, is_default, weight, {localize field='description'}description{/localize}, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id )
VALUES
	( @option_group_id_editor, {localize}'Joomla Default Editor'{/localize}, @max_value+1, NULL, NULL, 0, NULL, @max_weight+1, {localize}NULL{/localize}, 0, 1, 1, NULL, @domainID, NULL );

-- CRM-6846
CREATE TABLE `civicrm_price_field_value` 
  (`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Price Field Value',
  `price_field_id` int(10) unsigned NOT NULL COMMENT 'FK to civicrm_price_field',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Price field option name',
  {localize field='label'}`label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Price field option label'{/localize},
  {localize field='description'}`description` text COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Price field option description.'{/localize},
  `amount` varchar(512) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Price field option amount',
  `count` int(10) unsigned DEFAULT NULL COMMENT 'Number of participants per field option',
  `max_value` int(10) unsigned DEFAULT NULL COMMENT 'Max number of participants per field options',
  `weight` int(11) DEFAULT '1' COMMENT 'Order in which the field options should appear',
  `is_default` tinyint(4) DEFAULT '0' COMMENT 'Is this default price field option',
  `is_active` tinyint(4) DEFAULT '1' COMMENT 'Is this price field option active',
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_civicrm_price_field_value_price_field_id` FOREIGN KEY (`price_field_id`) REFERENCES civicrm_price_field(id) ON DELETE CASCADE )ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--CRM-7003
 ALTER TABLE `civicrm_uf_match` ADD INDEX `I_civicrm_uf_match_uf_id`(`uf_id`);

