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
{* this template is used for adding/editing/deleting contribution type  *}
<h3>{if $action eq 1}{ts}New Contribution Type{/ts}{elseif $action eq 2}{ts}Edit Contribution Type{/ts}{else}{ts}Delete Contribution Type{/ts}{/if}</h3>
<div class="crm-block crm-form-block crm-contribution_type-form-block">
   {if $action eq 8}
      <div class="messages status">
          <div class="icon inform-icon"></div>    
          {ts}WARNING: You cannot delete a contribution type if it is currently used by any Contributions, Contribution Pages or Membership Types. Consider disabling this option instead.{/ts} {ts}Deleting a contribution type cannot be undone.{/ts} {ts}Do you want to continue?{/ts}
      </div>
   {else}
     <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
     <table class="form-layout-compressed">
      <tr class="crm-contribution-form-block-name">
 	  <td class="label">{$form.name.label}</td>
	  <td class="html-adjust">{$form.name.html}</td>	
       </tr>
       <tr class="crm-contribution-form-block-description">	 
    	  <td class="label">{$form.description.label}</td>
	  <td class="html-adjust">{$form.description.html}</td>
       </tr>
       <tr class="crm-contribution-form-block-accounting_code">
    	  <td class="label">{$form.accounting_code.label}</td>
	  <td class="html-adjust">{$form.accounting_code.html}<br />
       	      <span class="description">{ts}Use this field to flag contributions of this type with the corresponding code used in your accounting system. This code will be included when you export contribution data to your accounting package.{/ts}</span>
	  </td>
       </tr>
       <tr>
	  <td class="label"> {$form.organisation_name.label}&nbsp;{help id="id-current-employer" file="CRM/Contact/Form/Contact.hlp"}
	  </td>
	  <td class="html-adjust">{$form.organisation_name.html|crmReplace:class:twenty}
          </td>
       </tr>

       <tr>
	  <td class="label">{$form.parent_financial_account.label}
	  </td>
	  <td class="html-adjust">{$form.parent_financial_account.html|crmReplace:class:twenty}
          </td>
       </tr>
       <tr>
	  <td class="label">{$form.financial_account_type_id.label}
	  </td>
	  <td class="html-adjust">{$form.financial_account_type_id.html|crmReplace:class:twenty}
          </td>
       </tr>


       <tr class="crm-contribution-form-block-is_deductible">
    	  <td class="label">{$form.is_deductible.label}</td>
	  <td class="html-adjust">{$form.is_deductible.html}<br />
	      <span class="description">{ts}Are contributions of this type tax-deductible?{/ts}</span>
	  </td>
       </tr>
       <tr class="crm-contribution-form-block-is_active">	 
    	  <td class="label">{$form.is_active.label}</td>
	  <td class="html-adjust">{$form.is_active.html}</td>
       </tr>
       <tr class="crm-contribution-form-block-is_header_account">	 
    	  <td class="label">{$form.is_header_account.label}</td>
	  <td class="html-adjust">{$form.is_header_account.html}</td>
       </tr>
       <tr class="crm-contribution-form-block-is_tax">	 
    	  <td class="label">{$form.is_tax.label}</td>
	  <td class="html-adjust">{$form.is_tax.html}</td>
       </tr>
        <tr class="crm-contribution-form-block-is_tax">	 
    	  <td class="label">{$form.tax_rate.label}</td>
	  <td class="html-adjust">{$form.tax_rate.html}</td>
       </tr>
       <tr class="crm-contribution-form-block-is_default">	 
    	  <td class="label">{$form.is_default.label}</td>
	  <td class="html-adjust">{$form.is_default.html}</td>
       </tr>
      </table> 
   {/if}
   <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="botttom"}</div>
</div>

{literal}
<script type="text/javascript">
var dataUrl        = "{/literal}{$dataURL}{literal}";
var newContactText = "{/literal}({ts}new contact record{/ts}){literal}";
cj('#organisation_name').autocomplete( dataUrl, { 
                                      width        : 250, 
                                      selectFirst  : false,
                                      matchCase    : true, 
                                      matchContains: true
    }).result( function(event, data, formatted) {
        var foundContact   = ( parseInt( data[1] ) ) ? cj( "#contact_id" ).val( data[1] ) : cj( "#contact_id" ).val('');
        if ( ! foundContact.val() ) {
            cj('div#employer_address').html(newContactText).show();    
        } else {
            cj('div#employer_address').html('').hide();    
        }
    }).bind('change blur', function() {
        if ( !cj( "#contact_id" ).val( ) ) {
            cj('div#employer_address').html(newContactText).show();    
        }
});

// remove current employer id when current employer removed.
cj("form").submit(function() {
  if ( !cj('#organisation_name').val() ) cj( "#contact_id" ).val('');
});

//current employer default setting
var employerId = "{/literal}{$organisationId}{literal}";
if ( employerId ) {
    var dataUrl = "{/literal}{crmURL p='civicrm/ajax/rest' h=0 q="className=CRM_Contact_Page_AJAX&fnName=getContactList&json=1&context=contact&org=1&id=" }{literal}" + employerId ;
    cj.ajax({ 
        url     : dataUrl,   
        async   : false,
        success : function(html){
            //fixme for showing address in div
            htmlText = html.split( '|' , 2);
            cj('input#organisation_name').val(htmlText[0]);
            cj('input#contact_id').val(htmlText[1]);
        }
    }); 
}

cj("input#organisation_name").click( function( ) {
    cj("input#contact_id").val('');
});
</script>
{/literal}

{literal}
<script type="text/javascript">
var dataUrl        = "{/literal}{$dataURLParentID}{literal}";
var newContactText = "{/literal}({ts}new contact record{/ts}){literal}";
cj('#parent_financial_account').autocomplete( dataUrl, { 
                                      width        : 250, 
                                      selectFirst  : false,
                                      matchCase    : true, 
                                      matchContains: true
    }).result( function(event, data, formatted) {
        var foundContact   = ( parseInt( data[1] ) ) ? cj( "#parent_id" ).val( data[1] ) : cj( "#parent_id" ).val('');
        if ( ! foundContact.val() ) {
            cj('div#employer_address').html(newContactText).show();    
        } else {
            cj('div#employer_address').html('').hide();    
        }
    }).bind('change blur', function() {
        if ( !cj( "#parent_id" ).val( ) ) {
            cj('div#employer_address').html(newContactText).show();    
        }
});

// remove current employer id when current employer removed.
cj("form").submit(function() {
  if ( !cj('#parent_financial_account').val() ) cj( "#parent_id" ).val('');
});

//current employer default setting
var employerId = "{/literal}{$parentId}{literal}";
if ( employerId ) {
    var dataUrl = "{/literal}{crmURL p='civicrm/ajax/rest' h=0 q="className=CRM_Financial_Page_AJAX&fnName=financialAccount&json=1&parentID=" }{literal}" + employerId ;
    cj.ajax({ 
        url     : dataUrl,   
        async   : false,
        success : function(html){
            //fixme for showing address in div
            htmlText = html.split( '|' , 2);
            cj('input#parent_financial_account').val(htmlText[0]);
            cj('input#parent_id').val(htmlText[1]);
        }
    }); 
}

cj("input#parent_financial_account").click( function( ) {
    cj("input#parent_id").val('');
});
</script>
{/literal}