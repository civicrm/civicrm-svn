{if $rows}
<div class="form-item">
     <span class="element-right">{$form.buttons.html}</span>
</div>

<div class="spacer"></div>

<div>
<br />
<table>
  <tr class="columnheader">
    <th>{ts}Display Name{/ts}</th>
    <th>{ts}Decision Date{/ts}</th>
    <th>{ts}Amount Requested{/ts}</th>
    <th>{ts}Amount Granted{/ts}</th>
  </tr>

  {foreach from=$rows item=row}
    <tr class="{cycle values="odd-row,even-row"} crm-grant">
        <td class="crm-grant-display_name">{$row.display_name}</td>
        <td class="crm-grant-decision_date">{$row.decision_date}</td>
        <td class="crm-grant-amount_requested">{$row.amount_requested}</td>
        <td class="crm-grant-amount_granted">{$row.amount_granted}</td>
    </tr>
  {/foreach}
</table>
</div>

<div class="form-item">
     <span class="element-right">{$form.buttons.html}</span>
</div>

{else}
   <div class="messages status">
          <div class="icon inform-icon"></div>
            {ts}There are no records selected.{/ts}
   </div>
{/if}
