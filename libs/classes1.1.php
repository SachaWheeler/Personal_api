<?php
include_once(dirname(__FILE__)."/MySQL.php");
define("DEFAULT_HISTORY_N", 30);
define("MAX_HISTORY_N", 100);
date_default_timezone_set("Europe/London");

class Environment extends BaseClass{
	protected $user;
	protected $latitude;
	protected $longitude;
	
	public function __construct($user){
		$location = new Location($user);
		$locarray = new SimpleXMLElement($location->latest("xml"));
		$this->longitude = $locarray->longitude;
		$this->latitude = $locarray->latitude;
	}
	
	public function Weather($format){
		$kelvin = 272.15;
		$url = "http://api.openweathermap.org/data/2.1/find/city?lat={$this->latitude}&lon={$this->longitude}&cnt=1";
        $temperature = json_decode($this->fetch($url));
        //print_r($url);
        return $this->format($format, array(
        	"temperature" => (string)($temperature->list[0]->main->temp-$kelvin),
			"pressure" => (string)$temperature->list[0]->main->pressure,
			"humidity" => (string)$temperature->list[0]->main->humidity,
			"temp_min" => (string)($temperature->list[0]->main->temp_min-$kelvin),
			"temp_max" => (string)($temperature->list[0]->main->temp_max-$kelvin),
			"wind" => (string)$temperature->list[0]->wind->speed,
			"wind-degrees" => (string)$temperature->list[0]->wind->deg
        ));
	}
	
	public function Daylight($format){
		$mday = date("d");
		$month = date("m");
		$url = "http://www.earthtools.org/services/sun.php?lat={$this->latitude}&lng={$this->longitude}&day={$mday}&month={$month}";
		$daylightHours = new SimpleXMLElement($this->fetch($url));
		//print_r($url); print_r($daylightHours);

		$hours = sprintf("%01.1f", ($this->timeToMins($daylightHours->evening->sunset) - $this->timeToMins($daylightHours->morning->sunrise))/60);
		return $this->format($format, array(
			"sunlight-hours" => $hours,
			"sunrise" => (string)$daylightHours->morning->sunrise,
			"sunset" => (string)$daylightHours->evening->sunset
			));
	}
	protected function timeToMins($time){
	        // 06:49:20
	        $arr = explode(":", $time);
	        return ($arr[0]*60)+$arr[1];
	}
}

class Wikipedia extends BaseClass{
	protected $user;
	protected $conn;
	
	public function __construct($user){
		$this->user = $user;
		$this->conn = new MySQL("swheeler_api", "swheeler_api", $this->getPassword("mysql"));
	}

	public function latest($format){
		// get latest
		$latest =(object)$this->conn->ExecuteSQL("Select * from wikipedia order by id desc limit 1");
		return $this->format($format, array(
			'id' => $latest->id,
			'title' => $latest->title,
			'timestamp' => $latest->timestamp
		));
	}

	public function history($format){
		global $_GET;
		if(isset($_GET['n']) && (intval($_GET['n']) > 0) && (intval($_GET['n']) <= MAX_HISTORY_N))
			$n = $_GET['n'];
		else
			$n = DEFAULT_HISTORY_N;
		// get history
		$history =$this->conn->ExecuteSQL("Select * from wikipedia order by id desc limit {$n}");
		return $this->format($format, $history);
	}
}

class GoogleSearch extends BaseClass{
	protected $user;
	protected $conn;
	
	public function __construct($user){
		$this->user = $user;
		$this->conn = new MySQL("swheeler_api", "swheeler_api", $this->getPassword("mysql"));
	}

	public function latest($format){
		// get latest
		$latest =(object)$this->conn->ExecuteSQL("Select * from google_search order by id desc limit 1");
		return $this->format($format, array(
			'id' => $latest->id,
			'title' => $latest->title,
			'timestamp' => $latest->timestamp
		));
	}

	public function history($format){
		global $_GET;
		if(isset($_GET['n']) && (intval($_GET['n']) > 0) && (intval($_GET['n']) <= MAX_HISTORY_N))
			$n = $_GET['n'];
		else
			$n = DEFAULT_HISTORY_N;
		// get history
		$history =$this->conn->ExecuteSQL("Select * from google_search order by id desc limit {$n}");
		return $this->format($format, $history);
	}
}

class Lastfm extends BaseClass{
	protected $user;
	
	protected $key;
	protected $domain;
	protected $events;
	protected $latestTrack;

