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
<h3>{if $action eq 8}{ts}Delete Financial Type Account{/ts} - {$fieldTitle}{elseif $action eq 1}{ts}Add New Financial Type Account{/ts}{elseif $action eq 2}{ts}Edit Financial Type Account{/ts} - {$fieldTitle}{/if}</h3>
<div class="crm-block crm-form-block crm-financial_type-form-block">
   {if $action eq 8}
      <div class="messages status">
          <div class="icon inform-icon"></div>    
          {ts}WARNING: You cannot delete a financial type if it is currently used by any Contributions, Contribution Pages or Membership Types. Consider disabling this option instead.{/ts} {ts}Deleting a financial type cannot be undone.{/ts} {ts}Do you want to continue?{/ts}
      </div>
   {else}
     <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
 
     <table class="form-layout">
     
       <tr class="crm-contribution-form-block-account_relationship">	 
    	  <td class="label">{$form.account_relationship.label}</td>
	  <td class="html-adjust">{$form.account_relationship.html}</td>
       </tr>
      <tr class="crm-contribution-form-block-financial_account_id">	 
    	  <td class="label">{$form.financial_account_id.label}</td>
	  <td class="html-adjust">{$form.financial_account_id.html}</td>
       </tr>
    
      </table> 
   {/if}
   <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="botttom"}</div>
</div>

