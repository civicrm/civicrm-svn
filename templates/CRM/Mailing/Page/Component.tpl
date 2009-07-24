{if $action eq 1 or $action eq 2}
   {include file="CRM/Mailing/Form/Component.tpl"}
{/if}

<div id="ltype">
 <p></p>
    <div class="form-item">
       {strip}
       {* handle enable/disable actions*}	
       {include file="CRM/common/enableDisable.tpl"}
       <table cellpadding="0" cellspacing="0" border="0">
        <thead class="sticky">
        <th>{ts}Name{/ts}</th>
        <th>{ts}Type{/ts}</th>
        <th>{ts}Subject{/ts}</th>
        <th>{ts}Body Text{/ts}</th>
        <th>{ts}Body HTML{/ts}</th>
        <th>{ts}Default?{/ts}</th>
        <th>{ts}Enabled?{/ts}</th>
        <th></th>
        </thead>
       {foreach from=$rows item=row}
         <tr id="row_{$row.id}" class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
           <td>{$row.name}</td>	
           <td>{$row.component_type}</td>
           <td>{$row.subject}</td>
           <td>{$row.body_text}</td>
           <td>{$row.body_html|escape}</td>
           <td>{if $row.is_default eq 1}<img src="{$config->resourceBase}/i/check.gif" alt="{ts}Default{/ts}" />{/if}&nbsp;</td>
	   <td id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
           <td>{$row.action|replace:'xx':$row.id}</td>
        </tr>
       {/foreach}
       </table>
       {/strip}

       {if $action ne 1 and $action ne 2}
	<br/>
       <div class="action-link">
    	 <a href="{crmURL q="action=add&reset=1"}" class="button"><span>&raquo; {ts}New Mailing Component{/ts}</span></a>
       </div>
       {/if}
    </div>
</div>
