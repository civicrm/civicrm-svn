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
{* Gotv form for voters. *}

{if $buildSelector}
 {* build selector *}
 {if $voters}
 <table id="voterRecords" class='display'>
   <thead>
     <tr class="columnheader">
	    <th>{ts}Name{/ts}</th>
	    <th></th>
     </tr>
   </thead>
  
   <tbody>  
     {counter start=0 skip=1 print=false}
     {foreach from=$voters item=voter}
     <tr id='rowid{$voter.voter_id}' class="{cycle values="odd-row,even-row"} crm-campaign">
  	<td>{$voter.sort_name}</td>
	<td>{$voter.voter_check}</td>
     </tr>
     {/foreach}
   </tbody>
 </table>
 {else}
 {include file="CRM/Campaign/Form/Search/EmptyResults.tpl"} 
 {/if}
 
{else}{* build search form *}
    <div class="crm-block crm-form-block crm-gotv-form-block">
    <div id='searchForm' class="crm-accordion-wrapper crm-campaign_search_form-accordion"> 

    <div class="crm-accordion-header crm-master-accordion-header">
        <div class="icon crm-accordion-pointer"></div> 
        {ts}Edit Search Criteria{/ts}
    </div><!-- /.crm-accordion-header -->

    <div class="crm-accordion-body">
    {strip} 
        <table class="form-layout">
	<tr>
            <td class="font-size12pt">
                {$form.campaign_survey_id.label}
            </td>
            <td>
	        {$form.campaign_survey_id.html}
            </td>

	    {if $showInterviewer}
	    <td class="font-size12pt">
	        {$form.survey_interviewer_name.label}
            </td>
            <td class="font-size12pt ">
	        {$form.survey_interviewer_name.html}
            </td>  
	    {/if}		    

	</tr>
        <tr>
            <td class="font-size12pt">
                {$form.sort_name.label}
            </td>
            <td>			
		{$form.sort_name.html|crmReplace:class:'twenty'}
            </td>       
        </tr>
	<tr>
            <td class="font-size12pt">
                {$form.street_name.label}
       	    </td>
            <td>	
                {$form.street_name.html}
            </td>
	</tr>	

	<tr>
            <td class="font-size12pt">
                {$form.street_number.label}
       	    </td>
            <td>	
                {$form.street_number.html}
            </td>
	</tr>

        <tr>
            <td class="font-size12pt">
                {$form.street_type.label}
       	    </td>
            <td>	
                {$form.street_type.html}
            </td>
	</tr>

	<tr>
            <td class="font-size12pt">
                {$form.street_address.label}
	    </td>
            <td>
                {$form.street_address.html}
            </td>
	</tr>
	<tr>
            <td class="font-size12pt">
                {$form.city.label}
            </td>
            <td>
                {$form.city.html}
            </td>
	</tr>

	{if $customSearchFields.ward}
	{assign var='ward' value=$customSearchFields.ward}
	<tr>
            <td class="font-size12pt">
                {$form.$ward.label}
            </td>
            <td>
                {$form.$ward.html}
            </td>
	</tr>
	{/if}

	{if $customSearchFields.precinct}
	{assign var='precinct' value=$customSearchFields.precinct}
	<tr>
            <td class="font-size12pt">
                {$form.$precinct.label}
            </td>
            <td>
                {$form.$precinct.html}
            </td>
	</tr>
	{/if}
        <tr>
	   <td colspan="2">
	   <a class="searchVoter button" style="float:left;" href="#" title={ts}Search{/ts} onClick="searchVoters( );return false;">{ts}Search{/ts}</a>	
	   </td>
        </tr>
        </table>
    {/strip}

    </div>
    </div>
    </div>

    <div id='voterList'></div>

{literal}
<script type="text/javascript">
	
  cj(function() {
      cj().crmaccordions(); 
  });

  //load interviewer autocomplete.
  var interviewerDataUrl = "{/literal}{$dataUrl}{literal}";
  var hintText = "{/literal}{ts}Type in a partial or complete name of an existing contact.{/ts}{literal}";
  cj( "#survey_interviewer_name" ).autocomplete( interviewerDataUrl, 
                                                 { width : 256, 
                                                   selectFirst : false, 
                                                   hintText: hintText, 
                                                   matchContains: true, 
                                                   minChars: 1
                                                  }
                                                 ).result( function( event, data, formatted ) { 
				                              cj( "#survey_interviewer_id" ).val( data[1] );
                                                         }).bind( 'click', function( ) { 
                                                              cj( "#survey_interviewer_id" ).val(''); 
                                                         });


function searchVoters( ) {
  //get the search criteria.
  var searchCritera = new Object;
  var searchParams = {/literal}{$searchParams}{literal};
  for ( param in searchParams ) {
     if ( val = cj( '#' + param ).val( ) ) searchCritera[param] = val;
  }  
    
  var dataUrl =  {/literal}"{crmURL p='civicrm/campaign/gotv' h=0 q='reset=1&search=1&snippet=4' }"{literal}
  
  {/literal}
  {if $qfKey}
  
  dataUrl = dataUrl + '&qfKey=' + '{$qfKey}'; 
  
  {/if}
  {literal}

  //post data to create interview.
  cj.post( dataUrl, searchCritera, function( voterList ) {
	  cj( '#voterList'  ).html( voterList );
	  cj( '#searchForm' ).addClass( 'crm-accordion-closed' );
  }, 'html' );
}

</script>
{/literal}

{/if} {* end of search form build *}


{* load jQuery databale *}
{literal}
<script type="text/javascript">

cj( function( ) {

	//load jQuery data table.
        cj('#voterRecords').dataTable( {
	    "sPaginationType": "full_numbers"
        });        
    });

</script>
{/literal}
