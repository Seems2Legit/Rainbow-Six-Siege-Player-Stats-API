<?php
include("config.php");
require_once("Operators.php");

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

include("UbiAPI.php");

$uapi = new UbiAPI($config["ubi-email"], $config["ubi-password"]);

$data = array();

$platform = $config["default-platform"];
if (isset($_GET['platform']))
	$platform = $_GET['platform'];

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
	if ($error === false)
		die(json_encode(array("players" => $notFound)));
	else
		die(json_encode(array("players" => array(), "error" => $error)));
}

$ids = "";
foreach ($data as $value)
	$ids = $ids . "," . $value["profile_id"];
$ids = substr($ids, 1);

$idresponse = json_decode($uapi->getOperators($ids, $platform), true);
if (!is_array($idresponse)) {
	print "{}";
	exit;
}
$final = array();
foreach($idresponse as $id => $value)
	$final[$id] = array_merge($value, array("profile_id" => $id, "nickname" => $data[$id]["nickname"], "platform" => $platform));

foreach($operators as $operator => $info) {
	$operatorArray[$operator] = array();
	if (!array_key_exists("name", $info))
		continue;
	$operatorArray[$operator]["name"] = ucfirst(strtolower($info["name"]));
	$operatorArray[$operator]["organisation"] = $info["ctu"];
	$operatorArray[$operator]["images"] = $info["images"];
	$operatorArray[$operator]["category"] = $info["category"];
	$operatorArray[$operator]["index"] = $info["index"];
	$operatorArray[$operator]["id"] = $operator;
}

print json_encode(array_merge(array("players" => array_merge($final, $notFound)), array("operators" => $operatorArray)));
