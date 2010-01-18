<div id="priceset_{$priceSetId}" class="section price_set-section">
    {if $priceSet.help_pre}
        <div class="description">{$priceSet.help_pre}</div>
    {/if}
          
    {foreach from=$priceSet.fields item=element key=field_id}
    <div class="section {$element.name}-section">
    {if ($element.html_type eq 'CheckBox' || $element.html_type == 'Radio') && $element.options_per_line}
      {assign var="element_name" value=price_$field_id}
        <div class="label">{$form.$element_name.label}</div>
        <div class="content">
            <div class="price-set-row">
        {assign var="count" value="1"}
        {foreach name=outer key=key item=item from=$form.$element_name}
            {if is_numeric($key) }
                <span class="price-set-option-content">{$form.$element_name.$key.html}</span>
                {if $count == $element.options_per_line}
                    </div><div class="price-set-row">
                    {assign var="count" value="1"}
                {else}
                    {assign var="count" value=`$count+1`}
                {/if}
            {/if}
        {/foreach}
            </div>
	    {if $element.help_post}
                    <div class="description">{$element.help_post}</div>
            {/if}
        </div>
        <div class="clear"></div>

    {else}

        {assign var="name" value="$element.name"}
        {assign var="element_name" value="price_"|cat:$field_id}

        <div class="label">{$form.$element_name.label}</div>
        <div class="content">{$form.$element_name.html}
              {if $element.help_post}<br /><span class="description">{$element.help_post}</span>{/if}
        </div>
        <div class="clear"></div>

    {/if}
    </div>
    {/foreach}
    {if $priceSet.help_post}
    <div class="description">{$priceSet.help_post}</div>
    {/if}

    {include file="CRM/Price/Form/Calculate.tpl"} 

</div>
