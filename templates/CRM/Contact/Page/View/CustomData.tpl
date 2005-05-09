{* template for custom data *}

{if $action eq 2}
    <form {$form.attributes}>
    <div class="form-item">
    <p>
    <fieldset><legend>Edit Custom Data</legend>
    {strip}
    {foreach from=$groupTree item=cd_edit key=group_id}
        <fieldset><legend>{$cd_edit.title}</legend>
        {foreach from=$cd_edit.fields item=element key=field_id}
        {assign var="element_html" value=`$element.html_type`} 
        {assign var="name" value=`$element.name`} 
        {assign var="element_name value=$group_id|cat:_|cat:$field_id|cat:_|cat:$name}
            {if $element_html eq "Radio" or $element_html eq "Checkbox"}
                <dl>
                <dt>{$element.label}</dt>
                <dd>
                {foreach from=$form.$element_name item=subElement}
                {$subElement.html}
                {/foreach}
                </dd>
                </dl>
            {else}
                <dl>
                {$form.note.html}
                <dt>{$element.label}</dt><dd>{$form.$element_name.html}</dd>
                </dl>
            {/if}
        {/foreach}
        </fieldset>
    {/foreach}
    {/strip}
    <dl>
    <dt></dt><dd>{$form.buttons.html}</dd>
    </dl>  
    </fieldset>
    </p>
    </div>
    </form>
{/if}

<div id="name" class="data-group form-item">
    <p>
	<label>{$displayName}</label>
        <a href="{crmURL p='civicrm/contact/view/cd' q="cid=`$contactId`&action=update"}">Edit custom data</a>
    </p>
</div>


<div class="form-item">
{strip}
{foreach from=$groupTree item=cd_view}
<fieldset><legend>{$cd_view.title}</legend>
    {foreach from=$cd_view.fields item=cd_value_view}
    <dl>
    <dt>{$cd_value_view.label}</dt>
    <dd>{if $cd_value_view.customValue}{$cd_value_view.customValue.data}{else}--{/if}</dd>
    </dl>
    {/foreach}
</fieldset>
{/foreach}
{/strip}
</div>
