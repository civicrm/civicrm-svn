{* Custom Data view mode*}
{assign var="customGroupCount" value = 1}
{foreach from=$viewCustomData item=customValues key=customGroupId}
    {assign var="count" value=$customGroupCount%2}
    {if $count eq $side }
        {foreach from=$customValues item=cd_edit key=cvID}
            <div class="customFieldGroup ui-corner-all">
                <table>
                  <tr>
                    <td colspan="2" class="grouplabel">{$cd_edit.title}</td>
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