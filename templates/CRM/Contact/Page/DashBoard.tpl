<table class="no-border">
    <tr>
        <td>
            {if empty($hookContent)}
                {include file="CRM/Contact/Page/DashBoardDashlet.tpl"}
            {else}
                {if $hookContentPlacement != 2 && $hookContentPlacement != 3}
                    {include file="CRM/Contact/Page/DashBoardDashlet.tpl"}
                {/if}

                {foreach from=$hookContent key=title item=content}
                <fieldset><legend>{$title}</legend>
                    {$content}
                </fieldset>
                {/foreach}

                {if $hookContentPlacement == 2}
                    {include file="CRM/Contact/Page/DashBoardDashlet.tpl"}
                {/if}
            {/if}
        </td>
    </tr>
</table>
