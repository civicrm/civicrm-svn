-- CRM-3507: upgrade message templates (if changed)
{include file='../CRM/Upgrade/3.1.alpha2.msg_template/civicrm_msg_template.tpl'}

--  CRM-5263

UPDATE civicrm_country SET is_province_abbreviated = 1 
WHERE name IN ('Canada', 'United States');