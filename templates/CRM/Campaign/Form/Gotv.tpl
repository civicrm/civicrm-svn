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
 
  {* load voter data *}	
  <script type="text/javascript">loadVoterList( );</script>
 
  <table id="voterRecords">
     <thead>
       <tr class="columnheader">
	   <th>{ts}Name{/ts}</th>
	   <th>{ts}Is Interview Conducted?{/ts}</th>
       </tr>
     </thead>
     <tbody></tbody>
  </table>
 
{else}{* build search form *}
    
    {include file='CRM/Campaign/Form/Search/Common.tpl' context='gotv'}
    <div id='voterList'></div>

{literal}
<script type="text/javascript">
 
 {/literal}
 {* load selector when force *}
 {if $force and !$buildSelector}
 {literal}
 cj( function( ) { 
    //collapse the search form. 	     
    cj( '#searchForm' ).addClass( 'crm-accordion-closed' );	      	  
    searchVoters( );
 }); 	
     	
 {/literal}
 {/if}
 {literal}	

 function searchVoters( ) {
      var dataUrl =  {/literal}"{crmURL p='civicrm/campaign/gotv' h=0 q='search=1&snippet=4' }"{literal}
      {/literal}{if $qfKey}
      dataUrl = dataUrl + '&qfKey=' + '{$qfKey}'; 
      {/if}{literal}

      cj.get( dataUrl, null, function( voterList ) {
	      cj( '#voterList' ).html( voterList );
	      //collapse the search form.
	      if ( !cj( '#searchForm' ).hasClass( 'crm-accordion-closed' ) ) {
	      	 cj( '#searchForm' ).addClass( 'crm-accordion-closed' ); 		 
	      } 
      }, 'html' );
}

</script>
{/literal}

{/if} {* end of search form build *}


{* load jQuery databale *}
{literal}
<script type="text/javascript">

 function loadVoterList( ) 
 {
     var sourceUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='snippet=4&className=CRM_Campaign_Page_AJAX&fnName=voterList' }"{literal};

     cj( '#voterRecords' ).dataTable({
     	        "bFilter"    : false,
		"bAutoWidth" : false,
	    	"bProcessing": true,
		"aoColumns":[{sClass:""},{bSortable:false}],
		"sPaginationType": "full_numbers",
	   	"bServerSide": true,
	   	"sAjaxSource": sourceUrl,
				
		"fnServerData": function ( sSource, aoData, fnCallback ) {
			var dataLength = aoData.length;
		       		
			//get the search criteria.
                        var searchParams = {/literal}{$searchParams}{literal};
                        for ( param in searchParams ) {
                            if ( val = cj( '#' + param ).val( ) ) {
			      aoData[dataLength++] = {name: param , value: val };
			    } 
                        } 

			cj.ajax( {
				"dataType": 'json', 
				"type": "POST", 
				"url": sSource, 
				"data": aoData, 
				"success": fnCallback
			} ); }
     		}); 					
 } 

function processInterview( element ) {
  var interviewActId = cj( element ).val( );
  var isDelete = 0;
  if ( cj( element ).attr( 'checked') ) isDelete = 1;

  if ( !interviewActId ) return;
   
  var actUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_Campaign_Page_AJAX&fnName=processInterview' }"{literal};
  cj.post( actUrl, {'actId': interviewActId, 'delete' :isDelete } );	 	 
}

</script>
{/literal}
