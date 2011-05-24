{literal}<?php{/literal}

function {$function}_example(){literal}{{/literal}
 $params = 
	array(
{foreach from=$params key=k item=v}
           '{$k}' 		=> {if is_array($v)}array({foreach from=$v key=subkey item=subvalue}
           '{$subkey}' => {if is_array($subvalue)} array(

           {foreach from=$subvalue key=subsubkey item=subsubvalue}
           '{$subsubkey}' => '{$subsubvalue}',

           {/foreach}),{else}'{$subvalue}',
{/if}
           {/foreach}),{else}'{$v}',
{/if}
{/foreach}

  );
  require_once 'api/api.php';
  $result = civicrm_api( '{$fnPrefix}','{$action}',$params );

  return $result;
{literal}}{/literal}

/*
 * Function returns array of result expected from previous function
 */
function {$function}_expectedresult(){literal}{{/literal}

  $expectedResult =
     array(
{foreach from=$result key=k item=v}
           '{$k}' 		=> {if is_array($v)}array({foreach from=$v key=subkey item=subvalue}
           '{$subkey}' => {if is_array($subvalue)} array(
           {foreach from=$subvalue key=subsubkey item=subsubvalue}
           '{$subsubkey}' => {if is_array($subsubvalue)} array(
           {foreach from=$subsubvalue key=subsubsubkey item=subsubsubvalue}
           '{$subsubsubkey}' => '{$subsubsubvalue}',
           {/foreach}),{else}'{$subsubvalue}',{/if}
           {/foreach}),{else}'{$subvalue}',{/if}
           {/foreach}),{else}'{$v}',{/if}

{/foreach}
      );

  return $expectedResult  ;
{literal}}{/literal}


