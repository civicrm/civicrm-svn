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
{* CiviCampaign DashBoard (launch page) *}

{* build the campaign selector *}
{if $subPageType eq 'campaign'}

{if $campaigns} 
  <div class="action-link">
      <a href="{$addCampaignUrl}" class="button"><span>&raquo; {ts}Add Campaign{/ts}</span></a>
  </div>
  {include file="CRM/common/enableDisable.tpl"}
  {include file="CRM/common/jsortable.tpl"}
  <div id="campaignType">
    <table id="options" class="display">
      <thead>
        <tr>      
          <th>{ts}Campaign Title{/ts}</th>
          <th>{ts}Description{/ts}</th>
          <th>{ts}Start Date{/ts}</th> 
          <th>{ts}End Date{/ts}</th>
          <th>{ts}Campaign Type{/ts}</th>
          <th>{ts}Status{/ts}</th>
          <th>{ts}Active?{/ts}</th>
          <th id="nosort"></th>
	</tr>
      </thead>
      {foreach from=$campaigns item=campaign}
        <tr id="row_{$campaign.campaign_id}" {if $campaign.is_active neq 1}class="disabled"{/if}>
          <td>{$campaign.title}</td>
          <td>{$campaign.description}</td>
          <td>{$campaign.start_date|crmDate}</td>
          <td>{$campaign.end_date|crmDate}</td>
          <td>{$campaign.campaign_type}</td>
          <td>{$campaign.status}</td>
          <td id="row_{$campaign.id}_status">{if $campaign.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
          <td>{$campaign.action}</td>
	</tr>
      {/foreach}
    </table>
  </div>

{else} 
    <div class="messages status">
        <div class="icon inform-icon"></div> &nbsp;
        {ts}No Campaigns found.{/ts}
    </div>
{/if}
<div class="action-link">
   <a href="{$addCampaignUrl}" class="button"><span>&raquo; {ts}Add Campaign{/ts}</span></a>
</div>

{* build the survey selector *}
{elseif $subPageType eq 'survey'}

{if $surveys} 
  <div class="action-link">
    <a href="{$addSurveyUrl}" class="button"><span>&raquo; {ts}Add Survey{/ts}</span></a>
  </div>
 {include file="CRM/common/enableDisable.tpl"}
 {include file="CRM/common/jsortable.tpl"}
  <div id="surveyList">
    <table id="options" class="display">
      <thead>
        <tr>  
          <th>{ts}Survey{/ts}</th>
          <th>{ts}Campaign{/ts}</th>
          <th>{ts}Survey Type{/ts}</th>   
          <th>{ts}Release Frequency{/ts}</th>
	  <th>{ts}Max Number Of Contacts{/ts}</th>
	  <th>{ts}Default Number Of Contacts{/ts}</th>
	  <th>{ts}Default?{/ts}</th>
	  <th>{ts}Active?{/ts}</th>
	  <th id="nosort"></th>
        </tr>
      </thead>
      {foreach from=$surveys item=survey}
        <tr id="row_{$survey.id}" {if $survey.is_active neq 1}class="disabled"{/if}>
	  <td>{$survey.title}</td>
          <td>{$survey.campaign_id}</td>
          <td>{$survey.activity_type}</td>
          <td>{$survey.release_frequency}</td>
          <td>{$survey.max_number_of_contacts}</td>
          <td>{$survey.default_number_of_contacts}</td>
          <td>{if $survey.is_default}<img src="{$config->resourceBase}/i/check.gif" alt="{ts}Default{/ts}" /> {/if}</td>
          <td id="row_{$survey.id}_status">{if $survey.is_active}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
 	  <td class="crm-report-optionList-action">{$survey.action}</td>
        </tr>
      {/foreach}
    </table>
  </div>

{else} 
  <div class="status">
    <div class="icon inform-icon"></div>&nbsp;{ts}No survey found.{/ts}
  </div> 
{/if}
<div class="action-link">
   <a href="{$addSurveyUrl}" class="button"><span>&raquo; {ts}Add Survey{/ts}</span></a>
</div>


{* build normal page *}
{else}

 <div id="mainTabContainer" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
     <ul class="crm-campaign-tabs-list">
           {foreach from=$allTabs key=tabName item=tabValue}
           <li id="tab_{$tabValue.id}" class="crm-tab-button ui-corner-bottom">
            	<a href="{$tabValue.url}" title="{$tabValue.title}"><span></span>{$tabValue.title}</a>
           </li>
           {/foreach}
     </ul>
 </div>

 <div class="spacer"></div>

{literal}
<script type="text/javascript">

//explicitly stop spinner
function stopSpinner( ) {
  cj('li.crm-tab-button').each(function(){ cj(this).find('span').text(' ');})	 
}

cj(document).ready( function( ) {
     {/literal}
     var spinnerImage = '<img src="{$config->resourceBase}i/loading.gif" style="width:10px;height:10px"/>';
     {literal} 
     
     var selectedTabIndex = {/literal}{$selectedTabIndex}{literal};
     cj("#mainTabContainer").tabs( { 
                                    selected: selectedTabIndex, 
                                    spinner: spinnerImage, 
				    cache: true, 
				    load: stopSpinner 
				    });
});
           
</script>
{/literal}
{/if}