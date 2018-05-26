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
#$rt = $uapi->refreshTicket("bynick","AE_SeemsLegit");

#if($rt["error"]){
#	$apianswer = $uapi->login(1);
#	if($apianswer["error"]) {
#		print "ERROR: Can't login";
#		die();
#	} 
#}

$data = array();

function printName($uid) {
	global $uapi, $data, $id;
	$su = $uapi->searchUser("byid",$uid);
	if($su["error"] != true){
		$data[] = array("profile_id" =>$su['uid'], "nickname" => $su['nick']);
	}
}

function printID($name) {
	global $uapi, $data, $id;
	$su = $uapi->searchUser("bynick",$name);
	if($su["error"] != true){
		$data[] = array("profile_id"=> $su['uid'] , "nickname" => $su['nick']);
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

print json_encode($data);
?>
