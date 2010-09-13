{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
{capture assign=newPageURL}{crmURL q='action=add&reset=1'}{/capture}
<div id="help">
    {ts}CiviContribute allows you to create and maintain any number of Online Contribution Pages. You can create different pages for different programs or campaigns - and customize text, amounts, types of information collected from contributors, etc.{/ts} {help id="id-intro"}
</div>

{include file="CRM/Contribute/Form/SearchContribution.tpl"}  
{if NOT ($action eq 1 or $action eq 2) }
    <table class="form-layout-compressed">
    <tr>
        <td><a href="{$newPageURL}" class="button"><span><div class="icon add-icon"></div>{ts}Add Contribution Page{/ts}</span></a></td>
        <td style="vertical-align: top"><a class="button" href="{crmURL p="civicrm/admin/pcp" q="reset=1"}"><span>{ts}Manage Personal Campaign Pages{/ts}</span></a> {help id="id-pcp-intro" file="CRM/Contribute/Page/PCP.hlp"}</td>
    </tr>
    </table>
{/if}

{if $rows}
    <div id="configure_contribution_page">
        {strip}
        
        {include file="CRM/common/pager.tpl" location="top"}
        {include file="CRM/common/pagerAToZ.tpl"}
        {* handle enable/disable actions *}
        {include file="CRM/common/enableDisable.tpl"}
        {include file="CRM/common/jsortable.tpl"}
        <table id="options" class="display">
          <thead>
          <tr>
            <th id="sortable">{ts}Title{/ts}</th>
            <th>{ts}ID{/ts}</th>
            <th>{ts}Enabled?{/ts}</th>
            <th></th>
          </tr>
          </thead>
          {foreach from=$rows item=row}
            <tr id="row_{$row.id}" class="{cycle values="odd-row,even-row"} {$row.class}{if NOT $row.is_active} disabled{/if}">
                <td>
                   <strong>{$row.title}</strong>
                </td>
                <td>{$row.id}</td>
                <td id="row_{$row.id}_status">{if $row.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>

		<td class="crm-contribution-page-actions right nowrap">
		
		  <div class="crm-contribution-page-configure-actions">
		      <span id="contribution-page-configure-{$row.id}" class="btn-slide">{ts}Configure{/ts}
		         <ul class="panel" id="panel_info_{$row.id}">
		      	     <li>
                                <a title="{ts}Title and Settings{/ts}" class="action-item-wrap" href="{crmURL p='civicrm/admin/contribute/settings' q="reset=1&action=update&id=`$row.id`"}">{ts}Title and Settings{/ts}
                                </a>
                             </li>
		      	     <li>
                                <a title="{ts}Contribution Amounts{/ts}" class="action-item-wrap" href="{crmURL p='civicrm/admin/contribute/amount' q="reset=1&action=update&id=`$row.id`"}">{ts}Contribution Amounts {/ts}
                                </a>
                             </li>
		      	     <li>
                                <a title="{ts}Membership Settings{/ts}" class="action-item-wrap" href="{crmURL p='civicrm/admin/contribute/membership' q="reset=1&action=update&id=`$row.id`"}">{ts}Membership Settings{/ts}
                                </a>
                             </li>
		      	     <li>
                                <a title="{ts}Include Profiles{/ts}" class="action-item-wrap" href="{crmURL p='civicrm/admin/contribute/custom' q="reset=1&action=update&id=`$row.id`"}">{ts}Include Profiles{/ts}
                                </a>
                             </li>
		      	     <li>
                                <a title="{ts}Thank-you and Receipting{/ts}" class="action-item-wrap" href="{crmURL p='civicrm/admin/contribute/thankYou' q="reset=1&action=update&id=`$row.id`"}">{ts}Thank-you and Receipting{/ts}
                                </a>
                             </li>
		      	     <li>
                                <a title="{ts}Tell a Friend{/ts}" class="action-item-wrap" href="{crmURL p='civicrm/admin/contribute/friend' q="reset=1&action=update&id=`$row.id`"}">{ts}Tell a Friend{/ts}
                                </a>
                             </li>
		      	     <li>
                                <a title="{ts}Personal Campaign Pages{/ts}" class="action-item-wrap" href="{crmURL p='civicrm/admin/contribute/pcp' q="reset=1&action=update&id=`$row.id`"}">{ts}Personal Campaign Pages{/ts}
                                </a>
                             </li>
		      	     <li>
                                <a title="{ts}Contribution Widget{/ts}" class="action-item-wrap" href="{crmURL p='civicrm/admin/contribute/widget' q="reset=1&action=update&id=`$row.id`"}">{ts}Contribution Widget{/ts}
                                </a>
                             </li>
		      	     <li>
                                <a title="{ts}Premiums{/ts}" class="action-item-wrap" href="{crmURL p='civicrm/admin/contribute/premium' q="reset=1&action=update&id=`$row.id`"}">{ts}Premiums{/ts}
                                </a>
                             </li>
		         </ul>
		      </span>
		  </div>

		  <div class="crm-contribution-links-actions">
		      <span id="contribution-page-links-{$row.id}" class="btn-slide">{ts}Links{/ts}
		         <ul class="panel" id="panel_info_{$row.id}">
		             <li>
                                <a title="{ts}Live Page{/ts}" class="action-item-wrap" href="{crmURL p='civicrm/contribute/transact' q="reset=1&id=`$row.id`"}">{ts}Live Page{/ts}
                                </a>
                             </li>
		      	     <li>
				<a title="{ts}Test-drive{/ts}" class="action-item-wrap" href="{crmURL p='civicrm/contribute/transact' q="reset=1&action=preview&id=`$row.id`"}">{ts}Test-drive{/ts}
				</a>
			     </li>
		         </ul>	    
		      </span> 
		  </div>

		  
		  <div class="crm-contribution-links-actions">
		      <span id="contribution-page-links-{$row.id}" class="btn-slide">{ts}Contributions{/ts}
		         <ul class="panel" id="panel_info_{$row.id}">
		             <li>
			     </li>
			 </ul>
		      </span>
		  </div> 	 
		  
		  <div class="crm-contribution-page-more">
                       {$row.action|replace:'xx':$row.id}
                  </div>

		</td>

            </tr>
        {/foreach}
        </table>
        
        {/strip}
    </div>
{else}
    {if $isSearch eq 1}
    <div class="status messages">
            <img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}"/>
            {capture assign=browseURL}{crmURL p='civicrm/contribute/manage' q="reset=1"}{/capture}
                {ts}No available Contribution Pages match your search criteria. Suggestions:{/ts}
                <div class="spacer"></div>
                <ul>
                <li>{ts}Check your spelling.{/ts}</li>
                <li>{ts}Try a different spelling or use fewer letters.{/ts}</li>
                <li>{ts}Make sure you have enough privileges in the access control system.{/ts}</li>
                </ul>
                {ts 1=$browseURL}Or you can <a href='%1'>browse all available Contribution Pages</a>.{/ts}
    </div>
    {else}
    <div class="messages status">
        <div class="icon inform-icon"></div> &nbsp;
        {ts 1=$newPageURL}No contribution pages have been created yet. Click <a accesskey="N" href='%1'>here</a> to create a new contribution page using the step-by-step wizard.{/ts}
    </div>
    {/if}
{/if}