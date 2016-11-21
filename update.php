<?php 
	include_once("libs/classes1.1.php");
	$loc = new Location("sacha@sachawheeler.com");
	/*
	
SosumiDevice Object
(
    [isLocating] => 1
    [locationTimestamp] => 2016-09-22 02:27:11
    [locationType] => Wifi
    [horizontalAccuracy] => 65
    [locationFinished] => 1
    [longitude] => -0.136959446805
    [latitude] => 51.4963913004
    [deviceModel] => iphone6-3b3b3c-b4b5b9
    [deviceStatus] => 200
    [id] => oKA3wmsqYZdzoeyLBP3TvEYqyZ2Cb1QI8yhGF8F2NhWIqXWpOfvcRuHYVNSUzmWV
    [name] => Sacha's 6
    [deviceClass] => iPhone
    [chargingStatus] => NotCharging
    [batteryLevel] => 0.87
)
	
	*/
	$loc->saveData();
?>
