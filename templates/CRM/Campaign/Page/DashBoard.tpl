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

<div id="campaign-dialog" class='hiddenElement'></div>
{if $campaigns} 
  <div class="action-link">
      <a href="#" onclick="createCampaign( );" class="button"><span>&raquo; {ts}Add Campaign{/ts}</span></a>
  </div>

  {include file="CRM/common/enableDisable.tpl"}
  {include file="CRM/common/jsortable.tpl"}
  <div id="campaignList">
    <table id="options" class="display">
      <thead>
        <tr class="columnheader">      
          <th>{ts}Title{/ts}</th>
          <th>{ts}Description{/ts}</th>
          <th>{ts}Start Date{/ts}</th> 
          <th>{ts}End Date{/ts}</th>
          <th>{ts}Type{/ts}</th>
          <th>{ts}Status{/ts}</th>
          <th>{ts}Active?{/ts}</th>
          <th id="nosort"></th>
	</tr>
      </thead>
      {foreach from=$campaigns item=campaign}
        <tr class="{cycle values="odd-row,even-row"} crm-campaign{if $campaign.is_active neq 1} disabled{/if}" id="row_{$campaign.campaign_id}">
          <td class="crm-campaign-title">{$campaign.title}</td>
          <td class="crm-campaign-description">{$campaign.description}</td>
          <td class="crm-campaign-start_date">{$campaign.start_date|crmDate}</td>
          <td class="crm-campaign-end_date">{$campaign.end_date|crmDate}</td>
          <td class="crm-campaign-campaign_type">{$campaign.campaign_type}</td>
          <td class="crm-campaign-campaign_status">{$campaign.status}</td>
          <td class="crm-campaign-campaign-is_active" id="row_{$campaign.id}_status">{if $campaign.is_active eq 1} {ts}Yes{/ts} {else} {ts}No{/ts} {/if}</td>
          <td class="crm-campaign-action">{$campaign.action}</td>
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
   <a href="#" onclick="createCampaign( );"  class="button"><span>&raquo; {ts}Add Campaign{/ts}</span></a>
</div>

{* build the survey selector *}
{elseif $subPageType eq 'survey'}

<div id="survey-dialog" class='hiddenElement'></div>
{if $surveys} 
  <div class="action-link">
    <a href="#" onclick="createSurvey( );" class="button"><span>&raquo; {ts}Add Survey{/ts}</span></a>
  </div>
 {include file="CRM/common/enableDisable.tpl"}
 {include file="CRM/common/jsortable.tpl"}
  <div id="surveyList">
    <table id="options" class="display">
      <thead>
        <tr class="columnheader">  
          <th>{ts}Title{/ts}</th>
          <th>{ts}Campaign{/ts}</th>
          <th>{ts}Survey Type{/ts}</th>   
          <th>{ts}Release Frequency{/ts}</th>
	  <th>{ts}Max Contacts{/ts}</th>
	  <th>{ts}Default Contacts{/ts}</th>
	  <th>{ts}Default?{/ts}</th>
	  <th>{ts}Active?{/ts}</th>
	  <th id="nosort"></th>
	  <th id="nosort"></th>
        </tr>
      </thead>
      {foreach from=$surveys item=survey}
        <tr id="row_{$survey.id}" class="{cycle values="odd-row,even-row"} crm-survey{if $survey.is_active neq 1} disabled{/if}">
	  <td class="crm-survey-title">{$survey.title}</td>
          <td class="crm-survey-campaign_id">{$survey.campaign_id}</td>
          <td class="crm-survey-activity_type">{$survey.activity_type}</td>
          <td class="crm-survey-release_frequency">{$survey.release_frequency}</td>
          <td class="crm-survey-max_number_of_contacts">{$survey.max_number_of_contacts}</td>
          <td class="crm-survey-default_number_of_contacts">{$survey.default_number_of_contacts}</td>
          <td class="crm-survey-is_default">{if $survey.is_default}<img src="{$config->resourceBase}/i/check.gif" alt="{ts}Default{/ts}" /> {/if}</td>
          <td class="crm-survey-is_active" id="row_{$survey.id}_status">{if $survey.is_active}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
 	  <td class="crm-survey-action">{$survey.action}</td>
	  <td class="crm-survey-voter_links">
	  {if $survey.voterLinks}
	    <span id="voter_links-{$survey.id}" class="btn-slide">{ts}more{/ts}
              <ul class="panel" id="panels_voter_links_{$survey.id}"> 
 	      {foreach from=$survey.voterLinks item=voterLink}
                <li>{$voterLink}</li>
              {/foreach}   
	      </ul>
	    </span>
	    &nbsp;
	  {/if}				
	  </td>
        </tr>
      {/foreach}
    </table>
  </div>

{else} 
  <div class="status">
    <div class="icon inform-icon"></div>&nbsp;{ts}No surveys found.{/ts}
  </div> 
{/if}
<div class="action-link">
   <a href="#" onclick="createSurvey( );" class="button"><span>&raquo; {ts}Add Survey{/ts}</span></a>
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
				    
				    //FIXME:first fix the template cache and then enable.  
				    //cache: true, 
				    
				    load: stopSpinner 
				    });
});
           
</script>
{/literal}
{/if}

{literal}
<script type="text/javascript">
 
  function createSurvey( ) {
    var dataURL   = {/literal}"{crmURL p='civicrm/survey/add' q='reset=1&snippet=5&context=dialog' h=0 }"{literal};
    var formTitle = {/literal}"{ts}Create New Survey{/ts}"{literal};
    openModal( dataURL, cj("#survey-dialog"), formTitle, 830 );	
  }

  function createCampaign(  ) {
    var dataURL = {/literal}"{crmURL p='civicrm/campaign/add' q='reset=1&snippet=5&context=dialog' h=0 }"{literal};
    var formTitle = {/literal}"{ts}Create New Campaign{/ts}"{literal};
    
    openModal( dataURL, cj("#campaign-dialog"), formTitle, 730 );	
  }
	
  function openModal( dataURL, modalElement, formTitle, formWidth ) {
     cj.ajax({
         url: dataURL,
         success: function( content ) {
             cj(modalElement).show( ).html( content ).dialog({
         	    	title: formTitle,
             		modal: true,
             		width: formWidth, 
			position: ['center',75],
             		overlay: { 
             			opacity: 0.5, 
             			background: "black" 
             		},

                 beforeclose: function(event, ui) {
                     cj(this).dialog("destroy");
                 }
             });
         }
      });
  }    
</script>
{/literal}