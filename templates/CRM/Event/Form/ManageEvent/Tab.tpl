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
	      <div id="crm-event-links-link"><span><div class="icon dropdown-icon"></div>Event Links</span></div>
	      <div class="ac_results" id="crm-event-links-list">
	      	   <div class="crm-event-links-list-inner">
	      	   	<ul><li><a class="crm-event-info" href="/civicrm/event/info?reset=1&amp;id={$id}">Event Info</a></li>
		            <li><a class="crm-event-test" href="/civicrm/event/register?reset=1&amp;action=preview&amp;id={$id}">Registration (Test-drive)</a></li>
		            <li><a class="crm-event-live" href="/civicrm/event/register?reset=1&amp;id={$id}">Registration (Live)</a></li>
		        </ul>
	           </div>
	      </div>
        </div>

	<div id="crm-participant-wrapper">
	      <div id="crm-participant-link"><span><div class="icon dropdown-icon"></div>Participants</span></div>
	      <div class="ac_results" id="crm-participant-list">
	      	   <div class="crm-participant-list-inner">
	      	   	<ul><li><a class="crm-participant-counted" href="/civicrm/event/search?reset=1&amp;force=1&amp;event={$id}&amp;status=true">{$findParticipants.statusCounted|replace:'/':', '}</a></li>
		            <li><a class="crm-participant-not-counted" href="/civicrm/event/search?reset=1&amp;force=1&amp;event={$id}&amp;status=false">{$findParticipants.statusNotCounted|replace:'/':', '}</a></li>
		            <li><a class="crm-participant-listing" href="/civicrm/event/participant?reset=1&amp;id={$id}">Public Participant Listing</a></li>
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
	 	});
	
	 cj('#crm-event-links-list').click(function(event){
	     event.stopPropagation();
	 	});

cj('#crm-event-links-list li').hover(
	function(){ cj(this).addClass('ac_over');},
	function(){ cj(this).removeClass('ac_over');}
	);

cj('#crm-event-links-link').click(function(event) {
	cj('#crm-event-links-list').toggle();
	event.stopPropagation();
	});


cj('body').click(function() {
	 	cj('#crm-participant-list').hide();
	 	});
	
	 cj('#crm-participant-list').click(function(event){
	     event.stopPropagation();
	 	});

cj('#crm-participant-list li').hover(
	function(){ cj(this).addClass('ac_over');},
	function(){ cj(this).removeClass('ac_over');}
	);

cj('#crm-participant-link').click(function(event) {
	cj('#crm-participant-list').toggle();
	event.stopPropagation();
	});

</script>

{/literal}