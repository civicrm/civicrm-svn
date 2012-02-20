-- CRM-9699
{if $addDedupeEmail}
   ALTER TABLE `civicrm_mailing` ADD `dedupe_email` TINYINT NULL DEFAULT '0' COMMENT 'Remove duplicate emails?';
{/if}
