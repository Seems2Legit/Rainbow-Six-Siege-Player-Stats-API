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

include("uAPI.php");

$uapi = new ubiapi($config["ubi-email"],$config["ubi-password"],null);
$rt = $uapi->refreshTicket("bynick","AE_SeemsLegit");

if($rt["error"]){
	$apianswer = $uapi->login(1);
	if($apianswer["error"]) {
		print "ERROR: Can't login";
		die();
	} 
}

$data = array();
$stats = $config["default-stats"];
$platform = $config["default-platform"];

if(isset($_GET['stats'])) {
	$stats = $_GET['stats'];	
}
if(isset($_GET['platform'])) {
	$platform = $_GET['platform'];	
}

function printName($uid) {
	global $uapi, $data, $id;
	$su = $uapi->searchUser("byid", $uid);
	if($su["error"] != true){
		$data[$su['uid']] = array("profile_id" =>$su['uid'], "nickname" => $su['nick']);
	}
}

function printID($name, $platform) {
	global $uapi, $data, $id;
	$su = $uapi->searchUser("bynick", $name, $platform);
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
		printID($value, $platform);
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

$idresponse = json_decode($uapi->getStats($ids, $stats, $platform), true);
$final = array();
foreach($idresponse["results"] as $value) {
	$id = array_search ($value, $idresponse["results"]);
	$final[$id] = array_merge($value, array("nickname"=>$data[$id]["nickname"]));
}
print str_replace(":infinite", "", json_encode(array("players" => $final)));
?>