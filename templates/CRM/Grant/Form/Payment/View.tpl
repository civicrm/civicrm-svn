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
{if $action eq 4}
<h3>{ts}View Payment{/ts}</h3>
<div class="crm-block crm-content-block crm-grant-view-block">
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>
    <table class="crm-info-panel">
        <tr class="crm-grant-view-form-block-name"><td class="label">{ts}Payment Number{/ts}</td><td class="bold">{$payment_number}</td></tr> 
        <tr class="crm-grant-view-form-block-grant_type_id"><td class="label">{ts}Batch Number{/ts}</td> <td>{$payment_batch_number}</td></tr>   
        <tr class="crm-grant-view-form-block-status_id"><td class="label">{ts}Payment Status{/ts}</td> <td>{$payment_status_id}</td></tr>
        <tr class="crm-grant-view-form-block-application_received_date"><td class="label">{ts}Payment Created Date{/ts}</td> <td>{$payment_created_date|crmDate}</td></tr>
        <tr class="crm-grant-view-form-block-decision_date"><td class="label">{ts}Payment Date{/ts}</td> <td>{$payment_date|crmDate}</td></tr>
        <tr class="crm-grant-view-form-block-rationale"><td class="label">{ts}Payee Name{/ts}</td> <td>{$payable_to_name}</td></tr>
	<tr class="crm-grant-view-form-block-rationale"><td class="label">{ts}Payee Address{/ts}</td> <td>{$payable_to_address}</td></tr>
        <tr class="crm-grant-view-form-block-amount_requested"><td class="label">{ts}Amount{/ts}</td> <td>{$amount|crmMoney}</td></tr>
        <tr class="crm-grant-view-form-block-note"><td class="label">{ts}Payment Reason{/ts}</td> <td>{$payment_reason}</td></tr>
    </table>
    {include file="CRM/Custom/Page/CustomDataView.tpl"} 
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>
{elseif $action eq 524288 }
<h3>{ts}Stop Payment{/ts}</h3>
<div class="crm-block crm-content-block crm-grant-view-block">  
     <div class="messages status">
          <div class="icon inform-icon"></div>    
           {ts}Do you want to record that a Stop payment request has been made with bank on this payment?{/ts}
      </div>
   <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>


{elseif $action eq 1048576 }
<h3>{ts}Reprint Payment{/ts}</h3>
<div class="crm-block crm-content-block crm-grant-view-block">
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="top"}
    </div>
    <table class="crm-info-panel">
        <tr class="crm-grant-view-form-block-name"><td class="label">{ts}Payment Number{/ts}</td><td class="bold">{$payment_number}</td></tr> 
        <tr class="crm-grant-view-form-block-grant_type_id"><td class="label">{ts}Batch Number{/ts}</td> <td>{$payment_batch_number}</td></tr>   
        <tr class="crm-grant-view-form-block-status_id"><td class="label">{ts}Payment Status{/ts}</td> <td>{$payment_status_id}</td></tr>
        <tr class="crm-grant-view-form-block-application_received_date"><td class="label">{ts}Payment Created Date{/ts}</td> <td>{$payment_created_date|crmDate}</td></tr>
        <tr class="crm-grant-view-form-block-decision_date"><td class="label">{ts}Payment Date{/ts}</td> <td>{$payment_date|crmDate}</td></tr>
        <tr class="crm-grant-view-form-block-rationale"><td class="label">{ts}Payee Name{/ts}</td> <td>{$payable_to_name}</td></tr>
	<tr class="crm-grant-view-form-block-rationale"><td class="label">{ts}Payee Address{/ts}</td> <td>{$payable_to_address}</td></tr>
        <tr class="crm-grant-view-form-block-amount_requested"><td class="label">{ts}Amount{/ts}</td> <td>{$amount|crmMoney}</td></tr>
        <tr class="crm-grant-view-form-block-note"><td class="label">{ts}Payment Reason{/ts}</td> <td>{$payment_reason}</td></tr>
	<tr class="crm-grant-view-form-block-note"><td class="label">{ts}Repaces Payment Id{/ts}</td> <td>{$replaces_payment_id}</td></tr>
    </table> 
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>
{elseif $action eq 2097152 }
<h3>{ts}Withdraw Payment{/ts}</h3>

<div class="crm-block crm-content-block crm-grant-view-block">  
     <div class="messages status">
          <div class="icon inform-icon"></div>    
           {ts}Do you want to record that this cheque will not be cashed, e.g. it has been destroyed or is stale dated?{/ts}
      </div>
   <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>	
{/if}
