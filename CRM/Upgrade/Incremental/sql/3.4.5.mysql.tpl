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

ALTER TABLE `civicrm_action_log` CHANGE `repetition_number` `repetition_number` INT( 10 ) UNSIGNED NULL COMMENT 'Keeps track of the sequence number of this repetition.';

-- CRM-8085
UPDATE civicrm_mailing SET domain_id = {$domainID} WHERE domain_id IS NULL;
