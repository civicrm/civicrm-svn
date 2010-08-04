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
{if $dataType eq 'campaign'}

{if $campaigns} 
  <div class="action-link">
      <a href="{$addCampaignUrl}" class="button"><span>&raquo; {ts}Add Campaign{/ts}</span></a>
  </div>
  {include file="CRM/common/enableDisable.tpl"}
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
          <td>{$campaign.start_date}</td>
          <td>{$campaign.end_date}</td>
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
{elseif $dataType eq 'survey'}

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
     <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
         <li id="campaign_view" class="crm-tab-button ui-state-active ui-corner-top ui-corner-bottom ui-tabs-selected"> 
             <a href="#campaign"><span>&nbsp;</span>&nbsp;{ts}Campaign{/ts}&nbsp;</a> 
	 </li>&nbsp;
		
	 <li id ="survey_view"  class="crm-tab-button ui-corner-top ui-corner-bottom ui-state-default" >
             <a href="#survey"><span>&nbsp;</span>&nbsp;{ts}Survey{/ts}&nbsp;</a>
         </li>
     </ul>

    <div id="campaignData"></div>
    <div id="surveyData"></div>
 </div>

 <div class="spacer"></div>

{literal}
<script type="text/javascript">
cj(document).ready( function( ) {

    cj('#campaign_view').click( function( ) {
        if ( cj('#campaign_view').hasClass('ui-state-default') ) { 
            cj('#campaign_view').removeClass('ui-state-default').addClass('ui-state-active ui-tabs-selected');
            cj('#survey_view').removeClass('ui-state-active ui-tabs-selected').addClass('ui-state-default');
            cj('#surveyData').html('');
	    
	    //refill campaign data.
	    buildDataView( 'campaign' );
        }
    });

    cj('#survey_view').click( function( ) {
        if ( cj('#survey_view').hasClass('ui-state-default') ) {
            cj('#survey_view').removeClass('ui-state-default').addClass('ui-state-active ui-tabs-selected');
            cj('#campaign_view').removeClass('ui-state-active ui-tabs-selected').addClass('ui-state-default');
	    cj('#campaignData').html( '' );
	    
	    //refill survey data.
	    buildDataView( 'survey' );	   
        }

    });

    buildDataView( {/literal}'{$subPage}'{literal} );
});

function buildDataView( dataType ) {
    var dataUrl = {/literal}"{crmURL p='civicrm/campaign' h=0}"{literal};
    dataUrl    += "&snippet=4&type=" + dataType;
    
    cj.ajax({
        url      : dataUrl,
        async    : false,
        success  : function( html ) {
	    cj( '#' + dataType + 'Data' ).html( html );
        }	 
    });
}        
           
</script>
{/literal}
{/if}