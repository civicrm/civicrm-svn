{* this div is being used to apply special css *}
    {if $section eq 1}
        {*include the graph*}
        {include file="CRM/Report/Form/Layout/Graph.tpl"}
    {elseif $section eq 2}
        {*include the table layout*}
        {include file="CRM/Report/Form/Layout/Table.tpl"}
    {else}
        {include file="CRM/Report/Form/Fields.tpl"}
    
        {*Statistics at the Top of the page*}
        {include file="CRM/Report/Form/Statistics.tpl" top=true}
    
        {*include the graph*}
        {include file="CRM/Report/Form/Layout/Graph.tpl"}
    
        {*include the table layout*}
        {include file="CRM/Report/Form/Layout/Table.tpl"}    
    
        {*Statistics at the bottom of the page*}
        {include file="CRM/Report/Form/Statistics.tpl" bottom=true}    
    
        {include file="CRM/Report/Form/ErrorMessage.tpl"}
    {/if}
