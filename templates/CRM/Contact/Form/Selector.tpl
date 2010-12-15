{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
{include file="CRM/common/pager.tpl" location="top"}

{include file="CRM/common/pagerAToZ.tpl"}

<table summary="{ts}Search results listings.{/ts}" class="selector">
  <thead class="sticky">
    <tr>
      <th scope="col" title="Select All Rows">{$form.toggleSelect.html}</th>
      {if $context eq 'smog'}
          <th scope="col">
            {ts}Status{/ts}
          </th>
      {/if}
      {foreach from=$columnHeaders item=header}
        <th scope="col">
        {if $header.sort}
          {assign var='key' value=$header.sort}
          {$sort->_response.$key.link}
        {else}
          {$header.name}
        {/if}
        </th>
      {/foreach}
    </tr>
  </thead>

  {counter start=0 skip=1 print=false}

  { if $id }
      {foreach from=$rows item=row}
        <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}">
            {assign var=cbName value=$row.checkbox}
            <td>{$form.$cbName.html}</td>
            {if $context eq 'smog'}
              {if $row.status eq 'Pending'}<td class="status-pending"}>
              {elseif $row.status eq 'Removed'}<td class="status-removed">
              {else}<td>{/if}
              {$row.status}</td>
            {/if}
            <td>{$row.contact_type}</td>
            <td><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}">{$row.sort_name}</a></td>
            {foreach from=$row item=value key=key} 
               {if ($key neq "checkbox") and ($key neq "action") and ($key neq "contact_type") and ($key neq "status") and ($key neq "sort_name") and ($key neq "contact_id") and ($key neq "contact_sub_type")}
                <td>
                {if $key EQ "household_income_total" }
                    {$value|crmMoney}
		{elseif strpos( $key, '_date' ) !== false }
                    {$value|crmDate}
                {else}
                    {$value}
                {/if}
                     &nbsp;
                 </td>
               {/if}
            {/foreach}
            <td>{$row.action|replace:'xx':$row.contact_id}</td>
        </tr>
     {/foreach}
  {else}
      {foreach from=$rows item=row}
         <tr id='rowid{$row.contact_id}' class="{cycle values="odd-row,even-row"}" title="{ts}Click anywhere in the row for an actions menu."{/ts}>
            {assign var=cbName value=$row.checkbox}
            <td>{$form.$cbName.html}</td>
            {if $context eq 'smog'}
                {if $row.status eq 'Pending'}<td class="status-pending"  rel="#crm-actionslist-{$row.contact_id}">
                {elseif $row.status eq 'Removed'}<td class="status-removed" rel="#crm-actionslist-{$row.contact_id}">
                {else}<td rel="#crm-actionslist-{$row.contact_id}">{/if}
                {$row.status}</td>
            {/if}
            <td rel="#crm-actionslist-{$row.contact_id}">{$row.contact_type}</td>	
            <td rel="#crm-actionslist-{$row.contact_id}"><a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}">{if $row.is_deleted}<del>{/if}{$row.sort_name}{if $row.is_deleted}</del>{/if}</a></td>
            {if $action eq 512 or $action eq 256}
              <td rel="#crm-actionslist-{$row.contact_id}">{$row.street_address|mb_truncate:22:"...":true}</td>
              <td rel="#crm-actionslist-{$row.contact_id}">{$row.city}</td>
              <td rel="#crm-actionslist-{$row.contact_id}">{$row.state_province}</td>
              <td rel="#crm-actionslist-{$row.contact_id}">{$row.postal_code}</td>
              <td rel="#crm-actionslist-{$row.contact_id}">{$row.country}</td>
              <td rel="#crm-actionslist-{$row.contact_id}"{if $row.on_hold}class="status-hold"{/if}>{$row.email|mb_truncate:17:"...":true}{if $row.on_hold}&nbsp;(On Hold){/if}</td>
              <td rel="#crm-actionslist-{$row.contact_id}">{$row.phone}</td> 
           {else}
              {foreach from=$row item=value key=key}
                {if ($key neq "checkbox") and ($key neq "action") and ($key neq "contact_type") and ($key neq "contact_sub_type") and ($key neq "status") and ($key neq "sort_name") and ($key neq "contact_id")}
                 <td>{$value}&nbsp;</td>
                {/if}   
              {/foreach}
            {/if}
            <td style='width:80px;' class="crm-actions-container">
            {$row.action|replace:'xx':$row.contact_id}
            <div id="crm-actionslist-{$row.contact_id}" class="crm-actionslist">
                {$row.action|replace:'xx':$row.contact_id}
            </div>
            </td>
         </tr>
    {/foreach}
  {/if}
</table>

<!-- Context Menu populated as per component and permission-->
<ul id="contactMenu" class="contextMenu">
{foreach from=$contextMenu item=value key=key}
  <li class="{$value.ref}"><a href="#{$value.key}">{$value.title}</a></li>
{/foreach}
</ul>
<script type="text/javascript">
 {* this function is called to change the color of selected row(s) *}
    var fname = "{$form.formName}";	
    on_load_init_checkboxes(fname);
 {literal}

cj(document).ready(function() {
      cj('.selector td').cluetip({
            local:true, 
            arrows:true,
            mouseOutClose:true, 
            sticky:true, 
            positionBy: 'mouse',
            showTitle:false,
            closePosition:'title', 
            activation:'toggle',
            topOffset: 8,
            leftOffset:-34,
            width: 180
            });
    });
      {/literal}
</script>
{include file="CRM/common/pager.tpl" location="bottom"}
