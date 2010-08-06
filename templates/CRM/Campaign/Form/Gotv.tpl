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
    {include file='CRM/Campaign/Form/Search/Common.tpl' context='gotv'}
    <div id='voterList'></div>

{literal}
<script type="text/javascript">

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
