<?php

	include_once("../libs/MySQL.php");
	include_once("../pass.php");
	global $passwords;

	$conn = new MySQL("swheeler_api", "swheeler_api", $passwords["mysql"]);
	//echo "POST:\n";
	//print_r($_POST);

	$data = $_POST['data'];
	$lines = explode("\n", $data);
	//print_r($lines);

	foreach($lines as $line)
	{
		$line = str_replace(" - Wikipedia, the free encyclopedia", "", $line);
		$d = explode("|", $line);
		if($d[1] == 'Wikipedia, the free encyclopedia') continue;
		//print_r($d);
		
		$search = array('id' => ''/*trim($d[0])*/, 'title' => trim($d[1]), 'timestamp' => date("Y-m-d H:i:s"));
		$conn->Insert($search, 'wikipedia');
//		print_r($conn);
	}
?>
