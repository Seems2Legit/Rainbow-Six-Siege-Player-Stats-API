<?php

$arrContextOptions = array(
	"ssl" => array(
		"verify_peer" => false,
		"verify_peer_name" => false,
	),
);

$operators_string = file_get_contents("https://api.statsdb.net/r6/config", false, stream_context_create($arrContextOptions));

$json = json_decode($operators_string, true);
$json = $json["payload"]["operators"];
$arr_result = new \stdClass;
foreach ($json as $name => $operator) {
	$arr_result->$name = new stdClass;
	$arr_result->$name->images = new stdClass;
	$arr_result->$name->stats = new stdClass;

	$arr_result->$name->id = $operator["id"];
	$arr_result->$name->index = $operator["index"];
	$arr_result->$name->category = $operator["category"];
	$arr_result->$name->ctu = $operator["ctu"];
	$arr_result->$name->images->badge = $operator["badge"];
	$arr_result->$name->images->figure = $operator["figure"];
	$arr_result->$name->images->mask = $operator["mask"];
	if (array_key_exists("uniqueStatistic", $operator)) {
		if (array_key_exists("pvp", $operator["uniqueStatistic"]))
			$arr_result->$name->stats->pvp = $operator["uniqueStatistic"]["pvp"]["statisticId"];
		if (array_key_exists("pve", $operator["uniqueStatistic"]))
			$arr_result->$name->stats->pve = $operator["uniqueStatistic"]["pve"]["statisticId"];
	}
}

$operators_string = json_encode($arr_result, JSON_UNESCAPED_SLASHES);

$operators_string = str_replace("assets/", "https://game-rainbow6.ubi.com/assets/", $operators_string);
$operators = json_decode($operators_string, true);

?>