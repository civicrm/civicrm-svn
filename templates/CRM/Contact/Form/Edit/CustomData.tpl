<script type="text/javascript">var showTab = Array( );</script>
{foreach from=$groupTree item=cd_edit key=group_id}    
<h3 class="head"> 
    <span id="custom{$group_id}" class="ui-icon ui-icon-triangle-1-e"></span><a href="#">{$cd_edit.title}</a>
</h3>
<div id="customData{$group_id}" class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom">
    <fieldset>{include file="CRM/Custom/Form/CustomData.tpl" formEdit=true}</fieldset>
</div>
<script type="text/javascript">
{if $cd_edit.collapse_display eq 0 }
    var eleSpan          = "span#custom{$group_id}";
    var eleDiv           = "div#customData{$group_id}";
    showTab[{$group_id}] = {literal}{"spanShow":eleSpan,"divShow":eleDiv}{/literal};
{else}
    showTab[{$group_id}] = {literal}{"spanShow":""}{/literal};
{/if}
</script>
{/foreach}
<div id='customData' ></div>