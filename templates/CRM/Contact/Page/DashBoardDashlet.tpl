<script type="text/javascript" src="{$config->resourceBase}js/dashboard.js"></script>
{include file="CRM/common/openFlashChart.tpl"}
<a href="javascript:addDashlet( );" class="button show-add" style="margin-left: 6px;"><span>&raquo; {ts}Configure Home Dashboard{/ts}</span></a>
<a style="display:none;" href="{crmURL p="civicrm/dashboard" q="reset=1"}" class="button show-done" style="margin-left: 6px;"><span>&raquo; {ts}Done{/ts}</span></a>
<a style="float:right;" href="{crmURL p="civicrm/dashboard" q="reset=1&resetCache=1"}" class="button show-add" style="margin-left: 6px;"><span>&raquo; {ts}Refresh Dashlets{/ts}</span></a>
<div class="spacer"></div>

<div id="configure-dashlet" class='hidden'></div>
<div id="civicrm-dashboard">
  <!-- You can put anything you like here.  jQuery.dashboard() will remove it. -->
  {ts}You need javascript to use the dashboard.{/ts}
</div>
<div class="clear"></div>

{literal}
<script type="text/javascript">
  function addDashlet(  ) {
      var dataURL = {/literal}"{crmURL p='civicrm/dashlet' q='reset=1&snippet=1' h=0 }"{literal};

      cj.ajax({
         url: dataURL,
         success: function( content ) {
             cj("#civicrm-dashboard").hide( );
             cj(".show-add").hide( );
             cj(".show-done").show( );
             cj("#configure-dashlet").show( ).html( content );
         }
      });
  }
        
</script>
{/literal}
