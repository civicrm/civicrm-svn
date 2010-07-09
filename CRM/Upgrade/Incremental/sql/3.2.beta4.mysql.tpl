-- CRM-6451
{if $multilingual}
  -- add a name column, populate it from the name_xx_YY chosen in 
  ALTER TABLE civicrm_membership_status ADD name VARCHAR(128) COMMENT 'Name for Membership Status';
  UPDATE      civicrm_membership_status SET name = name_{$seedLocale};
  -- add label_xx_YY columns and populate them from name_xx_YY, dropping the latter
  {foreach from=$locales item=loc}
    ALTER TABLE civicrm_membership_status ADD label_{$loc} VARCHAR(128) COMMENT 'Label for Membership Status';
    UPDATE      civicrm_membership_status SET label_{$loc} = name_{$loc};
    ALTER TABLE civicrm_membership_status DROP name_{$loc};
  {/foreach}
{else}
  -- add a label column and populate it from the name column
  ALTER TABLE civicrm_membership_status ADD label VARCHAR(128) COMMENT 'Label for Membership Status';
  UPDATE      civicrm_membership_status SET label = name;
{/if}
