{if $context EQ 'Search'}
    {include file="CRM/common/pager.tpl" location="top"}
{/if}

{strip}
<table class="selector">
  <tr class="columnheader">
{if ! $single and $context eq 'Search' }
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
  </tr>
  {counter start=0 skip=1 print=false}
  {foreach from=$rows item=row}
  <tr id='rowid{$row.pledge_id}' class="{cycle values="odd-row,even-row"}">
     {if ! $single }
        {if $context eq 'Search' }       
            {assign var=cbName value=$row.checkbox}
            <td>{$form.$cbName.html}</td> 
        {/if}	
	<td>{$row.contact_type}</td>
    	<td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}">{$row.sort_name}</a></td>
    {/if}
    <td>{$row.pledge_amount|crmMoney}</td>	
    <td>{$row.pledge_create_date|truncate:10:''|crmDate}</td>
    <td>{$row.pledge_frequency_interval} {$row.pledge_frequency_unit|capitalize:true}(s) </td>	
    <td>{$row.pledge_start_date|truncate:10:''|crmDate}</td>
    <td>{$row.pledge_status_id}</td>	
    <td>{$row.action}</td>
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
	