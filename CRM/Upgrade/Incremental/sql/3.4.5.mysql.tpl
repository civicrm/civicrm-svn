-- CRM-8370

ALTER TABLE `civicrm_action_log` CHANGE `repetition_number` `repetition_number` INT( 10 ) UNSIGNED NULL COMMENT 'Keeps track of the sequence number of this repetition.'
