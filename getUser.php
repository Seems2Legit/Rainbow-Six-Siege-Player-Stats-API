<?php
include ("config.php");

if (empty($_GET)) {
	print "ERROR: Wrong usage";
	die();
}

if (!isset($_GET["appcode"])) {
	print "ERROR: Wrong appcode";
	die();
}

if ($_GET["appcode"] != $config["appcode"]) {
	print "ERROR: Wrong appcode";
	die();
}

if (!isset($_GET["id"]) && !isset($_GET["name"])) {
	print "ERROR: Wrong usage";
	die();
}

$loadProgression = $config["default-progression"];

if (isset($_GET["progression"]))
	$loadProgression = $_GET["progression"];

if ($loadProgression != "true" && $loadProgression != "false")
	$loadProgression = $config["default-progression"];

include ("UbiAPI.php");

$uapi = new UbiAPI($config["ubi-email"], $config["ubi-password"]);
$data = array();
$region = $config["default-region"];
$season = -1;

if (isset($_GET['season']))
	$season = $_GET['season'];

$platform = $config["default-platform"];

if (isset($_GET['platform']))
	$platform = $_GET['platform'];

if (isset($_GET['region']))
	$region = $_GET['region'];

$notFound = [];

function printName($pid) {
	global $uapi, $data, $id, $platform, $notFound;
	$su = $uapi->searchUser("byid", $pid, $platform);
	if ($su["error"] != true) {
		$data[$su['pid']] = array(
			"profile_id" => $su['pid'],
			"user_id" => $su['uid'],
			"nickname" => $su['nick']
		);
	} else {
		$notFound[$pid] = [
			"profile_id" => $pid,
			"error" => [
				"message" => "User not found!"
			]
		];
	}
}

function printID($name) {
	global $uapi, $data, $id, $platform, $notFound;
	$su = $uapi->searchUser("bynick", $name, $platform);
	if ($su["error"] != true) {
		$data[$su['pid']] = array(
			"profile_id" => $su['pid'],
			"user_id" => $su['uid'],
			"nickname" => $su['nick']
		);
	} else {
		$notFound[$name] = [
			"nickname" => $name,
			"error" => [
				"message" => "User not found!"
			]
		];
	}
}

if (isset($_GET["id"])) {
	$str = $_GET["id"];
	if (strpos($str, ',') !== false)
		$tocheck = explode(',', $str);
	else {
		$tocheck = array(
			$str
		);
	}

	foreach($tocheck as $value)
		printName($value);
}

if (isset($_GET["name"])) {
	$str = $_GET["name"];
	if (strpos($str, ',') !== false)
		$tocheck = explode(',', $str);
	else {
		$tocheck = array(
			$str
		);
	}

	foreach($tocheck as $value)
		printID($value);
}

if (empty($data)) {
	$error = $uapi->getErrorMessage();
	if ($error === false) {
		die(json_encode(array(
			"players" => $notFound
		)));
	} else {
		die(json_encode(array(
			"players" => array(),
			"error" => $error
		)));
	}
}

function getValue($user, $progression) {
	foreach($progression as $usera) {
		if ($usera["profile_id"] == $user)
			return $usera;
	}
	return array();
}

$ids = "";

foreach($data as $value)
	$ids = $ids . "," . $value["profile_id"];

$ids = substr($ids, 1);
$idresponse = json_decode($uapi->getRanking($ids, $season, $region, $platform), true);

if ($loadProgression == "true") {
	$progressionJson = json_decode($uapi->getProgression($ids, $platform), true);
	if (!array_key_exists("player_profiles", $progressionJson)) {
		die(json_encode(array(
			"players" => $notFound
		)));
	}
	$progression = $progressionJson["player_profiles"];
}

$ranks = json_decode('{
  "0": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=0&champ=0",
    "name": "Unranked"
  },
  "1": {
    "image": "",
    "name": "Copper V"
  },
  "2": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=1&champ=0",
    "name": "Copper IV"
  },
  "3": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=2&champ=0",
    "name": "Copper III"
  },
  "4": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=3&champ=0",
    "name": "Copper II"
  },
  "5": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=4&champ=0",
    "name": "Copper I"
  },
  "6": {
    "image": "",
    "name": "Bronze V"
  },
  "7": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=5&champ=0",
    "name": "Bronze IV"
  },
  "8": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=6&champ=0",
    "name": "Bronze III"
  },
  "9": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=7&champ=0",
    "name": "Bronze II"
  },
  "10": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=8&champ=0",
    "name": "Bronze I"
  },
  "11": {
    "image": "https://r6tab.com/images/pngranks/9.png?x=3",
    "name": "Silver V"
  },
  "12": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=9&champ=0",
    "name": "Silver IV"
  },
  "13": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=10&champ=0",
    "name": "Silver III"
  },
  "14": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=11&champ=0",
    "name": "Silver II"
  },
  "15": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=12&champ=0",
    "name": "Silver I"
  },
  "16": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=14&champ=0",
    "name": "Gold III"
  },
  "17": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=15&champ=0",
    "name": "Gold II"
  },
  "18": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=16&champ=0",
    "name": "Gold I"
  },
  "19": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=17&champ=0",
    "name": "Platinum III"
  },
  "20": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=18&champ=0",
    "name": "Platinum II"
  },
  "21": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=19&champ=0",
    "name": "Platinum I"
  },
  "22": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=20&champ=0",
    "name": "Diamond"
  },
  "23": {
    "image": "https://tabstats.com/images/r6/ranks/?rank=21&champ=0",
    "name": "Champion"
  }
}', true);
$final = array();

if (!isset($idresponse)) {
	die(json_encode(array(
		"players" => $notFound
	)));
}

if (!array_key_exists("players", $idresponse)) {
	die(json_encode(array(
		"players" => $notFound
	)));
}


function GetSeasonName($seasonId) {
	$season_name = "";
	switch($seasonId) {
		case 6:
			$season_name = "Health";
		break;
		case 7:
			$season_name = "Blood Orchid";
		break;
		case 8:
			$season_name = "White Noise";
		break;
		case 9:
			$season_name = "Chimera";
		break;
		case 10: 
			$season_name = "Para Bellum";
		break;
		case 11:
			$season_name = "Grim Sky";
		break;
		case 12:
			$season_name = "Wind Bastion";
		break;
		case 13:
			$season_name = "Burnt Horizon";
		break;
		case 14:
			$season_name = "Phantom Sight";
		break;
		case 15:
			$season_name = "Ember Rise";
		break;
		case 16: 
			$season_name = "Shifting Tides";
		break;
		case 17:
			$season_name = "Void Edge";	
		break;	
		case 18:
			$season_name = "Steel Wave";	
		break;					
	}
	
	return $season_name;
}


foreach($idresponse["players"] as $value) {
	$id = $value["profile_id"];
	$final[$id] = array_merge(($loadProgression == "true" ? getValue($id, $progression) : array()) , $value, array(
		"nickname" => $data[$id]["nickname"], 
		"user_id" => $data[$id]["user_id"],
		"platform" => $platform,
		"season_name" => GetSeasonName($value["season"]),
		"rankInfo" => $ranks[$value["rank"]],
        "maxRankInfo" => $ranks[$value["max_rank"]]
	));
}

if (array_key_exists("season", $data))
	echo $data["season"];

print json_encode(array(
	"players" => array_merge($final, $notFound)
));
?>
