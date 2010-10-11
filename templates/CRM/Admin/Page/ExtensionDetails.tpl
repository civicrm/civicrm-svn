{$extension.description}<br/>
{foreach from=$extension.urls key=label item=url}
<strong>{$label}</strong>: <a href="{$url}">{$url}</a><br/>
{/foreach}
<strong>{ts}Author:{/ts}</strong> {$extension.maintainer.author} ({$extension.maintainer.email})<br/>

<strong>{ts}Location:{/ts}</strong> {$extension.path}<br/>
<strong>{ts}License:{/ts}</strong> {$extension.license}<br/>
<strong>{ts}Released on:{/ts}</strong> {$extension.releaseDate}<br/>
<strong>{ts}License:{/ts}</strong> {$extension.license}<br/>
<strong>{ts}Development stage:{/ts}</strong> {$extension.develStage}<br/>
<strong>{ts}Compatible with:{/ts}</strong>
{foreach from=$extension.compatibility.ver item=ver}
{$ver} &nbsp;
{/foreach}<br/>
<strong>{ts}Comments:{/ts}</strong> {$extension.comments}<br/>