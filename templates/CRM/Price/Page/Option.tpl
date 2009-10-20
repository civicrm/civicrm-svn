{if $action eq 1 or $action eq 2 or $action eq 4 or $action eq 8  and !$usedBy}
    {include file="CRM/Price/Form/Option.tpl"}
{/if}

{if $usedBy}
    <div class='spacer'></div>
    <div id="price_set_used_by" class="messages status">
      <dl>
      <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>      
      <dd>
        {if $action eq 8}
            {ts 1=$usedPriceSetTitle}Unable to delete the '%1' Price Field Option - it is currently in use by one or more active events.{/ts}
       	{/if}
        {ts}If you no longer want to use this Price Option Field, click the event title below, and modify the fees for that event.{/ts}<br />
        
        {if $usedBy.civicrm_event_page} {* If and when Price Sets and Price Fields are used by entities other than events, add condition here and change text above. *}
            <table class="report">
            <tr class="columnheader-dark">
                <th scope="col">{ts}Event{/ts}</th>
                <th scope="col">{ts}Type{/ts}</th>
                <th scope="col">{ts}Public{/ts}</th>
                <th scope="col">{ts}Date(s){/ts}</th>
            </tr>

            {foreach from=$usedBy.civicrm_event_page item=event key=id}
                <tr>
                    <td><a href="{crmURL p="civicrm/admin/event" q="action=update&reset=1&subPage=Fee&id=`$id`"}">{$event.title}</a></td>
                    <td>{$event.eventType}</td>
                    <td>{if $event.isPublic}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
                    <td>{$event.startDate}{if $event.endDate}&nbsp;to&nbsp;{$event.endDate}{/if}</td>
                </tr>
            {/foreach}
            </table>
        {/if}
      </dd>
      </dl>
    </div>
    {/if}



{if $customOption}
    
    <div id="field_page">
     <p></p>
        {strip}
	{* handle enable/disable actions*}
 	{include file="CRM/common/enableDisable.tpl"}
 	{include file="CRM/common/jsortable.tpl"}
        <table id="options" class="display">
        <thead>
         <tr>
            <th>{ts}Option Label{/ts}</th>
            <th>{ts}Option Amount{/ts}</th>
    	    <th>{ts}Default{/ts}</th>
            <th id="order">{ts}Order{/ts}</th>
	        <th>{ts}Enabled?{/ts}</th>
            <th></th>
         </tr>
        </thead>
        {foreach from=$customOption item=row}
    	<tr id="row_{$row.id}"class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
            <td>{$row.label}</td>
            <td>{$row.name|crmMoney}</td>
	        <td>{$row.is_default}</td>
            <td class="nowrap">{$row.order}</td>
            <td id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
            <td>{$row.action}</td>
            <td class="order hiddenElement">{$row.weight}</td>
        </tr>
        {/foreach}
        </tbody>
        </table>
        {/strip}
        {if $addMoreFields}
        <div class="action-link">
            <a href="{crmURL q="reset=1&action=add&fid=$fid"}" class="button"><span>&raquo; {ts 1=$fieldTitle}New Option for '%1'{/ts}</span></a>
        </div>
	{/if}
    </div>

{else}
    {if $action eq 16}
        <div class="messages status">
        <dl>
        <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/></dt>
        <dd>{capture assign=crmURL}{crmURL p='civicrm/admin/price/field/option' q="action=add&fid=$fid"}{/capture}{ts 1=$fieldTitle 2=$crmURL}There are no options for the price field '%1', <a href='%2'>add one</a>.{/ts}</dd>
        </dl>
        </div>
    {/if}
{/if}
