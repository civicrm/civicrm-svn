{* 
Default Thank-you page for verified signers.
You might have a specific page that displays more information that the form.
Check SocialNetwork.drupal as an example
*}

{*capture assign=petitionURL}{crmURL p='civicrm/petition/sign' q="sid=$petition_id" a=true}{/capture*}
{include file="CRM/common/SocialNetwork.tpl"}