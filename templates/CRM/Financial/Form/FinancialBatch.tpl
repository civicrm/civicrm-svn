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
<h3>{if $action eq 8}{ts}Delete Batch{/ts} - {$batchTitle}{elseif $action eq 1}{ts}Add New Batch{/ts}{elseif $action eq 2}{ts}Edit Batch{/ts} - {$batchTitle}{elseif $action eq 262144}{ts}Close Batch{/ts} - {$batchTitle}{elseif $action eq 128}{ts}Export Batch{/ts} - {$batchTitle}{/if}</h3>
<div class="crm-block crm-form-block crm-financial_type-form-block">
   {if $action eq 8}
      <div class="messages status">
          <div class="icon inform-icon"></div>    
          {ts}WARNING: You cannot delete a financial type if it is currently used by any Contributions, Contribution Pages or Membership Types. Consider disabling this option instead.{/ts} {ts}Deleting a financial type cannot be undone.{/ts} {ts}Do you want to continue?{/ts}
      </div>
      { elseif $action eq 524288 }
       <div class="messages status">
          <div class="icon inform-icon"></div>    
          {ts}WARNING: Do you want to Reopen '{$batchTitle}'- batch?{/ts}
      </div>
       { elseif $action eq 262144 }
       <div class="messages status">
          <div class="icon inform-icon"></div>    
          {ts}WARNING: You will not be able to change the batch after it is closed. Are you sure you want to close this batch?{/ts}
      </div>
        {elseif $action eq 128}
       <div class="messages status">
          <div class="icon inform-icon"></div>    
          {ts}Warning: You will not be able to reopen or change the batch after it is exported. Are you sure you want to export this batch?{/ts}
      </div>
   {else}
     <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
 
     <table class="form-layout">
     
       <tr class="crm-contribution-form-block-name">	 
    	  <td class="label">{$form.name.label}</td>
	  <td class="html-adjust">{$form.name.html}</td>
       </tr>
     
       <tr class="crm-contribution-form-block-description">	 
    	  <td class="label">{$form.description.label}</td>
	  <td class="html-adjust">{$form.description.html}</td>
       </tr>
       <tr class="crm-contribution-form-block-contact">	 
    	  <td class="label">{$form.contact_name.label}</td>
	  <td class="html-adjust">{$form.contact_name.html}</td>
       </tr>
       <tr class="crm-contribution-form-block-payment_instrument">	 
    	  <td class="label">{$form.payment_instrument_id.label}</td>
	  <td class="html-adjust">{$form.payment_instrument_id.html}</td>
       </tr>
       
       {if $action eq 2}
       <tr class="crm-contribution-form-block-batch_type">	 
    	  <td class="label">{$form.type_id.label}</td>
	  <td class="html-adjust">{$form.type_id.html}</td>
       </tr>
       {/if}
       <tr class="crm-contribution-form-block-manual_number_trans">	 
    	  <td class="label">{$form.manual_number_trans.label}</td>
	  <td class="html-adjust">{$form.manual_number_trans.html}</td>
       </tr>
       <tr class="crm-contribution-form-block-manual_total">	 
    	  <td class="label">{$form.manual_total.label}</td>
	  <td class="html-adjust">{$form.manual_total.html}</td>
       </tr>
       {if $action eq 2}
        <tr class="crm-contribution-form-block-open_date">	 
    	  <td class="label">{$form.created_date.label}</td>
	  <td class="html-adjust">{$form.created_date.html}</td>
       </tr>
        <tr class="crm-contribution-form-block-modified_date">	 
    	  <td class="label">{$form.modified_date.label}</td>
	  <td class="html-adjust">{$form.modified_date.html}</td>
       </tr>
       <tr class="crm-contribution-form-block-batch_status">	 
    	  <td class="label">{$form.status_id.label}</td>
	  <td class="html-adjust">{$form.status_id.html}</td>
       </tr>
      {/if}
      </table> 
   {/if}
   <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="botttom"}</div>
</div>

{literal}
<script type="text/javascript">
var dataUrl        = "{/literal}{$dataURL}{literal}";
var newContactText = "{/literal}({ts}new contact record{/ts}){literal}";
cj('#contact_name').autocomplete( dataUrl, { 
                                      width        : 250, 
                                      selectFirst  : false,
                                      matchCase    : true, 
                                      matchContains: true
    }).result( function(event, data, formatted) {
        var foundContact   = ( parseInt( data[1] ) ) ? cj( "#created_id" ).val( data[1] ) : cj( "#created_id" ).val('');
        if ( ! foundContact.val() ) {
            cj('div#employer_address').html(newContactText).show();    
        } else {
            cj('div#employer_address').html('').hide();    
        }
    }).bind('change blur', function() {
        if ( !cj( "#created_id" ).val( ) ) {
            cj('div#employer_address').html(newContactText).show();    
        }
});

// remove current employer id when current employer removed.
cj("form").submit(function() {
  if ( !cj('#contact_name').val() ) cj( "#created_id" ).val('');
});

cj("input#contact_name").click( function( ) {
    cj("input#created_id").val('');
});
</script>
{/literal}