	public function __construct($user){
		$this->user = $this->getUsername($user);
		$this->key = "03337138fe5149f05088428490c33f0b";
        $this->domain = "http://ws.audioscrobbler.com/2.0/?api_key=".$this->key;
        $this->latestTrack = $this->domain."&method=user.getrecenttracks&user={$this->user}&limit=1";
		$this->events = $this->domain."&method=user.getevents&user={$this->user}&limit=1";
	}
	
	public function nextevent($format){
		$events = new SimpleXMLElement($this->fetch($this->events));
		$return = array(
			"title" => (string)$events->events->event->title,
			"artist" => (string)$events->events->event->artists->headliner);
		if(count($events->events->event->artists->artist) > 1)
			$return["support"] = (string)$events->events->event->artists->artist[1];
		$return["venue"] = (string)$events->events->event->venue->name;
		$return["date"] = (string)$events->events->event->startDate;
		for($x=3;$x>=0;$x--){
			if(isset($events->events->event->image[$x])){
				$return['image'] = (string)$events->events->event->image[$x];
				break;
			}
		}
		return $this->format($format, $return);
	}
	public function latest($format){
		$latest = new SimpleXMLElement($this->fetch($this->latestTrack));
		$return = array(
			"artist" => (string)$latest->recenttracks->track->artist,
			"track" => (string)$latest->recenttracks->track->name,
			"album" => (string)$latest->recenttracks->track->album,
			"mbid" => (string)$latest->recenttracks->track->mbid
		);
		for($x=3;$x>=0;$x--){
			if(isset($latest->recenttracks->track->image[$x])){
				$return['image'] = (string)$latest->recenttracks->track->image[$x];
				break;
			}
		}
		return $this->format($format, $return);
	}
	
	protected function getUsername($user){
		return "game0ver";
	}
}

class Personal extends BaseClass{
	protected $dob;
	protected $weight;
	protected $height;
	protected $lifeexpectancy;
	
	public function __construct($user){
		$this->dob = $this->getUserDob($user);
		$this->weight = $this->getUserWeight($user);
		$this->height = $this->getUserHeight($user);
		$this->lifeexpectancy = $this->getLifeExpectancy($user);
	}
	
	public function age($format){
		global $_GET;
		switch($_GET['unit']){
			case "s": $constant=1;					$unit="second"; break;	// seconds
			case "m": $constant=60;					$unit="minute"; break;	// minutes
			case "h": $constant=60*60;				$unit="hour";	break;	// hours
			case "d": $constant=60*60*24;			$unit="day";	break;	// days
			default:  $constant=60*60*24*365.25;	$unit="year";	break;	// years
		}
		return $this->format($format, array("unit" => $unit,
											"age" => number_format((time() - $this->dob)/$constant, 2)));
	}
	
	public function height($format){
		global $_GET;
		switch($_GET['unit']){
			case "ft":	$constant=3.2808; $unit="feet";		break;	// feet
			case "in":	$constant=39.370; $unit="inch";		break;	// inch
			case "cm":	$constant=100; $unit="centimeter";	break;	// cm
			default:	$constant=1; $unit="metre"; 		break;	// metre
		}
		return $this->format($format, array("unit" => $unit,
											"height" => number_format(($this->height * $constant), 2)));
	}
	
	public function weight($format){
		global $_GET;
		switch($_GET['unit']){
			case "lb":	$constant=2.20462;	$unit="lb"; break;	// lb
			default:	$constant=1; 		$unit="kg"; break;	// kg
		}
		return $this->format($format, array("unit" => $unit,
											"weight" => number_format(($this->weight * $constant), 2)));
	}
	
	public function lifeExpectancy($format){
		global $_GET;
		switch($_GET['unit']){
			default:	$constant=1; $unit="year"; break;	// kg
		}
		return $this->format($format, array("unit" => $unit,
											"lifeexpectancy" => number_format(($this->lifeexpectancy * $constant), 2)));
	}
	
	protected function getLifeExpectancy($user){
		return strtotime("86.53 years");
	}
	
	protected function getUserDob($user){
		return strtotime("may 13th, 1969");
	}
	protected function getUserWeight($user){
		return 72;
	}
	protected function getUserHeight($user){
		return 1.8796;
	}
}

class Location extends BaseClass{
	protected $latitude;
	protected $longitude;
	protected $postaltown;
	protected $streetAddress;
	protected $googleMapsUrl;
	protected $user;
	protected $conn;
	
	public function __construct($user){
		$this->user = $user;
		$this->conn = new MySQL("swheeler_api", "swheeler_api", $this->getPassword("mysql"));
	}

