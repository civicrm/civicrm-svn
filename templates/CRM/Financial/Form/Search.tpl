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

{* Financial search component. *}
<div id="enableDisableStatusMsg" class="success-status" style="display:none"></div>
<div class="crm-submit-buttons">
  <a accesskey="N" href="{crmURL p='civicrm/financial/batch' q='reset=1&action=add'}" id="newBatch" class="button"><span><div class="icon add-icon"></div>{ts}New Accounting Batch{/ts}</span></a>
</div>
<div class="crm-form-block crm-search-form-block">
  <div class="crm-accordion-wrapper crm-activity_search-accordion">
    <div class="crm-accordion-header">
      {ts}Filter Results{/ts}
    </div>
    <div class="crm-accordion-body">
      <div id="financial-search-form" class="crm-block crm-form-block">
        <table class="form-layout-compressed">
          {* Loop through all defined search criteria fields (defined in the buildForm() function). *}
          {foreach from=$elements item=element}
            <tr class="crm-financial-search-form-block-{$element}">
              <td class="label">{$form.$element.label}</td>
              <td>{$form.$element.html}</td>
            </tr>
          {/foreach}
        </table>
      </div>
    </div>
  </div>
</div>
<div class="form-layout-compressed">{$form.batch_update.html}&nbsp;{$form.submit.html}</div><br/>
<table id="crm-batch-selector">
  <thead>
    <tr>
      <th class="crm-batch-checkbox">{$form.toggleSelect.html}</th>
      <th class="crm-batch-name">{ts}Batch Name{/ts}</th>
      <th class="crm-batch-payment_instrument_id">{ts}Payment Instrument{/ts}</th>
      <th class="crm-batch-item_count">{ts}Item Count{/ts}</th>
      <th class="crm-batch-total_amount">{ts}Total Amount{/ts}</th>
      <th class="crm-batch-status">{ts}Status{/ts}</th>
      <th class="crm-batch-created_by">{ts}Created By{/ts}</th>
      <th></th>
    </tr>
  </thead>
