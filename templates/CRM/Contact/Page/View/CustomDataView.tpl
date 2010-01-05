{* Custom Data view mode*}
{assign var="customGroupCount" value = 1}
{foreach from=$viewCustomData item=customValues key=customGroupId}
    {assign var="count" value=$customGroupCount%2}
    {if $count eq $side }
        {foreach from=$customValues item=cd_edit key=cvID}
            <div class="customFieldGroup ui-corner-all">
                <table id="{$cd_edit.name}_{$count}" >
                  <tr class="columnheader">
                    <td colspan="2" class="grouplabel">
                        <a href="#" class="show-block {if $cd_edit.collapse_display eq 0 } expanded collapsed {else} collapsed {/if}" onclick='cj("table#{$cd_edit.name}_{$count} tr:not(\".columnheader\")" ).toggle(); cj(this).toggleClass("expanded"); return false;'>
                            {$cd_edit.title}
                        </a>
                    </td>
                  </tr>
                  {foreach from=$cd_edit.fields item=element key=field_id}
                     {include file="CRM/Contact/Page/View/CustomDataFieldView.tpl"}
                  {/foreach}
                </table>
            </div>
        {/foreach}
    {/if}
    {assign var="customGroupCount" value = $customGroupCount+1}
{/foreach}