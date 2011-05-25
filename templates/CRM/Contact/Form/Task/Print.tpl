{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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
{if $rows}
<div class="crm-submit-buttons element-right">
     {include file="CRM/common/formButtons.tpl" location="top"}
</div>
<div class="spacer"></div>
<div>
<br />
<table>
  <tr class="columnheader">
{if $id}
  {foreach from=$columnHeaders item=header}
     <th>{$header}</th>
  {/foreach}
{else}
    <td>{ts}Name{/ts}</td>
    <td>{ts}Address{/ts}</td>
    <td>{ts}City{/ts}</td>
    <td>{ts}State{/ts}</td>
    <td>{ts}Postal{/ts}</td>
    <td>{ts}Country{/ts}</td>
    <td>{ts}Email{/ts}</td>
    <td>{ts}Phone{/ts}</td>
{/if}
  </tr>
{foreach from=$rows item=row}
    <tr class="{cycle values="odd-row,even-row"}">
{if $id}
        <td>{$row.sort_name}</td>
         {foreach from=$row item=value key=key}
           {if ($key neq "checkbox") and ($key neq "action") and ($key neq "contact_type") and ($key neq "status") and ($key neq "contact_id") and ($key neq "sort_name")}
              <td>{$value}</td>
           {/if}
         {/foreach}

{else}
        <td>{$row.sort_name}</td>
        <td>{$row.street_address}</td>
        <td>{$row.city}</td>
        <td>{$row.state_province}</td>
        <td>{$row.postal_code}</td>
        <td>{$row.country}</td>
        <td>{$row.email}</td>
        <td>{$row.phone}</td>
{/if}
    </tr>
{/foreach}
</table>
</div>

<div class="crm-submit-buttons element-right">
     {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{else}
   <div class="messages status">
  <div class="icon inform-icon"></div>
       {ts}There are no records selected for Print.{/ts}
  </div>
{/if}
