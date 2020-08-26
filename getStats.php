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

$uapi = new UbiAPI($config["ubi-email"], $config["ubi-password"]);

$data = array();
$stats = $config["default-stats"];
$season = -1;

if(isset($_GET['season']))
	$season = $_GET['season'];

$platform = $config["default-platform"];
if(isset($_GET['platform']))
	$platform = $_GET['platform'];

if(isset($_GET['stats']))
	$stats = $_GET['stats'];

$notFound = [];

function printName($pid) {
	global $uapi, $data, $id, $platform, $notFound;
	$su = $uapi->searchUser("byid", $pid, $platform);
	if ($su["error"] != true) {
		$data[$su['pid']] = array(
			"profile_id" => $su['pid'],
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
	else
		$tocheck = array($str);

	foreach ($tocheck as $value)
		printName($value);
}
if (isset($_GET["name"])) {
	$str = $_GET["name"];
	if (strpos($str, ',') !== false)
		$tocheck = explode(',', $str);
	else
		$tocheck = array($str);

	foreach ($tocheck as $value)
		printID($value);
}

if (empty($data)) {
	$error = $uapi->getErrorMessage();
	if($error === false)
		die(json_encode(array("players" => $notFound)));
	else
		die(json_encode(array("players" => array(), "error" => $error)));
}

$ids = "";
foreach ($data as $value)
	$ids = $ids . "," . $value["profile_id"];
$ids = substr($ids, 1);

$idresponse = json_decode($uapi->getStats($ids, $stats, $platform), true);
$final = array();
foreach ($idresponse["results"] as $value) {
	$id = array_search($value, $idresponse["results"]);
	$final[$id] = array_merge($value, array("nickname" => $data[$id]["nickname"], "profile_id" => $id, "platform" => $platform));
}
print str_replace(":infinite", "", json_encode(array("players" => array_merge($final,$notFound))));
?>
