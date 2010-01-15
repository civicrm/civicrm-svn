{* Displays contribution/event fees when price set is used. *}
{foreach from=$lineItem item=value key=priceset}
    {if $value neq 'skip'}
    {if $lineItem|@count GT 1} {* Header for multi participant registration cases. *}
        {if $priceset GT 0}<br />{/if}
        <strong>{ts}Participant {$priceset+1}{/ts}</strong>
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

<div class="section no-label total_amount-section">
    <div class="content bold">
        {if $context EQ "Contribution"}
            {ts}Contribution Total{/ts}:
        {elseif $context EQ "Event"}
            {ts}Event Total{/ts}: 
        {/if}
    {$totalAmount|crmMoney}
    </div>
    <div class="clear"></div>
</div>

{if $hookDiscount.message}
    <div class="section hookDiscount-section">
        <em>({$hookDiscount.message})</em>
    </div>
{/if}
