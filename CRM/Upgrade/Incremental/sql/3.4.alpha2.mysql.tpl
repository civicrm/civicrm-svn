-- CRM-7494
-- update navigation.

UPDATE civicrm_navigation 
   SET name  = 'Survey Report (Detail)',
       label = 'Survey Report (Detail)'
 WHERE name LIKE 'Walk List Survey Report';

-- update report instance.

UPDATE civicrm_report_instance 
   SET title       = 'Survey Report (Detail)',
       description = 'Detailed report for canvassing, phone-banking, walk lists or other surveys.'
 WHERE report_id LIKE 'survey/detail';

-- update report template option values.
{if $multilingual}

  {foreach from=$locales item=loc}

    UPDATE  civicrm_option_value val
INNER JOIN  civicrm_option_group grp ON ( grp.id = val.option_group_id )
       SET  val.label_{$loc}       = '{ts escape="sql"}Survey Report (Detail){/ts}',
            val.description_{$loc} = '{ts escape="sql"}Detailed report for canvassing, phone-banking, walk lists or other surveys.{/ts}'
     WHERE  val.name = 'CRM_Report_Form_Campaign_SurveyDetails' 
       AND  grp.name = 'report_template';  

  {/foreach}

{else}

    UPDATE  civicrm_option_value val
INNER JOIN  civicrm_option_group grp ON ( grp.id = val.option_group_id )
       SET  val.label       = '{ts escape="sql"}Survey Report (Detail){/ts}',
            val.description = '{ts escape="sql"}Detailed report for canvassing, phone-banking, walk lists or other surveys.{/ts}'
     WHERE  val.name = 'CRM_Report_Form_Campaign_SurveyDetails' 
       AND  grp.name = 'report_template';

{/if}

