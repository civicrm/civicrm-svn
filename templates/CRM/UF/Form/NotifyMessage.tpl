{if $grouptitle} 

Submitted For: {$displayName}
Date: {$currentDate}
Contact Summary: {$contactLink} 

===========================================================
{$grouptitle}

===========================================================
{foreach from=$values item=value key=valueName}
 {$valueName} : {$value}
{/foreach}

{/if}

