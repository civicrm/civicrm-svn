{literal}<?php 

function {/literal}{$function}_example(){literal}{{/literal}
    $params = array(
    
{foreach from=$params key=k item=v}
                  '{$k}' 		=> '{$v}',
{/foreach}

  );
  require_once 'api/api.php';
  $result = civicrm_api( 'civicrm_{$function}','{$entity}',$params );

  return $result;
{literal}}{/literal}

/*
 * Function returns array of result expected from previous function
 */
function {$function}_expectedresult(){literal}{{/literal}

  $expectedResult = 
            array(
{foreach from=$result key=k item=v}
                  '{$k}' 		=> {if is_array($v)}
                  array({foreach from=$v key=subkey item=subvalue}
                  '{$subkey}' => {if is_array($subvalue)} array({foreach from=$subvalue key=subsubkey item=subsubvalue}'{$subsubkey}' => '{$subsubvalue}'
                  ,{/foreach}){else}'{$subvalue}',{/if}
                  {/foreach}),{else}'{$v}',{/if}

{/foreach}

  );

  return $expectedResult  ;
{literal}}{/literal}


