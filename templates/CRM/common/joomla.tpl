{if $config->debug}
{include file="CRM/common/debug.tpl"}
{/if}

<div id="crm-container" lang="{$config->lcMessages|truncate:2:"":true}" xml:lang="{$config->lcMessages|truncate:2:"":true}">
    
{* Only include joomla.css in administrator (backend). Page layout style ids and classes conflict with typical front-end css and break the page layout. *}

{if $buildNavigation }
    {include file="CRM/common/Navigation.tpl" }
{/if}

<table border="0" cellpadding="0" cellspacing="0" id="crm-content">
  <tr>
{if $sidebarLeft}
    <td id="sidebar-left" valign="top">
        <div id="civi-sidebar-logo" style="margin: 0 0 .25em .25em"><img src="{$config->resourceBase}i/logo_words_small.png" title="{ts}CiviCRM{/ts}/></div><div class="spacer"></div>
       {$sidebarLeft}
    </td>
{/if}
    <td valign="top">
    {if $breadcrumb}
    <div class="breadcrumb">
      {foreach from=$breadcrumb item=crumb key=key}
        {if $key != 0}
           &raquo;
        {/if}
        <a href="{$crumb.url}">{$crumb.title}</a>
      {/foreach}
    </div>
    {/if}

    {if $pageTitle}
        <h1 class="title">{$pageTitle}</h1>
    {/if}
  
{if $browserPrint}
{* Javascript window.print link. Used for public pages where we can't do printer-friendly view. *}
<div id="printer-friendly"><a href="javascript:window.print()" title="{ts}Print this page.{/ts}"><img src="{$config->resourceBase}i/print-icon.png" alt="{ts}Print this page.{/ts}" /></a></div>
{else}
{* Printer friendly link/icon. *}
<div id="printer-friendly"><a href="{$printerFriendly}" title="{ts}Printer-friendly view of this page.{/ts}"><img src="{$config->resourceBase}i/print-icon.png" alt="{ts}Printer-friendly view of this page.{/ts}" /></a></div>
{/if}

{*{include file="CRM/common/langSwitch.tpl"}*}

    <div class="spacer"></div>

    {if $localTasks}
        {include file="CRM/common/localNav.tpl"}
    {/if}

    {include file="CRM/common/status.tpl"}

    <!-- .tpl file invoked: {$tplFile}. Call via form.tpl if we have a form in the page. -->
    {if $isForm}
        {include file="CRM/Form/$formTpl.tpl"}
    {else}
        {include file=$tplFile}
    {/if}

    {if ! $urlIsPublic}
    {include file="CRM/common/footer.tpl"}
    {/if}

    </td>

  </tr>
</table>

{* We need to set jquery $ object back to $*}
<script type="text/javascript">jQuery.noConflict(true);</script>
</div> {* end crm-container div *}
