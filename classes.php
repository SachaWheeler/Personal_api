<?php
include_once("../globalFunctions.php");

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
		$month = date("M");
		$url = "http://www.earthtools.org/services/sun.php?lat={$this->latitude}&lng={$this->longitude}&day={$mday}&month={$month}";
		$daylightHours = new SimpleXMLElement($this->fetch($url));

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
	protected $search;
	protected $url;
	
	public function __construct($user){
		$this->url = "http://dl.dropboxusercontent.com/u/12377/chrome_latest.txt";
		$this->user = $user;
	}
	
	public function latest($format){
		$projectName = "Api";
		$C = new Cache("Wikipedia.cache");
		if(!$C->exists() || !$C->isNewer("-5 minute")){			
			$this->search = $this->fetch($this->url);
			$results = explode("|", $this->search);
			$C->write($results);
		}else{
			$results = $C->read();
		}
		return $this->format($format, array(
			"url" => $results[0],
			"title" => preg_replace("/ - Wikipedia.*$/", "", $results[1])
		));
	}
}

class GoogleSearch extends BaseClass{
	protected $user;
	protected $search;
	protected $url;
	
	public function __construct($user){
		$this->url = "http://dl.dropboxusercontent.com/u/12377/google_latest.txt";
		$this->user = $user;
	}
	
	public function latest($format){
		$projectName = "Api";
		$C = new Cache("GoogleSearch.cache");
		if(!$C->exists() || !$C->isNewer("-1 minute")){			
			$this->search = $this->fetch($this->url);
			$results = explode("|", $this->search);
			$C->write($results);
		}else{
			$results = $C->read();
		}
		return $this->format($format, array(
			"url" => preg_replace("/&/", "&amp;", $results[0]),
			"title" => preg_replace("/ - Google .*$/", "", $results[1])
		));
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
			case "lb":	$constant=2.20462;	$unit="lb";	break;	// lb
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
		return 75;
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
	
	public function __construct($user){
		require '../libs/class.sosumi.php';

		$projectName = "Api";
		$locCache = new Cache("Location.cache");
		if(!$locCache->exists() || !$locCache->isNewer("-10 minutes")){
			// fetch location
			$pass = $this->getPassword($user);
			$ssm = new Sosumi($user, $pass);

			$location = $ssm->locate(2);
			$cache = $locCache->read();
			if($location['latitude'] != $cache['latitude']){
				$this->latitude = $location['latitude'];
				$this->longitude = $location['longitude'];				
				$this->googleMapsUrl = "http://maps.google.com/?q=".$this->latitude.",".$this->longitude;
				// http://maps.googleapis.com/maps/api/geocode/json?latlng=40.714224,-73.961452&sensor=true_or_false
				$locLookup = "http://maps.googleapis.com/maps/api/geocode/xml?latlng={$location['latitude']},{$location['longitude']}&sensor=true";
				$xml = new SimpleXMLElement($this->fetch($locLookup));

				foreach($xml->result as $place){
					if($place->type == "postal_town")
						$this->postaltown = (string)$place->formatted_address;
					elseif($place->type == "street_address")
						$this->streetAddress = (string)$place->formatted_address;
				}

				$location['postaltown'] = $this->postaltown;
				$location['street_address'] = $this->streetAddress;
				$locCache->write($location);
			}
		}else{
			$location = $locCache->read();
			$this->latitude = $location['latitude'];
			$this->longitude = $location['longitude'];
			$this->googleMapsUrl = "http://maps.google.com/?q=".$this->latitude.",".$this->longitude;
			$this->postaltown = $location['postaltown'];
			$this->streetAddress = $location['street_address'];
		}
		// load user info
	}
	
	public function latest($format){
		return $this->format($format, array("longitude" => $this->longitude,
											"latitude" => $this->latitude,
											"googleMapsUrl" => $this->googleMapsUrl,
											"street_address" => $this->streetAddress,
											"postaltown" => $this->postaltown
							));
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

	  $rounded = round($bearing / 22.5) % 16;
	  if (($rounded % 4) == 0) {
	    $dir = $dirs[$rounded / 4];
	  } else {
	    $dir = $dirs[2 * floor(((floor($rounded / 4) + 1) % 4) / 2)];
	    $dir .= $dirs[1 + 2 * floor($rounded / 8)];
	    #if ($rounded % 2 == 1)
	    #  $dir = $dirs[round_to_int($rounded/4) % 4] . "-" . $dir;
	  }

	  //return $dir;
	  return $bearing;
	}
	
	protected function getPassword($user){
		include_once("./pass.php");
		global $passwords;
		return $passwords[$user];
	}
}

// base class here

class BaseClass{
	protected function format($format, $output){
		$output['timestamp'] = time();
		if($format == 'xml')
			return '<?xml version="1.0"?>'.$this->arrayToXml(get_called_class(), $output);
		else
			return json_encode($output);
	}
	
	protected function arrayToXml($thisNodeName,$input){
        if(is_numeric($thisNodeName))
            throw new Exception("cannot parse into xml. remainder :".print_r($input,true));
        if(!(is_array($input) || is_object($input))){
            return "<$thisNodeName>$input</$thisNodeName>";
        }
        else{
            $newNode="<$thisNodeName>";
            foreach($input as $key=>$value){
                if(is_numeric($key))
                    $key=substr($thisNodeName,0,strlen($thisNodeName)-1);
                $newNode.= $this->arrayToXml($key,$value);
            }
            $newNode.="</$thisNodeName>";
            return $newNode;
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
}

?>
