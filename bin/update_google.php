<?php

	include_once("../libs/MySQL.php");
	include_once("../pass.php");
	global $passwords;

	$conn = new MySQL("swheeler_api", "swheeler_api", $passwords["mysql"]);
	//echo "POST:\n";
//	print_r($_POST);

	$data = $_POST['data'];
	$lines = explode("\n", $data);
	//print_r($lines);

	foreach($lines as $line)
	{
		$line = str_replace(" - Google Search", "", $line);
		$d = explode("|", $line);
		//print_r($d);
		
		$search = array('id' => ''/*trim($d[0])*/, 'title' => trim($d[1]), 'timestamp' => date("Y-m-d H:i:s"));
//		print_r($search);
		$conn->Insert($search, 'google_search');
//		print_r($conn);
	}

/*

POST:
Array
(
    [data] => 45865|wget post data - Google Search
45864|mac os x shell write to fileddddd - Google Search
45863|mac os x shell write to file - Google Search
45861|wget do not save to disc - Google Search
45860|wget do not download - Google Search
)

Array
(
    [0] => 45865
    [1] => wget post data - Google Search
)


$newLocation = array('latitude' => $this->latitude,
                        'longitude' => $this->longitude,
                        'street_addr' => $this->streetAddress,
                        'postal_town' => $this->postaltown,
                        'created' => date("Y-m-d H:i:s", strtotime("+8 hours")), // HACK - remove the 8 hours hack!
                        'updated' => date("Y-m-d H:i:s", strtotime("+8 hours"))); // HACK - remove the 8 hours hack!
                $this->conn->Insert($newLocation, 'location');


*/
?>
