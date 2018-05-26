<?php
if(empty($_GET)) {
	print "ERROR: Wrong usage";
	die();
}

if(!isset($_GET["appcode"])) {
	print "ERROR: Wrong appcode";
	die();
}

if($_GET["appcode"] != "**INSERT APPCODE**") {
	print "ERROR: Wrong appcode";
	die();
}

if(!isset($_GET["id"]) && !isset($_GET["name"])) {
	print "ERROR: Wrong usage";
	die();
}

include("uAPI.php");

$uapi = new ubiapi("**INSERT EMAIL**","**INSERT PASSWORD**",null);
$rt = $uapi->refreshTicket("bynick","AE_SeemsLegit");

if($rt["error"]){
	$apianswer = $uapi->login(1);
	if($apianswer["error"]) {
		print "ERROR: Can't login";
		die();
	} 
}

$data = array();
$region = "emea"; //EUROPEAN
$season = -1;

if(isset($_GET['season'])) {
	$season = $_GET['season'];	
}
if(isset($_GET['region'])) {
	$region = $_GET['region'];	
}

function printName($uid) {
	global $uapi, $data, $id;
	$su = $uapi->searchUser("byid",$uid);
	if($su["error"] != true){
		$data[$su['uid']] = array("profile_id" =>$su['uid'], "nickname" => $su['nick']);
	}
}

function printID($name) {
	global $uapi, $data, $id;
	$su = $uapi->searchUser("bynick",$name);
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
		die();
}

$ids = "";
foreach ($data as $value) {
	$ids = $ids . "," . $value["profile_id"];
}
$ids = substr($ids, 1);

$idresponse = json_decode($uapi->getStats($ids, $season, $region), true);
$final = array();
foreach($idresponse["players"] as $value) {
	$id = $value["profile_id"];
	$final[$id] = array_merge($value, array("nickname"=>$data[$id]["nickname"]));
}
print json_encode(array("players" => $final));
?>
