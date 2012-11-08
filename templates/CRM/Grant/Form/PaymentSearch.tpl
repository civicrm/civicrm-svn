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

<div class="crm-block crm-form-block crm-search-form-block">
	<div class="crm-accordion-wrapper crm-member_search_form-accordion {if $rows}crm-accordion-closed{else}crm-accordion-open{/if}">
	 <div class="crm-accordion-header crm-master-accordion-header">
	    <div class="icon crm-accordion-pointer"></div> 
	    {ts}Edit Search Criteria{/ts}
 	 </div><!-- /.crm-accordion-header -->
	<div class="crm-accordion-body">
	    {strip}
        <div id="help">
            {ts}Use this form to find Grant Payment(s) by Payee name , Status, Batch Number, Payment Number , etc .{/ts}
        </div>
        <table class="form-layout">
            <tr>
               <td class="font-size12pt" colspan="4">
                    {$form.payable_to_name.label}&nbsp;&nbsp;{$form.payable_to_name.html|crmReplace:class:'twenty'}&nbsp;&nbsp;&nbsp;{$form.buttons.html}<br />
               </td>       
            </tr>
            <tr>
	       <td colspan="2">
                    {$form.payment_status_id.label}&nbsp;&nbsp;
                    {$form.payment_status_id.html}
               </td>
               <td colspan="2">
                    {$form.payment_batch_number.label}&nbsp;&nbsp;
                    {$form.payment_batch_number.html}
               </td>
               <td colspan="2">
                    {$form.payment_number.label}&nbsp;&nbsp;
                    {$form.payment_number.html} 
               </td>
             </tr>
	     	<tr> 
		<td colspan="2">
		{$form.payment_created_date_low.label}&nbsp;&nbsp;
        	{include file="CRM/common/jcalendar.tpl" elementName=payment_created_date_high}
		</td>
	       <td colspan="2">
                    {$form.payment_created_date_high.label}&nbsp;&nbsp;
                    {include file="CRM/common/jcalendar.tpl" elementName=payment_created_date_high}
               </td>
               <td colspan="2">
                    {$form.amount.label}&nbsp;&nbsp;
                    {$form.amount.html}
               </td>
             </tr>    
        </table>
        {/strip}
    </div><!-- /.crm-accordion-body -->
  </div><!-- /.crm-accordion-wrapper -->
</div><!-- /.crm-form-block -->
<div class="crm-content-block">
{if $rowsEmpty}
    <div class="crm-results-block crm-results-block-empty">
        {include file="CRM/Grant/Form/Search/EmptyResults.tpl"}
    </div>
{/if}
{if $rows}
    {* Search request has returned 1 or more matching rows. Display results and collapse the search criteria fieldset. *}    
	<div class="crm-results-block">
        {* This section handles form elements for action task select and submit *}
        <div class="crm-search-tasks">
            {include file="CRM/Grant/Form/Task/paymentResultTask.tpl"}
        </div>
        {* This section displays the rows along and includes the paging controls *}
        <div class="crm-search-results">
            {include file="CRM/Grant/Form/PaymentSelector.tpl" context="Search"}
       </div>
    </div><!-- /.crm-results-block -->
{/if}
</div><!-- /.crm-content-block -->
{if $download}
<div id ="download"> 
<a href={$download} class="download" "style="text-decoration: none;" >Download</a>
</div>
{/if}
{literal}
<script type="text/javascript">
var download = "{/literal}{$download}{literal}";
if ( download ) {
cj("#download").hide();
window.location.href = cj(".download").attr('href');
}
cj(function() {	
   cj().crmaccordions(); 
});
</script>
{/literal}
