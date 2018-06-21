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
		die(json_encode(array("players" => array())));
}

$ids = "";
foreach ($data as $value) {
	$ids = $ids . "," . $value["profile_id"];
}
$ids = substr($ids, 1);

$idresponse = json_decode($uapi->getRanking($ids, $season, $region, $platform), true);
$final = array();
foreach($idresponse["players"] as $value) {
	$id = $value["profile_id"];
	$final[$id] = array_merge($value, array("nickname"=>$data[$id]["nickname"], "platform" => $platform));
}
print json_encode(array("players" => $final));
?>
