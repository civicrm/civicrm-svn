<fieldset><legend>{ts}Custom Options{/ts}</legend>

    <div class="form-item">
        <dl>
        <dt>{$form.label.label}</dt><dd>&nbsp;{$form.label.html}</dd>
        <dt>{$form.value.label}</dt><dd>&nbsp;{$form.value.html}</dd>
        <dt>{$form.weight.label}</dt><dd>&nbsp;{$form.weight.html}</dd>
        <dt>{$form.is_active.label}</dt><dd>&nbsp;{$form.is_active.html}</dd>
	</dl>
    </div>
    
    <div id="crm-submit-buttons" class="form-item">
    <dl>
    {if $action ne 4}
        <dt>&nbsp;</dt><dd>{$form.buttons.html}</dd>
    {else}
        <dt>&nbsp;</dt><dd>{$form.done.html}</dd>
    {/if} {* $action ne view *}
    <dl>
    </div>

</fieldset>
