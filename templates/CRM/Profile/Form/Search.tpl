{if ! empty( $fields )}
 {if $groupId }
    <div id="id_{$groupId}_show" class="data-group">
       <a href="#" onclick="hide('id_{$groupId}_show'); show('id_{$groupId}'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a><label>{ts}New Search{/ts}</label><br />
    </div>

    <div id="id_{$groupId}">
      <fieldset><legend><a href="#" onclick="hide('id_{$groupId}'); show('id_{$groupId}_show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}"/></a>{ts}Search Criteria{/ts}</legend>
{else}
    <div>
{/if}

    <table class="form-layout-compressed">
    {foreach from=$fields item=field key=name}
        {assign var=n value=$field.name}
	{if $field.is_search_range}
	   {assign var=from value=$field.name|cat:'_from'}
	   {assign var=to value=$field.name|cat:'_to'}
	        <tr>
        	    <td class="label">{$form.$from.label}</td>
	            <td class="description">{$form.$from.html}</td>
	            <td class="label">{$form.$to.label}</td>
        	    <td class="description">{$form.$to.html}</td>
	        </tr>
	{elseif $field.options_per_line}
	<tr>
        <td class="option-label">{$form.$n.label}</td>
        <td>
	    {assign var="count" value="1"}
        {strip}
        <table class="form-layout-compressed">
        <tr>
          {* sort by fails for option per line. Added a variable to iterate through the element array*}
          {assign var="index" value="1"}
          {foreach name=outer key=key item=item from=$form.$n}
          {if $index < 10}
              {assign var="index" value=`$index+1`}
          {else}
              <td class="labels font-light">{$form.$n.$key.html}</td>
              {if $count == $field.options_per_line}
                  </tr>
                   <tr>
                   {assign var="count" value="1"}
              {else}
          	       {assign var="count" value=`$count+1`}
              {/if}
          {/if}
          {/foreach}
        </tr>
        </table>
        {/strip}
        </td>
    </tr>
	{else}
	        <tr>
        	    <td class="label">{$form.$n.label}</td>
	            <td class="description">{$form.$n.html}</td>
        	</tr>
	{/if}
    {/foreach}
    <tr><td></td><td>{$form.buttons.html}</td></tr>
    </table>
</div>
{/if}

{if $groupId}
<script type="text/javascript">
    {if empty($rows) }
	var showBlocks = new Array("id_{$groupId}");
        var hideBlocks = new Array("id_{$groupId}_show");
    {else}
	var showBlocks = new Array("id_{$groupId}_show");
        var hideBlocks = new Array("id_{$groupId}");
    {/if}
    {* hide and display the appropriate blocks as directed by the php code *}
    on_load_init_blocks( showBlocks, hideBlocks );
</script>
{/if}