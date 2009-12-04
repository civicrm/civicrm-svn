<script type="text/javascript" src="{$config->resourceBase}packages/OpenFlashChart/js/json/json2.js"></script>
<script type="text/javascript" src="{$config->resourceBase}packages/OpenFlashChart/js/swfobject.js"></script>
{literal}
<script type="text/javascript">
function createSWFObject( chartID, divName, xSize, ySize ) 
{
   var flashFilePath = {/literal}"{$config->resourceBase}packages/OpenFlashChart/open-flash-chart.swf"{literal};

   //create object.  	   
   swfobject.embedSWF( flashFilePath,
    		       divName,
		       xSize, ySize, "9.0.0",
		       "expressInstall.swf",
		       {"get-data":"loadData", "id":chartID}
		       );
}
</script>
{/literal}