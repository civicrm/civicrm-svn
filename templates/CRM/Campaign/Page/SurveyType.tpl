<div class="crm-content-block crm-block">
{if $rows}
{if $action ne 1 and $action ne 2}
    <div class="action-link">
        <a href="{$addSurveyType}" class="button"><span><div class="icon add-icon"></div>{ts 1=$GName}Add %1{/ts}</span></a>
    </div>
{/if}
<div id={$gName}>
        {strip}
	{* handle enable/disable actions*} 
	{include file="CRM/common/enableDisable.tpl"}
    {include file="CRM/common/jsortable.tpl"}
        <table id="options" class="display">
	       <thead>
	       <tr>
            {if $showComponent}
                <th>{ts}Component{/ts}</th>
            {/if}
            <th>
                {if $gName eq "redaction_rule"}
                    {ts}Match Value or Expression{/ts}
                {else}
                    {ts}Label{/ts}
                {/if}
            </th>
	    {if $gName eq "case_status"}
	    	<th>
		    {ts}Status Class{/ts}
		</th>	    
            {/if}
            <th>
                {if $gName eq "redaction_rule"}
                    {ts}Replacement{/ts}
                {else}
                    {ts}Value{/ts}
                {/if}
            </th>
        
            <th id="nosort">{ts}Description{/ts}</th>
            <th id="order" class="sortable">{ts}Order{/ts}</th>
            <th>{ts}Reserved{/ts}</th>
            <th>{ts}Enabled?{/ts}</th>
            <th class="hiddenElement"></th>
            <th></th>
            </tr>
            </thead>
            <tbody>
        {foreach from=$rows item=row}
        <tr id="row_{$row.id}" class=" crm-admin-options crm-admin-options_{$row.id} {if NOT $row.is_active} disabled{/if}">
            {if $showComponent}
                <td class="crm-admin-options-component_name">{$row.component_name}</td>
            {/if}
	        <td class="crm-admin-options-label">{$row.label}</td>
	    {if $gName eq "case_status"}				
		<td class="crm-admin-options-grouping">{$row.grouping}</td>
            {/if}	
	        <td class="crm-admin-options-value">{$row.value}</td>
	        <td class="crm-admin-options-description">{$row.description}</td>	
	        <td class="nowrap crm-admin-options-order">{$row.order}</td>
	        <td class="crm-admin-options-is_reserved">{if $row.is_reserved eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	        <td class="crm-admin-options-is_active" id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
	        <td>{$row.action|replace:'xx':$row.id}</td>
	        <td class="order hiddenElement crm-participant-weight">{$row.weight}</td>
        </tr>
        {/foreach}
        </tbody>
        </table>
        {/strip}

        {if $action ne 1 and $action ne 2}
            <div class="action-link">
                <a href="{$addSurveyType}" class="button"><span><div class="icon add-icon"></div>{ts 1=$GName}Add %1{/ts}</span></a>
            </div>
        {/if}
</div>
{else}
    <div class="messages status">
         <div class="icon inform-icon"> &nbsp;
         {ts 1=$addSurveyType}There are no survey type entered. You can <a href='%1'>add one</a>.{/ts}</div>
    </div>    
{/if}
</div>