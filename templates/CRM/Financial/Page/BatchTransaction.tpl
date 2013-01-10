{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
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

<div id="enableDisableStatusMsg" class="crm-container" style="display:none;"></div>
<table id="batch-summary" cellpadding="0" cellspacing="0" border="0">
  <thead class="sticky">
  <tr>
  {foreach from=$columnHeaders item=head}
    <th>{$head}</th>
  {/foreach}
  </tr>
  </thead>
  <tbody>
  <tr>
  {foreach from=$columnHeaders item=head key=rowKey}
    <td id = "row_{$rowKey}" class="even-row"></td>
  {/foreach}
  </tr>
  </tbody>
</table>

{if $statusID eq 1}
<div class="crm-submit-buttons">{$form.close_batch.html}{$form.export_batch.html}</div><br/>
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
</div>
<br/>

{include file="CRM/Financial/Form/BatchTransaction.tpl"}

{literal}
<script type="text/javascript">
cj( function() {
  var entityID = {/literal}{$entityID}{literal};
  batchSummary(entityID);
  cj('#close_batch').click( function() {
    assignRemove(entityID, 'close');
    return false;
  });
  cj('#export_batch').click( function() {
    assignRemove(entityID, 'export');
    return false;
  });
});

function assignRemove(recordID, op) {
  var recordBAO = 'CRM_Batch_BAO_Batch';
  if (op == 'remove') {
    var st = {/literal}'{ts escape="js"}Remove from Batch{/ts}'{literal};
  }
  else if ( op == 'assign' ) {
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
          msg = statusMessage.status;
          if (op == 'close' || op == 'export') {
            var mismatch = checkMismatch();
            if (mismatch !== false) {
              msg += mismatch;
            }
            else if (op == 'export') {
              window.location.href = CRM.url('civicrm/financial/batch/export', {reset: 1, id: recordID, status: 1});
              msg = {/literal}'{ts escape="js"}Exporting Batch{/ts}'{literal};
            }
          }
          cj( '#enableDisableStatusMsg' ).show().html(msg);
        }
      }, 'json' );
    },
    buttons: {
      "Cancel": function() {
        cj(this).dialog("close");
      },
      "OK": function() {
        if (op == 'export') {
          window.location.href = CRM.url('civicrm/financial/batch/export', {reset: 1, id: recordID, status: 1});
          return;
        }
        saveRecord(recordID, op, recordBAO, entityID);
        if (op == 'close') {
          window.location.href = {/literal}"{crmURL p='civicrm/financial/financialbatches' h=0 q='reset=1&batchStatus=2'}"{literal};
        }
        cj(this).dialog("close");
      }
    }
  });
}

function noServerResponse() {
  if (!responseFromServer) {
    CRM.alert({/literal}'{ts escape="js"}No response from the server. Check your internet connection and try reloading the page.{/ts}', '{ts escape="js"}Network Error{/ts}'{literal}, 'error');
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
      buildTransactionSelectorAssign( true );
      buildTransactionSelectorRemove();
      batchSummary(entityID);
    }
    else {
      CRM.alert(html.status);
    }
  },
  'json');

  //if no response from server give message to user.
  setTimeout( "noServerResponse( )", 1500 );
}

function batchSummary(entityID) {
  var postUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_Financial_Page_AJAX&fnName=getBatchSummary'}"{literal};
  //post request and get response
  cj.post( postUrl, { batchID: entityID } , function( html ) {
    cj.each(html, function(i, val) {
      cj("#row_" + i).html(val);
    });
  },
  'json');
}

function checkMismatch() {
  var txt = '<ul>';
  var mismatch = false;
  var enteredItem = cj("#row_item_count").text();
  var assignedItem = cj("#row_assigned_item_count").text();
  var enteredTotal = cj("#row_total").text();
  var assignedTotal = cj("#row_assigned_total").text();
  if (enteredItem != "" & enteredItem != assignedItem) {
    mismatch = true;
    txt += '{/literal}<li><span class="crm-error">Item Count mismatch<br/>{ts escape="js"}Expected{/ts}:{literal}' + enteredItem +'{/literal}<br/>{ts escape="js"}Current Total{/ts}:{literal}' + assignedItem + '{/literal}</span></li>{literal}';
  }
  if (enteredTotal != "" & enteredTotal != assignedTotal) {
    mismatch = true;
    txt += '{/literal}<li><span class="crm-error">Total Amount mismatch<br/>{ts escape="js"}Expected{/ts}:{literal}' + enteredTotal +'{/literal}<br/>{ts escape="js"}Current Total{/ts}:{literal}' + assignedTotal + '{/literal}</span></li>{literal}';
  }
  if (mismatch) {
    txt += {/literal}'</ul><div class="messages status">{ts escape="js"}Click OK to override and update expected values.{/ts}</div>'{literal}
  }
  return mismatch ? txt : false;
}

</script>
{/literal}