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

if (isset($_GET["progression"])) {
	$loadProgression = $_GET["progression"];
}

if ($loadProgression != "true" && $loadProgression != "false") {
	$loadProgression = $config["default-progression"];
}

include ("UbiAPI.php");

$uapi = new UbiAPI($config["ubi-email"], $config["ubi-password"]);
$data = array();
$region = $config["default-region"];
$season = -1;

if (isset($_GET['season'])) {
	$season = $_GET['season'];
}

$platform = $config["default-platform"];

if (isset($_GET['platform'])) {
	$platform = $_GET['platform'];
}

if (isset($_GET['region'])) {
	$region = $_GET['region'];
}

$notFound = [];

function printName($pid)
{
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

function printID($name)
{
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
	if (strpos($str, ',') !== false) {
		$tocheck = explode(',', $str);
	}
	else {
		$tocheck = array(
			$str
		);
	}

	foreach($tocheck as $value) {
		printName($value);
	}
}

if (isset($_GET["name"])) {
	$str = $_GET["name"];
	if (strpos($str, ',') !== false) {
		$tocheck = explode(',', $str);
	}
	else {
		$tocheck = array(
			$str
		);
	}

	foreach($tocheck as $value) {
		printID($value);
	}
}

if (empty($data)) {
	$error = $uapi->getErrorMessage();
	if ($error === false) {
		die(json_encode(array(
			"players" => $notFound
		)));
	}
	else {
		die(json_encode(array(
			"players" => array() ,
			"error" => $error
		)));
	}
}

function getValue($user, $progression)
{
	foreach($progression as $usera) {
		if ($usera["profile_id"] == $user) {
			return $usera;
		}
	}

	return array();
}

$ids = "";

foreach($data as $value) {
	$ids = $ids . "," . $value["profile_id"];
}

$ids = substr($ids, 1);
$idresponse = json_decode($uapi->getRanking($ids, $season, $region, $platform) , true);

if ($loadProgression == "true") {
	$progressionJson = json_decode($uapi->getProgression($ids, $platform) , true);
	if (!array_key_exists("player_profiles", $progressionJson)){
		die(json_encode(array(
			"players" => $notFound
		)));
	}
	$progression = $progressionJson["player_profiles"];
}

$ranks = json_decode('{
  "0": {
    "image": "https://r6tab.com/images/pngranks/0.png?x=3",
    "name": "Unranked"
  },
  "1": {
    "image": "",
    "name": "Copper V"
  },
  "2": {
    "image": "https://r6tab.com/images/pngranks/1.png?x=3",
    "name": "Copper IV"
  },
  "3": {
    "image": "https://r6tab.com/images/pngranks/2.png?x=3",
    "name": "Copper III"
  },
  "4": {
    "image": "https://r6tab.com/images/pngranks/3.png?x=3",
    "name": "Copper II"
  },
  "5": {
    "image": "https://r6tab.com/images/pngranks/4.png?x=3",
    "name": "Copper I"
  },
  "6": {
    "image": "",
    "name": "Bronze V"
  },
  "7": {
    "image": "https://r6tab.com/images/pngranks/5.png?x=3",
    "name": "Bronze IV"
  },
  "8": {
    "image": "https://r6tab.com/images/pngranks/6.png?x=3",
    "name": "Bronze III"
  },
  "9": {
    "image": "https://r6tab.com/images/pngranks/7.png?x=3",
    "name": "Bronze II"
  },
  "10": {
    "image": "https://r6tab.com/images/pngranks/8.png?x=3",
    "name": "Bronze I"
  },
  "11": {
    "image": "https://r6tab.com/images/pngranks/9.png?x=3",
    "name": "Silver V"
  },
  "12": {
    "image": "https://r6tab.com/images/pngranks/10.png?x=3",
    "name": "Silver IV"
  },
  "13": {
    "image": "https://r6tab.com/images/pngranks/11.png?x=3",
    "name": "Silver III"
  },
  "14": {
    "image": "https://r6tab.com/images/pngranks/12.png?x=3",
    "name": "Silver II"
  },
  "15": {
    "image": "https://r6tab.com/images/pngranks/13.png?x=3",
    "name": "Silver I"
  },
  "16": {
    "image": "https://r6tab.com/images/pngranks/14.png?x=3",
    "name": "Gold III"
  },
  "17": {
    "image": "https://r6tab.com/images/pngranks/15.png?x=3",
    "name": "Gold II"
  },
  "18": {
    "image": "https://r6tab.com/images/pngranks/16.png?x=3",
    "name": "Gold I"
  },
  "19": {
    "image": "https://r6tab.com/images/pngranks/17.png?x=3",
    "name": "Platinum III"
  },
  "20": {
    "image": "https://r6tab.com/images/pngranks/18.png?x=3",
    "name": "Platinum II"
  },
  "21": {
    "image": "https://r6tab.com/images/pngranks/19.png?x=3",
    "name": "Platinum I"
  },
  "22": {
    "image": "https://r6tab.com/images/pngranks/20.png?x=3",
    "name": "Diamond"
  },
  "23": {
    "image": "https://r6tab.com/images/pngranks/21.png?x=3",
    "name": "Champion"
  }
}', true);
$final = array();

if (!isset($idresponse)) {
	die(json_encode(array(
		"players" => $notFound
	)));
}

if (!array_key_exists("players", $idresponse)){
	die(json_encode(array(
		"players" => $notFound
	)));
}

foreach($idresponse["players"] as $value) {
	$id = $value["profile_id"];
	$final[$id] = array_merge(($loadProgression == "true" ? getValue($id, $progression) : array()) , $value, array(
		"nickname" => $data[$id]["nickname"], 
		"user_id"=>$data[$id]["user_id"],
		"platform" => $platform,
		"rankInfo" => $ranks[$value["rank"]]
	));
}

print json_encode(array(
	"players" => array_merge($final, $notFound)
));
?>
