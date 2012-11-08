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
<h3>{if $action eq 1}{ts}New Grant Program{/ts}{elseif $action eq 2}{ts}Edit Grant Program{/ts}{elseif $action eq 4}{ts}View Grant Program{/ts}{else}{ts}Delete Grant Program{/ts}{/if}</h3>
<div class="crm-block crm-form-block crm-contribution_type-form-block crm-financial_type-form-block">
   {if $action eq 4}
    
   {elseif $action eq 8}
      <div class="messages status">
          <div class="icon inform-icon"></div>    
           {ts}Deleting a grant program cannot be undone.{/ts} {ts}Do you want to continue?{/ts}
      </div>
   {else}
     <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
     <table class="form-layout-compressed">
      <tr class="crm-grant_program-form-block-label">
 	  <td class="label">{$form.label.label}</td>
	  <td class="html-adjust">{$form.label.html}</td>	
       </tr>
       <tr class="crm-grant_program-form-block-grant_type_id">	 
    	  <td class="label">{$form.grant_type_id.label}</td>
	  <td class="html-adjust">{$form.grant_type_id.html}</td>
       </tr>
       <tr class="crm-grant_program-form-block-total_amount">
    	  <td class="label">{$form.total_amount.label}</td>
	  <td class="html-adjust">{$form.total_amount.html}</td>
       </tr>
       <tr class="crm-grant_program-form-block-remainder_amount">
    	  <td class="label">{$form.remainder_amount.label}</td>
	  <td class="html-adjust">{$form.remainder_amount.html}</td>
       </tr>
       <tr class="crm-grant_program-form-block-financial_type_id">
	  <td class="label">{$form.financial_type_id.label}</td>
	  <td class="html-adjust">{$form.financial_type_id.html}</td>
       </tr>
       <tr class="crm-grant_program-form-block-status_id">
	  <td class="label">{$form.status_id.label}</td>
	  <td class="html-adjust">{$form.status_id.html}</td>
       </tr>
       <tr class="crm-grant_program-form-block-allocation_algorithm">
	  <td class="label">{$form.allocation_algorithm.label}</td>
	  <td class="html-adjust">{$form.allocation_algorithm.html}</td>
       </tr>
       <tr class="crm-grant_program-form-block-applications_start_date">
           <td class="label">{$form.applications_start_date.label}</td>
	   <td>
    	      {if $hideCalendar neq true}
                 {include file="CRM/common/jcalendar.tpl" elementName=applications_start_date}
              {else}
                 {$form.applications_start_date.html|crmDate}
              {/if}
       	  </td>
       </tr>
       <tr class="crm-grant_program-form-block-applications_end_date">
           <td class="label">{$form.applications_end_date.label}</td>
	   <td>
    	      {if $hideCalendar neq true}
                 {include file="CRM/common/jcalendar.tpl" elementName=applications_end_date}
              {else}
                 {$form.applications_end_date.html|crmDate}
              {/if}
       	  </td>
       </tr>
       <tr class="crm-grant_program-form-block-allocation_date">
           <td class="label">{$form.allocation_date.label}</td>
	   <td>
    	      {if $hideCalendar neq true}
                 {include file="CRM/common/jcalendar.tpl" elementName=allocation_date}
              {else}
                 {$form.allocation_date.html|crmDate}
              {/if}
       	  </td>
       </tr>
       <tr class="crm-grant_program-form-block-is_active">	 
          <td class="label">{$form.is_active.label}</td>
	  <td class="html-adjust">{$form.is_active.html}</td>
       </tr>
       <tr class="crm-grant_program-form-block-is_auto_email">	 
          <td class="label">{$form.is_auto_email.label}</td>
	  <td class="html-adjust">{$form.is_auto_email.html}</td>
       </tr>
      </table> 
   {/if}
   <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="botttom"}</div>
</div>
