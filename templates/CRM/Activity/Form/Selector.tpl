{if $context EQ 'Search'}
    {include file="CRM/common/pager.tpl" location="top"}
{/if}

{strip}
<table class="selector">
   <thead class="sticky">
     {if !$single and $context eq 'Search' }
        <th scope="col" title="Select Rows">{$form.toggleSelect.html}</th> 
     {/if}
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
   </thead>


  {counter start=0 skip=1 print=false}
  {foreach from=$rows item=row}
  <tr class="{cycle values="odd-row,even-row"} {$row.class}">
	{if !$single }
        {if $context eq 'Search' }       
    	    {assign var=cbName value=$row.checkbox}
    	    <td>{$form.$cbName.html}</td> 
 		{/if}
  	<td>{$row.contact_type}</td>	
    	<td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}">{$row.sort_name}</a></td>
    {/if}

    <td>{$row.activity_type}</td>
   
	<td>{$row.activity_subject}</td>

    <td>
    {if !$row.source_contact_id}
      <em>n/a</em>
    {elseif $contactId NEQ $row.source_contact_id}
      <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.source_contact_id`"}" title="{ts}View contact{/ts}">{$row.source_contact_name}</a>
    {else}
      {$row.source_contact_name}	
    {/if}			
    </td>

    <td>
    {if $row.mailingId}
      <a href="{$row.mailingId}" title="{ts}View Mailing Report{/ts}">{$row.recipients}</a>
    {elseif $row.recipients}
      {$row.recipients}
    {elseif !$row.target_contact_name}
      <em>n/a</em>
    {elseif $row.target_contact_name}
        {assign var="showTarget" value=0}
        {foreach from=$row.target_contact_name item=targetName key=targetID}
            {if $showTarget < 5}
                {if $showTarget};&nbsp;{/if}<a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$targetID`"}" title="{ts}View contact{/ts}">{$targetName}</a>
                {assign var="showTarget" value=$showTarget+1}
            {/if}
        {/foreach}
        {if count($row.target_contact_name) > 5}({ts}more{/ts}){/if}
    {/if}
    </td>

    <td>
    {if !$row.assignee_contact_name}
        <em>n/a</em>
    {elseif $row.assignee_contact_name}
        {assign var="showAssignee" value=0}
        {foreach from=$row.assignee_contact_name item=assigneeName key=assigneeID}
            {if $showAssignee < 5}
                {if $showAssignee};&nbsp;{/if}<a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$assigneeID`"}" title="{ts}View contact{/ts}">{$assigneeName}</a>
                {assign var="showAssignee" value=$showAssignee+1}
            {/if}
        {/foreach}
        {if count($row.assignee_contact_name) > 5}({ts}more{/ts}){/if}
    {/if}	
    </td>

    <td>{$row.activity_date_time|crmDate}</td>

    <td>{$row.activity_status}</td>

    <td>{$row.action|replace:'xx':$row.id}</td>
  </tr>
  {/foreach}

</table>
{/strip}



{if $context EQ 'Search'}
 <script type="text/javascript">
 {* this function is called to change the color of selected row(s) *}
    var fname = "{$form.formName}";	
    on_load_init_checkboxes(fname);
 </script>
{/if}

{if $context EQ 'Search'}
    {include file="CRM/common/pager.tpl" location="bottom"}
{/if}