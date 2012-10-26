{capture assign='adminURL'}{crmURL p='civicrm/admin/setting/path' q="reset=1&civicrmDestination=$destination"}{/capture}
<div class="crm-content-block crm-block">
  {foreach from=$extAddNewReqs item=req}
  <div class="messages status no-popup">
       <div class="icon inform-icon"></div>
       {$req.title}<br/>
       {$req.message}
       {*
       {ts 1=$adminURL}Your extensions directory is not set or is not writable. Click <a href='%1'>here</a> to set the extensions directory.{/ts}
       *}
  </div>
  {/foreach}
</div>
