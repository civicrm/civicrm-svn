-- CRM-7796 

ALTER TABLE `civicrm_dashboard` ADD `fullscreen_url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'fullscreen url for dashlet';

UPDATE `civicrm_dashboard` SET fullscreen_url='civicrm/dashlet/activity&reset=1&snippet=4&context=dashletFullscreen' WHERE url='civicrm/dashlet/activity&reset=1&snippet=4';
UPDATE `civicrm_dashboard` SET fullscreen_url='civicrm/dashlet/myCases&reset=1&snippet=4&context=dashletFullscreen' WHERE url='civicrm/dashlet/myCases&reset=1&snippet=4';
UPDATE `civicrm_dashboard` SET fullscreen_url='civicrm/dashlet/allCases&reset=1&snippet=4&context=dashletFullscreen' WHERE url='civicrm/dashlet/allCases&reset=1&snippet=4';

