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

{if $rows}
  <div class="form-layout-compressed">{$form.trans_remove.html}&nbsp;{$form.rSubmit.html}</div><br/>
  <div id="ltype">
    <p></p>
    <div class="form-item">
    {strip}
    <table id="crm-transaction-selector-remove" cellpadding="0" cellspacing="0" border="0">
      <thead class="sticky">
      <tr>
        <th class='crm-batch-checkbox' scope="col" title="Select All Rows">{$form.toggleSelects.html}</th>
        {foreach from=$columnHeader item=head key=class}
	  <th class='crm-{$class}'>{$head}</th>
	{/foreach}
        <th></th>
      </tr>
      </thead>

      {foreach from=$rows item=row}
        <tr id="row_{$row.id}"class="{cycle values="odd-row,even-row"} {$row.class}">
	  {assign var=cbName value=$row.checkbox}
          <td>{$form.$cbName.html}</td>
	  {foreach from=$columnHeader item=rowValue key=rowKey}
	    <td>{$row.$rowKey}</td>
	  {/foreach}
	  <td>{$row.action}</td>  
        </tr>
      {/foreach}
    </table>
    {/strip}

  
    </div>
  </div><br/>  
{/if}
{include file="CRM/Financial/Form/BatchTransaction.tpl"}

{literal}
<script type="text/javascript">

function assignRemove(recordID, op) {
  var recordBAO = 'CRM_Financial_BAO_EntityFinancialItem';	
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
  var postUrl = {/literal}"{crmURL p='civicrm/ajax/ar' h=0 }"{literal};
  //post request and get response
  cj.post( postUrl, { recordID: recordID, recordBAO: recordBAO, op:op, entityID:entityID, key: {/literal}"{crmKey name='civicrm/ajax/ar'}"{literal}  }, function( html ){
    responseFromServer = true;      
    //this is custom status set when record update success.
    if (html.status == 'record-updated-success') {
      document.location.reload();
    } 
  }, 'json');

  //if no response from server give message to user.
  setTimeout( "noServerResponse( )", 1500 ); 
}	
</script>
{/literal}