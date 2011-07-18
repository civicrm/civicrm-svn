-- CRM-8348

CREATE TABLE IF NOT EXISTS civicrm_action_log (
     id                   int UNSIGNED NOT NULL AUTO_INCREMENT,
     contact_id           int UNSIGNED NULL DEFAULT NULL COMMENT 'FK to Contact ID',
     entity_id            int UNSIGNED NOT NULL COMMENT 'FK to id of the entity that the action was performed on. Pseudo - FK.',
     entity_table         varchar(255) COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'name of the entity table for the above id, e.g. civicrm_activity, civicrm_participant',
     action_schedule_id   int UNSIGNED NOT NULL COMMENT 'FK to the action schedule that this action originated from.',
     action_date_time     DATETIME NULL DEFAULT NULL COMMENT 'date time that the action was performed on.',
     is_error             TINYINT( 4 ) NULL DEFAULT '0' COMMENT 'Was there any error sending the reminder?',
     message              TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'Description / text in case there was an error encountered.',
     repetition_number    INT( 10 ) UNSIGNED NULL COMMENT 'Keeps track of the sequence number of this repetition.',
     PRIMARY KEY ( id ),
     CONSTRAINT FK_civicrm_action_log_contact_id FOREIGN KEY (contact_id) REFERENCES civicrm_contact(id) ON DELETE CASCADE,
     CONSTRAINT FK_civicrm_action_log_action_schedule_id FOREIGN KEY (action_schedule_id) REFERENCES civicrm_action_schedule(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- CRM-8370


-- CRM-8085
UPDATE civicrm_mailing SET domain_id = {$domainID} WHERE domain_id IS NULL;

-- CRM-8402
DELETE civicrm_entity_tag.* FROM civicrm_entity_tag,
( SELECT MAX( id ) AS dtid, COUNT(*) AS dupcount FROM civicrm_entity_tag GROUP BY entity_table, entity_id, tag_id HAVING dupcount > 1 ) AS duplicates
WHERE civicrm_entity_tag.id=duplicates.dtid;

ALTER TABLE civicrm_entity_tag 
DROP INDEX index_entity;

ALTER TABLE civicrm_entity_tag 
ADD UNIQUE INDEX UI_entity_id_entity_table_tag_id( entity_table, entity_id, tag_id );

-- CRM-8513

SELECT @report_template_gid := MAX(id) FROM civicrm_option_group WHERE name = 'report_template';

{if $multilingual}
   {foreach from=$locales item=locale}
      UPDATE civicrm_option_value SET label_{$locale} = 'Pledge Report (Detail)', description_{$locale} = 'Pledge Report' WHERE option_group_id = @report_template_gid AND value = 'pledge/summary';
   {/foreach}
{else}
      UPDATE civicrm_option_value SET label = 'Pledge Report (Detail)', description = 'Pledge Report' WHERE option_group_id = @report_template_gid AND value = 'pledge/summary';
{/if}

UPDATE civicrm_option_value SET name = 'CRM_Report_Form_Pledge_Detail', value = 'pledge/detail' WHERE option_group_id = @report_template_gid AND value = 'pledge/summary';

UPDATE civicrm_report_instance SET report_id = 'pledge/detail' WHERE report_id = 'pledge/summary';

SELECT @weight              := MAX(weight) FROM civicrm_option_value WHERE option_group_id = @report_template_gid;
SELECT @pledgeCompId        := MAX(id)     FROM civicrm_component where name = 'CiviPledge';
INSERT INTO civicrm_option_value
  (option_group_id, {localize field='label'}label{/localize}, value, name, weight, {localize field='description'}description{/localize}, is_active, component_id) VALUES
  (@report_template_gid, {localize}'Pledge Summary Report'{/localize}, 'pledge/summary', 'CRM_Report_Form_Pledge_Summary', @weight := @weight + 1, {localize}'Pledge Summary Report.'{/localize}, 1, @pledgeCompId);
