<tr><td colspan="3" style="padding:0;">
<table style="border:none;">
<tr>
    {if $form.address.$blockId.city}
       <td>
          {$form.address.$blockId.city.label}<br />
          {$form.address.$blockId.city.html}
       </td>
    {/if}
    {if $form.address.$blockId.postal_code}
       <td>
          {$form.address.$blockId.postal_code.label}<br />
          {$form.address.$blockId.postal_code.html}
          {$form.address.$blockId.postal_code_suffix.html}<br />
          <span class="description font-italic" style="white-space:nowrap;">Enter optional 'add-on' code after the dash ('plus 4' code for U.S. addresses).</span>
       </td>
    {/if}
    <td colspan="2">&nbsp;&nbsp;</td>
</tr>
</table>
</td></tr>
