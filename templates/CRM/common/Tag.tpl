{if $parentId}
<input type="text" name="taglist" id="taglist" />
<script type="text/javascript">
{literal}
    eval( 'tokenClass = { tokenList: "token-input-list-facebook", token: "token-input-token-facebook", tokenDelete: "token-input-delete-token-facebook", selectedToken: "token-input-selected-token-facebook", highlightedToken: "token-input-highlighted-token-facebook", dropdown: "token-input-dropdown-facebook", dropdownItem: "token-input-dropdown-item-facebook", dropdownItem2: "token-input-dropdown-item2-facebook", selectedDropdownItem: "token-input-selected-dropdown-item-facebook", inputToken: "token-input-input-token-facebook" } ');
    
    var tagUrl = {/literal}"{$tagUrl}"{literal};
    var entityTags;
    {/literal}{if $entityTags}{literal}
        eval( 'entityTags = ' + {/literal}'{$entityTags}'{literal} );
    {/literal}{/if}{literal}
    var hintText = "{/literal}{ts}Type in a partial or complete name of an existing tag.{/ts}{literal}";
    cj( "#taglist"  ).tokenInput( tagUrl, { prePopulate: entityTags, classes: tokenClass, hintText: hintText, ajaxCallbackFunction: 'processTags' });

    function processTags( action, id ) {
        var postUrl     = "{/literal}{crmURL p='civicrm/ajax/processTags' h=0}{literal}";
        var parentId    = "{/literal}{$parentId}{literal}";
        var entityId    = "{/literal}{$entityId}{literal}";
        var entityTable = "{/literal}{$entityTable}{literal}";
         
        cj.post( postUrl, { action: action, tagID: id, parentId: parentId, entityId: entityId, entityTable: entityTable } );
    }
{/literal}
</script>
{/if}