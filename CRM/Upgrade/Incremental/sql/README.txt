====================
Specifying Domain ID
====================
(TBD)

==========================
Translate or Localize Text
==========================
Text which is visible to users needs to be translated or set to localizable (which encompasses translation). Localize is used for fields that support
multiple language values in multi-language installs. Check the schema definition for a given field if you're not sure whether a string is localizable.

For example, to check if civicrm_option_value.label is localizable look at the Label field in xml/schema/Core/OptionValue.xml
<field>
     <name>label</name>
     <title>Option Label</title>
     <type>varchar</type>
     <required>true</required>
     <length>255</length>
     <localizable>true</localizable>
     <comment>Option string as displayed to users - e.g. the label in an HTML OPTION tag.</comment>
     <add>1.5</add>
</field>

Localizable is true so we need to do inserts using the {localize} tag around that column. Check the Option Value insert example in the next section.

If a field is NOT localizable, but we just need to make sure it can be translated - use the {ts} tag with sql escape parameter as shown below.

----------------------------------------------------
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES    
    ( @domainID, 'civicrm/admin&reset=1', '{ts escape="sql" skip="true"}Administration Console{/ts}', 'Administration Console', 'administer CiviCRM', '', @adminlastID, '1', NULL, 1 );
----------------------------------------------------

===========================
Inserting Option Value Rows
===========================
When you insert an option value row during an upgrade, do NOT use hard-coded integers for the "value" and "weight" columns. Since in many cases additional
option value rows can be defined by users, you can't determine the next available unique value by looking at a sample installation. Use SELECT max() into
a variable and increment it.

Here's an example which localizes the Label and grabs next available integer for value and weight columns
------------------------------------------------------------------------------
SELECT @caseCompId := id FROM `civicrm_component` where `name` like 'CiviCase';

SELECT @option_group_id_activity_type := max(id) from civicrm_option_group where name = 'activity_type';
SELECT @max_val    := MAX(ROUND(op.value)) FROM civicrm_option_value op WHERE op.option_group_id  = @option_group_id_activity_type;
SELECT @max_wt     := max(weight) from civicrm_option_value where option_group_id=@option_group_id_activity_type;

INSERT INTO civicrm_option_value
  (option_group_id,                {localize field='label'}label{/localize}, {localize field='description'}description{/localize}, value,                           name,               weight,                        filter, component_id)
VALUES
    (@option_group_id_activity_type, {localize}'Change Custom Data'{/localize},{localize}''{/localize}, (SELECT @max_val := @max_val+1), 'Change Custom Data', (SELECT @max_wt := @max_wt+1), 0, @caseCompId);

------------------------------------------------------------------------------
