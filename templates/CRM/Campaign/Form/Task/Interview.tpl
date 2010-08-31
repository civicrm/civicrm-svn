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

{if $votingTab and $errorMessages}
  <div class='messages status-fatal'>
     <div class="icon inform-icon"></div>
        <ul>
	   {foreach from=$errorMessages item=errorMsg}	
             <li>{ts}{$errorMsg}{/ts}</li>
           {/foreach}
       </ul>
     </div>
  </div>

{elseif $voterDetails}
<div class="form-item">
<fieldset>

<div id='help'>
    {if $votingTab}
    {ts}Click <strong>vote</strong> button to update values for each voter as needed.{/ts}
    {else}
    {ts}Click <strong>vote</strong> button to update values for each voter as needed. <br />Click <strong>Release Voters >></strong> button below to continue for release voters. <br />Click <strong>Reserve More Voters >></strong> button below to continue for reserve voters. {/ts}
    {/if}
</div>

<table id="voterRecords" class="display">
    <thead>
       <tr class="columnheader">
             {foreach from=$readOnlyFields item=fTitle key=fName}
	        <th class="contact_details">{$fTitle}</th>
	     {/foreach}
	    
	     {* display headers for survey fields *}
	     {foreach from=$surveyFields item=field key=fieldName}
                  <th>{if $field.data_type eq 'Date' } <img  src="{$config->resourceBase}i/copy.png" alt="{ts 1=$field.title}Click to copy %1 from row one to all rows.{/ts}" onclick="copyValuesDate('{$field.name}')" class="action-icon" title="{ts}Click here to copy the value in row one to ALL rows.{/ts}" /> {else} <img  src="{$config->resourceBase}i/copy.png" alt="{ts 1=$fieldName}Click to copy %1 from row one to all rows.{/ts}" onclick="copyValues('{$fieldName}')" class="action-icon" title="{ts}Click here to copy the value in row one to ALL rows.{/ts}" />{/if}{$field.title}</th>
             {/foreach}

	     <th><img  src="{$config->resourceBase}i/copy.png" alt="{ts 1=note}Click to copy %1 from row one to all rows.{/ts}" onclick="copyValues('note')" class="action-icon" title="{ts}Click here to copy the value in row one to ALL rows.{/ts}" />{ts}Note{/ts}</th>
	     <th><img  src="{$config->resourceBase}i/copy.png" alt="{ts 1=result}Click to copy %1 from row one to all rows.{/ts}" onclick="copyValues('result')" class="action-icon" title="{ts}Click here to copy the value in row one to ALL rows.{/ts}" />{ts}Result{/ts}</th> 
       </tr>
    </thead>

    <tbody>
	{foreach from=$componentIds item=voterId}
	<tr id="row_{$voterId}" class="{cycle values="odd-row,even-row"}">
	    {foreach from=$readOnlyFields item=fTitle key=fName}
	       <td class='name'>{$voterDetails.$voterId.$fName}</td>
	    {/foreach}

	    {* do check for profile fields *}
	    {assign var=surveyFieldCount value=$surveyFields|@count}
	    
	    {* here build the survey fields *}
	    {if $surveyFieldCount}
	    {assign var=currentCount value='1'}
	    {foreach from=$surveyFields item=field key=fieldName}
                {assign var=n value=$field.name}
		<td class="compressed">
                {if ( $field.data_type eq 'Date') or 
		    ( $n eq 'thankyou_date' ) or ( $n eq 'cancel_date' ) or ( $n eq 'receipt_date' ) or (  $n eq 'activity_date_time') }
                    {include file="CRM/common/jcalendar.tpl" elementName=$fieldName elementIndex=$voterId batchUpdate=1}
                {else}
                   {$form.field.$voterId.$n.html}
                {/if}
		</td> 
		{assign var=currentCount value=$currentCount+1}     
            {/foreach}
	    {/if}
	    
	    <td class='note'>{$form.field.$voterId.note.html}</td>
	    <td class='result'>{$form.field.$voterId.result.html}
		&nbsp;&nbsp;&nbsp;<a class="saveVoter button" style="float:right;" href="#" title={ts}Vote{/ts} onClick="registerInterview( {$voterId} );return false;">{ts}vote{/ts}</a>&nbsp;&nbsp;&nbsp;
		<span id='restmsg_{$voterId}' class="ok" style="display:none; float:right;">{ts}Vote Saved.{/ts}</span> 
	    </td>

	</tr>
	{/foreach}
    </tbody>
</table>

 {if !$votingTab}
 <div class="spacer"></div>
 <div class="crm-submit-buttons">{$form.buttons.html}</div>
 {/if}

</fieldset>
</div>


{literal}
<script type="text/javascript">
    var updateVote = "{/literal}{ts}Update Vote{/ts}{literal}";	
    cj( function( ) {
        var count = 0; var columns=''; var sortColumn = '';
	
        cj('#voterRecords th').each( function( ) {
          if ( cj(this).attr('class') == 'contact_details' ) {
	    sortColumn += '[' + count + ', "asc" ],'; 
	    columns += '{"sClass": "contact_details"},';
	  } else {
	    columns += '{ "bSortable": false },';
	  }
	  count++; 
	});

	columns    = columns.substring(0, columns.length - 1 );
	sortColumn = sortColumn.substring(0, sortColumn.length - 1 );
	eval('sortColumn =[' + sortColumn + ']');
	eval('columns =[' + columns + ']');

	//load jQuery data table.
        cj('#voterRecords').dataTable( {
		"sPaginationType": "full_numbers",
		"aaSorting"  : sortColumn,
		"aoColumns"  : columns
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
	data['note']             = cj( '#field_' + voterId + '_note' ).val( );

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
{*include batch copy js js file*}
{include file="CRM/common/batchCopy.tpl"}
{/if}

