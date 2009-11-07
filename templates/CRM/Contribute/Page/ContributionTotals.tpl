{*Table displays contribution totals for a contact or search result-set *}
{if $annual.count OR $contributionSummary}
    <table class="form-layout-compressed">
    
    {if $annual.count}
        <tr>
            <th class="contriTotalLeft right">{ts}Current Year-to-Date{/ts} - {$annual.amount}</th>
            <th class="right"> &nbsp; {ts}# Contributions{/ts} - {$annual.count}</th>
            <th class="right contriTotalRight"> &nbsp; {ts}Avg Amount{/ts} - {$annual.avg}</th>
            {if $contributionSummary.cancel.amount}
                <td>&nbsp;</td>
            {/if}
        </tr>
    {/if}

    {if $contributionSummary }
      <tr>
          {if $contributionSummary.total.amount}
            <th class="contriTotalLeft right">{ts}Total Amount{/ts} - {$contributionSummary.total.amount}</th>
            <th class="right"> &nbsp; {ts}# Contributions{/ts} - {$contributionSummary.total.count}</th>
            <th class="right contriTotalRight"> &nbsp; {ts}Avg Amount{/ts} - {$contributionSummary.total.avg}</th>
          {/if}
          {if $contributionSummary.cancel.amount}
            <th class="disabled right contriTotalRight"> &nbsp; {ts}Total Cancelled Amount{/ts} - {$contributionSummary.cancel.amount}</th>
          {/if}
      </tr>  
    {/if}
    
    </table>
{/if}