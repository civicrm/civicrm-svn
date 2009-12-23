--Add permission for dashlet access

ALTER TABLE `civicrm_dashboard` ADD `is_reserved` TINYINT NULL DEFAULT '0' COMMENT 'Is this dashlet reserved?';

DELETE FROM `civicrm_dashboard` WHERE `id` BETWEEN 2 AND 5;

UPDATE `civicrm_dashboard` SET `permission` = 'access CiviCRM', `is_reserved` = 1 WHERE `id` = 1;

SELECT @domain_id := min(id) FROM civicrm_domain;
INSERT INTO `civicrm_dashboard` 
    ( `domain_id`, `label`, `url`, `content`, `permission`, `permission_operator`, `column_no`, `is_minimized`, `is_active`, `weight`, `created_date`, `is_fullscreen`) 
    VALUES 
    ( @domain_id, '{ts escape="sql"}Donor Report (Summary){/ts}'       , 'civicrm/report/instance/3&reset=1&section=1&snippet=4&charts=barChart',  NULL, 'access CiviCRM,access CiviContribute', 'AND', 0, 0,'1', 2, NULL, '0'),
    ( @domain_id, '{ts escape="sql"}Top Donors Report{/ts}'            , 'civicrm/report/instance/20&reset=1&section=2&snippet=4',                 NULL, 'access CiviCRM,access CiviContribute', 'AND', 0, 0,'1', 3, NULL, '1'),
    ( @domain_id, '{ts escape="sql"}Event Income Report (Summary){/ts}', 'civicrm/report/instance/13&reset=1&section=1&snippet=4&charts=pieChart', NULL, 'access CiviCRM,access CiviEvent'     , 'AND', 0, 0,'1', 4, NULL, '0'),
    ( @domain_id, '{ts escape="sql"}Membership Report (Summary){/ts}'  , 'civicrm/report/instance/9&reset=1&section=2&snippet=4',                  NULL, 'access CiviCRM,access CiviMember'    , 'AND', 0, 0,'1', 5, NULL, '1');
    