	public function saveData(){
		// fetch location
		require_once '../libs/class.sosumi.php';
		$ssm = new Sosumi($this->user, $this->getPassword($this->user));
//		print_r($ssm);
		foreach($ssm->devices as $device){
			if($device->deviceClass != 'iPhone') // get the phone and then quit
				continue;
			$this->latitude = $device->latitude;
			$this->longitude = $device->longitude;
//			echo "got one!\n";
//			print_r($device);
			break;
		}
		// http://maps.googleapis.com/maps/api/geocode/json?latlng=40.714224,-73.961452&sensor=true_or_false
		$locLookup = "http://maps.googleapis.com/maps/api/geocode/xml?latlng={$this->latitude},{$this->longitude}&sensor=true";
		$xml = new SimpleXMLElement($this->fetch($locLookup));
//		print_r($locLookup);
//		print_r($xml);
		foreach($xml->result as $place){
//print_r($place);
			if($place->type == "postal_town"){
				$this->postaltown = (string)$place->formatted_address;
				//break;
			}elseif($place->type == "street_address"){
				$this->streetAddress = (string)$place->formatted_address;
				//break;
			}
		}

		$location['postaltown'] = $this->postaltown;
		$location['street_address'] = $this->streetAddress;
		// save user info IF user has moved
		// find their last location
		$latest =(object)$this->conn->ExecuteSQL("Select * from location order by id desc limit 1");
		$lastID = $latest->id;

		$locPrecision = 3;
		$locPrecisionConstant = 2;
/* */		echo "previous lat", "\n",
			$latest->latitude/$locPrecisionConstant, "\n",
			$this->latitude/$locPrecisionConstant, "\n",
			round($latest->latitude/$locPrecisionConstant, $locPrecision), "\n",
			round($this->latitude/$locPrecisionConstant, $locPrecision), "\n\n",
			
			"previous long", "\n",
			$latest->longitude/$locPrecisionConstant, "\n",
			$this->longitude/$locPrecisionConstant, "\n",
			round($latest->longitude/$locPrecisionConstant, $locPrecision), "\n",
			round($this->longitude/$locPrecisionConstant, $locPrecision), "\n";
/* */

		// have they moved? if so, insert
		if((round($latest->latitude/$locPrecisionConstant, $locPrecision) != round($this->latitude/$locPrecisionConstant, $locPrecision)) &&
			(round($latest->longitude/$locPrecisionConstant, $locPrecision) != round($this->longitude/$locPrecisionConstant, $locPrecision))){
			// they've moved
			$newLocation = array('latitude' => $this->latitude,
                        'longitude' => $this->longitude,
                        'street_addr' => $this->streetAddress,
                        'postal_town' => $this->postaltown,
                        'created' => date("Y-m-d H:i:s", strtotime("+8 hours")), // HACK - remove the 8 hours hack!
                        'updated' => date("Y-m-d H:i:s", strtotime("+8 hours"))); // HACK - remove the 8 hours hack!
    		$this->conn->Insert($newLocation, 'location');
		}else{
			// update the last timestamp
			$now = date("Y-m-d H:i:s", strtotime("+8 hours")); // HACK - remove the 8 hours hack!
			$this->conn->ExecuteSQL("UPDATE location set updated = '{$now}' where id = {$lastID}");
		}
	}
	
