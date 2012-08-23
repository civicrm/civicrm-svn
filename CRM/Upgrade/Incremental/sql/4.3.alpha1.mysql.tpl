-- CRM-8507
ALTER TABLE civicrm_custom_field
  ADD UNIQUE INDEX `UI_name_custom_group_id` (`name`, `custom_group_id`);

--CRM-10473 Added Missing Provinces of Ningxia Autonomous Region of China
INSERT INTO `civicrm_state_province`(`country_id`, `abbreviation`, `name`) VALUES
(1045, 'YN', 'Yinchuan'),
(1045, 'SZ', 'Shizuishan'),
(1045, 'WZ', 'Wuzhong'),
(1045, 'GY', 'Guyuan'),
(1045, 'ZW', 'Zhongwei');
