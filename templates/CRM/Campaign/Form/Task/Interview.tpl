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
<div class="form-item">
<fieldset>
<div id="help">
    {ts}Update field values for each voter as needed. Click <strong>Record Voters Interview</strong> below to save all your changes. To set a field to the same value for ALL rows.{/ts}
</div>

{if $voterDetails}

<table id="voterRecords" class="display">
    <thead>
       <tr class="columnheader">
             {foreach from=$readOnlyFields item=fTitle key=fName}
	        <th>{$fTitle}</th>
	     {/foreach}
	     
	     {if $hasResultField}
	     	<th>{ts}Result{/ts}</th> 
	     {/if}
	     
	     {* display headers for survey fields *}
	     {foreach from=$surveyFields item=field key=fieldName}
                  <th>{$field.title}</th>
             {/foreach}
	     
       </tr>
    </thead>

    <tbody>
	{foreach from=$voterIds item=voterId}
	<tr id="row_{$voterId}" class="{cycle values="odd-row,even-row"}">
	    {foreach from=$readOnlyFields item=fTitle key=fName}
	       <td class='name'>{$voterDetails.$voterId.$fName}</td>
	    {/foreach}

	    {* do check for profile fields *}
	    {assign var=surveyFieldCount value=$surveyFields|@count}
	    
	    {if $hasResultField}
	      	<td class='result'>{$form.field.$voterId.result.html}
		{if !$surveyFieldCount}{*no profile fields*}
		   &nbsp;&nbsp;&nbsp;<a class="saveVoter" href="#" title={ts}Vote{/ts} onClick="registerInterview( {$voterId} );return false;">{ts}vote{/ts}</a>&nbsp; <span id='restmsg_{$voterId}' class="ok" style="display:none">{ts}Vote Saved.{/ts}</span> 
		{/if}
		</td>
	    {/if}
	    
	    {* here build the survey fields *}
	    {if $surveyFieldCount}
	    {assign var=currentCount value='1'}
	    {foreach from=$surveyFields item=field key=fieldName}
                {assign var=n value=$field.name}
		<td class="compressed">
                {if ( $fields.$n.data_type eq 'Date') or 
		    ( $n eq 'thankyou_date' ) or ( $n eq 'cancel_date' ) or ( $n eq 'receipt_date' )}
                   {include file="CRM/common/jcalendar.tpl" elementName=$n elementIndex=$voterId batchUpdate=1}</td>
                {else}
                   {$form.field.$voterId.$n.html}
                {/if}

		{if $currentCount eq $surveyFieldCount}
		    &nbsp;&nbsp;&nbsp;<a class="saveVoter" href="#" title={ts}Vote{/ts} onClick="registerInterview( {$voterId} );return false;">{ts}vote{/ts}</a>&nbsp; <span id='restmsg_{$voterId}' class="ok" style="display:none">{ts}Vote Saved.{/ts}</span>
 		{/if}
		</td> 
		{assign var=currentCount value=$currentCount+1}     
            {/foreach}
	    {/if}
	    
	</tr>
	{/foreach}
    </tbody>
</table>

<div class="spacer"></div>
<div class="crm-submit-buttons">{$form.buttons.html}</div>
</fieldset>
{/if}

{literal}
<script type="text/javascript">
    var updateVote = "{/literal}{ts}Update Vote{/ts}{literal}";	
    cj( function( ) {

	//load jQuery data table.
        cj('#voterRecords').dataTable( {
		"sPaginationType": "full_numbers"
        });        

    });

    function registerInterview( voterId )
    {
    	var data = new Object;
    	var fieldName = 'field_' + voterId + '_custom_';
	cj( '[id^="'+ fieldName +'"]' ).each( function( ) {
	    if( cj(this).attr( 'type' ) == 'select-multiple' ) {
	      var eleId = cj(this).attr('id');
	      cj('#' + eleId +" option").each( function(i) {
	        if ( cj(this).attr('selected') == true ) {
		  data[eleId + '['+cj(this).val()+']'] = cj(this).val();
		} 
	      });
	    } else {
	      data[cj(this).attr( 'id' )] = cj( this ).val( );
            }
        });
		
	var multiValueFields = 'field['+ voterId +'][custom_';		
	cj( '[id^="'+ multiValueFields +'"]' ).each( function( ) {
	   if ( cj(this).attr( 'type' ) == 'checkbox' ) {
	     if ( cj(this).attr('checked') == true ) {
	       data[cj(this).attr( 'id' )] = 1;
             } else {
	       data[cj(this).attr( 'id' )] = '';
	     }
           }
        });
	
	var surveyActivityIds = {/literal}{$surveyActivityIds}{literal};
	activityId =  eval( "surveyActivityIds.activity_id_" + voterId );
	if ( !activityId ) return; 	

	data['voter_id']         = voterId;
	data['interviewer_id']   = {/literal}{$interviewerId}{literal};
	data['activity_type_id'] = {/literal}{$surveyTypeId}{literal};
	data['activity_id']      = activityId;
	data['result']           = cj( '#field_' + voterId + '_result' ).val( ); 

	var dataUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_Campaign_Page_AJAX&fnName=registerInterview' }"{literal}	          
	
	//post data to create interview.
	cj.post( dataUrl, data, function( interview ) {
	       if ( interview.status == 'success' ) {
	       	 cj("#row_"+voterId+' td.name').attr('class', 'name disabled' );
		 cj("#restmsg_"+voterId).fadeIn("slow").fadeOut("slow");
		 cj("#row_"+voterId+' a.saveVoter').html(updateVote);
	       }		 
	}, "json" );
    }
    
</script>
{/literal}

