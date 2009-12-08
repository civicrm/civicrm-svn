{* Display weekly,Quarterly,monthly and yearly contributions using pChart (Bar and Pie) *}
{if $hasOpenFlashChart}
<table class="chart">
        <tr>
            <td>
                <div id="open_flash_chart_{$uniqueId}"></div>
            </td>
        </tr>
</table>
{elseif $chartEnabled && $chartSupported && $rows}
    <table class="chart">
        <tr>
            <td>
                <img src="{$graphFilePath}"/>
            </td>
        </tr>
    </table>
{/if} 

{if $hasOpenFlashChart}
    {if !$section}
        {include file="CRM/common/openFlashChart.tpl"}
    {/if}
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
	     var divName = {/literal}"open_flash_chart_{$uniqueId}"{literal};

         var loadDataFunction  = {/literal}"loadData{$uniqueId}"{literal};
	     createSWFObject( chartID, divName, xSize, ySize, loadDataFunction );  
     });
  }
  
  function loadData{/literal}{$uniqueId}{literal}( chartID ) {
      var allData = {/literal}{$openFlashChartData}{literal};
      var data    = eval( "allData." + chartID + ".object" );
      return JSON.stringify( data );
  }  
</script>
{/literal}
{/if}
