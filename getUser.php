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

function printName($uid)
{
	global $uapi, $data, $id, $platform;
	$su = $uapi->searchUser("byid", $uid, $platform);
	if ($su["error"] != true) {
		$data[$su['uid']] = array(
			"profile_id" => $su['uid'],
			"nickname" => $su['nick']
		);
	}
}

function printID($name)
{
	global $uapi, $data, $id, $platform;
	$su = $uapi->searchUser("bynick", $name, $platform);
	if ($su["error"] != true) {
		$data[$su['uid']] = array(
			"profile_id" => $su['uid'],
			"nickname" => $su['nick']
		);
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
			"players" => array()
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
	$progression = json_decode($uapi->getProgression($ids, $platform) , true) ["player_profiles"];
}

$ranks = json_decode('{
  "0": {
    "image": "https://i.imgur.com/jNJ1BBl.png",
    "name": "Unranked"
  },
  "1": {
    "image": "https://i.imgur.com/deTjm7V.png",
    "name": "Copper Ⅳ"
  },
  "2": {
    "image": "https://i.imgur.com/zx5KbBO.png",
    "name": "Copper Ⅲ"
  },
  "3": {
    "image": "https://i.imgur.com/RTCvQDV.png",
    "name": "Copper Ⅱ"
  },
  "4": {
    "image": "https://i.imgur.com/SN55IoP.png",
    "name": "Copper Ⅰ"
  },
  "5": {
    "image": "https://i.imgur.com/DmfZeRP.png",
    "name": "Bronze Ⅳ"
  },
  "6": {
    "image": "https://i.imgur.com/QOuIDW4.png",
    "name": "Bronze Ⅲ"
  },
  "7": {
    "image": "https://i.imgur.com/ry1KwLe.png",
    "name": "Bronze Ⅱ"
  },
  "8": {
    "image": "https://i.imgur.com/64eQSbG.png",
    "name": "Bronze Ⅰ"
  },
  "9": {
    "image": "https://i.imgur.com/fOmokW9.png",
    "name": "Silver Ⅳ"
  },
  "10": {
    "image": "https://i.imgur.com/e84XmHl.png",
    "name": "Silver Ⅲ"
  },
  "11": {
    "image": "https://i.imgur.com/f68iB99.png",
    "name": "Silver Ⅱ"
  },
  "12": {
    "image": "https://i.imgur.com/iQGr0yz.png",
    "name": "Silver Ⅰ"
  },
  "13": {
    "image": "https://i.imgur.com/DelhMBP.png",
    "name": "Gold Ⅳ"
  },
  "14": {
    "image": "https://i.imgur.com/5fYa6cM.png",
    "name": "Gold Ⅲ"
  },
  "15": {
    "image": "https://i.imgur.com/7c4dBTz.png",
    "name": "Gold Ⅱ"
  },
  "16": {
    "image": "https://i.imgur.com/cOFgDW5.png",
    "name": "Gold Ⅰ"
  },
  "17": {
    "image": "https://i.imgur.com/to1cRGC.png",
    "name": "Platinum Ⅲ"
  },
  "18": {
    "image": "https://i.imgur.com/vcIEaEz.png",
    "name": "Platinum Ⅱ"
  },
  "19": {
    "image": "https://i.imgur.com/HAU5DLj.png",
    "name": "Platinum Ⅰ"
  },
  "20": {
    "image": "https://i.imgur.com/Rt6c2om.png",
    "name": "Diamond Ⅰ"
  }
}', true);
$final = array();

foreach($idresponse["players"] as $value) {
	$id = $value["profile_id"];
	$final[$id] = array_merge(($loadProgression == "true" ? getValue($id, $progression) : array()) , $value, array(
		"nickname" => $data[$id]["nickname"],
		"platform" => $platform,
		"rankInfo" => $ranks[$value["rank"]]
	));
}

print json_encode(array(
	"players" => $final
));
?>