</table>
{include file="CRM/Form/validate.tpl"}
{literal}
<script type="text/javascript">
cj(function($) {
  var batchSelector;
  buildBatchSelector();
  $("#batch_update").removeAttr('disabled');

  $('#financial-search-form :input').change(function() {
    if (!$(this).hasClass('crm-inline-error')) {
      batchSelector.fnDraw();
    }
  });

  $("#toggleSelect").click(function() {
    $("#crm-batch-selector input[id^='check_']").prop('checked', $(this).is(':checked'));
  });

  function buildBatchSelector(filterSearch) {
    var ZeroRecordText = '<div class="status messages">{/literal}{ts escape="js"}No matching Accounting Batches found for your search criteria.{/ts}{literal}</li></ul></div>';

    var columns = '';
    var sourceUrl = {/literal}'{crmURL p="civicrm/ajax/batchlist" h=0 q="snippet=4&context=financialBatch"}'{literal};

    batchSelector = $('#crm-batch-selector').dataTable({
      "bFilter" : false,
      "bAutoWidth" : false,
      "aaSorting" : [],
      "aoColumns" : [
        {sClass:'crm-batch-checkbox', bSortable:false},
        {sClass:'crm-batch-name'},
        {sClass:'crm-batch-payment_instrument_id'},
        {sClass:'crm-batch-item_count right'},
        {sClass:'crm-batch-total_amount right'},
        {sClass:'crm-batch-status'},
        {sClass:'crm-batch-created_by'},
        {sClass:'crm-batch-links', bSortable:false},
       ],
      "bProcessing": true,
      "asStripClasses" : ["odd-row", "even-row"],
      "sPaginationType": "full_numbers",
      "sDom" : '<"crm-datatable-pager-top"lfp>rt<"crm-datatable-pager-bottom"ip>',
      "bServerSide": true,
      "bJQueryUI": true,
      "sAjaxSource": sourceUrl,
      "iDisplayLength": 25,
      "oLanguage": {
        "sZeroRecords": ZeroRecordText,
        "sProcessing": {/literal}"{ts escape='js'}Processing...{/ts}"{literal},
        "sLengthMenu": {/literal}"{ts escape='js'}Show _MENU_ entries{/ts}"{literal},
        "sInfo": {/literal}"{ts escape='js'}Showing _START_ to _END_ of _TOTAL_ entries{/ts}"{literal},
        "sInfoEmpty": {/literal}"{ts escape='js'}Showing 0 to 0 of 0 entries{/ts}"{literal},
        "sInfoFiltered": {/literal}"{ts escape='js'}(filtered from _MAX_ total entries) {/ts}"{literal},
        "sSearch": {/literal}"{ts escape='js'}Search:{/ts}"{literal},
        "oPaginate": {
          "sFirst": {/literal}"{ts escape='js'}First{/ts}"{literal},
          "sPrevious": {/literal}"{ts escape='js'}Previous{/ts}"{literal},
          "sNext": {/literal}"{ts escape='js'}Next{/ts}"{literal},
          "sLast": {/literal}"{ts escape='js'}Last{/ts}"{literal}
        }
      },
      "fnServerParams": function (aoData) {
        $('#financial-search-form :input').each(function() {
          if ($(this).val()) {
            aoData.push(
              {name:$(this).attr('id'), value: $(this).val()}
            );
          }
        });
      },
      "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        var box = $(aData[0]);
        $(nRow).addClass('crm-entity').attr('data-entity', 'batch').attr('data-id', box.attr('data-id')).attr('data-status_id', box.attr('data-status_id'));
        $('td:eq(1)', nRow).wrapInner('<div class="crm-editable crmf-title" />');
        return nRow;
      },
      "fnDrawCallback": function(oSettings) {
        $('.crm-editable').not('.crm-editable-enabled').crmEditable();
      }
    });
  }

  function editRecords(records, op) {
    var recordBAO = 'CRM_Batch_BAO_Batch';

    $("#enableDisableStatusMsg").dialog({
      title: {/literal}'{ts escape="js"}Confirm Changes{/ts}'{literal},
      modal: true,
      bgiframe: true,
      position: "center",
      overlay: {
        opacity: 0.5,
        background: "black"
      },
      open:function() {
        switch (op) {{/literal}
          case 'reopen':
            var msg = '{ts escape="js"}Are you sure you want to re-open:{/ts}';
            break;
          case 'delete':
            var msg = '{ts escape="js"}Are you sure you want to delete:{/ts}';
            break;
          case 'close':
            var msg = '{ts escape="js"}Are you sure you want to close:{/ts}';
            break;
        {literal}}
        msg += listRecords(records);
        $('#enableDisableStatusMsg').show().html(msg);
      },
      buttons: {
        {/literal}"{ts escape='js'}Cancel{/ts}"{literal}: function() {
          $(this).dialog("close");
        },
        {/literal}"{ts escape='js'}OK{/ts}{literal}": function() {
          saveRecords(records, op, recordBAO);
          $(this).dialog("close");
        }
      }
    });
  }

  function listRecords(records) {
    var msg = '<ul>';
    for (var i in records) {
      msg += '<li>' + $('tr[data-id='+records[i]+'] .crmf-title').text() + '</li>';
    }
    return msg + '</ul>';
  }

  function saveRecords(records, op, recordBAO) {
    var postUrl = {/literal}"{crmURL p='civicrm/ajax/rest' h=0 q='className=CRM_Financial_Page_AJAX&fnName=assignRemove'}"{literal};
    //post request and get response
    $.post(postUrl, {records: records, recordBAO: recordBAO, op: op, key: {/literal}"{crmKey name='civicrm/ajax/ar'}"{literal}},
      function(response) {
        //this is custom status set when record update success.
        if (response.status == 'record-updated-success') {
          batchSelector.fnDraw();
          $().crmAlert(listRecords(records), op == 'delete' ? {/literal}'{ts escape="js"}Deleted{/ts}' : '{ts escape="js"}Updated{/ts}'{literal}, 'success');
        }
        else {
          serverError();
        }
      },
      'json').error(serverError);
       
  }
  
  function serverError() {
     $().crmError({/literal}'{ts escape="js"}No response from the server. Check your internet connection and try reloading the page.{/ts}', '{ts escape="js"}Network Error{/ts}'{literal});
  }

  $('#Go').click(function() {
    var op = $("#batch_update").val();
    if (op == "") {
       $().crmAlert({/literal}'{ts escape="js"}Please select an action from the menu.{/ts}', '{ts escape="js"}No Action Selected{/ts}'{literal});
       return false;
    }
    else if (!$("input.crm-batch-select:checked").length) {
       $().crmAlert({/literal}'{ts escape="js"}Please select one or more batches for this action.{/ts}', '{ts escape="js"}No Batches Selected{/ts}'{literal});
       return false;
    }
    else if (op == 'close' || op == 'reopen' || op == 'delete') {
      records = [];
      $("input.crm-batch-select:checked").each(function() {
        records.push($(this).attr('data-id'));
      });
      editRecords(records, op);
      return false;
    }
  });

  $('#crm-container').on('click', 'a.action-item[href="#"]', function(event) {
    event.stopImmediatePropagation();
    editRecords([$(this).closest('tr').attr('data-id')], $(this).attr('rel'));
    return false;
  });

});

</script>
{/literal}
