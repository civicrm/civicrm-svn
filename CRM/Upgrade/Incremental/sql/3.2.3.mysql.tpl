-- CRM-6663
ALTER TABLE `civicrm_pledge_payment` ADD `actual_amount` decimal(20,2) DEFAULT NULL COMMENT 'Actual amount that is paid as the Pledged installment amount.' AFTER `scheduled_amount`;
UPDATE `civicrm_pledge_payment` SET actual_amount = scheduled_amount WHERE contribution_id IS NOT NULL;