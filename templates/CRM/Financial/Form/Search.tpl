{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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

{* Template for "Sample" custom search component. *}
<div id="enableDisableStatusMsg" class="success-status" style="display:none;"></div> 
<div class="crm-form-block crm-search-form-block">
<div class="crm-accordion-wrapper crm-activity_search-accordion">
 <div class="crm-accordion-header crm-master-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
   {ts}Edit Search Criteria{/ts}
</div><!-- /.crm-accordion-header -->
<div class="crm-accordion-body">
<div id="searchForm" class="crm-block crm-form-block crm-contact-custom-search-activity-search-form-block">
      <div class="crm-submit-buttons">{$form.buttons.html}</div>
        <table class="form-layout-compressed">
            {* Loop through all defined search criteria fields (defined in the buildForm() function). *}
            {foreach from=$elements item=element}
                <tr class="crm-contact-custom-search-Constituent-search-form-block-{$element}">
                    <td class="label">{$form.$element.label}</td>
                    <td>{$form.$element.html}</td>
                </tr>
            {/foreach}
        </table>
</div>
</div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->
</div><!-- /.crm-form-block -->
{if $batchStatus eq 1}
    {assign var=batchStatusLabel value="Open"}
{elseif $batchStatus eq 2}
    {assign var=batchStatusLabel value="Closed"}
{else}
    {assign var=batchStatusLabel value="Exported"}
{/if}		
<div class="crm-submit-buttons">
    <a accesskey="N" href="{crmURL p='civicrm/financial/batch' q='reset=1&action=add'}" id="newBatch" class="button"><span><div class="icon add-icon"></div>{ts}New Financial Batch{/ts}</span></a><br/>
</div><br/>
<div class="form-layout-compressed">{$form.batch_status.html}&nbsp;{$form.submit.html}</div><br/>
<table id="crm-batch-selector">
  <thead>
    <tr>
      <th class="crm-batch-checkbox">{$form.toggleSelect.html}</th>
      <th class="crm-batch-name">{ts}Batch Name{/ts}</th>
      <th class="crm-batch-type">{ts}Type{/ts}</th>
      <th class="crm-batch-item_count">{ts}Item Count{/ts}</th>
      <th class="crm-batch-total_amount">{ts}Total Amount{/ts}</th>
      <th class="crm-batch-status">{ts}Status{/ts}</th>
      <th class="crm-batch-created_by">{ts}Created By{/ts}</th>
      <th></th>
    </tr>
  </thead>
</table>
{literal}
<script type="text/javascript">
cj( function() {
    buildBatchSelector( false );
    cj('#_qf_Search_refresh').click( function() {
        buildBatchSelector( true );
    });
    cj("#batch_status").attr('disabled',true);
    cj("#toggleSelect").click( function() {
      if (cj("#toggleSelect").is(':checked')) {
       	  cj("#crm-batch-selector input[id^='check_']").prop('checked',true);    
      }
      else {
          cj("#crm-batch-selector input[id^='check_']").prop('checked',false);	
      }
   });
});
function enableActions() {
    cj("#batch_status").attr('disabled',false);
}

function buildBatchSelector( filterSearch ) {
  var status = {/literal}{$status}{literal};
  if ( filterSearch ) {
    crmBatchSelector.fnDestroy();
    var ZeroRecordText = '<div class="status messages">{/literal}{ts escape="js"}No matching Financial Batches found for your search criteria.{/ts}{literal}</li></ul></div>';
  } else if ( status == 1 ) {
    var ZeroRecordText = {/literal}'<div class="status messages">{ts escape="js"}You do not have any {$batchStatusLabel} Financial Batches.{/ts}</div>'{literal};
  } else {
    var ZeroRecordText = {/literal}'<div class="status messages">{ts escape="js"}No Financial Batches have been created for this site.{/ts}</div>'{literal};
  }

    var columns = '';
    var sourceUrl = {/literal}'{crmURL p="civicrm/ajax/batchlist" h=0 q="snippet=4&context=financialBatch&batchStatus=$batchStatus"}'{literal};

    crmBatchSelector = cj('#crm-batch-selector').dataTable({
        "bFilter"    : false,
        "bAutoWidth" : false,
        "aaSorting"  : [],
        "aoColumns"  : [
		        {sClass:'crm-batch-checkbox', bSortable:false},
                        {sClass:'crm-batch-name'},
                        {sClass:'crm-batch-type'},
                        {sClass:'crm-batch-item_count right'},
                        {sClass:'crm-batch-total_amount right'},
                        {sClass:'crm-batch-status'},
                        {sClass:'crm-batch-created_by'},
                        {sClass:'crm-batch-links', bSortable:false}
                       ],
        "bProcessing": true,
        "asStripClasses" : [ "odd-row", "even-row" ],
        "sPaginationType": "full_numbers",
        "sDom"       : '<"crm-datatable-pager-top"lfp>rt<"crm-datatable-pager-bottom"ip>',
        "bServerSide": true,
        "bJQueryUI": true,
        "sAjaxSource": sourceUrl,
        "iDisplayLength": 25,
        "oLanguage": { "sZeroRecords":  ZeroRecordText,
                       "sProcessing":    {/literal}"{ts escape='js'}Processing...{/ts}"{literal},
                       "sLengthMenu":    {/literal}"{ts escape='js'}Show _MENU_ entries{/ts}"{literal},
                       "sInfo":          {/literal}"{ts escape='js'}Showing _START_ to _END_ of _TOTAL_ entries{/ts}"{literal},
                       "sInfoEmpty":     {/literal}"{ts escape='js'}Showing 0 to 0 of 0 entries{/ts}"{literal},
                       "sInfoFiltered":  {/literal}"{ts escape='js'}(filtered from _MAX_ total entries){/ts}"{literal},
                       "sSearch":        {/literal}"{ts escape='js'}Search:{/ts}"{literal},
                       "oPaginate": {
                            "sFirst":    {/literal}"{ts escape='js'}First{/ts}"{literal},
                            "sPrevious": {/literal}"{ts escape='js'}Previous{/ts}"{literal},
                            "sNext":     {/literal}"{ts escape='js'}Next{/ts}"{literal},
                            "sLast":     {/literal}"{ts escape='js'}Last{/ts}"{literal}
                        }
                    },
        "fnServerData": function ( sSource, aoData, fnCallback ) {
            if ( filterSearch ) {
                aoData.push(
                    {name:'title', value: cj('#title').val()}
                );
            }
            cj.ajax( {
                "dataType": 'json',
                "type": "POST",
                "url": sSource,
                "data": aoData,
                "success": fnCallback
            } );
        }
    });
}

function closeReopen( recordID, op ) {

	var recordBAO = 'CRM_Batch_BAO_Batch';
	if ( op == 'close' ) {
       	   var st = {/literal}'{ts escape="js"}Close the Batch{/ts}'{literal};
    	} else if ( op == 'reopen' ) {
       	   var st = {/literal}'{ts escape="js"}Reopen the Batch{/ts}'{literal};
    	}

	cj("#enableDisableStatusMsg").show( );
	cj("#enableDisableStatusMsg").dialog({
		title: st,
		modal: true,
		bgiframe: true,
		position: "right",
		overlay: { 
			opacity: 0.5, 
			background: "black" 
		},

		open:function() {
       		        var postUrl = {/literal}"{crmURL p='civicrm/ajax/statusmsg' h=0 }"{literal};
		        cj.post( postUrl, { recordID: recordID, recordBAO: recordBAO, op: op  }, function( statusMessage ) {
			        if ( statusMessage.status ) {
 			            cj( '#enableDisableStatusMsg' ).show().html( statusMessage.status );
       	     		        }
			
	       	        }, 'json' );
		},
	
		buttons: { 
			"Cancel": function() { 
				cj(this).dialog("close"); 
			},
			"OK": function() {    
			        saveRecord( recordID, op, recordBAO);
			        cj(this).dialog("close");			        
			}
		} 
	});
}
		
function noServerResponse( ) {
    if ( !responseFromServer ) { 
        var serverError =  '{/literal}{ts escape="js"}There is no response from server therefore selected record is not updated.{/ts}{literal}'  + '&nbsp;&nbsp;<a href="javascript:hideEnableDisableStatusMsg();"><img title="{/literal}{ts escape="js"}close{/ts}{literal}" src="' +resourceBase+'i/close.png"/></a>';
        cj( '#enableDisableStatusMsg' ).show( ).html( serverError ); 
    }
}

function saveRecord( recordID, op, recordBAO) {
    cj( '#enableDisableStatusMsg' ).hide( );
    var postUrl = {/literal}"{crmURL p='civicrm/ajax/ar' h=0 }"{literal};
    //post request and get response
    cj.post( postUrl, { recordID: recordID, recordBAO: recordBAO, op:op, key: {/literal}"{crmKey name='civicrm/ajax/ar'}"{literal}  }, function( html ){
        responseFromServer = true;      
       
        //this is custom status set when record update success.
        if ( html.status == 'record-updated-success' ) {
               document.location.reload( );
           } 

              }, 'json' );

        //if no response from server give message to user.
        setTimeout( "noServerResponse( )", 1500 ); 
    }

function selectAction() {
  if (cj('#batch_status').is(':disabled')) {
     return false;
  }
  else if (!cj("#toggleSelect").is(':checked') && !cj("#crm-batch-selector input[id^='check_']").is(':checked') && cj("#batch_status").val() != "") {
     alert ("Please select one or more batches for this action.");
     return false;
  }
  else if (cj("#batch_status").val() == "") {
     alert ("Please select an action from the drop-down menu.");
     return false; 
  }
}
 
</script>
{/literal}
