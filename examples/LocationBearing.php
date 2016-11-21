<?php
	include_once("../libs/classes1.1.php");
	$loc = new Location("sacha@sachawheeler.com");
	$location = new SimpleXMLElement($loc->latest("xml"));
 ?>
<!DOCTYPE html>
<html>
	<head><title>SachaWheeler.com API - bearing example</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
<style>div#skyscraper {
    position: absolute;
    top: 50px;
    left: 790px;
}</style>
  </head>
  <body>
<div id="skyscraper">
            <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- API skyscraper -->
<ins class="adsbygoogle"
     style="display:inline-block;width:300px;height:600px"
     data-ad-client="ca-pub-0797355803969480"
     data-ad-slot="6336133752"></ins>
        <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
</script></div>
<? include_once "../analytics.php"; ?>
	Sacha is currently located at:
<ul>
	<li>Latitude: <?php echo $location->latitude ?></li>
	<li>Longitude: <?php echo $location->longitude ?></li>
	<li>Street Address: <?php echo $location->street_addr ?></li>
	<li>Heading: <?php echo $location->bearing ?></li>
	<li>Since: <?php echo $location->created ?></li>
</ul>
<?php 
	switch($location->bearing){
		case "N":	$heading = 0; break;
		case "NE":	$heading = 45; break;
		case "E":	$heading = 90; break;
		case "SE":	$heading = 135; break;
		case "S":	$heading = 180; break;
		case "SW":	$heading = 225; break;
		case "W":	$heading = 270; break;
		case "NW":	$heading = 315; break;
	}
?>
<p>This image from google streetview approximates what he is looking at.</p>
<img src="https://geo0.ggpht.com/cbk?output=thumbnail&thumb=2&h=275&yaw=<?php echo $heading; ?>&pitch=0&ll=<?php echo $location->latitude ?>,<?php echo $location->longitude ?>">
  </body>
</html>
