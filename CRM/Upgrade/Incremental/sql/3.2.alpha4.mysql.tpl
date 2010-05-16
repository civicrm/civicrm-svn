-- schema changes after 3.2 alpha4 tag
--  change is_hidden to is_tagset in civicrm_tag
ALTER TABLE `civicrm_tag` CHANGE `is_hidden` `is_tagset` TINYINT( 4 ) NULL DEFAULT '0';