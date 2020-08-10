<?php
include("config.php");

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
if(isset($_GET['platform']))
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
		die(json_encode($notFound));
	else
		die(json_encode(array("error" => $error)));
}

print json_encode(array_merge($data, $notFound));

?>
