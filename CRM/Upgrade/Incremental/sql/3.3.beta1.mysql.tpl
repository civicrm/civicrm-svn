--CRM-6455
SELECT @domainID               := MIN(id) FROM civicrm_domain;
SELECT @option_group_id_editor := MAX(id) from civicrm_option_group where name = 'wysiwyg_editor';
SELECT @max_value              := MAX(ROUND(value)) from civicrm_option_value where option_group_id = @option_group_id_editor;
SELECT @max_weight             := MAX(ROUND(weight)) from civicrm_option_value where option_group_id = @option_group_id_editor;

INSERT INTO civicrm_option_value
        ( option_group_id, {localize field='label'}label{/localize}, value, name, grouping, filter, is_default, weight, {localize field='description'}description{/localize}, is_optgroup, is_reserved, is_active, component_id, domain_id, visibility_id )
VALUES
	( @option_group_id_editor, {localize}'Joomla Default Editor'{/localize}, @max_value+1, NULL, NULL, 0, NULL, @max_weight+1, {localize}NULL{/localize}, 0, 1, 1, NULL, @domainID, NULL );