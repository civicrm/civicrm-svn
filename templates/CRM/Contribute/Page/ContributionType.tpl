{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/Contribute/Form/ContributionType.tpl"}
{else}
    <div id="help">
        <p>{ts}Contribution types are used to categorize contributions for reporting and accounting purposes. These are also referred to as <strong>Funds</strong>. You may set up as many types as needed. Each type can carry an accounting code which can be used to map contributions to codes in your accounting system. Commonly used contribution types are: Donation, Campaign Contribution, Membership Dues...{/ts}</p>
    </div>
{/if}

{if $rows}
<div id="ltype">
<p></p>
    <div class="form-item">
        {strip}
	{* handle enable/disable actions*}
 	{include file="CRM/common/enableDisable.tpl"}
        <table cellpadding="0" cellspacing="0" border="0">
           <thead class="sticky">
            <th>{ts}Name{/ts}</th>
            <th>{ts}Description{/ts}</th>
            <th>{ts}Acctg Code{/ts}</th>
            <th>{ts}Deductible?{/ts}</th>
            <th>{ts}Reserved?{/ts}</th>
            <th>{ts}Enabled?{/ts}</th>
            <th></th>
          </thead>
         {foreach from=$rows item=row}
        <tr id="row_{$row.id}"class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
	        <td>{$row.name}</td>	
	        <td>{$row.description}</td>
            	<td>{$row.accounting_code}</td>
	        <td>{if $row.is_deductible eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	        <td>{if $row.is_reserved eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	        <td id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	        <td>{$row.action|replace:'xx':$row.id}</td>
        </tr>
        {/foreach}
         </table>
        {/strip}

        {if $action ne 1 and $action ne 2}
	    <div class="action-link">
    	<a href="{crmURL q="action=add&reset=1"}" id="newContributionType" class="button"><span>&raquo; {ts}New Contribution Type{/ts}</span></a>
        </div>
        {/if}
    </div>
</div>
{else}
    <div class="messages status">
    <dl>
        <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>
        {capture assign=crmURL}{crmURL q="action=add&reset=1"}{/capture}
        <dd>{ts 1=$crmURL}There are no Contribution Types entered. You can <a href='%1'>add one</a>.{/ts}</dd>
        </dl>
    </div>    
{/if}
