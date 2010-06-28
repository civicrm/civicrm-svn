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

	     {* display headers for survey fields *}
	     {foreach from=$surveyFields item=fVal key=fId}
	        <th>{$fVal.label}</th>
	     {/foreach}
	     <th></th>
       </tr>
    </thead>

    <tbody>
	{foreach from=$voterIds item=voterId}
	<tr class="{cycle values="odd-row,even-row"}">
	    {foreach from=$readOnlyFields item=fTitle key=fName}
	       <td>{$voterDetails.$voterId.$fName}</td>
	    {/foreach}

	    {* here build the survey fields *}
	    {foreach from=$surveyFields item=field key=fId}
		{assign var=name value=$field.element_name}
	        <td>{$form.field.$voterId.$name.html}</td>
	    {/foreach}
		<td> 
		&nbsp;&nbsp;<a id='saveVoter' href="#" title={ts}Save{/ts} onClick="registerInterview( {$voterId} );return false;">{ts}save{/ts}</a>
		</td>
	</tr>
	{/foreach}
    </tbody>
</table>

<div class="crm-submit-buttons">{$form.buttons.html}</div>
</fieldset>
{/if}

{literal}
<script type="text/javascript">
	
    cj( function( ) {

	//load jQuery data table.
        cj('#voterRecords').dataTable( {
            "bPaginate": false,
            "bInfo": false,
        } );        

    });

    function registerInterview( voterId )
    {
    	var data = new Object;
    	var fieldName = 'field_' + voterId + '_custom_';
	cj( '[id^="'+ fieldName +'"]' ).each( function( ) {
	    data[cj(this).attr( 'id' )] = cj( this ).val( );
        });

	data['voter_id']       = voterId;
	data['interviewer_id'] = {/literal}{$interviewerId}{literal};
	data['survey_type_id'] = {/literal}{$surveyTypeId}{literal};	
				     
	var dataUrl = {/literal}"{crmURL p='civicrm/survey/interview' h=0 }"{literal}	          
	
	//post data to create interview.
	cj.post( dataUrl, data );
    }
    
</script>
{/literal}

