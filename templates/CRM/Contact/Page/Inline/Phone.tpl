{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
{* template for building phone block*}
<div id="crm-phone-content" class="crm-table2div-layout{if $permission EQ 'edit'} crm-inline-edit" data-edit-params='{ldelim}"cid": "{$contactId}", "class_name": "CRM_Contact_Form_Inline_Phone"{rdelim}' title="{ts}Add or edit phone{/ts}{/if}">
  <div class="crm-clear"><!-- start of main -->
    {if $permission EQ 'edit'}
      <div class="crm-edit-help">
        <span class="batch-edit"></span>{if empty($phone)}{ts}Add phone{/ts}{else}{ts}Add or edit phone{/ts}{/if}
      </div>
    {/if}
    {if empty($phone)}
      <div class="crm-label">{ts}Phone{/ts}</div>
      <div class="crm-content"></div>
    {/if}
    {foreach from=$phone item=item}
      {if $item.phone}
        <div class="crm-label">{$item.location_type}&nbsp;{$item.phone_type}</div>
        <div class="crm-content crm-contact_phone {if $item.is_primary eq 1}primary{/if}">
          <span {if $privacy.do_not_phone} class="do-not-phone" title="{ts}Privacy flag: Do Not Phone{/ts}"{/if}>
    {$item.phone}{if $item.phone_ext}&nbsp;&nbsp;{ts}ext.{/ts} {$item.phone_ext}{/if}
          </span>
        </div>
      {/if}
    {/foreach}
   </div> <!-- end of main -->
</div>