	public function latest($format){
		$recent = $this->conn->ExecuteSQL("Select id, latitude, longitude, 
									street_addr, postal_town, created, updated from location order by id desc limit 2");
		$latest = $recent[0];
		$previous = $recent[1];
		$bearing = $this->bearing($previous['latitude'], $previous['longitude'], $latest['latitude'], $latest['longitude']);
		foreach($latest as $k => $v){
			$return[$k] = $v;
			if($k=='street_addr'){
				$return['googleMapsUrl'] = "http://maps.google.com/?q=".$latest['latitude'].",".$latest['longitude'];
				$return['bearing'] = $bearing;
				// $return['googleStreetView'] = "https://geo0.ggpht.com/cbk?output=thumbnail&thumb=2&h=275&yaw=172&pitch=0&ll=".round($latest['latitude'], 6).",".round($latest['longitude'], 6);
			}
		}
		return $this->format($format, $return);
	}
	
	public function history($format){
		global $_GET;
		if(isset($_GET['n']) && (intval($_GET['n']) > 0) && (intval($_GET['n']) <= MAX_HISTORY_N))
			$n = intval($_GET['n']);
		else
			$n = DEFAULT_HISTORY_N;
		$history = $this->conn->ExecuteSQL("Select * from location order by id desc limit {$n}");
		return $this->format($format, $history);
	}
	
	public function historybyhour($format){
		global $_GET;
		if(isset($_GET['n']) && (intval($_GET['n']) > 0) && (intval($_GET['n']) <= MAX_HISTORY_N))
			$n = $_GET['n'];
		else
			$n = DEFAULT_HISTORY_N;
		$history = $this->conn->ExecuteSQL("Select id, day(updated) as 'day',
			hour(updated) as 'hour', avg(latitude) as latitude, avg(longitude) as longitude
			from location group by day(updated), hour(updated) order by id desc limit {$n}");
		return $this->format($format, $history);
	}
	
	public function bydate($format){
		global $_GET;
		if(isset($_GET['d']))
			$d = $_GET['d'];
		else
			$d = "1year";
		
		if(preg_match("/(\d+)(year|month|month|week|day|hour)/", $d, $matches)){
			$date = strtotime("-".$matches[1]." ".$matches[2]);
		}
		$history = $this->conn->ExecuteSQL("Select id, created as date, latitude, longitude
			from location
			where created > '{$date}'
			order by created ASC limit 1");
		return $this->format($format, $history);
	}
	
	public function distanceFrom($format){
		// calulate distance and bearing from $_GET['latitude'] & $_GET['longitude']
//		print_r($_GET);
		$lat1 = $this->latitude;
		$lon1 = $this->longitude;
		$lat2 = $_GET['latitude'];
		$lon2 = $_GET['longitude'];
		$return['distance_in_km'] = $this->distance($lat1, $lon1, $lat2, $lat2);
		$return['bearing'] = $this->bearing($lat1, $lon1, $lat2, $lat2);

		return $this->format($format, $return);
	}
	
	protected function distance($lat1, $lon1, $lat2, $lon2) {
	  $theta = $lon1 - $lon2;
	  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	  $dist = acos($dist);
	  $dist = rad2deg($dist);
	  $dist = $dist * 60 * 1.1515;
	$dist *= 1.609344;
	  return round($dist, 1);
	}

	protected function bearing($lat1, $lon1, $lat2, $lon2) {
	  if (round($lon1, 1) == round($lon2, 1)) {
	    if ($lat1 < $lat2) {
	      $bearing = 0;
	    } else {
	      $bearing = 180;
	    }
	  } else {
	    $dist = $this->distance($lat1, $lon1, $lat2, $lon2);
	    $arad = acos((sin(deg2rad($lat2)) - sin(deg2rad($lat1)) * cos(deg2rad($dist / 60))) / (sin(deg2rad($dist /
	60)) * cos(deg2rad($lat1))));
	    $bearing = $arad * 180 / pi();
	    if (sin(deg2rad($lon2 - $lon1)) < 0) {
	      $bearing = 360 - $bearing;
	    }
	  }

	  $dirs = array("N","E","S","W");
	  //$dirs = array("N", "NE", "E", "SE", "S", "SW", "W", "NW");

	  $rounded = round($bearing / 22.5) % 16;
	  if (($rounded % 4) == 0) {
	    $dir = $dirs[$rounded / 4];
	  } else {
	    $dir = $dirs[2 * floor(((floor($rounded / 4) + 1) % 4) / 2)];
	    $dir .= $dirs[1 + 2 * floor($rounded / 8)];
	    #if ($rounded % 2 == 1)
	    #  $dir = $dirs[round_to_int($rounded/4) % 4] . "-" . $dir;
	  }

	  return $dir;
	  //return $bearing;
	}
}

// base class here

class BaseClass{
	protected function format($format = 'json', $output){
		//if(!isset($output['timestamp']))
			//$output['timestamp'] = time();
		if($format == 'xml'){
			$xml= new SimpleXMLElement("<?xml version=\"1.0\"?><".get_called_class()."></".get_called_class().">");
 
			// function call to convert array to xml
			$this->array_to_xml($output,$xml);
 
			//saving generated xml file
			return $xml->asXML();
 
		}elseif($format == 'json'){
			return json_encode($output);
		}elseif($format == 'html'){
			return $this->array_to_html($output);
		}
	}

	protected function array_to_html($output){
		return "<pre>\n".htmlspecialchars(print_r($output, true))."\n</pre>\n";
	}

	protected function array_to_xml($output, &$xml) {
		foreach($output as $key => $value) {
			if(is_array($value)) {
				$key = is_numeric($key) ? "item$key" : $key;
				$subnode = $xml->addChild("$key");
				$this->array_to_xml($value, $subnode);
			}else {
				$key = is_numeric($key) ? "item$key" : $key;
				$xml->addChild("$key","$value");
			}
		}
	}

	protected function fetch($url){
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	protected function getPassword($user){
		include_once(dirname(__FILE__)."/../pass.php");
		global $passwords;
		return $passwords[$user];
	}
}

?>