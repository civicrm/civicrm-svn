{foreach from=$tagset item=tagset}

<div class="section tag-section tag-{$tagset.parentID}-section">
<div class="label">
<label>{$tagset.parentName}</label>
</div>
<div class="content">
<input type="text" name="taglist_{$tagset.parentID}" id="taglist_{$tagset.parentID}" />

<script type="text/javascript">
{literal}
    eval( 'tokenClass = { tokenList: "token-input-list-facebook", token: "token-input-token-facebook", tokenDelete: "token-input-delete-token-facebook", selectedToken: "token-input-selected-token-facebook", highlightedToken: "token-input-highlighted-token-facebook", dropdown: "token-input-dropdown-facebook", dropdownItem: "token-input-dropdown-item-facebook", dropdownItem2: "token-input-dropdown-item2-facebook", selectedDropdownItem: "token-input-selected-dropdown-item-facebook", inputToken: "token-input-input-token-facebook" } ');
    
    var tagUrl = {/literal}"{$tagset.tagUrl}"{literal};
    var entityTags;
    {/literal}{if $tagset.entityTags}{literal}
        eval( 'entityTags = ' + {/literal}'{$tagset.entityTags}'{literal} );
    {/literal}{/if}{literal}
    var hintText = "{/literal}{ts}Type in a partial or complete name of an existing tag.{/ts}{literal}";
    
    cj( "#taglist_{/literal}{$tagset.parentID}{literal}"  ).tokenInput( tagUrl, { prePopulate: entityTags, classes: tokenClass, hintText: hintText, ajaxCallbackFunction: 'processTags_{/literal}{$tagset.parentID}{literal}'});

    function processTags_{/literal}{$tagset.parentID}{literal}( action, id ) {
        var postUrl     = "{/literal}{crmURL p='civicrm/ajax/processTags' h=0}{literal}";
        var parentId    = "{/literal}{$tagset.parentID}{literal}";
        var entityId   = "{/literal}{$tagset.entityId}{literal}";
        var entityTable = "{/literal}{$tagset.entityTable}{literal}";
         
        cj.post( postUrl, { action: action, tagID: id, parentId: parentId, entityId: entityId, entityTable: entityTable } );
    }
{/literal}
</script>
</div>
<div class="clear"></div> 

</div>

{/foreach}

