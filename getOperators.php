<?php
include("config.php");
require_once("Operators.php");

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

$platform = $config["default-platform"];
if(isset($_GET['platform'])) {
	$platform = $_GET['platform'];
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

$idresponse = json_decode($uapi->getOperators($ids, $platform), true);
$final = array();
foreach($idresponse as $id=>$value) {
	$final[$id] = array_merge($value, array("profile_id"=>$id, "nickname"=>$data[$id]["nickname"], "platform" => $platform));
}

$operatorArray = array();

foreach($operators as $operator=>$info) {
	$operatorArray[$operator] = array();
	$operatorArray[$operator]["images"] = $info["images"];
	$operatorArray[$operator]["category"] = $info["category"];
	$operatorArray[$operator]["index"] = $info["index"];
	$operatorArray[$operator]["id"] = $operator;
}

print json_encode(array_merge(array("players" => $final),array("operators" => $operatorArray)));
?>
