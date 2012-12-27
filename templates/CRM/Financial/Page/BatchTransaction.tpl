{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
	
 <div id="enableDisableStatusMsg" class="success-status" style="display:none;"></div> 
 {if $statusID eq 1}
     <div class="form-layout-compressed">{$form.trans_remove.html}&nbsp;{$form.rSubmit.html}</div><br/>
 {/if}
  <div id="ltype">
    <p></p>
    <div class="form-item">
    {strip}
    <table id="crm-transaction-selector-remove" cellpadding="0" cellspacing="0" border="0">
      <thead>
          <tr>
            <th class="crm-transaction-checkbox">{if $statusID eq 1}{$form.toggleSelects.html}{/if}</th>
	    <th class="crm-contact-type"></th>
            <th class="crm-contact-name">{ts}Contact Name{/ts}</th>
            <th class="crm-amount">{ts}Amount{/ts}</th>
            <th class="crm-received">{ts}Received{/ts}</th>
	    <th class="crm-payment-method">{ts}Payment Method{/ts}</th>
      	    <th class="crm-type">{ts}Type{/ts}</th>
      	    <th class="crm-transaction-links"></th>
    	  </tr>
  	</thead>
    </table>
    {/strip}

  
    </div>
  </div><br/>  
{include file="CRM/Financial/Form/BatchTransaction.tpl"}

{literal}
<script type="text/javascript">

function assignRemove(recordID, op) {
  var recordBAO = 'CRM_Batch_BAO_Batch';	
  if (op == 'remove') {
    var st = {/literal}'{ts escape="js"}Remove from Batch{/ts}'{literal};
  } else if ( op == 'assign' ) {
       	   var st = {/literal}'{ts escape="js"}Assign to Batch{/ts}'{literal};
  }
  var entityID = {/literal}"{$entityID}"{literal};
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
        saveRecord( recordID, op, recordBAO, entityID);
        cj(this).dialog("close");			        
      }
    } 
  });
}

function noServerResponse() {
  if (!responseFromServer) { 
    var serverError =  '{/literal}{ts escape="js"}There is no response from server therefore selected record is not updated.{/ts}{literal}'  + '&nbsp;&nbsp;<a href="javascript:hideEnableDisableStatusMsg();"><img title="{/literal}{ts escape="js"}close{/ts}{literal}" src="' +resourceBase+'i/close.png"/></a>';
    cj( '#enableDisableStatusMsg' ).show( ).html( serverError ); 
  }
}

function saveRecord(recordID, op, recordBAO, entityID) {
  cj( '#enableDisableStatusMsg' ).hide( );
  var postUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_Financial_Page_AJAX&fnName=assignRemove'}"{literal};
  //post request and get response
  cj.post( postUrl, { records: [recordID], recordBAO: recordBAO, op:op, entityID:entityID, key: {/literal}"{crmKey name='civicrm/ajax/ar'}"{literal}  }, function( html ){
    responseFromServer = true;      
    //this is custom status set when record update success.
    if (html.status == 'record-updated-success') {
       buildTransactionSelectorAssign( false );
       buildTransactionSelectorRemove();	
    } 
  }, 'json');

  //if no response from server give message to user.
  setTimeout( "noServerResponse( )", 1500 ); 
}	
</script>
{/literal}
