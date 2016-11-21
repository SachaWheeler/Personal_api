<?php
	include_once("./data.php");
	include_once("../OAuth.php");
	include_once("./fitbitphp.php");
	$fitbit = new FitBitPHP(FITBIT_KEY, FITBIT_SECRET);

	$fitbit->setUser('XXXXXX');
	$xml = $fitbit->getProfile();
	print_r($xml);
?>
