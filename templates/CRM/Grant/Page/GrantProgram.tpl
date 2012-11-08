{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}

{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/Grant/Form/GrantProgram.tpl"}
{elseif $action eq 4}
   {include file="CRM/Grant/Form/GrantProgramView.tpl"}
{else}
{if $rows}
<div id="ltype">
<p></p>
    <div class="form-item">
    {if $action ne 1 and $action ne 2}
	    <div class="action-link">
    	<a href="{crmURL q="action=add&reset=1"}" id="grant_program" class="button"><span><div class="icon add-icon"></div>{ts}Add Grant Program{/ts}</span></a>
        </div>
        {/if}
        {strip}
        <table cellpadding="0" cellspacing="0" border="0">
           <thead class="sticky">
            <th>{ts}Label{/ts}</th>
            <th>{ts}Type{/ts}</th>
            <th>{ts}Description{/ts}</th>
            <th>{ts}Total{/ts}</th>
            <th>{ts}Status{/ts}</th>
	    <th>{ts}Allocation Date{/ts}</th>
	    <th>{ts}Enabled?{/ts}</th>	
            <th></th>
          </thead>
         {foreach from=$rows item=row}
        <tr id="row_{$row.id}"class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
	        <td>{$row.label}</td>	
	        <td>{$row.grant_type_id}</td>
            	<td>{$row.description}</td>
	        <td>{$row.total_amount}</td>
	        <td>{$row.status_id}</td>
		<td>{$row.allocation_date|truncate:10:''|crmDate}</td>
	        <td>{if $row.is_active}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
	        <td>{$row.action|replace:'xx':$row.id}</td>
        </tr>
        {/foreach}
         </table>
        {/strip}

        {if $action ne 1 and $action ne 2}
	    <div class="action-link">
    	<a href="{crmURL q="action=add&reset=1"}" id="grant_program" class="button"><span><div class="icon add-icon"></div>{ts}Add Grant Program{/ts}</span></a>
        </div>
        {/if}
    </div>
</div>
{else}
    <div class="messages status">
        <div class="icon inform-icon"></div>
        {capture assign=crmURL}{crmURL q="action=add&reset=1"}{/capture}
        {ts 1=$crmURL}There are no Grant Programs entered. You can <a href='%1'>add one</a>.{/ts}
    </div>    
{/if}
{/if}