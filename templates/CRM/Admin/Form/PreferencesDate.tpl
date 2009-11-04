{* this template is used for adding/editing location type  *}
<div class="form-item">
    <fieldset><legend>{ts}Edit Date Settings{/ts}</legend>
        <table class='form-layout'>
            <tr>
                <td class="label">{$form.name.label}</td><td>{$form.name.html}</td>
            </tr>
            <tr>
                <td class="label">{$form.description.label}</td><td>{$form.description.html}</td>
            </tr>
            <tr>    
                <td class="label">{$form.date_format.label}</td><td>{$form.date_format.html}</td>
            </tr>
            {if $form.time_format.label}
            <tr>    
                <td class="label">{$form.time_format.label}</td><td>{$form.time_format.html}</td>
            </tr>
            {/if}
            <tr>    
                <td class="label">{$form.start.label}</td><td>{$form.start.html}</td>
            </tr>
            <tr>    
                <td class="label">{$form.end.label}</td><td>{$form.end.html}</td>
            </tr>

            <tr> 
                <td>&nbsp;</td><td>{$form.buttons.html}</td>
            </tr>
        </table> 
    </fieldset>
</div>
