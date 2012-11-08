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
{* Confirmation of Grant pay  *}
{if $approved}
<div class="messages status">
        <p><div class="icon inform-icon"></div>&nbsp;
        {ts}'{$paid} of the {$total} selected grants have already been paid. {$notApproved} of the {$total} selected grants are not approved. {if $multipleCurrency } {$multipleCurrency} of {$total} grants have different currency of same user. {/if} Would you like to proceed to paying the {$approved} approved but unpaid grants?'{/ts}</p>
</div>
{else if}
<div class="messages status">
        <p><div class="icon inform-icon"></div>&nbsp;
        {ts}Please select at least one grant that has been approved and not been paid.{/ts}</p>
</div>
{/if}
<div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>
