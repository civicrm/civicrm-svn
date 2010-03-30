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
{* this template is used for adding/editing activities for a case. *}
{if $cdType }
   {include file="CRM/Custom/Form/CustomData.tpl"}
{else}
    {if $action neq 8 and $action  neq 32768 }

{* added onload javascript for source contact*}
{literal}
<script type="text/javascript">
var target_contact = assignee_contact = target_contact_id = '';
{/literal}

{if $targetContactValues}
{foreach from=$targetContactValues key=id item=name}
     {literal} target_contact += '{"name":"'+{/literal}"{$name}"{literal}+'","id":"'+{/literal}"{$id}"{literal}+'"},';{/literal}
{/foreach}
{literal} eval( 'target_contact = [' + target_contact + ']'); {/literal}
{/if}

{if $assigneeContactCount}
{foreach from=$assignee_contact key=id item=name}
     {literal} assignee_contact += '{"name":"'+{/literal}"{$name}"{literal}+'","id":"'+{/literal}"{$id}"{literal}+'"},';{/literal}
{/foreach}
{literal} eval( 'assignee_contact = [' + assignee_contact + ']'); {/literal}
{/if}

{literal}
var target_contact_id = assignee_contact_id = null;
//loop to set the value of cc and bcc if form rule.
var toDataUrl = "{/literal}{crmURL p='civicrm/ajax/checkemail' q='id=1&noemail=1' h=0 }{literal}"; {/literal}
{foreach from=","|explode:"assignee,target" key=key item=element}
  {assign var=currentElement value=`$element`_contact_id}
  {if $form.$currentElement.value}
     {literal} var {/literal}{$currentElement}{literal} = cj.ajax({ url: toDataUrl + "&cid={/literal}{$form.$currentElement.value}{literal}", async: false }).responseText;{/literal}
  {/if}
{/foreach}

{literal}
if ( target_contact_id ) {
  eval( 'target_contact = ' + target_contact_id );
}
if ( assignee_contact_id ) {
  eval( 'assignee_contact = ' + assignee_contact_id );
}

