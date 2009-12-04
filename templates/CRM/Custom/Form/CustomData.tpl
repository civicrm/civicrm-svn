{* Custom Data form*}
{foreach from=$groupTree item=cd_edit key=group_id}    
    <div id="{$cd_edit.name}_show_{$cgCount}" class="section-hidden section-hidden-border">
            <a href="#" onclick="cj('#{$cd_edit.name}_show_{$cgCount}').hide(); cj('#{$cd_edit.name}_{$cgCount}').show(); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a>
            <label>{$cd_edit.title}</label><br />
    </div>

    <div id="{$cd_edit.name}_{$cgCount}" class="form-item">
	<fieldset>
	    <legend><a href="#" onclick="cj('#{$cd_edit.name}_{$cgCount}').hide(); cj('#{$cd_edit.name}_show_{$cgCount}').show(); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}"/></a>{$cd_edit.title}</legend>
            {if $cd_edit.help_pre}
                <div class="messages help">{$cd_edit.help_pre}</div>
            {/if}
            <table class="form-layout-compressed">
                {foreach from=$cd_edit.fields item=element key=field_id}
                   {include file="CRM/Custom/Form/CustomField.tpl"}
                {/foreach}
            </table>
            <div class="spacer"></div>
            {if $cd_edit.help_post}<div class="messages help">{$cd_edit.help_post}</div>{/if}
        </fieldset>
        {if $cd_edit.is_multiple and ( ( $cd_edit.max_multiple eq '' )  or ( $cd_edit.max_multiple > 0 and $cd_edit.max_multiple >= $cgCount ) ) }
            <div id="add-more-link-{$cgCount}"><a href="javascript:buildCustomData('{$cd_edit.extends}','{$cd_edit.extends_entity_column_id}', '{$cd_edit.extends_entity_column_value}', {$cgCount}, {$group_id}, true );">{ts 1=$cd_edit.title}Add another %1 record{/ts}</a></div>	
        {/if}
    </div>
    <div id="custom_group_{$group_id}_{$cgCount}"></div>

    <script type="text/javascript">
    {if $cd_edit.collapse_display eq 0 }
            cj('#{$cd_edit.name}_show_{$cgCount}').hide(); cj('#{$cd_edit.name}_{$cgCount}').show();
    {else}
            cj('#{$cd_edit.name}_show_{$cgCount}').show(); cj('#{$cd_edit.name}_{$cgCount}').hide();
    {/if}
    </script>
{/foreach}
