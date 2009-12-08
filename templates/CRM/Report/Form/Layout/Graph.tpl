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
      {/literal}
      {assign var="chartData" value="openFlashChartData_"|cat:$uniqueId}
      var chartData = "{$chartData};
      {literal}
     cj.each( chartData, function( chartID, chartValues ) {
	     var xSize   = eval( "chartValues.size.xSize" );
	     var ySize   = eval( "chartValues.size.ySize" );
	     var divName = {/literal}"open_flash_chart_{$uniqueId}"{literal};

	     createSWFObject( chartID, divName, xSize, ySize );  
     });
  }
  
  function loadData( chartID ) {
      {/literal}
      {assign var='chatData' value='openFlashChartData_'|cat:$uniqueId}
      var chartData = "{$chatData}";
      {literal}
      var data    = eval( "chartData." + chartID + ".object" );
      return JSON.stringify( data );
  }
</script>
{/literal}
{/if}
