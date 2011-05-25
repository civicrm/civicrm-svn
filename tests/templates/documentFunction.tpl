{literal}<?php{/literal}

{* I agree - there must be a better way to do the nested arrays *}
function {$function}_example(){literal}{{/literal}
 $params = 
     array(
{foreach from=$params key=k item=v}
           '{$k}' 		=> {if is_array($v)}array({foreach from=$v key=subkey item=subvalue}
           '{$subkey}' => {if is_array($subvalue)} array(
           {foreach from=$subvalue key=subsubkey item=subsubvalue}
           '{$subsubkey}' => {if is_array($subsubvalue)} array(
{foreach from=$subsubvalue key=subsubsubkey item=subsubsubvalue}
                            '{$subsubsubkey}' => {if is_array($subsubsubvalue)} array(
{foreach from=$subsubsubvalue key=subsubsubsubkey item=subsubsubsubvalue}
                                '{$subsubsubsubkey}' => {if is_array($subsubsubsubvalue)} array(
{foreach from=$subsubsubsubvalue key=subsubsubsubsubkey item=subsubsubsubsubvalue}
                                            '{$subsubsubsubsubkey}' => '{$subsubsubsubsubvalue}',
{/foreach}									),
{else}'{$subsubsubsubvalue}',
{/if}
                            {/foreach}),
{else}'{$subsubsubvalue}',
{/if}
{/foreach}        ),
{else}'{$subsubvalue}',
{/if}
{/foreach}           ),{else}'{$subvalue}',
{/if}
{/foreach}           ),{else}'{$v}',{/if}

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
                            '{$subsubsubkey}' => {if is_array($subsubsubvalue)} array(
{foreach from=$subsubsubvalue key=subsubsubsubkey item=subsubsubsubvalue}
                                '{$subsubsubsubkey}' => {if is_array($subsubsubsubvalue)} array(
{foreach from=$subsubsubsubvalue key=subsubsubsubsubkey item=subsubsubsubsubvalue}
                                            '{$subsubsubsubsubkey}' => '{$subsubsubsubsubvalue}',
{/foreach}									),
{else}'{$subsubsubsubvalue}',{/if}
                            {/foreach}),
{else}'{$subsubsubvalue}',
{/if}
{/foreach}        ),
{else}'{$subsubvalue}',
{/if}
{/foreach}           ),{else}'{$subvalue}',{/if}
{/foreach}           ),{else}'{$v}',{/if}

{/foreach}
      );

  return $expectedResult  ;
{literal}}{/literal}


