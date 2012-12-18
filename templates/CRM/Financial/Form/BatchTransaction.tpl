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
{* this template is used for adding/editing/deleting financial type  *}
<div class="crm-form-block crm-search-form-block">
  <div class="crm-accordion-wrapper crm-activity_search-accordion {if $searchRows}crm-accordion-closed{else}crm-accordion-open{/if}">
    <div class="crm-accordion-header crm-master-accordion-header">
      <div class="icon crm-accordion-pointer"></div> 
      {ts}Edit Search Criteria{/ts}
    </div><!-- /.crm-accordion-header -->
 
    <div class="crm-accordion-body">
      <div id="searchForm" class="crm-block crm-form-block crm-contact-custom-search-activity-search-form-block">
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
        <table class="form-layout-compressed">
        </table> 
        <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="botttom"}</div>
      </div>
    </div>	
  </div>
</div>
{if $searchRows}
  <div class="form-layout-compressed">{$form.trans_assign.html}&nbsp;{$form.submit.html}</div><br/>
  <div id="ltype">
    <p></p>
    <div class="form-item">
      {strip}
      <table id="crm-transaction-selector-assign" cellpadding="0" cellspacing="0" border="0">
        <thead class="sticky">
 	  <tr>
            <th class='crm-batch-checkbox' scope="col" title="Select All Rows">{$form.toggleSelect.html}</th>
              {foreach from=$columnHeader item=head key=class}
	        <th class='crm-{$class}'>{$head}</th>
	      {/foreach}
            <th></th>
            </tr>
        </thead>

        {foreach from=$searchRows item=row}
        <tr id="row_{$row.id}"class="{cycle values="odd-row,even-row"} {$row.class}">
	  {assign var=cbName value=$row.checkbox}
          <td>{$form.$cbName.html}</td>
	  {foreach from=$searchColumnHeader item=rowValue key=rowKey}
	    <td>{$row.$rowKey}</td>
	  {/foreach}
	  <td>{$row.action}</td>  
        </tr>
        {/foreach}
      </table>
      {/strip}
    </div>
  </div>   
{/if}

{literal}
<script type="text/javascript">
cj( function() {
   buildTransactionSelector("crm-transaction-selector-assign");
   buildTransactionSelector("crm-transaction-selector-remove");

   cj("#trans_assign").attr('disabled',true);
   cj("#trans_remove").attr('disabled',true);	
   cj('#crm-transaction-selector-assign #toggleSelect').click( function() {
     enableActions('x');
   });
   cj('#crm-transaction-selector-remove #toggleSelects').click( function() {
     enableActions('y');
   });
   cj('#Go').click( function() {
     return selectAction("trans_assign","toggleSelect", "crm-transaction-selector-assign input[id^='mark_x_']");
   });
   cj('#GoRemove').click( function() {
     return selectAction("trans_remove","toggleSelects", "crm-transaction-selector-remove input[id^='mark_y_']");
   });
 
   cj("#crm-transaction-selector-assign input[id^='mark_x_']").click( function() {
     enableActions('x');
   });
   cj("#crm-transaction-selector-remove input[id^='mark_y_']").click( function() {
     enableActions('y');
   });
 
   cj("#crm-transaction-selector-assign #toggleSelect").click( function() {
     if (cj("#crm-transaction-selector-assign #toggleSelect").is(':checked')) {
       	 cj("#crm-transaction-selector-assign input[id^='mark_x_']").prop('checked',true);    
     }
     else {
         cj("#crm-transaction-selector-assign input[id^='mark_x_']").prop('checked',false);	
     }
  });
   cj("#crm-transaction-selector-remove #toggleSelects").click( function() {
     if (cj("#crm-transaction-selector-remove #toggleSelects").is(':checked')) {
       	 cj("#crm-transaction-selector-remove input[id^='mark_y_']").prop('checked',true);    
     }
     else {
         cj("#crm-transaction-selector-remove input[id^='mark_y_']").prop('checked',false);	
     }
  });

  
});

function enableActions( type ) {
  if (type == 'x') {
    cj("#trans_assign").attr('disabled',false);
  }
  else {
    cj("#trans_remove").attr('disabled',false);  
  }
}

function buildTransactionSelector( tableID ) {
  var columns='';
  cj("#" + tableID + " th").each( function( ) {
        columns += '{ "bSortable": false },';
  });

  columns    = columns.substring(0, columns.length - 1 );
  eval('columns =[' + columns + ']');
  //load jQuery data table.
  cj("#" + tableID).dataTable( {
			    "bAutoWidth" : false,
    		  	    "sPaginationType": "full_numbers",
    		            "bJQueryUI"  : true,
    	            	    "aoColumns"  : columns,
			    "sDom"       : '<"crm-datatable-pager-top"lfp>rt<"crm-datatable-pager-bottom"ip>',
			    "iDisplayLength": 25,
    			    "bFilter"    : false,
			    "asStripClasses" : [ "odd-row", "even-row" ]
        	         });

}

function selectAction( id, toggleSelectId, checkId ) {
  if (cj("#"+ id ).is(':disabled')) {
     return false;
  }
  else if (!cj("#" + toggleSelectId).is(':checked') && !cj("#" + checkId).is(':checked') && cj("#" + id).val() != "") {
     alert ("Please select one or more contributions for this action.");
     return false;
  }
  else if (cj("#" + id).val() == "") {
     alert ("Please select an action from the drop-down menu.");
     return false; 
  }
}

</script>
{/literal}
