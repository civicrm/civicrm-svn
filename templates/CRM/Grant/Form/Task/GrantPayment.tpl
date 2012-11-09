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
<h3>{ts}Pay Grants{/ts}</h3>
<div class="crm-block crm-form-block crm-financial_type-form-block">
     <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
     <table class="form-layout-compressed">
       <tr class="crm-grant_payment-form-block-financial_type_id">
	  <td class="label">{$form.financial_type_id.label}</td>
	  <td class="html-adjust">{$form.financial_type_id.html}</td>
       </tr>
       <tr class="crm-grant_payment-form-block-payment_batch_number">
 	  <td class="label">{$form.payment_batch_number.label}</td>
	  <td class="html-adjust">{$form.payment_batch_number.html}</td>	
       </tr>
       <tr class="crm-grant_payment-form-block-payment_number">
 	  <td class="label">{$form.payment_number.label}</td>
	  <td class="html-adjust">{$form.payment_number.html}</td>	
       </tr>
       <tr class="crm-grant_payment-form-block-payment_date">
           <td class="label">{$form.payment_date.label}</td>
	   <td>
    	      {if $hideCalendar neq true}
                 {include file="CRM/common/jcalendar.tpl" elementName=payment_date}
              {else}
                 {$form.payment_date.html|crmDate}
              {/if}
       	  </td>
       </tr>
       <tr class="crm-grant_payment-form-block-csv">
 	  <td class="label">{$form.download_file.label}</td>
	  <td class="html-adjust">{$form.download_file.html}</td>	
       </tr>
      </table> 
 
   <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="botttom"}</div>
</div>
