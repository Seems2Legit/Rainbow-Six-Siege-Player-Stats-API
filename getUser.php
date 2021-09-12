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
	if (is_null($progressionJson) || !array_key_exists("player_profiles", $progressionJson)) {
		die(json_encode(array(
			"players" => $notFound
		)));
	}
	$progression = $progressionJson["player_profiles"];
}

function getRankImage($rank, $png) {
    return 'https://api.statsdb.net/r6/assets/ranks/' . ($png ? 'png/' : '') . $rank;
}

function getRankName($rank) {
    switch($rank) {
        case 0: return "Unranked";
        case 1: return "Copper V";
        case 2: return "Copper IV";
        case 3: return "Copper III";
        case 4: return "Copper II";
        case 5: return "Copper I";
        case 6: return "Bronze V";
        case 7: return "Bronze IV";
        case 8: return "Bronze III";
        case 9: return "Bronze II";
        case 10: return "Bronze I";
        case 11: return "Silver V";
        case 12: return "Silver IV";
        case 13: return "Silver III";
        case 14: return "Silver II";
        case 15: return "Silver I";
        case 16: return "Gold III";
        case 17: return "Gold II";
        case 18: return "Gold I";
        case 19: return "Platinum III";
        case 20: return "Platinum II";
        case 21: return "Platinum I";
        case 22: return "Diamond III";
        case 23: return "Diamond II";
        case 24: return "Diamond I";
        case 25: return "Champion";
        default: return null;
    }
}

function getRankArray($rank, $png) {
    return [
        'image' => getRankImage($rank, $png),
        'name' => getRankName($rank)
    ];
}

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

function getSeasonName($seasonId) {
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
		case 19:
			$season_name = "Shadow Legacy";
		break;
		case 20:
			$season_name = "Neon Dawn";
		break;	
		case 21:
			$season_name = "Crimson Heist";
		break;
		case 22:
			$season_name = "North Star";
		break;
		case 23:
			$season_name = "Crystal Guard";
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
		"season_name" => getSeasonName($value["season"]),
		"rankInfo" => getRankArray($value["rank"], $config['rank-images-png']),
        "maxRankInfo" => getRankArray($value["max_rank"], $config['rank-images-png'])
	));
}

header("Content-Type: application/json");

if (array_key_exists("season", $data))
	echo $data["season"];

print json_encode(array(
	"players" => array_merge($final, $notFound)
));
