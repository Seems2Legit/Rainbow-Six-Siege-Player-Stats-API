<?php
include("config.php");

if(empty($_GET)) {
	print "ERROR: Wrong usage";
	die();
}

if(!isset($_GET["appcode"])) {
	print "ERROR: Wrong appcode";
	die();
}

if($_GET["appcode"] != $config["appcode"]) {
	print "ERROR: Wrong appcode";
	die();
}

if(!isset($_GET["id"]) && !isset($_GET["name"])) {
	print "ERROR: Wrong usage";
	die();
}

$loadProgression = $config["default-progression"];
if(isset($_GET["progression"])) {
	$loadProgression = $_GET["progression"];
}
if($loadProgression != "true" && $loadProgression != "false") {
	$loadProgression = $config["default-progression"];
}

include("UbiAPI.php");

$uapi = new UbiAPI($config["ubi-email"],$config["ubi-password"]);

$data = array();
$region = $config["default-region"];
$season = -1;

if(isset($_GET['season'])) {
	$season = $_GET['season'];
}

$platform = $config["default-platform"];
if(isset($_GET['platform'])) {
	$platform = $_GET['platform'];
}

if(isset($_GET['region'])) {
	$region = $_GET['region'];
}

function printName($uid) {
	global $uapi, $data, $id, $platform;
	$su = $uapi->searchUser("byid",$uid, $platform);
	if($su["error"] != true){
		$data[$su['uid']] = array("profile_id" =>$su['uid'], "nickname" => $su['nick']);
	}
}

function printID($name) {
	global $uapi, $data, $id, $platform;
	$su = $uapi->searchUser("bynick",$name, $platform);
	if($su["error"] != true){
		$data[$su['uid']] = array("profile_id"=> $su['uid'] , "nickname" => $su['nick']);
	}
}

if(isset($_GET["id"])) {
	$str = $_GET["id"];
	if (strpos($str, ',') !== false) {
		$tocheck = explode(',', $str);
	}else{
		$tocheck = array($str);
	}

	foreach ($tocheck as $value) {
		printName($value);
	}
}
if(isset($_GET["name"])) {
	$str = $_GET["name"];
	if (strpos($str, ',') !== false) {
		$tocheck = explode(',', $str);
	}else{
		$tocheck = array($str);
	}

	foreach ($tocheck as $value) {
		printID($value);
	}
}

if(empty($data)) {
	$error = $uapi->getErrorMessage();
	if($error === false) {
		die(json_encode(array("players" => array())));
	}else{
		die(json_encode(array("players" => array(), "error" => $error)));
	}
}

function getValue($user, $progression) {
	foreach($progression as $usera) {
		if($usera["profile_id"] == $user) {
			return $usera;
		}
	}
	return array();
}

$ids = "";
foreach ($data as $value) {
	$ids = $ids . "," . $value["profile_id"];
}
$ids = substr($ids, 1);

$idresponse = json_decode($uapi->getRanking($ids, $season, $region, $platform), true);
if($loadProgression == "true") {
	$progression = json_decode($uapi->getProgression($ids, $platform), true)["player_profiles"];
}
$ranks = json_decode('{
  "0": {
    "image": "https://i.imgur.com/sB11BIz.png",
    "name": "Unranked"
  },
  "13": {
    "image": "https://i.imgur.com/6Qg6aaH.jpg",
    "name": "Gold 4"
  },
  "15": {
    "image": "https://i.imgur.com/ELbGMc7.jpg",
    "name": "Gold 2"
  },
  "14": {
    "image": "https://i.imgur.com/B0s1o1h.jpg",
    "name": "Gold 3"
  },
  "16": {
    "image": "https://i.imgur.com/ffDmiPk.jpg",
    "name": "Gold 1"
  },
  "7": {
    "image": "https://i.imgur.com/9AORiNm.jpg",
    "name": "Bronze 2"
  },
  "9": {
    "image": "https://i.imgur.com/D36ZfuR.jpg",
    "name": "Silver 4"
  },
  "6": {
    "image": "https://i.imgur.com/QD5LYD7.jpg",
    "name": "Bronze 3"
  },
  "10": {
    "image": "https://i.imgur.com/m8GToyF.jpg",
    "name": "Silver 3"
  },
  "5": {
    "image": "https://i.imgur.com/42AC7RD.jpg",
    "name": "Bronze 4"
  },
  "20": {
    "image": "https://i.imgur.com/nODE0QI.jpg",
    "name": "Diamond"
  },
  "8": {
    "image": "https://i.imgur.com/hmPhPBj.jpg",
    "name": "Bronze 1"
  },
  "3": {
    "image": "https://i.imgur.com/eI11lah.jpg",
    "name": "Copper 2"
  },
  "4": {
    "image": "https://i.imgur.com/0J0jSWB.jpg",
    "name": "Copper 1"
  },
  "19": {
    "image": "https://i.imgur.com/xx03Pc5.jpg",
    "name": "Platinum 1"
  },
  "1": {
    "image": "https://i.imgur.com/ehILQ3i.jpg",
    "name": "Copper 4"
  },
  "11": {
    "image": "https://i.imgur.com/EswGcx1.jpg",
    "name": "Silver 2"
  },
  "18": {
    "image": "https://i.imgur.com/Uq3WhzZ.jpg",
    "name": "Platinum 2"
  },
  "2": {
    "image": "https://i.imgur.com/6CxJoMn.jpg",
    "name": "Copper 3"
  },
  "12": {
    "image": "https://i.imgur.com/KmFpkNc.jpg",
    "name": "Silver 1"
  },
  "17": {
    "image": "https://i.imgur.com/Sv3PQQE.jpg",
    "name": "Platinum 3"
  }
}', true);

$final = array();
foreach($idresponse["players"] as $value) {
	$id = $value["profile_id"];
	$final[$id] = array_merge(($loadProgression == "true" ? getValue($id,$progression) : array()),$value, array("nickname"=>$data[$id]["nickname"], "platform" => $platform, "rankInfo" => $ranks[$value["rank"]]));
}
print json_encode(array("players" => $final));
?>
