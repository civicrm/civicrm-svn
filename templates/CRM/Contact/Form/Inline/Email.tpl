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
{* This file provides the template for inline editing of emails *}
<table>
    {section name='i' start=1 loop=$totalBlocks} 
    {assign var='blockId' value=$smarty.section.i.index} 
        <tr id="Email_Block_{$blockId}">
            <td class="label">{ts}Email{/ts}</td>
            <td>{$form.email.$blockId.email.html|crmReplace:class:twenty}&nbsp;{$form.email.$blockId.location_type_id.html}
            </td>
            <td align="center" id="Email-Primary-html" {if $blockId eq 1}class="hiddenElement"{/if}>{$form.email.$blockId.is_primary.1.html}</td>
            {if $blockId gt 1}
            <td><a href="#" title="{ts}Delete Email Block{/ts}" onClick="removeBlock( 'Email', '{$blockId}' ); return false;">{ts}delete{/ts}</a></td>
            {/if}
        </tr>
    {/section}
</table>
