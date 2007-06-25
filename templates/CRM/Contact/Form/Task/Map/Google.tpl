  <script src="http://maps.google.com/maps?file=api&v=2&key={$mapKey}" type="text/javascript"></script>
  {literal}
  <script type="text/javascript">
    function initMap() {

      //<![CDATA[
      var map     = new GMap2(document.getElementById("map"));
      var span    = new GSize({/literal}{$span.lng},{$span.lat}{literal});
      var center  = new GLatLng({/literal}{$center.lat},{$center.lng}{literal});

      map.addControl(new GLargeMapControl());
      map.addControl(new GMapTypeControl());
      map.setCenter(new GLatLng( 0, 0 ), 0 );
      var bounds = new GLatLngBounds( );

      // Creates a marker whose info window displays the given number
      function createMarker(point, data) {
        var marker = new GMarker(point);

        GEvent.addListener(marker, "click", function() {
          marker.openInfoWindowHtml(data);
        });

        return marker;
      }
      
      {/literal}
      {foreach from=$locations item=location}
      {literal}

	 var data = "{/literal}<a href={$location.url}>{$location.displayName}</a><br />{$location.location_type}<br />{$location.address}<br /><br />Get Directions TO:&nbsp;<input type=text id=to size=20>&nbsp;<a href=\"javascript:popUp();\">&raquo; Go</a>{literal}";
	 var address = "{/literal}{$location.address}{literal}";
{/literal}
{if $location.lat}
       	var point = new GLatLng({$location.lat},{$location.lng});
       	var marker = createMarker(point, data);
        map.addOverlay(marker);
        bounds.extend(point);
{/if}
      {/foreach}
      map.setZoom(map.getBoundsZoomLevel(bounds));
      map.setCenter(bounds.getCenter());
      {literal}

     //]]>  
   }

    function popUp() {
       {/literal}
       var from = "{$location.displayAddress}";
       {literal}
       var to   = document.getElementById('to').value;
       var URL  = "http://maps.google.com/maps?saddr=" + from + "&daddr=" + to;
       day = new Date();
       id = day.getTime();
       eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=780,height=640,left = 202,top = 100');");
    }

    if (window.addEventListener) {
        window.addEventListener("load", initMap, false);
    } else if (window.attachEvent) {
        document.attachEvent("onreadystatechange", initMap);
    }

  </script>
{/literal}

  <div id="map" style="width: 600px; height: 400px"></div>
