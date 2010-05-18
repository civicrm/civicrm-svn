{if $rows}
<div class="form-item">
     <span class="element-right">{include file="CRM/common/formButtons.tpl"}</span>
</div>

<div class="spacer"></div>

<div>
<br />
<table>
  <tr class="columnheader">
    <th>{ts}Display Name{/ts}</th>
    <th>{ts}Start Date{/ts}</th>
    <th>{ts}End Date{/ts}</th>
    <th>{ts}Source{/ts}</th>
  </tr>

  {foreach from=$rows item=row}
    <tr class="{cycle values="odd-row,even-row"} crm-membership">
        <td class="crm-membership-display_name">{$row.display_name}</td>
        <td class="crm-membership-start_date">{$row.start_date}</td>
        <td class="crm-membership-end_date">{$row.end_date}</td>
        <td class="crm-membership-source">{$row.source}</td>
    </tr>
  {/foreach}
</table>
</div>

<div class="form-item">
     <span class="element-right">{include file="CRM/common/formButtons.tpl"}</span>
</div>

{else}
   <div class="messages status">
            <div class="icon inform-icon"></div>
               {ts}There are no records selected.{/ts}
   </div>
{/if}