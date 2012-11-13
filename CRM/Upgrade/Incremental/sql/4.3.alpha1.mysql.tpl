{include file='../CRM/Upgrade/4.3.alpha1.msg_template/civicrm_msg_template.tpl'}
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

-- CRM-10553
ALTER TABLE civicrm_contact
  ADD COLUMN `created_date` timestamp NULL DEFAULT NULL
  COMMENT 'When was the contact was created.';
ALTER TABLE civicrm_contact
  ADD COLUMN `modified_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  COMMENT 'When was the contact (or closely related entity) was created or modified or deleted.';

-- CRM-9199
--
-- Table structure for table `civicrm_financial_type`
--

CREATE TABLE IF NOT EXISTS `civicrm_financial_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID of original financial_type so you can search this table by the financial_type.id and then select the relevant version based on the timestamp',
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Financial Type Name.',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Financial Type Description.',
  `is_deductible` tinyint(4) DEFAULT '1' COMMENT 'Is this financial type tax-deductible? If true, contributions of this type may be fully OR partially deductible - non-deductible amount is stored in the Contribution record.',
  `is_reserved` tinyint(4) DEFAULT NULL COMMENT 'Is this a predefined system object?',
  `is_active` tinyint(4) DEFAULT NULL COMMENT 'Is this property active?',
  `version_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'A Unix timestamp indicating when this version was created.',
  `is_current_revision` tinyint(4) DEFAULT '1' COMMENT 'is_current_revision',
  `original_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UI_name` (`name`),
  UNIQUE KEY `UI_id` (`id`),
  KEY `UI_is_current_revision` (`is_current_revision`),
  KEY `FK_civicrm_financial_type_original_id` (`original_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

--
-- Triggers `civicrm_financial_type`
--
DROP TRIGGER IF EXISTS `civicrm_financial_type_ad`;
DELIMITER //
CREATE TRIGGER `civicrm_financial_type_ad` AFTER UPDATE ON `civicrm_financial_type`
 FOR EACH ROW BEGIN
DECLARE financialTypeId INT;
  SELECT MAX(id) INTO financialTypeId FROM civicrm_financial_type;
  UPDATE civicrm_contribution_page SET financial_type_id = financialTypeId WHERE financial_type_id = NEW.id;
  UPDATE civicrm_event SET financial_type_id = financialTypeId WHERE financial_type_id = NEW.id;
  UPDATE civicrm_membership_type SET financial_type_id = financialTypeId WHERE financial_type_id = NEW.id;
  UPDATE civicrm_pledge SET financial_type_id = financialTypeId WHERE financial_type_id = NEW.id;
  UPDATE civicrm_contribution_recur SET financial_type_id = financialTypeId WHERE financial_type_id = NEW.id;
  UPDATE civicrm_product SET financial_type_id = financialTypeId WHERE financial_type_id = NEW.id;
  UPDATE civicrm_price_field SET financial_type_id = financialTypeId WHERE financial_type_id = NEW.id;
  UPDATE civicrm_price_field_value SET financial_type_id = financialTypeId WHERE financial_type_id = NEW.id;
  UPDATE civicrm_premiums_product SET financial_type_id = financialTypeId WHERE financial_type_id = NEW.id;
  UPDATE civicrm_entity_financial_account SET entity_id = financialTypeId WHERE entity_id = NEW.id;
END
//
DELIMITER ;

--
-- Constraints for table `civicrm_financial_type`
--
ALTER TABLE `civicrm_financial_type`
  ADD CONSTRAINT `FK_civicrm_financial_type_original_id` FOREIGN KEY (`original_id`) REFERENCES `civicrm_financial_type` (`id`);


--
-- Table structure for table `civicrm_entity_financial_account`
--

