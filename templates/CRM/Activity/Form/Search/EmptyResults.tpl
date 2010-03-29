{* No matches for submitted search request. *}
<div class="messages status">
  <div class="icon inform-icon"></div>
        {if $qill}{ts}No matches found for:{/ts}
            {include file="CRM/common/displaySearchCriteria.tpl"}
        {else}
            {ts}No matching activity results found.{/ts}
        {/if}
</div>
<div>
        <h3>{ts}Suggestions:{/ts}</h3>
        <ul>
        <li>{ts}If you are searching by activity name, check your spelling or use fewer letters.{/ts}</li>
        <li>{ts}If you are searching within a date  range, try a wider range of values.{/ts}</li>
        <li>{ts}Make sure you have enough privileges in the access control system.{/ts}</li>
        </ul>
</div>