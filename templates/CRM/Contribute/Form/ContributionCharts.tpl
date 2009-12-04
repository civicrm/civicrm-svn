{* Display monthly and yearly contributions using Google charts (Bar and Pie) *} 
{if $hasContributions}
<table class="chart">
  <tr>
     <td>
         {if $hasByMonthChart}
      	     {* display monthly chart *}
             <div id="open_flash_chart_1"></div>
         {else}
	     {ts}There were no contributions during the selected year.{/ts}  
         {/if}	
     </td> 
     <td>
       	 {* display yearly chart *}
         <div id="open_flash_chart_2"></div>
     </td>
  </tr>
</table>

<table  class="form-layout-compressed" >
      <td class="label">{$form.select_year.label}</td><td>{$form.select_year.html}</td> 
      <td class="label">{$form.chart_type.label}</td><td>{$form.chart_type.html}</td> 
      <td class="html-adjust">
        {$form.buttons.html}<br />
        <span class="add-remove-link"><a href="{crmURL p="civicrm/contribute" q="reset=1"}">{ts}Table View{/ts}...</a></span>
      </td> 
</table>
{else}
 <div class="messages status"> 
      <dl> 
        <dd>{ts}There are no live contribution records to display.{/ts}</dd> 
      </dl> 
 </div>
{/if}

{if $hasOpenFlashChart}
{include file="CRM/common/openFlashChart.tpl"}

{literal}
<script type="text/javascript">

  cj( function( ) {
      buildChart( );
  });

  function buildChart( ) {
     var chartData = {/literal}{$openFlashChartData}{literal};	
     cj.each( chartData, function( chartID, chartValues ) {

	 var xSize   = eval( "chartValues.size.xSize" );
	 var ySize   = eval( "chartValues.size.ySize" );
	 var divName = eval( "chartValues.divName" );

	 createSWFObject( chartID, divName, xSize, ySize );  
     });
  }
  
  function loadData( chartID ) {
     var allData = {/literal}{$openFlashChartData}{literal};
     var data    = eval( "allData." + chartID + ".object" );
     return JSON.stringify( data );
  }
 
  function byMonthOnClick( barIndex ) {
     var allData = {/literal}{$openFlashChartData}{literal};
     var url     = eval( "allData.by_month.on_click_urls.url_" + barIndex );
     if ( url ) window.location = url;
  }

  function byYearOnClick( barIndex ) {
     var allData = {/literal}{$openFlashChartData}{literal};
     var url     = eval( "allData.by_year.on_click_urls.url_" + barIndex );
     if ( url ) window.location = url;
  }

</script>
{/literal}
{/if}
