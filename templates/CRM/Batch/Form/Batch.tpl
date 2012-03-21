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
{* this template is used for adding/editing batch  *}
<h3>{ts}New Batch{/ts}</h3>
<div class="crm-block crm-form-block crm-batch-form-block">
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="top"}</div>
{if $action eq 8}
  <div class="messages status">
     <div class="icon inform-icon"></div>
        {ts}WARNING: Deleting this batch will result in the loss of all assocatiated contribution/membership records which use the batch.{/ts} {ts}This may mean the loss of a substantial amount of data, and the action cannot be undone.{/ts} {ts}Do you want to continue?{/ts}
      </div>
{else}
  <table class="form-layout-compressed">
      <tr class="crm-batch-form-block-title">
          <td class="label">{$form.title.label}</td>
          <td>{$form.title.html}</td>
      </tr>
      <tr class="crm-batch-form-block-type_id">
          <td class="label">{$form.type_id.label}</td>
          <td>{$form.type_id.html}</td>
      </tr>
      <tr class="crm-batch-form-block-description">
          <td class="label">{$form.description.label}</td>
          <td>{$form.description.html}</td>
      </tr>
      <tr class="crm-batch-form-block-status_id">
          <td class="label">{$form.status_id.label}</td>
          <td>{$form.status_id.html}</td>
      </tr>
      <tr class="crm-batch-form-block-item_count">
          <td class="label">{$form.item_count.label}</td>
          <td>{$form.item_count.html}</td>
      </tr>
      <tr  class="crm-batch-form-block-total">
           <td class="label">{$form.total.label}</td>
           <td>{$form.total.html}</td>
      </tr>
  </table>
{/if}
    <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