cj(document).ready( function( ) {
{/literal}
{if $source_contact and $admin and $action neq 4} 
{literal} cj( '#source_contact_id' ).val( "{/literal}{$source_contact}{literal}");{/literal}
{/if}

{literal}

eval( 'tokenClass = { tokenList: "token-input-list-facebook", token: "token-input-token-facebook", tokenDelete: "token-input-delete-token-facebook", selectedToken: "token-input-selected-token-facebook", highlightedToken: "token-input-highlighted-token-facebook", dropdown: "token-input-dropdown-facebook", dropdownItem: "token-input-dropdown-item-facebook", dropdownItem2: "token-input-dropdown-item2-facebook", selectedDropdownItem: "token-input-selected-dropdown-item-facebook", inputToken: "token-input-input-token-facebook" } ');

var sourceDataUrl = "{/literal}{$dataUrl}{literal}";
var tokenDataUrl  = "{/literal}{$tokenUrl}{literal}";

var hintText = "{/literal}{ts}Type in a partial or complete name or email address of an existing contact.{/ts}{literal}";
cj( "#assignee_contact_id").tokenInput( tokenDataUrl, { prePopulate: assignee_contact, classes: tokenClass, hintText: hintText });
cj( "#target_contact_id"  ).tokenInput( tokenDataUrl, { prePopulate: target_contact,   classes: tokenClass, hintText: hintText });
cj( 'ul.token-input-list-facebook, div.token-input-dropdown-facebook' ).css( 'width', '450px' );
cj( "#source_contact_id").autocomplete( sourceDataUrl, { width : 180, selectFirst : false, matchContains:true
                            }).result( function(event, data, formatted) { cj( "#source_contact_qid" ).val( data[1] );
                            }).bind( 'click', function( ) { cj( "#source_contact_qid" ).val(''); });
});
</script>
{/literal}

    {/if}

    <fieldset>
        <legend>
           {if $action eq 8}
              {ts}Delete{/ts}
           {elseif $action eq 4}
              {ts}View{/ts}
           {elseif $action eq 32768}
              {ts}Restore{/ts}
           {/if}
           {$activityTypeName}
        </legend>
        <table class="form-layout">
           {if $action eq 8 or $action eq 32768 }
            <div class="messages status"> 
              <dl> 
                 <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt> 
                 <dd> 
                 {if $action eq 8}
                    {ts 1=$activityTypeName}Click Delete to move this &quot;%1&quot; activity to the Trash.{/ts}
                 {else}
                    {ts 1=$activityTypeName}Click Restore to retrieve this &quot;%1&quot; activity from the Trash.{/ts}
                 {/if}  
                 </dd> 
              </dl> 
            </div> 
           {else}
            {if $activityTypeDescription }
           <tr>
              <div id="help">{$activityTypeDescription}</div>
           </tr>
            {/if}
           <tr>
	       {if not $multiClient}
              <td class="label font-size12pt">{ts}Client{/ts}</td>
              <td class="view-value font-size12pt">{$client_name|escape}&nbsp;&nbsp;&nbsp;&nbsp;
	       {else}
              <td class="label font-size12pt">{ts}Clients{/ts}</td>
              <td class="view-value font-size12pt">
		        {foreach from=$client_names item=client name=clients}
		            {$client.display_name}{if not $smarty.foreach.clients.last}; &nbsp; {/if}
                {/foreach}

	       {/if}
	       {if $action eq 2}
	            {if $multiClient}<p/>{/if}<a href="#" onClick="buildTargetContact(1); return false;"><span class="add-remove-link">&raquo; {ts}With other contact(s){/ts}</span></a>
	       {/if}
	          </td>
           </tr>
    	   {if $action eq 2}
        	   <tr>
        	      <td class="label font-size10pt hide-block" id="withContactsLabel">{ts}With Contact{/ts}</td>
         	      <td class="hide-block"  id="withContactsWidget">{$form.target_contact_id.html}</td>
        	      <td class="hide-block">{$form.hidden_target_contact.html}</td>
        	   </tr>
    	   {/if}
           <tr>
              <td class="label">{ts}Activity Type{/ts}</td>
              <td class="view-value bold">{$activityTypeName|escape}</td>
           </tr>
           <tr>
              <td class="label">{$form.source_contact_id.label}</td>
              <td class="view-value"> {if $admin}{$form.source_contact_id.html}{/if}</td>
            </tr>
            <tr>
                <td class="label">{ts}Assigned To {/ts}</td>
                <td>{$form.assignee_contact_id.html}                   
                    {edit}<span class="description">
                           {ts}You can optionally assign this activity to someone.{/ts}
                           {if $config->activityAssigneeNotification}
                               <br />{ts}A copy of this activity will be emailed to each Assignee.{/ts}
                           {/if}
                          </span>
                    {/edit}
                </td>
            </tr>

            {* Include special processing fields if any are defined for this activity type (e.g. Change Case Status / Change Case Type). *}
            {if $activityTypeFile}
                {include file="CRM/Case/Form/Activity/$activityTypeFile.tpl"}
            {/if}
	    {if $activityTypeFile neq 'ChangeCaseStartDate'}
            <tr>
              <td class="label">{$form.subject.label}</td><td class="view-value">{$form.subject.html}</td>
            </tr>
	    {/if}
           <tr>
              <td class="label">{$form.medium_id.label}</td>
              <td class="view-value">{$form.medium_id.html}&nbsp;&nbsp;&nbsp;{$form.location.label} &nbsp;{$form.location.html}</td>
           </tr> 
           <tr>
              <td class="label">{$form.activity_date_time.label}</td>
              <td class="view-value">{include file="CRM/common/jcalendar.tpl" elementName=activity_date_time}</td>
           </tr>
           <tr>
              <td colspan="2"><div id="customData"></div></td>
           </tr>
           <tr>
              <td class="label">{$form.details.label}</td><td class="view-value">{$form.details.html|crmReplace:class:huge}</td>
           </tr>
           <tr>
              <td colspan="2">{include file="CRM/Form/attachment.tpl"}</td>
           </tr>
           {if $searchRows} {* We've got case role rows to display for "Send Copy To" feature *}
            <tr>
                <td colspan="2">
                    <div id="sendcopy_show" class="section-hidden section-hidden-border">
                        <a href="#" onclick="hide('sendcopy_show'); show('sendcopy'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="open section"/></a><label>{ts}Send a Copy{/ts}</label><br />
                    </div>

                    <div id="sendcopy" class="section-shown">
                    <fieldset><legend><a href="#" onclick="hide('sendcopy'); show('sendcopy_show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="close section"/></a>{ts}Send a Copy{/ts}</legend>
                    <div class="description">{ts}Email a complete copy of this activity record to other people involved with the case. Click the top left box to select all.{/ts}</div>
                   {strip}
                   <table>
                      <tr class="columnheader">
                          <th>{$form.toggleSelect.html}&nbsp;</th>
                          <th>{ts}Case Role{/ts}</th>
                          <th>{ts}Name{/ts}</th>
                          <th>{ts}Email{/ts}</th>
                       </tr>
                       {foreach from=$searchRows item=row key=id}
                       <tr class="{cycle values="odd-row,even-row"}">
                           <td>{$form.contact_check[$id].html}</td>
                           <td>{$row.role}</td>
                           <td>{$row.display_name}</td>
                           <td>{$row.email}</td>
                       </tr>
                       {/foreach}
                   </table>
                   {/strip}
                  </fieldset>
                  </div>
                </td>
            </tr>
            {/if}
           <tr>
              <td colspan="2">
                <div id="follow-up_show" class="section-hidden section-hidden-border">
                 <a href="#" onclick="hide('follow-up_show'); show('follow-up'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="open section"/></a><label>{ts}Schedule Follow-up{/ts}</label><br />
                </div>

                <div id="follow-up" class="section-shown">
                <fieldset><legend><a href="#" onclick="hide('follow-up'); show('follow-up_show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="close section"/></a>{ts}Schedule Follow-up{/ts}</legend>
                    <table class="form-layout-compressed">
                        <tr><td class="label">{ts}Schedule Follow-up Activity{/ts}</td>
                            <td>{$form.followup_activity_type_id.html}&nbsp;{$form.interval.label}&nbsp;{$form.interval.html}&nbsp;{$form.interval_unit.html}</td>
                        </tr>
                        <tr>
                           <td class="label">{$form.followup_activity_subject.label}</td>
                           <td>{$form.followup_activity_subject.html}</td>
                        </tr>
                    </table>
                </fieldset>
                </div>
              </td>
           </tr>
           <tr>
              <td class="label">{$form.duration.label}</td>
              <td class="view-value">
                {$form.duration.html}
                 <span class="description">{ts}Total time spent on this activity (in minutes).{/ts}
              </td>
           </tr> 
           <tr>
              <td class="label">{$form.status_id.label}</td><td class="view-value">{$form.status_id.html}</td>
           </tr>
	   <tr>
              <td class="label">{$form.priority_id.label}</td><td class="view-value">{$form.priority_id.html}</td>
           </tr>
	   {if $form.tag.html}
             <tr>
                <td class="label">{$form.tag.label}</td>
                <td class="view-value"><div class="crm-select-container">{$form.tag.html}</div>
                                        {literal}
                                        <script type="text/javascript">
                                                               $("select[multiple]").crmasmSelect({
                                                                        addItemTarget: 'bottom',
                                                                        animate: true,
                                                                        highlight: true,
                                                                        sortable: true,
                                                                        respectParents: true
                                                               });
                                        </script>
                                        {/literal}

                </td>
             </tr>
             {/if}
           {/if}
           <tr>
              <td>&nbsp;</td><td class="buttons">{$form.buttons.html}</td>
            </tr>
        </table>
    </fieldset>

    {if $action eq 1 or $action eq 2}
        {*include custom data js file*}
        {include file="CRM/common/customData.tpl"}
        {literal}
        <script type="text/javascript">
            cj(document).ready(function() {
                {/literal}
                buildCustomData( '{$customDataType}' );
                {if $customDataSubType}
                    buildCustomData( '{$customDataType}', {$customDataSubType} );
                {/if}
                {literal}
            });
        </script>
        {/literal}
    {/if}

    {if $action neq 8 and $action neq 32768} 
        <script type="text/javascript">
            {if $searchRows}
                hide('sendcopy');
                show('sendcopy_show');
            {/if}

            hide('follow-up');
            show('follow-up_show');

        </script>
    {/if}
    
    {* include jscript to warn if unsaved form field changes *}
    {include file="CRM/common/formNavigate.tpl"}

    {literal}
    <script type="text/javascript">   

    {/literal}{if $action eq 2}{literal}
    cj(document).ready( function( ) {
       var reset = {/literal}{if $targetContactValues}true{else}false{/if}{literal};	    
       buildTargetContact( reset );
    });{/literal}
    {/if}{literal}
    
    function buildTargetContact( resetVal ) {
	 var hideWidget  = showWidget = false;	
    	 var value       = cj("#hidden_target_contact").attr( 'checked' );	      
	 
	 if ( resetVal ) {
	     if ( value ) {
	       hideWidget  = true;
	       value       = false;
	     } else {
	       showWidget  = true;
	       value       = true;
	     }
	 } else {
            if ( value ) {
	       showWidget = true;
	     } else {
	       hideWidget = true;
	     }
	 }
	 
	 if ( hideWidget ) {
	    cj('#withContactsLabel').hide( );
	    cj('#withContactsWidget').hide( );
  	 }
	 if ( showWidget ) {
	     cj('#withContactsLabel').show( );
	     cj('#withContactsWidget').show( ); 
	 }
	 cj("#hidden_target_contact").attr( 'checked', value );
    }	
    </script>
    {/literal}

{/if} {* end of main if block*}
</script>
