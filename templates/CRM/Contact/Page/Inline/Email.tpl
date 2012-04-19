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
{* template for building email block*}
<table>
<tr><td colspan="3">
<span id="edit-email" class="hiddenElement batch-edit" title="{ts}Click to edit{/ts}"></span>
</td></tr>
{foreach from=$email key="blockId" item=item}
{if $item.email}
<tr>
<td class="label">{$item.location_type}&nbsp;{ts}Email{/ts}</td>
<td class="crm-contact_email"><span class={if $privacy.do_not_email}"do-not-email" title="{ts}Privacy flag: Do Not Email{/ts}" {elseif $item.on_hold}"email-hold" title="{ts}Email on hold - generally due to bouncing.{/ts}" {elseif $item.is_primary eq 1}"primary"{/if}><a href="mailto:{$item.email}">{$item.email}</a>{if $item.on_hold == 2}&nbsp;({ts}On Hold - Opt Out{/ts}){elseif $item.on_hold}&nbsp;({ts}On Hold{/ts}){/if}{if $item.is_bulkmail}&nbsp;({ts}Bulk{/ts}){/if}</span></td>
<td class="description">{if $item.signature_text OR $item.signature_html}<a href="#" title="{ts}Signature{/ts}" onClick="showHideSignature( '{$blockId}' ); return false;">{ts}(signature){/ts}</a>{/if}</td>
</tr>
<tr id="Email_Block_{$blockId}_signature" class="hiddenElement">
<td><strong>{ts}Signature HTML{/ts}</strong><br />{$item.signature_html}<br /><br />
<strong>{ts}Signature Text{/ts}</strong><br />{$item.signature_text|nl2br}</td>
<td colspan="2"></td>
</tr>
{/if}
{/foreach}
</table>

