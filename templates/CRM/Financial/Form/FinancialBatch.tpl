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
<h3>{if $action eq 8}{ts}Delete Batch{/ts} - {$batchTitle}{elseif $action eq 1}{ts}Add New Batch{/ts}{elseif $action eq 2}{ts}Edit Batch{/ts} - {$batchTitle}{/if}</h3>
<div class="crm-block crm-form-block crm-financial_type-form-block">
   {if $action eq 8}
      <div class="messages status">
          <div class="icon inform-icon"></div>    
          {ts}WARNING: You cannot delete a financial type if it is currently used by any Contributions, Contribution Pages or Membership Types. Consider disabling this option instead.{/ts} {ts}Deleting a financial type cannot be undone.{/ts} {ts}Do you want to continue?{/ts}
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
       <tr class="crm-contribution-form-block-payment_instrument">	 
    	  <td class="label">{$form.payment_instrument_id.label}</td>
	  <td class="html-adjust">{$form.payment_instrument_id.html}</td>
       </tr>
       
       {if $action eq 2}
       <tr class="crm-contribution-form-block-batch_type">	 
    	  <td class="label">{$form.batch_type_id.label}</td>
	  <td class="html-adjust">{$form.batch_type_id.html}</td>
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
    	  <td class="label">{$form.open_date.label}</td>
	  <td class="html-adjust">{$form.open_date.html}</td>
       </tr>
        <tr class="crm-contribution-form-block-close_date">	 
    	  <td class="label">{$form.close_date.label}</td>
	  <td class="html-adjust">{$form.close_date.html}</td>
       </tr>
       
       <tr class="crm-contribution-form-block-batch_status">	 
    	  <td class="label">{$form.batch_status_id.label}</td>
	  <td class="html-adjust">{$form.batch_status_id.html}</td>
       </tr>
      {/if}
      </table> 
   {/if}
   <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="botttom"}</div>
</div>

