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
<div id="help">
    {ts}This screen presents the list of scheduled jobs (cron tasks) log entries.{/ts} {$docLink}
</div>

{if $action eq 1 or $action eq 2 or $action eq 8}
   {include file="CRM/Admin/Form/Job.tpl"}
{else}

{if $rows}
<div id="ltype">
        {strip}
        {* handle enable/disable actions*}
 	{include file="CRM/common/enableDisable.tpl"}
        <table class="selector">
        <tr class="columnheader">
            <th >{ts}Date{/ts}</th>
            <th >{ts}Job Name{/ts}</th>
            <th >{ts}Command{/ts}/{ts}Description{/ts}/{ts}Additional information{/ts}</th>
        </tr>
        {foreach from=$rows item=row}
        <tr id="row_{$row.id}" class="crm-job {cycle values="odd-row,even-row"} {$row.class}">
            <td class="crm-joblog-run_datetime">{$row.run_time}</td>
            <td class="crm-joblog-name">{$row.name}</td>
            <td class="crm-joblog-details">
                <div class="crm-joblog-command">{$row.command}</div>
                <div class="crm-joblog-description">{$row.description}</div>
	        <div class="crm-joblog-data">{$row.data}</div>
            </td>
        </tr>
        {/foreach}
        </table>
        {/strip}

</div>
{elseif $action ne 1}
    <div class="messages status">
      <div class="icon inform-icon"></div>
        {ts}There are no scheduled job log entries.{/ts}
     </div>    
{/if}
{/if}