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
{if $mode eq 'auto_renew'}
  <h3>{ts}Cancel Automatic Renewal Option for {$membershipType} Membership{/ts}</h3>
{else}
  <h3>{ts}Cancel Recurring Contribution{/ts}</h3>
{/if}
<div class="crm-block crm-form-block crm-auto-renew-membership-cancellation">
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
<div class="messages status">
   <div class="icon inform-icon"></div>       
   {if $mode eq 'auto_renew'}
      {ts}Click the button below if you want to cancel the auto-renewal option for your {$membershipType} membership? This will not cancel your membership. However you will need to arrange payment for renewal when your membership expires.{/ts}  
   {else}
      {ts}Click the button below if you want to cancel the recurring contribution? This will set the CiviCRM recurring contribution status to Cancelled.{/ts}  
   {/if}
</div>
<table class="form-layout">
   <tr>
      <td class="label">{$form.send_cancel_request.label}</td>
      <td class="html-adjust">{$form.send_cancel_request.html}</td>
   </tr>
   <tr>
      <td class="label">{$form.is_notify.label}</td>
      <td class="html-adjust">{$form.is_notify.html}</td>
   </tr>
</table>
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
</div>
