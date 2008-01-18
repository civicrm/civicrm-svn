{include file="CRM/common/WizardHeader.tpl"}
{if $widget_id} {* If we have a widget for this page, construct the embed code.*}
    {capture assign=widgetVars}serviceUrl={$config->resourceBase}packages/amfphp/gateway.php&amp;contributionPageID={$id}&amp;widgetID=1{/capture}
    {capture assign=widget_code}
<div style="text-align: center;width:260px">
<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="550" height="400" id="widget" align="middle">
<param name="allowScriptAccess" value="sameDomain" />
<param name="FlashVars" value="{$widgetVars}">
<param name="movie" value="widget.swf" />
<param name="quality" value="high" />
<param name="bgcolor" value="#ffffff" />
<embed flashvars="{$widgetVars}" src="{$config->resourceBase}extern/Widget/widget.swf" quality="high" bgcolor="#ffffff" width="220" height="220" name="widget" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object></div>{/capture}
{/if}

<div id="form" class="form-item">
    <fieldset><legend>{ts}Configure Widget{/ts}</legend>
    <div id="help">
        {ts}CiviContribute widgets allow you and your supporters to easily promote this fund-raising campaign. Widget code can be added to
        any web page - and will provide a real-time display of current contribution results, and a direct link to this contribution page.{/ts} {help id="id-intro"}
    </div>
    <table class="form-layout-compressed">
    	<tr><td style="width: 12em;">&nbsp;</td><td class="font-size11pt">{$form.is_active.html}&nbsp;{$form.is_active.label}</dd>
    </table>
    <div class="spacer"></div>
    
    <div id="widgetFields">
        <table class="form-layout-compressed">
        {foreach from=$fields item=field key=name}
          <tr><td class="label">{$form.$name.label}</td><td>{$form.$name.html}</td></tr>   
        {/foreach}
        </table>
        
        <div id="id-get_code">
            <fieldset>
            <legend>{ts}Preview Widget and Get Code{/ts}</legend>
            <div class="col1">
                {if $widget_id}
                    <div class="description">
                        {ts}Click <strong>Save & Preview</strong> to save any changes to your settings, and preview the widget again on this page.{/ts}
                    </div>
                    {$widget_code}<br />
                {else}
                    <div class="description">
                        {ts}Click <strong>Save & Preview</strong> to save your settings and preview the widget on this page.{/ts}<br />
                    </div>
                {/if}
                <div style="text-align: center;width:260px">{$form._qf_Widget_refresh.html}</div>
            </div>
            <div class="col2">
                {* Include "get widget code" section if widget has been created for this page and is_active. *}
                {if $widget_id}
                    <div class="description">
                        {ts}Add this widget to any web page by copying and pasting the code below.{/ts}
                    </div>
                    <textarea rows="8" cols="50" name="widget_code" id="widget_code">{$widget_code}</textarea>
                    <br />
                    <strong><a href="#" onclick="Widget.widget_code.select(); return false;">&raquo; Select Code</a></strong>
                {else}
                    <div class="description">
                        {ts}The code for adding this widget to web pages will be displayed here after you click <strong>Save and Preview</strong>.{/ts}
                    </div>
                {/if}
            </div>
            </fieldset>
        </div>

        
        <div id="id-colors-show" class="section-hidden section-hidden-border" style="clear: both;">
            <a href="#" onclick="hide('id-colors-show'); show('id-colors'); return false;"><img src="{$config->resourceBase}i/TreePlus.gif" class="action-icon" alt="{ts}open section{/ts}"/></a><label>{ts}Edit Widget Colors{/ts}</label><br />
        </div>
        <div id="id-colors" class="section-shown">
        <fieldset>
        <legend><a href="#" onclick="hide('id-colors'); show('id-colors-show'); return false;"><img src="{$config->resourceBase}i/TreeMinus.gif" class="action-icon" alt="{ts}close section{/ts}"/></a>{ts}Widget Colors{/ts}</legend>
        <div class="description">
            {ts}Enter colors in hexadecimal format prefixed with <em>0x</em>. EXAMPLE: <em>0xFF0000</em> = Red. You can do a web search
            on "hexadecimal colors" to find a chart of color codes.{/ts}
        </div>
        <table class="form-layout-compressed">
        {foreach from=$colorFields item=field key=name}
          <tr><td class="label">{$form.$name.label}</td><td>{$form.$name.html}</td></tr>   
        {/foreach}
        </table>
        </fieldset>
        </div>

    </div>

    {if $action ne 4}
    <div id="crm-submit-buttons">
        <dl><dt></dt><dd>{$form.buttons.html}</dd></dl>  
    </div>
    {else}
    <div id="crm-done-button">
         <dl><dt></dt><dd>{$form.buttons.html}<br></dd></dl>
    </div>
    {/if} {* $action ne view *}
    </fieldset>

</div>      
{include file="CRM/common/showHide.tpl"}

{literal}
<script type="text/javascript">
	var is_act = document.getElementsByName('is_active');
  	if ( ! is_act[0].checked) {
           hide('widgetFields');
	}
    function widgetBlock(chkbox) {
        if (chkbox.checked) {
	      show('widgetFields');
	      return;
        } else {
	      hide('widgetFields');
          return;
	   }
    }
</script>
{/literal}