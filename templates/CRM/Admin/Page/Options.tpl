{capture assign=docURLTitle}{ts}Opens online documentation in a new window.{/ts}{/capture}
<div id="help">
  {if $gName eq "gender"}
    <p>{ts}CiviCRM is pre-configured with standard options for individual gender (e.g. Male, Female, Transgender). You can use this page to customize these options and add new options as needed for your installation.{/ts}</p>
  {elseif $gName eq "individual_prefix"}
      <p>{ts}CiviCRM is pre-configured with standard options for individual contact prefixes (e.g. Ms., Mr., Dr. etc.). You can use this page to customize these options and add new ones as needed for your installation.{/ts}</p>
  {elseif $gName eq "mobile_provider"}
     <p>{ts}When recording mobile phone numbers for contacts, it may be useful to include the Mobile Phone Service Provider (e.g. Cingular, Sprint, etc.). CiviCRM is installed with the most commonly encountered service providers. Administrators may define as many additional providers as needed.{/ts}</p>
  {elseif $gName eq "instant_messenger_service"}
     <p>{ts}When recording Instant Messenger (IM) 'screen names' for contacts, it is useful to include the IM Service Provider (e.g. AOL, Yahoo, etc.). CiviCRM is installed with the most commonly encountered service providers. Administrators may define as many additional providers as needed.{/ts}</p>
  {elseif $gName eq "individual_suffix"}
     <p>{ts}CiviCRM is pre-configured with standard options for individual contact name suffixes (e.g. Jr., Sr., II etc.). You can use this page to customize these options and add new ones as needed for your installation.{/ts}</p>
  {elseif $gName eq "activity_type"}
     <p>{ts}Activities are 'interactions with contacts' which you want to record and track. CiviCRM has several reserved (e.g. 'built-in') activity types (meetings, phone calls, emails sent). Create additional 'activity types' here if you need to record other types of activities. For example, you might want to include 'New Client Intake', or 'Site Audit', etc. ...as types of trackable activities.{/ts}</p>
     {capture assign=crmURL}{crmURL p='civicrm/admin/custom/group' q='reset=1'}{/capture}
     <p>{ts 1=$crmURL}Subject, location, date/time and description fields are provided for all activity types. You can add custom fields for tracking additional information about activities <a href="%1">here</a>.{/ts}</p>
     <p>{ts 1="http://wiki.civicrm.org/confluence//x/Eh" 2=$docURLTitle}Scheduled and Completed Activities are searchable by type and/or activity date using 'Advanced Search'. Other applications may record activities for CiviCRM contacts using our APIs. For more information, refer to the online <a href="%1" target="_blank" title="%2">API Documentation</a>.{/ts}</p>
  {elseif $gName eq "payment_instrument"}
     <p>{ts}You may choose to record the Payment Instrument used for each Contribution. The common payment methods are installed by default and can not be modified (e.g. Check, Cash, Credit Card...). If your site requires additional payment methods, you can add them here.{/ts}</p>
  {elseif $gName eq "accept_creditcard"}
        <p>{ts}This page lists the credit card options that will be offered to contributors using your Online Contribution pages. You will need to verify which cards are accepted by your chosen Payment Processor and update these entries accordingly.{/ts}</p>
        <p>{ts}IMPORTANT: This page does NOT control credit card/payment method choices for sites and/or contributors using the PayPal Express service (e.g. where billing information is collected on the Payment Processor's website).{/ts}</p>
  {elseif $gName eq "acl_role"}
    {capture assign=aclURL}{crmURL p='civicrm/acl' q='reset=1'}{/capture}
    {capture assign=erURL}{crmURL p='civicrm/acl/entityrole' q='reset=1'}{/capture}
    <p>{ts 1="http://wiki.civicrm.org/confluence//x/SyU" 2=$docURLTitle}ACL's allow you control access to CiviCRM data. An ACL consists of an <strong>Operation</strong> (e.g. 'View' or 'Edit'), a <strong>set of data</strong> that the operation can be performed on (e.g. a group of contacts), and a <strong>Role</strong> that has permission to do this operation. Refer to the <a href="%1" target="_blank" title="%2">Access Control Documentation</a> for more info.{/ts}</p>
    <p>{ts 1=$aclURL 2=$erURL}You can add or modify your ACL Roles below. You can create ACL&rsquo;s and grant permission to roles <a href="%1">here</a>... and you can assign role(s) to CiviCRM contacts who are users of your site <a href="%2">here</a>.{/ts}</p>
  {elseif $gName eq 'event_type'}
    <p>{ts}Use Event Types to categorize your events. Event feeds can be filtered by Event Type and participant searches can use Event Type as a criteria.{/ts}</p>
  {elseif $gName eq 'participant_role'}
    <p>{ts}Define participant roles for events here (e.g. Attendee, Host, Speaker...). You can then assign roles and search for participants by role.{/ts}</p>
  {elseif $gName eq 'participant_status'}
    <p>{ts}Define statuses for event participants here (e.g. Registered, Attended, Cancelled...). You can then assign statuses and search for participants by status.{/ts}</p>
  {else}
        <p>{ts}The existing option choices for {$GName} group are listed below. You can add, edit or delete them from this screen.{/ts}</p>
  {/if}
</div>

{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/Admin/Form/Options.tpl"}
{/if}	

{if $rows}
<div id={$gName}>
<p></p>
    <div class="form-item">
        {strip}
        <table  enableMultipleSelect="true" enableAlternateRows="true" rowAlternateClass="alternateRow" cellpadding="0" cellspacing="0" border="0">
	<thead>
        <tr class="columnheader">
            <th field="Label" dataType="String" >{ts}Label{/ts}</th>
            <th field="Description" dataType="String" >{ts}Description{/ts}</th>
            <th field="Weight" dataType="Number" sort="asc">{ts}Weight{/ts}</th>
	    <th field="Reserved" dataType="String" >{ts}Reserved{/ts}</th>
            <th field="Enabled"  dataType="String" >{ts}Enabled?{/ts}</th>
            <th datatype="html"></th>
        </tr>
        </thead>
	
	<tbody>
        {foreach from=$rows item=row}
        <tr class="{$row.class}{cycle values="odd-row even-row"}{if NOT $row.is_active} disabled{/if}">
	        <td>{$row.label}</td>	
	        <td>{$row.description}</td>	
	        <td>{$row.weight}</td>
	        <td>{if $row.is_reserved eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	        <td>{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	        <td>{$row.action}</td>
        </tr>
        {/foreach}
	</tbody>
        </table>
        {/strip}

        {if $action ne 1 and $action ne 2}
            <div class="action-link">
                <a href="{crmURL q="group="|cat:$gName|cat:"&action=add&reset=1"}" id="new"|cat:$GName >&raquo; {ts}New {$GName}{/ts}</a>
            </div>
        {/if}
    </div>
</div>
{else}
    <div class="messages status">
    <dl>
        <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/></dt>
        {capture assign=crmURL}{crmURL p='civicrm/admin/options' q="group="|cat:$gName|cat:"&action=add&reset=1"}{/capture}
        <dd>{ts 1=$crmURL}There are no option values entered. You can <a href="%1">add one</a>.{/ts}</dd>
        </dl>
    </div>    
{/if}
