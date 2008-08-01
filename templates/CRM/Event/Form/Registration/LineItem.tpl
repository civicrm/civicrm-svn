    {foreach from=$lineItem item=value key=priceset}
    {if $value neq 'skip'}
    {if $priceset GT 0}
    <br /><strong>{ts}Additional Participant {$priceset}{/ts}</strong>
    {/if}				 
    <table>
            <tr class="columnheader">
                <th>{ts}Item{/ts}</th>
                <th class="right">{ts}Qty{/ts}</th>
                <th class="right">{ts}Unit Price{/ts}</th>
                <th class="right">{ts}Total Price{/ts}</th>
            </tr>
                {foreach from=$value item=line}
            <tr>
                <td>{$line.description}</td>
                <td class="right">{$line.qty}</td>
                <td class="right">{$line.unit_price|crmMoney}</td>
                <td class="right">{$line.line_total|crmMoney}</td>
            </tr>
            {/foreach}
    </table>
    {/if}
    {/foreach}
    <br /><strong>{ts}Event Total{/ts}: {$totalAmount|crmMoney}</strong>
