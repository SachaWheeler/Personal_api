<?php

/*
 * personal API
 * all calls follow pattern:
 *  /ObjectName/Method/format
 *
 *   where ObjectName is one of:
 *   Location
 *  Twitter
 *  Youtube
 *  etc.
 *
 *  and fornat is one of:
 *     xml
 *    json
 *    html
 */

error_reporting(0);
$VERSION = "v1.1";

if (substr_count($_SERVER[‘HTTP_ACCEPT_ENCODING’], ‘gzip’))
  ob_start(“ob_gzhandler”);
else
  ob_start();

include_once("libs/classes1.1.php");
if($_GET['q'] !=""){
  $path = explode("/", $_GET['q']);
  $ObjectName = $path[0];
  $Method = $path[1];
  $Format = $path[2];
  $formats = array("json", "xml", "html");

  if(!method_exists($ObjectName, $Method)){
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1>";
      echo "The Method that you have requested does not exist.";
    exit(0);
  }elseif(!in_array($Format, $formats) || $Format == ''){
    echo "invalid format";
    exit(0);
  }
  if($Format =="xml")
    header("Content-type: text/xml; charset=utf-8");
  elseif($Format == "json")
    header('Content-Type: application/json');
  else
    header("Content-type: text/html");

  $obj = new $ObjectName("sacha@sachawheeler.com");
  echo $obj->$Method($Format);

}else{ ?>
  <html>
    <head>
      <title>SachaWheeler.com API</title>
      <link rel="stylesheet" type="text/css" href="css/bootstrap.css" />
      <link rel="stylesheet" type="text/css" href="css/default.css" />
      <style>
        /* Skyscraper ad*/
        div#skyscraper
        {
          position:absolute;
          top:50px;
          left:790px;
        }
      </style>
    </head>
  <body>
  <script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-658862-1']);
    _gaq.push(['_trackPageview']);

    (function() {
      var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
      ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
      var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
  </script>
    <div class="container">
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
    <h1>Sacha Wheeler&apos;s API</h1>
    <h2>Methods</h2>
    <ul>
      <li>/<?=$VERSION ?></li>
      <ul>
        <li>/Location</li>
        <ul>
          <li class="complete">/latest/[  <a href="/<?=$VERSION ?>/Location/latest/xml">xml</a>,
                          <a href="/<?=$VERSION ?>/Location/latest/json">json</a>,
                          <a href="/<?=$VERSION ?>/Location/latest/html">html</a>]</li>
          <li class="complete">/history/[ <a href="/<?=$VERSION ?>/Location/history/xml">xml</a>,
                          <a href="/<?=$VERSION ?>/Location/history/json">json</a>,
                          <a href="/<?=$VERSION ?>/Location/history/html">html</a>]?n=
                          <a href="/<?=$VERSION ?>/Location/history/xml?n=100">100</a> (default 30)</li>
          <li class="complete">/historybyhour/[ <a href="/<?=$VERSION ?>/Location/historybyhour/xml">xml</a>,
                          <a href="/<?=$VERSION ?>/Location/historybyhour/json">json</a>,
                          <a href="/<?=$VERSION ?>/Location/historybyhour/html">html</a>]?n=
                          <a href="/<?=$VERSION ?>/Location/historybyhour/xml?n=100">100</a> (default 30)</li>
          <li class="complete">/bydate/[ <a href="/<?=$VERSION ?>/Location/bydate/xml">xml</a>,
                          <a href="/<?=$VERSION ?>/Location/bydate/json">json</a>,
                          <a href="/<?=$VERSION ?>/Location/bydate/html">html</a>]?n=
                          <a href="/<?=$VERSION ?>/Location/bydate/xml?d=1year">1year</a> (default)</li>
          <li>Examples:</li>
          <ul>
            <li><a href="examples/LocationBearing.php">Location with streetview</a></li>
            <li><a href="examples/LocationHistory.php">Location history on map</a></li>
            <li><a href="examples/LocationHistoryByHour.php?n=100">Hourly aggregated Location history on map</a></li>
          </ul>
        </ul>
        <li>/GoogleSearch</li>
        <ul>
          <li class="complete">/latest/[  <a href="/<?=$VERSION ?>/GoogleSearch/latest/xml">xml</a>,
                          <a href="/<?=$VERSION ?>/GoogleSearch/latest/json">json</a>.
                          <a href="/<?=$VERSION ?>/GoogleSearch/latest/html">html</a>]</li>
          <li class="complete">/history/[  <a href="/<?=$VERSION ?>/GoogleSearch/history/xml">xml</a>,
                          <a href="/<?=$VERSION ?>/GoogleSearch/history/json">json</a>.
                          <a href="/<?=$VERSION ?>/GoogleSearch/history/html">html</a>]?n=
                          <a href="/<?=$VERSION ?>/GoogleSearch/history/xml?n=100">100</a> (default 30)</li>
        </ul>
        <li>/Wikipedia</li>
        <ul>
          <li class="complete">/latest/[  <a href="/<?=$VERSION ?>/Wikipedia/latest/xml">xml</a>,
                          <a href="/<?=$VERSION ?>/Wikipedia/latest/json">json</a>.
                          <a href="/<?=$VERSION ?>/Wikipedia/latest/html">html</a>]</li>
          <li class="complete">/history/[  <a href="/<?=$VERSION ?>/Wikipedia/history/xml">xml</a>,
                          <a href="/<?=$VERSION ?>/Wikipedia/history/json">json</a>.
                          <a href="/<?=$VERSION ?>/Wikipedia/history/html">html</a>]?n=
                          <a href="/<?=$VERSION ?>/Wikipedia/history/xml?n=100">100</a> (default 30)</li>
        </ul>
        <li>/Lastfm</li>
        <ul>
          <li class="complete">/latest/[    <a href="/<?=$VERSION ?>/Lastfm/latest/xml">xml</a>,
                            <a href="/<?=$VERSION ?>/Lastfm/latest/json">json</a>.
                            <a href="/<?=$VERSION ?>/Lastfm/latest/html">html</a>]</li>
          <li class="complete">/nextevent/[  <a href="/<?=$VERSION ?>/Lastfm/nextevent/xml">xml</a>,
                            <a href="/<?=$VERSION ?>/Lastfm/nextevent/json">json</a>.
                            <a href="/<?=$VERSION ?>/Lastfm/nextevent/html">html</a>]</li>
          <li class="under-dev">/latestLoved/[xml, json, html]</li>
        </ul>
        <li>/Personal</li>
        <ul>
          <li class="complete">/age/[  <a href="/<?=$VERSION ?>/Personal/age/xml">xml</a>,
                        <a href="/<?=$VERSION ?>/Personal/age/json">json</a>,
                        <a href="/<?=$VERSION ?>/Personal/age/html">html</a>]?unit=[
                        <a href="/<?=$VERSION ?>/Personal/age/xml?unit=s">s</a> (second),
                        <a href="/<?=$VERSION ?>/Personal/age/xml?unit=m">m</a> (minute),
                        <a href="/<?=$VERSION ?>/Personal/age/xml?unit=h">h</a> (hour),
                        <a href="/<?=$VERSION ?>/Personal/age/xml?unit=d">d</a> (day),
                        <a href="/<?=$VERSION ?>/Personal/age/xml?unit=y">y</a> (year)(default)]</li>
          <li class="complete">/weight/[  <a href="/<?=$VERSION ?>/Personal/weight/xml">xml</a>,
                          <a href="/<?=$VERSION ?>/Personal/weight/json">json</a>,
                          <a href="/<?=$VERSION ?>/Personal/weight/html">html</a>]?unit=[
                          <a href="/<?=$VERSION ?>/Personal/weight/xml?unit=lb">lb</a> (pound),
                          <a href="/<?=$VERSION ?>/Personal/weight/xml?unit=kg">kg</a> (kilogram)(default)]</li>
          <li class="complete">/height/[  <a href="/<?=$VERSION ?>/Personal/height/xml">xml</a>,
                          <a href="/<?=$VERSION ?>/Personal/height/json">json</a>,
                          <a href="/<?=$VERSION ?>/Personal/height/html">html</a>]?unit=[
                          <a href="/<?=$VERSION ?>/Personal/height/xml?unit=ft">ft</a> (feet),
                          <a href="/<?=$VERSION ?>/Personal/height/xml?unit=in">in</a> (inch),
                          <a href="/<?=$VERSION ?>/Personal/height/xml?unit=cm">cm</a> (centimetre)]
                          <a href="/<?=$VERSION ?>/Personal/height/xml?unit=m">m</a> (metre)(default)]</li>
        </ul>
        <li>/Environment</li>
        <ul>
          <li class="complete">/Weather/[  <a href="/<?=$VERSION ?>/Environment/Weather/xml">xml</a>,
                            <a href="/<?=$VERSION ?>/Environment/Weather/json">json</a>,
                            <a href="/<?=$VERSION ?>/Environment/Weather/html">html</a>]</li>
          <li class="complete">/Daylight/[  <a href="/<?=$VERSION ?>/Environment/Daylight/xml">xml</a>,
                              <a href="/<?=$VERSION ?>/Environment/Daylight/json">json</a>,
                              <a href="/<?=$VERSION ?>/Environment/Daylight/html">html</a>]</li>
        </ul>
        <li>/Instagram</li>
        <ul>
          <li class="under-dev">/latestPhoto/[xml, json, html]</li>
          <li class="under-dev">/latestLike/[xml, json, html]</li>
        </ul>
        <li>/Twitter</li>
        <ul>
          <li class="under-dev">/latest/[xml, json, html]</li>
          <li class="under-dev">/followers/[xml, json, html]</li>
          <li class="under-dev">/following/[xml, json, html]</li>
        </ul>
        <li>/Youtube</li>
        <ul>
          <li class="under-dev">/latestUpload/[xml, json, html]</li>
          <li class="under-dev">/allUploads/[xml, json, html]</li>
          <li class="under-dev">/latestView/[xml, json, html]</li>
          <li class="under-dev">/allViews/[xml, json, html]</li>
        </ul>
      </ul>
    </ul>
    <p>More coming soon...</p>

    <h2>Sources</h2>
    <p>I grab this information from a variety of sources, including:</p>
    <ul>
      <li>Apple&apos;s Find-my-phone</li>
      <li>Twitter.com API</li>
      <li>Last.fm API</li>
      <li>Instagram API</li>
      <li>Facebook API</li>
      <li>youtube.com API</li>
      <li>Foursquare API</li>
      <li>Fitbit API</li>
      <li>openweathermap.org</li>
      <li>www.earthtools.org</li>
      <li>google.com</li>
      <li>wikipedia.org</li>
      <li>Chrome History</li>
      <li>etc.</li>
    </ul>

    <p><a href="http://sachawheeler.com">Back to SachaWheeler.com</a></p>
    </div>
<img src="https://www.linkedin.com/profile/view?authToken=zRgB&authType=name&id=11170810" />
  </body>

  </html>


<?php } ?>
