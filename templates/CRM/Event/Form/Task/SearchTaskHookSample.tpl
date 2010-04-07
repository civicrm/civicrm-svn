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
    <th>{ts}Amount{/ts}</th>
    <th>{ts}Register Date{/ts}</th>
    <th>{ts}Source{/ts}</th>
  </tr>

  {foreach from=$rows item=row}
    <tr class="{cycle values="odd-row,even-row"}">
        <td>{$row.display_name}</td>
        <td>{$row.amount}</td>
        <td>{$row.register_date}</td>
        <td>{$row.source}</td>
    </tr>
  {/foreach}
</table>
</div>

<div class="form-item">
     <span class="element-right">{$form.buttons.html}</span>
</div>

{else}
   <div class="messages status">
      <dl>
          <dt><div class="icon inform-icon"></div></dt>
          <dd>
            {ts}There are no records selected.{/ts}
          </dd>
      </dl>
   </div>
{/if}