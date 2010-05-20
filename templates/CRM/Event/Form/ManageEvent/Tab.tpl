{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
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
<div>
	<div id="crm-event-links-wrapper">
	      <div id="crm-event-links-link"><span><div class="icon dropdown-icon"></div>{ts}Event Links{/ts}</span></div>
	      <div class="ac_results" id="crm-event-links-list">
	      	   <div class="crm-event-links-list-inner">
	      	   	<ul><li><a class="crm-event-info" href="{crmURL p='civicrm/event/info' q="reset=1&id=`$id`"}">{ts}Event Info{/ts}</a></li>
		            <li><a class="crm-event-test" href="{crmURL p='civicrm/event/register' q="reset=1&action=preview&id=`$id`"}">{ts}Registration (Test-drive){/ts}</a></li>
		            <li><a class="crm-event-live" href="{crmURL p='civicrm/event/register' q="reset=1&id=`$id`"}">{ts}Registration (Live){/ts}</a></li>
		        </ul>
	           </div>
	      </div>
        </div>

	<div id="crm-participant-wrapper">
	      <div id="crm-participant-link"><span><div class="icon dropdown-icon"></div>{ts}Participants{/ts}</span></div>
	      <div class="ac_results" id="crm-participant-list">
	      	   <div class="crm-participant-list-inner">
	      	   	<ul>
			    {if $findParticipants.statusCounted}
			    	<li><a class="crm-participant-counted" href="{crmURL p='civicrm/event/search' q="reset=1&force=1&event=`$id`&status=true"}">{$findParticipants.statusCounted|replace:'/':', '}</a></li>
			    {/if}
		            {if $findParticipants.statusNotCounted}
			    	<li><a class="crm-participant-not-counted" href="{crmURL p='civicrm/event/search' q="reset=1&force=1&event=`$id`&status=false"}">{$findParticipants.statusNotCounted|replace:'/':', '}</a>
				</li>
			    {/if}
		            <li><a class="crm-participant-listing" href="{crmURL p='civicrm/event/participant' q="reset=1&id=`$id`"}">{ts}Public Participant Listing{/ts}</a></li>
		        </ul>
	           </div>
	      </div>
        </div>
	&nbsp;
</div>

{help id="id-configure-events"}
{include file="CRM/common/TabHeader.tpl"}

{literal}
<script>

cj('body').click(function() {
	cj('#crm-event-links-list').hide();
	cj('#crm-participant-list').hide();
	});

cj('#crm-event-links-link').click(function(event) {
	cj('#crm-event-links-list').toggle();
	cj('#crm-participant-list').hide();
	event.stopPropagation();
	});

cj('#crm-participant-link').click(function(event) {
	cj('#crm-participant-list').toggle();
        cj('#crm-event-links-list').hide();						  
	event.stopPropagation();
	});

</script>
{/literal}