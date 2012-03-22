{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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

<div id="help">
    {ts}Current batches{/ts}
</div>

{if $rows}
<div id="crm-batch">
  <table id="options" class="display">
    <thead>
        <tr>
            <th>{ts}Title{/ts}</th>
            <th>{ts}Type{/ts}</th>
            <th>{ts}Item Count{/ts}</th>
            <th>{ts}Total Amount{/ts}</th>
            <th>{ts}Status{/ts}</th>
            <th></th>
        </tr>
    </thead>
    {foreach from=$rows item=row}
        <tr id="row_{$row.id}"class="{cycle values="odd-row,even-row"}">
            <td class="crm-batch-title">{$row.title}</td>	
            <td class="crm-batch-type">{$row.type_id}</td>	
            <td class="crm-item_count">{$row.item_count}</td>	
            <td class="crm-total">{$row.total|crmMoney}</td>	
            <td class="crm-status_id">{$row.status_id}</td>	
            <td>{if $row.status_id eq 1}{$row.action|replace:'xx':$row.id}{/if}</td>
        </tr>
    {/foreach}
  </table>

  <div class="action-link">
    <a href="{crmURL p='civicrm/batch/add' q='action=add&reset=1'}" id="newBatch" class="button"><span>&raquo; {ts}New Batch{/ts}</span></a>
  </div>
</div>
{else}
    <div class="messages status">
        <img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/>
        {capture assign=crmURL}{crmURL p="civicrm/batch/add" q="action=add&reset=1"}{/capture}
        {ts 1=$crmURL}There are no batches. You can <a href='%1'>add one</a>.{/ts}
    </div>    
{/if}
