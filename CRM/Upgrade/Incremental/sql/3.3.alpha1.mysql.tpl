-- CRM-6696
ALTER TABLE civicrm_option_value {localize field='description'}MODIFY COLUMN description text{/localize};

-- CRM-6442
SELECT @option_group_id_website := MAX(id) from civicrm_option_group where name = 'website_type';
SELECT @max_value               := MAX(ROUND(value)) from civicrm_option_value where option_group_id = @option_group_id_website;
SELECT @max_weight              := MAX(ROUND(weight)) from civicrm_option_value where option_group_id = @option_group_id_website;;

INSERT INTO civicrm_option_value
        (option_group_id, {localize field='label'}label{/localize}, value, name, grouping, filter, is_default, weight, {localize field='description'}description{/localize}, is_optgroup, is_reserved, is_active, component_id, visibility_id)
VALUES
	(@option_group_id_website, {localize}'Main'{/localize}, @max_value+1, 'Main', NULL, 0, NULL, @max_weight+1, {localize}NULL{/localize}, 0, 0, 1, NULL, NULL);
	
-- CRM-6763
UPDATE civicrm_option_group 
   SET is_reserved = 0
 WHERE civicrm_option_group.name = 'encounter_medium';

-- CRM-6814
ALTER TABLE `civicrm_note` 
  ADD `privacy` INT( 10 ) NOT NULL COMMENT 'Foreign Key to Note Privacy Level (which is an option value pair and hence an implicit FK)';

-- CRM-6748
UPDATE civicrm_navigation SET url = 'civicrm/admin/contribute/add&reset=1&action=add'
        WHERE civicrm_navigation.name = 'New Contribution Page';