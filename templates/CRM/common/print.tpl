{* Print.tpl: wrapper for Print views. Provides complete HTML doc. Includes print media stylesheet.*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">

<head>
  <title>{$pageTitle}</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <base href="{crmURL p="" a=true}" /><!--[if IE]></base><![endif]-->
  <style type="text/css" media="screen, print">@import url({$config->resourceBase}css/civicrm.css);</style>
  <style type="text/css" media="screen, print">@import url({$config->resourceBase}css/instance.css);</style>
  <style type="text/css" media="print">@import url({$config->resourceBase}css/print.css);</style>
  <style type="text/css">@import url({$config->resourceBase}css/skins/aqua/theme.css);</style>
  <script type="text/javascript" src="{$config->resourceBase}js/Common.js"></script>
</head>

<body>
{if $config->debug}
{include file="CRM/common/debug.tpl"}
{/if}
{include file="CRM/common/jquery.tpl"}
<div id="crm-container" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
{* Check for Status message for the page (stored in session->getStatus). Status is cleared on retrieval. *}
{if $session->getStatus(false)}
<div class="messages status">
  <dl>
  <dt><img src="{$config->resourceBase}i/Inform.gif" alt="{ts}status{/ts}" /></dt>
  <dd>{$session->getStatus(true)}</dd>
  </dl>
</div>
{/if}

{if isset($display_name) and $display_name}
    <h3 style="margin: .25em;">{$display_name}</h3>
{/if}

<!-- .tpl file invoked: {$tplFile}. Call via form.tpl if we have a form in the page. -->
{if $isForm}
    {include file="CRM/Form/$formTpl.tpl"}
{else}
    {include file=$tplFile}
{/if}


</div> {* end crm-container div *}
</body>
</html>