CREATE TABLE IF NOT EXISTS `civicrm_entity_financial_account` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Links to an entity_table like civicrm_financial_type',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Links to an id in the entity_table, such as vid in civicrm_financial_type',
  `account_relationship` int(10) unsigned NOT NULL COMMENT 'FK to a new civicrm_option_value (account_relationship)',
  `financial_account_id` int(10) unsigned NOT NULL COMMENT 'FK to the financial_account_id',
  PRIMARY KEY (`id`),
  KEY `FK_civicrm_entity_financial_account_financial_account_id` (`financial_account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;


--
-- Constraints for table `civicrm_entity_financial_account`
--
ALTER TABLE `civicrm_entity_financial_account`
  ADD CONSTRAINT `FK_civicrm_entity_financial_account_financial_account_id` FOREIGN KEY (`financial_account_id`) REFERENCES `civicrm_financial_account` (`id`);


-- CRM-8425
-- Rename table civicrm_contribution_type to civicrm_financial_account
RENAME TABLE `civicrm_contribution_type` TO `civicrm_financial_account`;

-- ADD fields w.r.t 10.6 mwb
ALTER TABLE `civicrm_financial_account`
ADD `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'Version identifier of financial_type' AFTER `name`,
ADD `financial_account_type_id` int(10) unsigned NOT NULL DEFAULT '3' COMMENT 'Version identifier of financial_type' AFTER `contact_id`,
ADD `parent_id` int(10) unsigned DEFAULT NULL COMMENT 'Parent ID in account hierarchy' AFTER `description`,
ADD `is_header_account` tinyint(4) DEFAULT NULL COMMENT 'Is this a header account which does not allow transactions to be posted against it directly, but only to its sub-accounts?' AFTER `parent_id`,
ADD `is_tax` tinyint(4) DEFAULT '0' COMMENT 'Is this account for taxes?' AFTER `is_deductible`,
ADD `tax_rate` decimal(9,8) DEFAULT '0.00' COMMENT 'The percentage of the total_amount that is due for this tax.' AFTER `is_tax`,
ADD `is_default` tinyint(4) DEFAULT NULL COMMENT 'Is this account the default one (or default tax one) for its financial_account_type?' AFTER `is_active`,
ADD CONSTRAINT `FK_civicrm_financial_account_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(id),
ADD CONSTRAINT `FK_civicrm_financial_account_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `civicrm_financial_account`(id);


-- CRM-8425

UPDATE civicrm_navigation SET  `label` = 'Financial Account', `name` = 'Financial Account', `url` = 'civicrm/admin/financial/financialAccount?reset=1' WHERE `name` = 'Contribution Types'


-- Insert an entry for financial_account_type in civicrm_option_group and for the the following financial account types in civicrm_option_value as per CRM-8425
INSERT INTO
   `civicrm_option_group` (`name`, `title`, `is_reserved`, `is_active`)
VALUES
('financial_account_type', '{localize}Financial Account Type{/localize}' , 1, 1)
('batch_modes' , {localize}'Batch Modes'{/localize}, 1, 1);

SELECT @option_group_id_fat := max(id) from civicrm_option_group where name = 'financial_account_type';

INSERT INTO
   `civicrm_option_value` (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `visibility_id`)
VALUES
 (@option_group_id_fat, '{ts escape="sql"}Asset{/ts}', 1, 'Asset', NULL, 0, 0, 1, 'Things you own', 0, 1, 1, 2, NULL),
   (@option_group_id_fat, '{ts escape="sql"}Liability{/ts}', 2, 'Liability', NULL, 0, 0, 2, 'Things you own, like a grant still to be disbursed', 0, 1, 1, 2, NULL),
   (@option_group_id_fat, '{ts escape="sql"}Revenue{/ts}', 3, 'Revenue', NULL, 0, 1, 3, 'Income from contributions and sales of tickets and memberships', 0, 1, 1, 2, NULL),
   (@option_group_id_fat, '{ts escape="sql"}Cost of Sales{/ts}', 4, 'Cost of Sales', NULL, 0, 0, 4, 'Costs incurred to get revenue, e.g. premiums for donations, dinner for a fundraising dinner ticket', 0, 1, 1, 2, NULL),
   (@option_group_id_fat, '{ts escape="sql"}Expenses{/ts}', 5, 'Expenses', NULL, 0, 0, 5, 'Things that are paid for that are consumable, e.g. grants disbursed', 0, 1, 1, 2, NULL);

-- CRM 9189 and CRM-8425 change fk's to financial_account.id in our branch that will need to be changed to an fk to financial_type.id
ALTER TABLE `civicrm_pledge`
DROP FOREIGN KEY FK_civicrm_pledge_contribution_type_id,
DROP INDEX FK_civicrm_pledge_contribution_type_id;

ALTER TABLE `civicrm_pledge`
CHANGE `contribution_type_id` `financial_type_id` int unsigned;

ALTER TABLE `civicrm_pledge`
ADD CONSTRAINT FK_civicrm_pledge_financial_type_id  FOREIGN KEY (`financial_type_id`) REFERENCES civicrm_financial_type (id);

ALTER TABLE `civicrm_membership_type`
DROP FOREIGN KEY FK_civicrm_membership_type_contribution_type_id,
DROP INDEX FK_civicrm_membership_type_contribution_type_id;

ALTER TABLE `civicrm_membership_type`
CHANGE `contribution_type_id` `financial_type_id` int unsigned;

ALTER TABLE `civicrm_membership_type`
ADD CONSTRAINT FK_civicrm_membership_type_financial_type_id  FOREIGN KEY (`financial_type_id`) REFERENCES civicrm_financial_type (id);

ALTER TABLE `civicrm_event`
CHANGE `contribution_type_id` `financial_type_id` int unsigned;

ALTER TABLE `civicrm_contribution`
DROP FOREIGN KEY FK_civicrm_contribution_contribution_type_id,
DROP INDEX FK_civicrm_contribution_contribution_type_id;

ALTER TABLE `civicrm_contribution`
CHANGE `contribution_type_id` `financial_type_id` int unsigned;

ALTER TABLE `civicrm_contribution`
ADD CONSTRAINT FK_civicrm_contribution_financial_type_id FOREIGN KEY (`financial_type_id`) REFERENCES civicrm_financial_type (id);

ALTER TABLE `civicrm_batch`
ADD `saved_search_id` INT(10) UNSIGNED DEFAULT NULL COMMENT 'FK to Saved Search ID';

ALTER TABLE `civicrm_batch`
ADD CONSTRAINT FK_civicrm_batch_saved_search_id FOREIGN KEY (`saved_search_id`) REFERENCES civicrm_ saved_search(id);

ALTER TABLE `civicrm_contribution_recur`
DROP FOREIGN KEY FK_civicrm_contribution_recur_contribution_type_id,
DROP INDEX FK_civicrm_contribution_recur_contribution_type_id;

ALTER TABLE `civicrm_contribution_recur`
CHANGE `contribution_type_id` `financial_type_id` int unsigned;

ALTER TABLE `civicrm_contribution_recur`
ADD CONSTRAINT FK_civicrm_contribution_recur_financial_type_id FOREIGN KEY (`financial_type_id`) REFERENCES civicrm_financial_type (id);


-- CRM-9083
ALTER TABLE `civicrm_financial_trxn`
DROP FOREIGN KEY FK_civicrm_financial_trxn_to_account_id,
DROP INDEX FK_civicrm_financial_trxn_to_account_id;

ALTER TABLE `civicrm_financial_trxn` CHANGE `to_account_id` `to_financial_account_id` int unsigned;

ALTER TABLE `civicrm_financial_trxn`
ADD CONSTRAINT FK_civicrm_financial_trxn_to_financial_type_id FOREIGN KEY (`to_financial_account_id`) REFERENCES civicrm_financial_account (id);

ALTER TABLE `civicrm_financial_trxn`
DROP FOREIGN KEY FK_civicrm_financial_trxn_from_account_id,
DROP INDEX FK_civicrm_financial_trxn_from_account_id;

ALTER TABLE `civicrm_financial_trxn` CHANGE `from_account_id` `from_financial_account_id` int unsigned;

ALTER TABLE `civicrm_financial_trxn`
ADD CONSTRAINT FK_civicrm_financial_trxn_from_financial_account_id FOREIGN KEY (`from_financial_account_id`) REFERENCES civicrm_financial_type (id);

ALTER TABLE `civicrm_financial_trxn` ADD `payment_processor_id` int unsigned COMMENT 'Payment Processor for this contribution Page';

--
-- Constraints for table `civicrm_financial_trxn`
--
ALTER TABLE `civicrm_financial_trxn`
  ADD CONSTRAINT `FK_civicrm_financial_trxn_payment_processor_id` FOREIGN KEY (`payment_processor_id`) REFERENCES `civicrm_payment_processor` (`id`) ON DELETE SET NULL;

-- Drop index for civicrm_financial_trxn.trxn_id and set default to null
ALTER TABLE `civicrm_financial_trxn` CHANGE `trxn_id` `trxn_id` varchar( 255 ) DEFAULT NULL ;
ALTER TABLE `civicrm_financial_trxn` DROP INDEX UI_ft_trxn_id;

-- remove trxn_typ field
ALTER TABLE `civicrm_financial_trxn` DROP `trxn_type`;

-- Fill in the payment_processor_id based on a lookup using the payment_processor field
UPDATE `civicrm_payment_processor` cppt,  `civicrm_financial_trxn` cft
SET cft.`payment_processor_id` = cppt.`id`
WHERE cft.`payment_processor_id` = cppt.`payment_processor_type` and `is_test` = 0;

-- remove payment_processor field
ALTER TABLE `civicrm_financial_trxn` DROP `payment_processor`;

-- Historical manual contributions in `civicrm_entity_financial_trxn` and `civicrm_financial_trxn`

UPDATE `civicrm_financial_trxn`, `civicrm_contribution` SET civicrm_financial_trxn.status_id = civicrm_contribution.contribution_status_id,  civicrm_financial_trxn.to_financial_account_id = civicrm_contribution.financial_type_id WHERE civicrm_financial_trxn.trxn_id = civicrm_contribution.trxn_id;

INSERT INTO `civicrm_financial_trxn` ( `trxn_date`, `status_id`, `total_amount`, `fee_amount`, `net_amount`, `currency`, `trxn_id` )
SELECT  c.receive_date, c.contribution_status_id, c.total_amount, c.fee_amount, c.net_amount, c.currency, c.id
FROM `civicrm_contribution` c LEFT JOIN `civicrm_entity_financial_trxn` eft ON c.id = eft.entity_id AND eft.entity_table = 'civicrm_contribution'
WHERE eft.entity_id IS NULL AND c.contribution_status_id = 1;

INSERT INTO `civicrm_entity_financial_trxn` ( `entity_table`, `entity_id`,  `financial_trxn_id`,  `amount`  )
SELECT 'civicrm_contribution', ft.trxn_id, ft.id, ft.total_amount
FROM `civicrm_financial_trxn` ft LEFT JOIN `civicrm_entity_financial_trxn` eft ON ft.id = eft.financial_trxn_id AND eft.entity_table = 'civicrm_contribution' WHERE eft.financial_trxn_id IS NULL;

UPDATE `civicrm_financial_trxn` as cft ,`civicrm_contribution` as c
SET cft.trxn_id = c.trxn_id
WHERE c.id = cft.trxn_id;


-- CRM-9199


-- Insert menu item at Administer > CiviContribute, below the section break below Premiums (Thank-you Gifts), just above Financial Account.

SELECT @parent_id := id from `civicrm_navigation` where name = 'CiviContribute';
SELECT @add_weight_id := weight from `civicrm_navigation` where `name` = 'Financial Account' and `parent_id` = @parent_id;

UPDATE `civicrm_navigation`
SET `weight` = `weight`+1
WHERE `parent_id` = @parent_id
AND `weight` >= @add_weight_id;

INSERT INTO `civicrm_navigation`
        ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
	( {$domainID}, 'civicrm/admin/financial/financialType&reset=1',      '{ts escape="sql" skip="true"}Financial Type{/ts}',         'Financial Type',        'access CiviContribute,administer CiviCRM', 'AND', @parent_id, '1', NULL, @add_weight_id );


--
-- Data migration from civicrm_contibution_type to civicrm_financial_account, civicrm_financial_type, civicrm_entity_financial_account
--

SELECT @revaccount  := max(id) FROM civicrm_option_value WHERE name = 'Revenue' AND option_group_id =  @option_group_id_fat;

SELECT @domainContactId := contact_id from civicrm_domain where id = {$domainID};

INSERT INTO `civicrm_financial_account`
       (`id`, `name`, `description`, `is_deductible`, `is_reserved`, `is_active`, `financial_account_type_id`, `contact_id`)
SELECT id, name, description, is_deductible, is_reserved, is_active, @revaccount, @domainContactId FROM `civicrm_financial_type`;

--
-- Create an entry for account_relationship in option groups
--

INSERT INTO
   `civicrm_option_group` (`name`, `title`, `is_reserved`, `is_active`)
VALUES
   ('account_relationship'          , '{ts escape="sql"}Account Relationship{/ts}'               , 1, 1),
   ('financial_item_status'         , '{ts escape="sql"}}Financial Item Status{/ts}'             , 1, 1);
   
SELECT @option_group_id_arel           := max(id) from civicrm_option_group where name = 'account_relationship';
SELECT @option_group_id_financial_item_status := max(id) from civicrm_option_group where name = 'financial_item_status';

INSERT INTO
   `civicrm_option_value` (`option_group_id`, `label`, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id`, `visibility_id`)
VALUES
    (@option_group_id_arel, '{ts escape="sql"}Income Account is{/ts}', 1, 'Income Account is', NULL, 0, 1, 1, 'Income Account is', 0, 1, 1, 2, NULL),
    (@option_group_id_arel, '{ts escape="sql"}Credit/Contra Account is{/ts}', 2, 'Credit/Contra Account is', NULL, 0, 0, 2, 'Credit/Contra Account is', 0, 1, 0, 2, NULL),
    (@option_group_id_arel, '{ts escape="sql"}AR Account is{/ts}', 3, 'AR Account is', NULL, 0, 0, 3, 'AR Account is', 0, 1, 1, 2, NULL),
    (@option_group_id_arel, '{ts escape="sql"}Credit Liability Account is{/ts}', 4, 'Credit Liability Account is', NULL, 0, 0, 4, 'Credit Liability Account is', 0, 1, 0, 2, NULL),
    (@option_group_id_arel, '{ts escape="sql"}Expense Account is{/ts}', 5, 'Expense Account is', NULL, 0, 0, 5, 'Expense Account is', 0, 1, 1, 2, NULL),
    (@option_group_id_arel, '{ts escape="sql"}Asset Account of{/ts}', 6, 'Asset Account of', NULL, 0, 0, 6, 'Asset Account of', 0, 1, 1, 2, NULL),
    (@option_group_id_arel, '{ts escape="sql"}Cost of Sales Account is{/ts}', 7, 'Cost of Sales Account is', NULL, 0, 0, 7, 'Cost of Sales Account is', 0, 1, 1, 2, NULL),
    (@option_group_id_arel, '{ts escape="sql"}Premiums Inventory Account is{/ts}', 8, 'Premiums Inventory Account is', NULL, 0, 0, 8, 'Premiums Inventory Account is', 0, 1, 1, 2, NULL),
    (@option_group_id_arel, '{ts escape="sql"}Discounts Account is{/ts}', 9, 'Discounts Account is', NULL, 0, 0, 9, 'Discounts Account is', 0, 1, 1, 2, NULL),

-- Financial Item Status
    (@option_group_id_financial_item_status, '{ts escape="sql"}Paid{/ts}', 1, 'Paid', NULL, 0, 0, 1, 'Paid', 0, 1, 1, 2, NULL),
    (@option_group_id_financial_item_status, '{ts escape="sql"}Partially paid{/ts}', 2, 'Partially paid', NULL, 0, 0, 2, 'Partially paid', 0, 1, 1, 2, NULL),
    (@option_group_id_financial_item_status, '{ts escape="sql"}Unpaid{/ts}', 3, 'Unpaid', NULL, 0, 0, 1, 'Unpaid', 0, 1, 1, 2, NULL);


SELECT @option_value_rel_id  := value FROM `civicrm_option_value` WHERE `option_group_id` = @option_group_id_arel AND `name` = 'Income Account is';




-- CRM-9306
INSERT INTO `civicrm_navigation`
        ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, CONCAT('civicrm/report/instance/', @instanceID,'&reset=1'), '{ts escape="sql"}Mailing Detail Report{/ts}', 'Mailing Detail Report', 'administer CiviMail', 'OR', @reportlastID, '1', NULL, @nav_max_weight+1 );
UPDATE civicrm_report_instance SET navigation_id = LAST_INSERT_ID() WHERE id = @instanceID;

-- CRM-9600
ALTER TABLE `civicrm_custom_group` CHANGE `extends_entity_column_value` `extends_entity_column_value` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'linking custom group for dynamic object.';

-- CRM-9534
ALTER TABLE `civicrm_prevnext_cache` ADD COLUMN is_selected tinyint(4) DEFAULT '0';


-- CRM-9731

ALTER TABLE `civicrm_payment_processor` ADD `payment_processor_type_id` int(10) unsigned NULL AFTER `description`,
ADD CONSTRAINT `FK_civicrm_payment_processor_payment_processor_type_id` FOREIGN KEY (`payment_processor_type_id`) REFERENCES `civicrm_payment_processor_type` (`id`);

UPDATE `civicrm_payment_processor` , `civicrm_payment_processor_type`
SET payment_processor_type_id = `civicrm_payment_processor_type`.id
WHERE payment_processor_type = `civicrm_payment_processor_type`.name;

ALTER TABLE `civicrm_payment_processor` DROP `payment_processor_type`;

-- CRM-9730

--
-- Table structure for table `civicrm_financial_item`
--

CREATE TABLE IF NOT EXISTS `civicrm_financial_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date and time the item was created',
  `transaction_date` datetime NOT NULL COMMENT 'Date and time of the source transaction',
  `contact_id` int(10) unsigned NOT NULL COMMENT 'FK to Contact ID of contact the item is from',
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Human readable description of this item, to ease display without lookup of source item.',
  `amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT 'Total amount of this item',
  `currency` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Currency for the amount',
  `financial_account_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to civicrm_financial_account',
  `status_id` int(10) unsigned DEFAULT NULL COMMENT 'Payment status: test, paid, part_paid, unpaid (if empty assume unpaid)',
  `entity_table` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'The table providing the source of this item such as civicrm_line_item',
  `entity_id` int(10) unsigned DEFAULT NULL COMMENT 'The specific source item that is responsible for the creation of this financial_item',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UI_id` (`id`),
  KEY `IX_created_date` (`created_date`),
  KEY `IX_transaction_date` (`transaction_date`),
  KEY `IX_entity` (`entity_table`,`entity_id`),
  KEY `FK_civicrm_financial_item_contact_id` (`contact_id`),
  KEY `FK_civicrm_financial_item_financial_account_id` (`financial_account_id`)
);

--
-- Constraints for table `civicrm_financial_item`
--
ALTER TABLE `civicrm_financial_item`
  ADD CONSTRAINT `FK_civicrm_financial_item_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`),
  ADD CONSTRAINT `FK_civicrm_financial_item_financial_account_id` FOREIGN KEY (`financial_account_id`) REFERENCES `civicrm_financial_account` (`id`);

ALTER TABLE `civicrm_price_field_value` ADD `deductible_amount` DECIMAL( 20, 2 ) NOT NULL COMMENT 'Tax-deductible portion of the amount';

ALTER TABLE `civicrm_line_item` ADD `deductible_amount` DECIMAL( 20, 2 ) NOT NULL COMMENT 'Tax-deductible portion of the amount';

ALTER TABLE `civicrm_price_field`  ADD
`financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Financial Type.',
  ADD CONSTRAINT `FK_civicrm_price_field_financial_type_id` FOREIGN KEY (`financial_type_id`) REFERENCES `civicrm_financial_type` (`id`);

ALTER TABLE `civicrm_price_field_value` ADD
`financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Financial Type.',
 ADD CONSTRAINT `FK_civicrm_price_field_value_financial_type_id` FOREIGN KEY (`financial_type_id`) REFERENCES `civicrm_financial_type` (`id`);

ALTER TABLE `civicrm_line_item` ADD
`financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Financial Type.',
 ADD CONSTRAINT `FK_civicrm_line_item_financial_type_id` FOREIGN KEY (`financial_type_id`) REFERENCES `civicrm_financial_type` (`id`);

ALTER TABLE `civicrm_grant` ADD
`financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Financial Type.',
 ADD CONSTRAINT `FK_civicrm_grant_financial_type_id` FOREIGN KEY (`financial_type_id`) REFERENCES `civicrm_financial_type` (`id`);

ALTER TABLE `civicrm_contribution_recur` ADD
`financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Financial Type.',
ADD CONSTRAINT `FK_civicrm_contribution_recur_financial_type_id` FOREIGN KEY (`financial_type_id`) REFERENCES `civicrm_financial_type` (`id`);

ALTER TABLE `civicrm_product` ADD
`financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Financial Type.',
ADD CONSTRAINT `FK_civicrm_product_financial_type_id` FOREIGN KEY (`financial_type_id`) REFERENCES `civicrm_financial_type` (`id`);

ALTER TABLE `civicrm_premiums_product` ADD
`financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Financial Type.',
ADD CONSTRAINT `FK_civicrm_premiums_product_financial_type_id` FOREIGN KEY (`financial_type_id`) REFERENCES `civicrm_financial_type` (`id`);

ALTER TABLE `civicrm_contribution_product` ADD
`financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Financial Type.',
ADD CONSTRAINT `FK_civicrm_contribution_product_financial_type_id` FOREIGN KEY (`financial_type_id`) REFERENCES `civicrm_financial_type` (`id`);

ALTER TABLE `civicrm_payment_processor` ADD
`financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Financial Type.';


ALTER TABLE `civicrm_payment_processor`
 ADD CONSTRAINT `FK_civicrm_payment_processor_financial_type_id` FOREIGN KEY (`financial_type_id`) REFERENCES `civicrm_financial_type` (`id`);

UPDATE `civicrm_financial_account` SET `is_default` =0;


-- Change loc_block_id to contact_id

ALTER TABLE `civicrm_domain` CHANGE `contact_id` `contact_id` INT( 10 ) UNSIGNED NULL DEFAULT NULL COMMENT 'FK to Contact ID. This is specifically not an FK to avoid circular constraints',
 ADD CONSTRAINT `FK_civicrm_domain_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`);

-- CRM-9730

ALTER TABLE `civicrm_price_field_value` ADD `deductible_amount` DECIMAL( 20, 2 ) NOT NULL COMMENT 'Tax-deductible portion of the amount';

ALTER TABLE `civicrm_line_item` ADD `deductible_amount` DECIMAL( 20, 2 ) NOT NULL COMMENT 'Tax-deductible portion of the amount';


ALTER TABLE `civicrm_payment_processor` ADD
`financial_type_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Financial Type.';


ALTER TABLE `civicrm_payment_processor`
 ADD CONSTRAINT `FK_civicrm_payment_processor_financial_type_id` FOREIGN KEY (`financial_type_id`) REFERENCES `civicrm_financial_type` (`id`);

UPDATE `civicrm_financial_account` SET `is_default` =0;

SELECT @opval := value FROM civicrm_option_value WHERE name = 'Revenue' and option_group_id = @option_group_id_fat;
SELECT @opexp := value FROM civicrm_option_value WHERE name = 'Expenses' and option_group_id = @option_group_id_fat;
SELECT @opAsset := value FROM civicrm_option_value WHERE name = 'Asset' and option_group_id = @option_group_id_fat;
SELECT @opLiability := value FROM civicrm_option_value WHERE name = 'Liability' and option_group_id = @option_group_id_fat;
SELECT @opCost := value FROM civicrm_option_value WHERE name = 'Cost of Sales' and option_group_id = @option_group_id_fat;

-- CRM-11127
UPDATE civicrm_financial_account SET name = 'Donations', description = 'Default account for donations', accounting_code = 4200 WHERE name = 'Donation';
UPDATE civicrm_financial_account SET description = 'Default account for event ticket sales', accounting_code =4300 WHERE name = 'Event Fee';
UPDATE civicrm_financial_account SET description = 'Sample account for recording payments to a campaign', accounting_code = 4100 WHERE name = 'Campaign Contribution';
UPDATE civicrm_financial_account SET description = 'Default account for membership sales', accounting_code = 4400 WHERE name = 'Member Dues';

INSERT INTO
   `civicrm_financial_account` (`name`, `contact_id`, `financial_account_type_id`, `description`, `accounting_code`, `is_reserved`, `is_active`, `is_deductible`, `is_default`)
VALUES
  ('{ts escape="sql"}Banking Fees{/ts}'         , @domainContactId, @opexp, 'Payment processor fees and manually recorded banking fees', '5200', 0, 1, 0, 0),
  ('{ts escape="sql"}Deposit bank account{/ts}' , @domainContactId, @opAsset, 'All manually recorded cash and cheques go to this account', '1100', 0, 1, 0, 0),
  ('{ts escape="sql"}Accounts Receivable{/ts}'  , @domainContactId, @opAsset, 'Amounts to be received later (eg pay later event revenues)', '1200', 0, 1, 0, 1),
  ('{ts escape="sql"}Accounts Payable{/ts}'     , @domainContactId, @opLiability, 'Amounts to be paid out such as grants and refunds', '2200', 0, 1, 0, 0),
  ('{ts escape="sql"}Checking Account{/ts}'     , @domainContactId, @opAsset, 'Bank accounts against which checks to pay grants, refunds, etc are written', '1100', 0, 1, 0, 0),
  ('{ts escape="sql"}Premiums{/ts}'             , @domainContactId, @opCost, 'Account to record cost of premiums provided to payors', '5100', 0, 1, 0, 0),
  ('{ts escape="sql"}Premiums inventory{/ts}'   , @domainContactId, @opAsset, 'Account representing value of premiums inventory', '1375', 0, 1, 0, 0),
  ('{ts escape="sql"}Discounts{/ts}'            , @domainContactId, @opval, 'Contra-revenue account for amounts discounted from sales', '4900', 0, 1, 0, 0),
  ('{ts escape="sql"}Refunds{/ts}'              , @domainContactId, @opval, 'Contra-revenue account for amounts refunded', '4800', 0, 1, 0, 0);

-- Change loc_block_id to contact_id

ALTER TABLE `civicrm_domain` CHANGE `contact_id` `contact_id` INT( 10 ) UNSIGNED NULL DEFAULT NULL COMMENT 'FK to Contact ID. This is specifically not an FK to avoid circular constraints',

 ADD CONSTRAINT `FK_civicrm_domain_contact_id` FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact` (`id`);

-- CRM-9923 and CRM-11037

SELECT @option_group_id_batch_status   := max(id) from civicrm_option_group where name = 'batch_status';

SELECT @weight                 := MAX(value) FROM civicrm_option_value WHERE option_group_id = @option_group_id_batch_status;

INSERT INTO
   `civicrm_option_value` (`option_group_id`, {localize field='label'}label{/localize}, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`)
VALUES
   (@option_group_id_batch_status, {localize}'Data Entry'{/localize}, @weight = @weight + 1, 'Data Entry', NULL, 0, 0, @weight = @weight + 1),
   (@option_group_id_batch_status, {localize}'Reopened'{/localize}, @weight = @weight + 1, 'Reopened', NULL, 0, 0, @weight = @weight + 1),
   (@option_group_id_batch_status, {localize}'Exported'{/localize}, @weight = @weight + 1, 'Exported' , NULL, 0, 0, @weight = @weight + 1);

-- Insert Batch Modes.

SELECT @option_group_id_batch_modes   := max(id) from civicrm_option_group where name = 'batch_modes';
INSERT INTO
   `civicrm_option_value` (`option_group_id`, {localize field='label'}label{/localize}, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`)
VALUES
   (@option_group_id_batch_modes, {localize}'Manual Batch'{/localize}, 1, 'Manual Batch', NULL, 0, 0, 1),
   (@option_group_id_batch_modes, {localize}'Automatic Batch'{/localize}, 2, 'Automatic Batch' , NULL, 0, 0, 2);

-- Table structure for table `civicrm_financial_batch`

CREATE TABLE IF NOT EXISTS `civicrm_financial_batch` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique Address ID',
  `batch_id` int(10) unsigned NOT NULL COMMENT 'fk to civicrm_batch.id',
  `payment_instrument_id` int(10) unsigned DEFAULT NULL COMMENT 'fk to Payment Instrument options in civicrm_option_values',
  `manual_number_trans` int(10) unsigned DEFAULT NULL,
  `manual_total` decimal(20,2) DEFAULT NULL,
  `exported_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_civicrm_financial_batch_batch_id` (`batch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
  ALTER TABLE `civicrm_financial_batch`
  ADD CONSTRAINT `FK_civicrm_financial_batch_batch_id` FOREIGN KEY (`batch_id`) REFERENCES `civicrm_contact` (`id`);

-- CRM-10296
DELETE FROM civicrm_job WHERE `api_action` = 'process_membership_reminder_date';
ALTER TABLE civicrm_membership 			DROP COLUMN reminder_date;
ALTER TABLE civicrm_membership_log 	DROP COLUMN renewal_reminder_date;
ALTER TABLE civicrm_membership_type
	DROP COLUMN renewal_reminder_day,
	DROP FOREIGN KEY FK_civicrm_membership_type_renewal_msg_id,
	DROP INDEX FK_civicrm_membership_type_renewal_msg_id,
	DROP COLUMN renewal_msg_id,
	DROP COLUMN autorenewal_msg_id;

-- CRM-10738
ALTER TABLE civicrm_msg_template
      CHANGE msg_text msg_text LONGTEXT NULL COMMENT 'Text formatted message',
      CHANGE msg_html msg_html LONGTEXT NULL COMMENT 'HTML formatted message';

-- CRM-10860
ALTER TABLE civicrm_contribution_page ADD COLUMN is_recur_installments tinyint(4) DEFAULT '0';
UPDATE civicrm_contribution_page SET is_recur_installments='1';

-- CRM-10944

SELECT @contributionlastID := max(id) from civicrm_navigation where name = 'Contributions';

SELECT @pledgeWeight := weight from civicrm_navigation where name = 'Pledges' and parent_id = @contributionlastID;


UPDATE `civicrm_navigation`
SET `weight` = `weight`+1
WHERE `parent_id` = @contributionlastID
AND `weight` > @pledgeWeight;

INSERT INTO civicrm_navigation
    (domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight)
VALUES
    ({$domainID}, NULL, '{ts escape="sql" skip="true"}Financial Transaction Batches{/ts}',  'Financial Transaction Batches', 'access CiviContribute', '', @contributionlastID, '1',  1,   @pledgeWeight+1);

SET @financialTransactionID:=LAST_INSERT_ID();


INSERT INTO civicrm_navigation
    (domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ({$domainID}, 'civicrm/financial/batch&reset=1&action=add',                             '{ts escape="sql" skip="true"}New Batch{/ts}',          'New Batch',         'access CiviContribute', 'AND',  @financialTransactionID, '1', NULL, 1),
    ({$domainID}, 'civicrm/contact/search/custom?reset=1&csid=16&context=Open&force=1', '{ts escape="sql" skip="true"}Open Batches{/ts}',          'Open Batches',         'access CiviContribute', 'AND',  @financialTransactionID, '1', NULL, 2),
    ({$domainID}, 'civicrm/contact/search/custom?reset=1&csid=16&context=Closed&force=1', '{ts escape="sql" skip="true"}Closed Batches{/ts}',          'Closed Batches',         'access CiviContribute', 'AND',  @financialTransactionID, '1', NULL, 3),
    ({$domainID}, 'civicrm/contact/search/custom?reset=1&csid=16&context=Exported&force=1', '{ts escape="sql" skip="true"}Exported Batches{/ts}',          'Exported Batches',         'access CiviContribute', 'AND',  @financialTransactionID, '1', NULL, 4);

-- CRM-10863
SELECT @country_id := id from civicrm_country where name = 'Luxembourg' AND iso_code = 'LU';
INSERT IGNORE INTO `civicrm_state_province`(`country_id`, `abbreviation`, `name`) VALUES
(@country_id, 'L', 'Luxembourg');

-- CRM-10899
{if $multilingual}
  {foreach from=$locales item=locale}
    UPDATE civicrm_option_group SET title_{$locale} = '{ts escape="sql"}Currencies Enabled{/ts}' WHERE name = "currencies_enabled";
  {/foreach}
{else}
    UPDATE civicrm_option_group SET title = '{ts escape="sql"}Currencies Enabled{/ts}' WHERE name = "currencies_enabled";
{/if}

-- CRM-11047
ALTER TABLE civicrm_job DROP COLUMN api_prefix;

-- CRM-11122
ALTER TABLE `civicrm_discount`
DROP FOREIGN KEY FK_civicrm_discount_option_group_id,
DROP INDEX FK_civicrm_discount_option_group_id;

ALTER TABLE `civicrm_discount` CHANGE `option_group_id` `price_set_id` INT( 10 ) UNSIGNED;

ALTER TABLE `civicrm_discount`
  ADD CONSTRAINT `FK_civicrm_discount_price_set_id` FOREIGN KEY (`price_set_id`) REFERENCES `civicrm_price_set` (`id`) ON DELETE CASCADE;

-- CRM-11068
ALTER TABLE civicrm_group
  ADD refresh_date datetime default NULL COMMENT 'Date and time when we need to refresh the cache next.' AFTER `cache_date`;

INSERT INTO `civicrm_job`
    ( domain_id, run_frequency, last_run, name, description, api_entity, api_action, parameters, is_active )
VALUES
    ( {$domainID}, 'Always' , NULL, '{ts escape="sql" skip="true"}Rebuild Smart Group Cache{/ts}', '{ts escape="sql" skip="true"}Rebuilds the smart group cache.{/ts}', 'job', 'group_rebuild', '{ts escape="sql" skip="true"}limit=Number optional-Limit the number of smart groups rebuild{/ts}', 0);

-- CRM-11117
INSERT IGNORE INTO `civicrm_setting` (`group_name`, `name`, `value`, `domain_id`, `is_domain`) VALUES ('CiviCRM Preferences', 'activity_assignee_notification_ics', 's:1:"0";', {$domainID}, '1');

-- CRM-10885
ALTER TABLE civicrm_dedupe_rule_group
  ADD used enum('Unsupervised','Supervised','General') COLLATE utf8_unicode_ci NOT NULL COMMENT 'Whether the rule should be used for cases where usage is Unsupervised, Supervised OR General(programatically)' AFTER threshold;

UPDATE civicrm_dedupe_rule_group
  SET used = 'General' WHERE is_default = 0;

UPDATE civicrm_dedupe_rule_group
    SET used = CASE level
        WHEN 'Fuzzy' THEN 'Supervised'
        WHEN 'Strict'   THEN 'Unsupervised'
    END
WHERE is_default = 1;

UPDATE civicrm_dedupe_rule_group
  SET name = CONCAT_WS('', `contact_type`, `used`)
WHERE is_default = 1 OR is_reserved = 1;

UPDATE civicrm_dedupe_rule_group
  SET  title = 'Name and Email'
WHERE contact_type IN ('Organization', 'Household') AND used IN ('Unsupervised', 'Supervised');

UPDATE civicrm_dedupe_rule_group
    SET title = CASE used
        WHEN 'Supervised' THEN 'Name and Email (reserved)'
        WHEN 'Unsupervised'   THEN 'Email (reserved)'
         WHEN 'General' THEN 'Name and Address (reserved)'
    END
WHERE contact_type = 'Individual' AND is_reserved = 1;

ALTER TABLE civicrm_dedupe_rule_group DROP COLUMN level;

-- CRM-10771
ALTER TABLE civicrm_uf_field
  ADD `is_multi_summary` tinyint(4) DEFAULT '0' COMMENT 'Include in multi-record listing?';

-- CRM-1115
-- note that country names are not translated in the DB
SELECT @region_id   := max(id) from civicrm_worldregion where name = "Europe and Central Asia";
INSERT INTO civicrm_country (name,iso_code,region_id,is_province_abbreviated) VALUES("Kosovo", "XK", @region_id, 0);

UPDATE civicrm_country SET name = 'Libya' WHERE name LIKE 'Libyan%';
UPDATE civicrm_country SET name = 'Congo, Republic of the' WHERE name = 'Congo';

-- CRM-10926

SELECT @option_value_rel_id_exp  := value FROM `civicrm_option_value` WHERE `option_group_id` = @option_group_id_arel AND `name` = 'Expense Account is';
SELECT @option_value_rel_id_ar  := value FROM `civicrm_option_value` WHERE `option_group_id` = @option_group_id_arel AND `name` = 'AR Account is';
SELECT @option_value_rel_id_as  := value FROM `civicrm_option_value` WHERE `option_group_id` = @option_group_id_arel AND `name` = 'Asset Account of';

SELECT @financial_account_id_bf	       := max(id) FROM `civicrm_financial_account` WHERE `name` = 'Banking Fees';
SELECT @financial_account_id_ap	       := max(id) FROM `civicrm_financial_account` WHERE `name` = 'Accounts Receivable';
SELECT @financial_account_id_ar	       := max(id) FROM `civicrm_financial_account` WHERE `name` = 'Deposit bank account';


INSERT INTO `civicrm_entity_financial_account`
     ( entity_table, entity_id, account_relationship, financial_account_id )
SELECT 'civicrm_financial_type', ft.id, @option_value_rel_id, fa.id
FROM `civicrm_financial_type` as ft LEFT JOIN `civicrm_financial_account` as fa ON ft.id = fa.id;

-- Banking Fees
INSERT INTO `civicrm_entity_financial_account`
     ( entity_table, entity_id, account_relationship, financial_account_id )
SELECT 'civicrm_financial_type', ft.id, @option_value_rel_id_exp,  @financial_account_id_bf
FROM `civicrm_financial_type` as ft;

-- Accounts Receivable
INSERT INTO `civicrm_entity_financial_account`
     ( entity_table, entity_id, account_relationship, financial_account_id )
SELECT 'civicrm_financial_type', ft.id, @option_value_rel_id_ar, @financial_account_id_ap
FROM `civicrm_financial_type` as ft;

-- Deposit Bank account
INSERT INTO `civicrm_entity_financial_account`
     ( entity_table, entity_id, account_relationship, financial_account_id )
SELECT 'civicrm_financial_type', ft.id, @option_value_rel_id_as, @financial_account_id_ar
FROM `civicrm_financial_type` as ft;


-- CRM-10621 Add component report links to reports menu for upgrade
SELECT @reportlastID       := MAX(id) FROM civicrm_navigation where name = 'Reports';
SELECT @max_weight     := MAX(ROUND(weight)) from civicrm_navigation WHERE parent_id = @reportlastID;

INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, 'civicrm/report/list&compid=99&reset=1', '{ts escape="sql" skip="true"}Contact Reports{/ts}', 'Contact Reports', 'administer CiviCRM', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1) );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, 'civicrm/report/list&compid=2&reset=1', '{ts escape="sql" skip="true"}Contribution Reports{/ts}', 'Contribution Reports', 'access CiviContribute', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1) );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, 'civicrm/report/list&compid=6&reset=1', '{ts escape="sql" skip="true"}Pledge Reports{/ts}', 'Pledge Reports', 'access CiviPledge', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1) );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, 'civicrm/report/list&compid=1&reset=1', '{ts escape="sql" skip="true"}Event Reports{/ts}', 'Event Reports', 'access CiviEvent', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1));
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, 'civicrm/report/list&compid=4&reset=1', '{ts escape="sql" skip="true"}Mailing Reports{/ts}', 'Mailing Reports', 'access CiviMail', '', @reportlastID, '1', 0,   (SELECT @max_weight := @max_weight+1));
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, 'civicrm/report/list&compid=3&reset=1', '{ts escape="sql" skip="true"}Membership Reports{/ts}', 'Membership Reports', 'access CiviMember', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1));
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, 'civicrm/report/list&compid=9&reset=1', '{ts escape="sql" skip="true"}Campaign Reports{/ts}', 'Campaign Reports', 'interview campaign contacts,release campaign contacts,reserve campaign contacts,manage campaign,administer CiviCampaign,gotv campaign contacts', 'OR', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1));
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, 'civicrm/report/list&compid=7&reset=1', '{ts escape="sql" skip="true"}Case Reports{/ts}', 'Case Reports', 'access my cases and activities,access all cases and activities,administer CiviCase', 'OR', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1) );
INSERT INTO civicrm_navigation
    ( domain_id, url, label, name, permission, permission_operator, parent_id, is_active, has_separator, weight )
VALUES
    ( {$domainID}, 'civicrm/report/list&compid=5&reset=1', '{ts escape="sql" skip="true"}Grant Reports{/ts}', 'Grant Reports', 'access CiviGrant', '', @reportlastID, '1', 0, (SELECT @max_weight := @max_weight+1) );

-- CRM-11148 Multiple terms membership signup and renewal via price set
ALTER TABLE `civicrm_price_field_value` ADD COLUMN `membership_num_terms` INT(10) NULL DEFAULT NULL COMMENT 'Maximum number of related memberships.' AFTER `membership_type_id`;

-- CRM-11070
SELECT @option_group_id_tuf := max(id) from civicrm_option_group where name = 'tag_used_for';
SELECT @weight              := MAX(weight) FROM civicrm_option_value WHERE option_group_id = @option_group_id_tuf;

INSERT INTO
`civicrm_option_value` (`option_group_id`, {localize field='label'}label{/localize}, `value`, `name`, `grouping`, `filter`, `is_default`, `weight`)
VALUES
(@option_group_id_tuf, {localize}'Attachments'{/localize}, 'civicrm_file', 'Attachments', NULL, 0, 0, @weight = @weight + 1);