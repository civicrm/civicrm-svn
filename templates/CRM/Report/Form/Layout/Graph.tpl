{assign var=chartId value=$chartType|cat:"_$instanceId"}
{assign var=uploadURL value=$config->imageUploadURL|replace:'persist/contribute':'upload/openFlashChart'}
{* Display weekly,Quarterly,monthly and yearly contributions using pChart (Bar and Pie) *}
{if $chartEnabled and $chartSupported}
<table class="chart">
        <tr>
            <td>
                {if $outputMode eq 'print' OR $outputMode eq 'pdf'}
                    <img src="{$uploadURL|cat:$chartId}.png" />                
                {else}
	            <div id="open_flash_chart_{$uniqueId}"></div>
                {/if}
            </td>
        </tr>
</table>

{if !$section}
        {include file="CRM/common/openFlashChart.tpl"}
{/if}

{literal}
<script type="text/javascript">
   cj( function( ) {
      buildChart( );
      
      var resourceURL = "{/literal}{$config->userFrameworkResourceURL}{literal}";
      var uploadURL   = "{/literal}{$uploadURL|cat:$chartId}{literal}.png";
      var uploadDir   = "{/literal}{$config->uploadDir}openFlashChart/{literal}"; 

      cj("input[id$='submit_print'],input[id$='submit_pdf']").bind('click', function(){ 
        var url = resourceURL +'packages/OpenFlashChart/php-ofc-library/ofc_upload_image.php';  // image creator php file path
           url += '?name={/literal}{$chartId}{literal}.png';                                    // append image name
           url += '&defaultPath=' + uploadDir;                                                  // append directory path
        
        //fetch object
        swfobject.getObjectById("open_flash_chart_{/literal}{$uniqueId}{literal}").post_image( url, true, false );
        });
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
