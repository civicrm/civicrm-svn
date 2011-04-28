-- CRM-7796 

ALTER TABLE `civicrm_dashboard` ADD `fullscreen_url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'fullscreen url for dashlet';

UPDATE `civicrm_dashboard` SET fullscreen_url='civicrm/dashlet/activity&reset=1&snippet=4&context=dashletFullscreen' WHERE url='civicrm/dashlet/activity&reset=1&snippet=4';
UPDATE `civicrm_dashboard` SET fullscreen_url='civicrm/dashlet/myCases&reset=1&snippet=4&context=dashletFullscreen' WHERE url='civicrm/dashlet/myCases&reset=1&snippet=4';
UPDATE `civicrm_dashboard` SET fullscreen_url='civicrm/dashlet/allCases&reset=1&snippet=4&context=dashletFullscreen' WHERE url='civicrm/dashlet/allCases&reset=1&snippet=4';

-- CRM-7956
DELETE FROM civicrm_navigation where name = 'CiviCampaign';
DELETE FROM civicrm_navigation where name = 'Survey Types';
DELETE FROM civicrm_navigation where name = 'Campaign Types';
DELETE FROM civicrm_navigation where name = 'Campaign Status';
DELETE FROM civicrm_navigation where name = 'Engagement Index';

SELECT @domainID        := MIN(id) FROM civicrm_domain;
SELECT @administerID    := MAX(id) FROM civicrm_navigation where name = 'Administer';
SELECT @adminCampaignWeight := MAX(weight)+1 FROM civicrm_navigation where parent_id = @administerID;

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, NULL, '{ts escape="sql" skip="true"}CiviCampaign{/ts}', 'CiviCampaign', 'administer CiviCampaign,administer CiviCRM', 'AND', @administerID, '1', NULL, @adminCampaignWeight );

SET @adminCampaignID:=LAST_INSERT_ID();

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES     
    ( @domainID, 'civicrm/admin/campaign/surveyType&reset=1',                            '{ts escape="sql" skip="true"}Survey Types{/ts}',  'Survey Types', 'administer CiviCampaign',    '', @adminCampaignID, '1', NULL, 1 ),
    ( @domainID, 'civicrm/admin/options/campaign_type&group=campaign_type&reset=1',      '{ts escape="sql" skip="true"}Campaign Types{/ts}',  'Campaign Types', 'administer CiviCampaign',    '', @adminCampaignID, '1', NULL, 2 ),
    ( @domainID, 'civicrm/admin/options/campaign_status&group=campaign_status&reset=1',      '{ts escape="sql" skip="true"}Campaign Status{/ts}',  'Campaign Status', 'administer CiviCampaign',    '', @adminCampaignID, '1', NULL, 3 ),
    ( @domainID, 'civicrm/admin/options/engagement_index&group=engagement_index&reset=1', '{ts escape="sql" skip="true"}Engagement Index{/ts}',  'Engagement Index', 'administer CiviCampaign', '', @adminCampaignID, '1', NULL, 4 );

-- insert drupal wysiwyg editor option
SELECT @option_group_id_we := max(id) from civicrm_option_group where name = 'wysiwyg_editor';

INSERT INTO civicrm_option_value 
  ( option_group_id, label, value, name, grouping, filter, is_default, weight, description, is_optgroup, is_active, component_id, domain_id, visibility_id )
VALUES 
  ( @option_group_id_we, 'Drupal Default Editor', 4, NULL, NULL, 0, NULL, 4, NULL, 0, 1, 1, NULL, NULL );
   
