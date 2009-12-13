{include file="CRM/common/dashboard.tpl"}
{include file="CRM/common/openFlashChart.tpl"}
<a href="javascript:addDashlet( );" class="button show-add" style="margin-left: 6px;"><span>&raquo; {ts}Configure Your Dashboard{/ts}</span></a>
<a style="display:none;" href="{crmURL p="civicrm/dashboard" q="reset=1"}" class="button show-done" style="margin-left: 6px;"><span>&raquo; {ts}Done{/ts}</span></a>
<a style="float:right;" href="{crmURL p="civicrm/dashboard" q="reset=1&resetCache=1"}" class="button show-refresh" style="margin-left: 6px;"><span>&raquo; {ts}Refresh Dashboard Data{/ts}</span></a>
<div class="spacer"></div>

<div id="empty-message" class='hiddenElement'>{ts}Welcome to your new dashboard.{/ts}</div>

<div id="configure-dashlet" class='hiddenElement'></div>
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
             cj('.show-add').hide( );
             cj('.show-refresh').hide( );
             cj('.show-done').show( );
             cj("#empty-message").hide( );
             cj("#configure-dashlet").show( ).html( content );
         }
      });
  }
        
</script>
{/literal}
