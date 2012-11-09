{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.1                                                |
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
{* this template is used for viewing grants *} 
<h3>{ts}View Grant Program{/ts}</h3>
<div class="crm-block crm-content-block crm-grant-view-block">
    <div class="crm-submit-buttons">
        {if call_user_func(array('CRM_Core_Permission','check'), 'edit grants')}
            {assign var='urlParams' value="action=update&id=$id&reset=1"}
            {if ( $context eq 'fulltext' || $context eq 'search' ) && $searchKey}
                {assign var='urlParams' value="action=update&id=$id&reset=1"}
            {/if}
            <a class="button" href="{crmURL p='civicrm/grant_program' q=$urlParams}" accesskey="e"><span><div class="icon edit-icon"></div> {ts}Edit{/ts}</span></a>
        {/if}
        {if call_user_func(array('CRM_Core_Permission','check'), 'delete in CiviGrant')}
    	    {assign var='urlParams' value="action=delete&id=$id&reset=1"}
            {if ( $context eq 'fulltext' || $context eq 'search' ) && $searchKey}
                {assign var='urlParams' value="action=delete&id=$id&reset=1"}
            {/if}
            <a class="button" href="{crmURL p='civicrm/grant_program' q=$urlParams}"><span><div class="icon delete-icon"></div>{ts}Delete{/ts}</span></a>
        {/if}
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>
    <table class="crm-info-panel">
        <tr class="crm-grant-program-view-form-block-name"><td class="label">{ts}Name{/ts}</td><td class="bold">{$name}</td></tr>    
        <tr class="crm-grant-program-view-form-block-grant_type_id"><td class="label">{ts}Grant Type{/ts}</td> <td>{$grantType}</td></tr>
	<tr class="crm-grant-program-view-form-block-total_amount"><td class="label">{ts}Total Amount{/ts}</td> <td>{$total_amount|crmMoney}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" id="allocation" value="Trial Allocation">&nbsp;<input type="button" id="finalize" value="Finalize Pending Grants">&nbsp;<input type="button" id="reject" value="Reject Pending Grants"></td></tr>
	<tr class="crm-grant-program-view-form-block-remainder_amount"><td class="label">{ts}Remainder Amount{/ts}</td> <td>{$remainder_amount|crmMoney}</td></tr>
	<tr class="crm-grant-program-view-form-block-financial_type_id"><td class="label">{ts}Financial Type{/ts}</td> <td>{$contributionType}</td></tr>
        <tr class="crm-grant-program-view-form-block-status_id"><td class="label">{ts}Grant Status{/ts}</td> <td>{$grantProgramStatus}</td></tr>
	<tr class="crm-grant-program-view-form-block-allocation_algorithm"><td class="label">{ts}Allocation Algorithm{/ts}</td> <td>{$grantProgramAlgorithm}</td></tr>
        <tr class="crm-grant-program-view-form-block-allocation_date"><td class="label">{ts}Allocation Date{/ts}</td> <td>{$allocation_date|crmDate}</td></tr>
        <tr class="crm-grant-program-view-form-block-is_active"><td class="label">{ts}Enabled?{/ts}</td> <td>{if $is_active}{ts}Yes{/ts} {else}{ts}No{/ts}{/if}</td></tr>
	<tr class="crm-grant-program-view-form-block-is_active"><td class="label">{ts}Auto Email?{/ts}</td> <td>{if $is_auto_email}{ts}Yes{/ts} {else}{ts}No{/ts}{/if}</td></tr>
    </table>
    {include file="CRM/Custom/Page/CustomDataView.tpl"} 
    <div class="crm-submit-buttons">
        {if call_user_func(array('CRM_Core_Permission','check'), 'edit grants')}
            {assign var='urlParams' value="action=update&id=$id&reset=1"}
	        {if ( $context eq 'fulltext' || $context eq 'search' ) && $searchKey}
	            {assign var='urlParams' value="action=update&id=$id&reset=1"}
	        {/if}
            <a class="button" href="{crmURL p='civicrm/grant_program' q=$urlParams}" accesskey="e"><span><div class="icon edit-icon"></div> {ts}Edit{/ts}</span></a>
        {/if}
        {if call_user_func(array('CRM_Core_Permission','check'), 'delete in CiviGrant')}
		    {assign var='urlParams' value="action=update&id=$id&reset=1"}
	        {if ( $context eq 'fulltext' || $context eq 'search' ) && $searchKey}
	            {assign var='urlParams' value="action=update&id=$id&reset=1"}
	        {/if}
            <a class="button" href="{crmURL p='civicrm/grant_program' q=$urlParams}"><span><div class="icon delete-icon"></div>{ts}Delete{/ts}</span></a>
        {/if}
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>


{literal}
<script type="text/javascript">
cj('#allocation').click(function(){
var r=confirm("You want to do trial allocation?");
if (r==true)
  {
	    
     var data = 'pid={/literal}{$id}{literal}&amount={/literal}{$total_amount}{literal}&algorithm={/literal}{$grantProgramAlgorithm}{literal}';
     var dataURL = {/literal}"{crmURL p='civicrm/grant_program/allocate'}"{literal};
     cj.ajax({ 
         url: dataURL,	
         data: data,
         type: 'POST',
         success: function(output) { 
           setTimeout("location.reload(true);",1500);
	 }
      });
   }
});

cj('#finalize').click(function(){
 var confirmed = 0;
 var totalAmounts = 0;
 var grantedAmount = 0;
 var data = 'pid={/literal}{$id}{literal}}&amount={/literal}{$total_amount}{literal}';
     var dataURL = {/literal}"{crmURL p='civicrm/grant_program/finalize'}"{literal};
     cj.ajax({ 
         url: dataURL,	
         data: data,
         type: 'POST',
         success: function(output) { 
	 var result = eval('(' + output + ')');
	 cj.each( result, function( index, value ) {
alert(index);alert(value);
	 if( index == 'confirm' ) {
	   confirmed = value;
	 }
	 if( index == 'total_amount' ) {
	   totalAmounts = value;
	 }
	 if( index == 'amount_granted' ) {
	   grantedAmount = value;
	   var data = 'amount_granted = '+value;
	   alert(data);
	 }
         });
	 alert(data);
	 if (confirmed == 'confirm' ) {
	    var r=confirm("Do you want finalize the award of grants for this grant program to the amounts currently allocated?");
	    if (r==true)
  	    { 
	    var dataURL = {/literal}"{crmURL p='civicrm/grant_program/processFinalization'}"{literal};
     	    cj.ajax({ 
              url: dataURL,	
              data: data,
              type: 'POST',
              success: function(output) { 
	      setTimeout("location.reload(true);",1500);
	      }
	      });
	    }
	 } else {
alert("The sum of the grants to be allocated ($"+grantedAmount+".00) is greater than the total amount available to be allocated by the program ($"+totalAmounts+"). Please reduce the amount granted in pending applications or increase the total amount available to be granted.");
	  }
	}
   });  
});
cj('#reject').click(function(){

var r=confirm("Do you want to reject all Pending grant applications for this Grant Program??");
if (r==true)
  {
 var data = 'pid={/literal}{$id}{literal}';
     var dataURL = {/literal}"{crmURL p='civicrm/grant_program/reject'}"{literal};
     cj.ajax({ 
         url: dataURL,	
         data: data,
         type: 'POST',
         success: function(output) { 
	   setTimeout("location.reload(true);",1500);
	 }
   });
  }     
});
</script>
{/literal}