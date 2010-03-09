<div class="spacer"></div>
<div class="messages status">
  <dl>
    <dt><img src="{$config->resourceBase}i/Inform.gif"
    alt="{ts}status{/ts}" /></dt>
    <dd>
      <p>{ts}Are you sure you want to unhold email of selected contact(s)?. This operation cannot be undone.{/ts}</p>
      <p>{include file="CRM/Contact/Form/Task.tpl"}</p>
    </dd>
  </dl>
</div>
<div class="form-item">
 {$form.buttons.html}
</div>
