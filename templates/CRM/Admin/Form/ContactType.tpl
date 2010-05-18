{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.1                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
{* this template is used for adding/editing Contact Type  *}

<div class="form-item crm-block crm-form-block">
<fieldset><legend>{if $action eq 1}{ts}New Contact Type{/ts}{elseif $action eq 2}{ts}Edit Contact Type{/ts}{else}{ts}Delete Contact Type{/ts}{/if}</legend>
{if $action eq 8}
  <div class="messages status">
    <div class="icon inform-icon"></div>
        {ts}WARNING: {ts}This action cannot be undone.{/ts} {ts}Do you want to continue?{/ts}{/ts}
    </div>
{else}
 <table class="form-layout-compressed">
   <tr class="crm-contactType-form-block-label">
      <td class="label">{$form.label.label}</td>
           {if $action eq 2}
            {include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_contact_type' field='label' 
              id= $id }
            {/if}
      <td>{$form.label.html}</td>
   </tr>
   <tr class="crm-contactType-form-block-parent_id">
      <td class="label">{$form.parent_id.label}</td>
           {if $is_parent OR $action EQ 1}
             <td>{$form.parent_id.html}</td>
           {else}
             <td>{ts}{$contactTypeName} (built-in){/ts}</td>
           {/if}
   </tr>
   <tr class="crm-contactType-form-block-image_URL">
      <td class="label">{$form.image_URL.label}</td>
      <td>{$form.image_URL.html|crmReplace:class:'huge40'}{help id="id-image_URL"}</td>
   </tr> 
   <tr class="crm-contactType-form-block-description">
     <td class="label">{$form.description.label}</td>
          {if $action eq 2}
	    {include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_contact_type' field='description' 
             id= $id }
          {/if}
     <td>{$form.description.html}</td>
   </tr>
         {if $is_parent OR $action eq 1}
   <tr class="crm-contactType-form-block-is_active">
     <td class="label">{$form.is_active.label}</td><td>{$form.is_active.html}</td>
   </tr>
        {/if}
    {/if}
   <tr class="crm-submit-buttons">
      <td colspan="3">{include file="CRM/common/formButtons.tpl"}</td>
   </tr> 
</table>
</fieldset>
</div>
