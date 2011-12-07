{if $addWightForActivity}
  ALTER TABLE `civicrm_activity` ADD weight` INT( 11 ) NULL DEFAULT NULL;
{/if}