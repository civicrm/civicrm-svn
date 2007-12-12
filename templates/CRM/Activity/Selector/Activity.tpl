{* Displays Activities. *}

<div>
  <fieldset>
  <legend>{ts}Activities{/ts}</legend>

{if $rows}
  <form title="activity_pager" action="{crmURL}" method="post">
  {include file="CRM/common/pager.tpl" location="top"}

  {strip}
    <table>
      <tr class="columnheader">
      {foreach from=$columnHeaders item=header}
        <th scope="col">
        {if $header.sort}
          {assign var='key' value=$header.sort}
          {$sort->_response.$key.link}
        {else}
          {$header.name}
        {/if}
        </th>
      {/foreach}
      </tr>

      {counter start=0 skip=1 print=false}
      {foreach from=$rows item=row}
      <tr class="{cycle values="odd-row,even-row"} {$row.class}">

        <td>{$row.activity_type}</td>
        {if $enableCase}
           <td><a href="{crmURL p='civicrm/contact/view/case' 
                             q="action=view&selectedChild=case&id=`$row.case_id`&cid=`$row.source_contact_id`&context=$context"}">
                             {$row.case_subject}</a>
           </td>
        {/if}
       	<td>{$row.subject}</td>
	
        <td>
        {if !$row.source_contact_id}
	  <em>n/a</em>
	{elseif $contactId NEQ $row.source_contact_id}
          <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.source_contact_id`"}">{$row.source_contact_name}</a>
        {else}
          <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.source_contact_id`"}">{$row.source_contact_name}</a> 	
        {/if}			
        </td>

        <td>
        {if !$row.target_contact_id}
          <em>n/a</em>
        {elseif $contactId NEQ $row.target_contact_id}
          <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.target_contact_id`"}">{$row.target_contact_name}</a>
        {else}
          <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.target_contact_id`"}">{$row.target_contact_name}
        {/if}			
        </td>

        <td>
        {if !$row.assignee_contact_id}
	  <em>n/a</em>
        {elseif $contactId NEQ $row.assignee_contact_id}
          <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.assignee_contact_id`"}">{$row.assignee_contact_name}</a>
        {else}
          {$row.assignee_contact_name}
        {/if}			
        </td>

        <td>{$row.activity_date_time|crmDate}</td>

        <td>{$row.action}</td>    
      </tr>
      {/foreach}

    </table>
  {/strip}

  {include file="CRM/common/pager.tpl" location="bottom"}
  </form>

</fieldset>
</div>

{else}

  <div class="messages status">
    {if $caseview}
      {ts}There is no activities attached to this case record. Go to Activities tab to attach some activity records.{/ts}
    {else}
      {ts}No Activites for this contact.{/ts}
    {/if}
  </div>

{/if}