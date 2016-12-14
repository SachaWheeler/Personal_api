<?php
    include_once("../libs/classes1.1.php");
    $loc = new Location("sacha@sachawheeler.com");
    $location = new SimpleXMLElement($loc->latest("xml"));
    //print_r($location);
    $CENTER = "{$location->latitude}, {$location->longitude}";
    $_GET['d'] = "1day";
    $history = new SimpleXMLElement($loc->bydate("xml"));
    //echo "<!-- ", print_r($history, true), " -->\n";

 ?>
<!DOCTYPE html>
<html>
    <head><title>SachaWheeler.com API - map example</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <style>
      html, body, #map-canvas {
        height: 100%;
        margin: 0px;
        padding: 0px
      }
    </style>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBRC0kyEn2hJKbiHEu4K9zqpkcSlgT-wUo"></script>
    <script>
var map;
function initialize() {
  var mapOptions = {
    zoom: 14,
    center: new google.maps.LatLng(<?php echo $CENTER; ?>),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };
  map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
  var bounds = new google.maps.LatLngBounds ();
  var polylinepoints = new Array();

  var locations =
    [
<?php
    if(count($history)){
        foreach($history as $place){
            if($x++) echo ",\n";
                echo "\t\t",
                    "['", addslashes($place->street_addr), "', '",
                    $place->latitude, "', '", $place->longitude, "',
                    '", $place->updated, "']";
        }
    }else{
                echo "\t\t",
                    "['', '",
                    $location->latitude, "', '", $location->longitude, "', '']";

    }
    echo "\n";
?>
    ];

for(var i = 0; i < locations.length; i++)
    {
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(locations[i][1], locations[i][2]),
            map: map,
            title: locations[i][3]+" "+locations[i][0]
});
        bounds.extend(new google.maps.LatLng(locations[i][1], locations[i][2]));
        polylinepoints.push(new google.maps.LatLng(locations[i][1], locations[i][2]));
    }
    map.fitBounds (bounds);
    var path = new google.maps.Polyline({
        path: polylinepoints,
        geodesic: true,
        strokeColor: '#FF0000',
        strokeOpacity: 1.0,
        strokeWeight: 2
      });

  path.setMap(map);
}
google.maps.event.addDomListener(window, 'load', initialize);

    </script>
  </head>
  <body>
<? include_once "../analytics.php"; ?>
    <div id="map-canvas"></div>
  </body>
</html>
