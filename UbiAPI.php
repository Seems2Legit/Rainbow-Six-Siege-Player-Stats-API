<?php

require_once("Operators.php");

class UbiAPI{
	private $b64authcreds;
	public $http_useragent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36";

	function __construct($email, $password){
		$this->b64authcreds=$this->generateB64Creds($email.":".$password);
	}

  public function getErrorMessage() {
    $ticket = json_decode($this->saveTicket(false), true);
    if(isset($ticket["errorCode"])) {
      return $ticket;
    }
    return false;
  }

  public function generateB64Creds($emailandpassword){
    return base64_encode($emailandpassword);
  }

	function parseHeaders($headers) {
		$head = array();
		foreach($headers as $k=>$v) {
			$t = explode(':',$v,2);
			if(isset($t[1])) {
				$head[ trim($t[0])] = trim($t[1]);
			}else{
				$head[] = $v;
				if(preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#",$v,$out))
					$head['reponse_code'] = intval($out[1]);
			}
		}
		return $head;
	}

	public function searchUser($mode,$content, $platform){
		$prefixUrl = "https://api-ubiservices.ubi.com/v2/profiles?";
		if($mode == 1 || $mode == "bynick"){
			$content = urlencode($content);
			$request_url = $prefixUrl."nameOnPlatform=".$content."&platformType=$platform";
		}
		if($mode == 2 || $mode == "byid"){
			$request_url = $prefixUrl."profileId=".$content;
		}
		$request_header_ubiappid = "314d4fef-e568-454a-ae06-43e3bece12a6";
		$request_header_ubisessionid = "a651a618-bead-4732-b929-4a9488a21d27";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers =[
			"Accept: */*",
			"ubi-appid: ".$request_header_ubiappid,
			"ubi-sessionid: ".$request_header_ubisessionid,
			"authorization: ".$this->uplayticket(),
			"Referer: https://club.ubisoft.com/en-US/friends",
			"Accept-Language: en-US",
			"Origin: https://club.ubisoft.com",
			"Accept-Encoding: deflate, br",
			"User-Agent: ".$this->http_useragent,
			"Host: api-ubiservices.ubi.com",
			"Cache-Control: no-cache"];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		curl_close($ch);
		$orginaloutput=$ubioutput;
		$jsonoutput = json_decode($ubioutput,true);
		if(!isset($jsonoutput['profiles']) || count($jsonoutput["profiles"]) == 0) {
			return array("error"=>true,
						 "content"=>"Ubi Response is empty!");
		}
		return array("error"=>false,
					 "raw"=>$ubioutput,
					 "json"=>$jsonoutput,
					 "nick"=>$jsonoutput['profiles'][0]['nameOnPlatform'],
					 "uid"=>$jsonoutput['profiles'][0]['profileId']);
	}

	public function getOperators($users, $platform) {
		global $operators;
		$stats = "operatorpvp_smoke_poisongaskill,operatorpvp_timeplayed,operatorpvp_roundwon,operatorpvp_roundlost,operatorpvp_kills,operatorpvp_death,operatorpvp_mute_gadgetjammed,operatorpvp_sledge_hammerhole,operatorpvp_thatcher_gadgetdestroywithemp,operatorpvp_castle_kevlarbarricadedeployed,operatorpvp_ash_bonfirewallbreached,operatorpvp_pulse_heartbeatspot,operatorpvp_thermite_reinforcementbreached,operatorpvp_doc_teammaterevive,operatorpvp_rook_armortakenteammate,operatorpvp_twitch_gadgetdestroybyshockdrone,operatorpvp_montagne_shieldblockdamage,operatorpvp_glaz_sniperkill,operatorpvp_fuze_clusterchargekill,operatorpvp_kapkan_boobytrapkill,operatorpvp_tachanka_turretkill,operatorpvp_blitz_flashedenemy,operatorpvp_iq_gadgetspotbyef,operatorpvp_jager_gadgetdestroybycatcher,operatorpvp_bandit_batterykill,operatorpvp_buck_kill,operatorpvp_frost_dbno,operatorpvp_blackbeard_gunshieldblockdamage,operatorpvp_valkyrie_camdeployed,operatorpvp_capitao_lethaldartkills,operatorpvp_caveira_interrogations,operatorpvp_hibana_detonate_projectile,operatorpvp_echo_enemy_sonicburst_affected,operatorpvp_cazador_assist_kill,operatorpvp_black_mirror_gadget_deployed,operatorpvp_dazzler_gadget_detonate,operatorpvp_caltrop_enemy_affected,operatorpvp_concussionmine_detonate,operatorpvp_concussiongrenade_detonate,operatorpvp_phoneshacked,operatorpvp_attackerdrone_diminishedrealitymode,operatorpvp_tagger_tagdevice_spot,operatorpvp_rush_adrenalinerush,operatorpvp_barrage_killswithturret,operatorpvp_deceiver_revealedattackers,operatorpvp_clash_sloweddown,operatorpvp_maverick_wallbreached,operatorpvp_Nomad_Assist,operatorpvp_Kaid_Electroclaw_Hatches";
		$stats = $this->getStatsRaw($users, $stats, $platform);
		$stats = json_decode($stats, true)["results"];
		$final = array();
		$normalStats = array("operatorpvp_roundlost", "operatorpvp_death", "operatorpvp_roundwon", "operatorpvp_kills", "operatorpvp_death", "operatorpvp_timeplayed");

		foreach($stats as $id=>$value) {
			$final[$id] = array();
			foreach($operators as $operator=>$info) {
				$index = $info["index"];
				$final[$id][$operator] = array();
				$info = $info["stats"];
				foreach($normalStats as $stat) {
					$rstat = $stat . ":" . $index;
					if(isset($value[$rstat])) {
						$final[$id][$operator][$stat] = $value[$rstat];
					}else{
						$final[$id][$operator][$stat] = 0;
					}
				}
				foreach ($info as $stat) {
					$rstat = explode(":", $stat)[0];
					if(isset($value[$stat])) {
						$final[$id][$operator][$rstat] = $value[$stat];
					}else{
						$final[$id][$operator][$rstat] = 0;
					}
				}
			}
		}
		return json_encode($final);
	}

	public function getStats($users, $stats, $platform){
		$array = array_chunk(explode(",",$stats),19,true);
		$final = array();

		foreach($array as $row) {
			$stats = implode(",",$row);
			$stats = $this->getStatsRaw($users, $stats, $platform);
			$stats = json_decode($stats, true);
			$final[] = $stats;
		}

		$result = array();

		foreach($final as $key => $val) {
			if (array_key_exists("results", $val)){
				foreach($val["results"] as $user => $value) {
					if(isset($result[$user])) {
						$result[$user] = array_merge($result[$user], $value);
						continue;
					}
					$result[$user] = $value;
				}
			}
		}
		return json_encode(array("results" => $result));
	}

	private function getStatsRaw($users, $stats, $platform){
		$user = explode(",",$users)[0];
		$request_urls = array("uplay" =>
							  "https://public-ubiservices.ubi.com/v1/spaces/5172a557-50b5-4665-b7db-e3f2e8c5041d/sandboxes/OSBOR_PC_LNCH_A/playerstats2/statistics"
							  ,"xbl" =>
							  "https://public-ubiservices.ubi.com/v1/spaces/98a601e5-ca91-4440-b1c5-753f601a2c90/sandboxes/OSBOR_XBOXONE_LNCH_A/playerstats2/statistics"
							  ,"psn" =>
							  "https://public-ubiservices.ubi.com/v1/spaces/05bfb3f7-6c21-4c42-be1f-97a33fb5cf66/sandboxes/OSBOR_PS4_LNCH_A/playerstats2/statistics"
							 );

		$request_url = $request_urls[$platform] . "?populations=$users&statistics=$stats";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers =[
			"Authorization: ".$this->uplayticket(),
			"Origin: https://game-rainbow6.ubi.com",
			"Accept-Encoding: deflate, br",
			"Host: public-ubiservices.ubi.com",
			"Accept-Language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7",
			"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36 OPR/52.0.2871.99",
			"Accept: application/json, text/plain, */*",
			"Ubi-AppId: 39baebad-39e5-4552-8c25-2c9b919064e2",
			"Ubi-SessionId: a4df2e5c-7fee-41ff-afe5-9d79e68e8048",
			"Referer: https://game-rainbow6.ubi.com/de-de/uplay/player-statistics/$user/multiplayer",
			"Connection: keep-alive"];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		curl_close($ch);
		return str_replace(":infinite", "", $ubioutput);
	}

	public function getRanking($users, $season, $region, $platform){
		$user = explode(",",$users)[0];
		$request_urls = array(
			"uplay" =>
			"https://public-ubiservices.ubi.com/v1/spaces/5172a557-50b5-4665-b7db-e3f2e8c5041d/sandboxes/OSBOR_PC_LNCH_A/r6karma/players"
			,"xbl" =>
			"https://public-ubiservices.ubi.com/v1/spaces/98a601e5-ca91-4440-b1c5-753f601a2c90/sandboxes/OSBOR_XBOXONE_LNCH_A/r6karma/players"
			,"psn" =>
			"https://public-ubiservices.ubi.com/v1/spaces/05bfb3f7-6c21-4c42-be1f-97a33fb5cf66/sandboxes/OSBOR_PS4_LNCH_A/r6karma/players"
		);
		$request_url = $request_urls[$platform] . "?board_id=pvp_ranked&profile_ids=$users&region_id=$region&season_id=$season";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers =[
			"Authorization: ".$this->uplayticket(),
			"Origin: https://game-rainbow6.ubi.com",
			"Accept-Encoding: gzip, deflate, br",
			"Host: public-ubiservices.ubi.com",
			"Accept-Language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7",
			"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36 OPR/52.0.2871.99",
			"Accept: application/json, text/plain, */*",
			"Ubi-AppId: 39baebad-39e5-4552-8c25-2c9b919064e2",
			"Ubi-SessionId: a4df2e5c-7fee-41ff-afe5-9d79e68e8048",
			"Referer: https://game-rainbow6.ubi.com/de-de/uplay/player-statistics/$user/multiplayer",
			"Connection: keep-alive"];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		curl_close($ch);
		return $ubioutput;
	}

	public function getProgression($users, $platform){
		$user = explode(",", $users)[0];
		$request_urls = array("uplay" =>
							  "https://public-ubiservices.ubi.com/v1/spaces/5172a557-50b5-4665-b7db-e3f2e8c5041d/sandboxes/OSBOR_PC_LNCH_A/r6playerprofile/playerprofile/progressions"
							  ,"xbl" =>
							  "https://public-ubiservices.ubi.com/v1/spaces/98a601e5-ca91-4440-b1c5-753f601a2c90/sandboxes/OSBOR_XBOXONE_LNCH_A/r6playerprofile/playerprofile/progressions"
							  ,"psn" =>
							  "https://public-ubiservices.ubi.com/v1/spaces/05bfb3f7-6c21-4c42-be1f-97a33fb5cf66/sandboxes/OSBOR_PS4_LNCH_A/r6playerprofile/playerprofile/progressions"
							 );

		$request_url = $request_urls[$platform] . "?profile_ids=$users";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers =[
			"Authorization: ".$this->uplayticket(),
			"Origin: https://game-rainbow6.ubi.com",
			"Accept-Encoding: deflate, br",
			"Host: public-ubiservices.ubi.com",
			"Accept-Language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7",
			"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36 OPR/52.0.2871.99",
			"Accept: application/json, text/plain, */*",
			"Ubi-AppId: 39baebad-39e5-4552-8c25-2c9b919064e2",
			"Ubi-SessionId: a4df2e5c-7fee-41ff-afe5-9d79e68e8048",
			"Referer: https://game-rainbow6.ubi.com/de-de/uplay/player-statistics/$user/multiplayer",
			"Connection: keep-alive"];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		curl_close($ch);
		return $ubioutput;
	}

	public function login(){
		$request_url = "https://connect.ubi.com/ubiservices/v2/profiles/sessions";
		#$request_header_ubiappid="314d4fef-e568-454a-ae06-43e3bece12a6";
		$request_header_ubiappid="39baebad-39e5-4552-8c25-2c9b919064e2";
		$request_header_authbasic=$this->b64authcreds;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{"rememberMe":true}');
		$headers = [
			"Content-Type: application/json; charset=utf-8",
			"Accept: */*",
			"Ubi-AppId: ".$request_header_ubiappid,
			"Ubi-RequestedPlatformType: uplay",
			"Authorization: Basic ".$request_header_authbasic,
			"X-Requested-With: XMLHttpRequest",
			"Referer: https://connect.ubi.com/Default/Login?appId=".$request_header_ubiappid."&lang=en-US&nextUrl=https%3A%2F%2Fclub.ubisoft.com%2Flogged-in.html%3Flocale%3Den-US",
			"Accept-Language: en-US",
			"Accept-Encoding: deflate, br",
			"User-Agent: ".$this->http_useragent,
			"Host: connect.ubi.com",
			"Content-Lenght: 19",
			"Cache-Control: no-cache",
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		$orginaloutput=$ubioutput;
		curl_close($ch);
		$test_beforeSave=$this->saveTicket(false);
		$this->saveTicket(true, $ubioutput);
		$test_afterSave=$this->saveTicket(false);
		$test_fileUpdated=false;
		if($test_beforeSave != $test_afterSave){
			$test_fileUpdated=true;
		}
		return array("error"=>false,
					 "content"=>"Ticket Updated? (1==true):".$test_fileUpdated);
	}

  public function uplayticket($check = true) {
    $ticket = json_decode($this->saveTicket(false), true);
    if((!isset($ticket["expiration"]) || isset($ticket["error"]) && $ticket["error"] == true || isset($ticket["errorCode"])) && $check) {
      $this->login();
      return $this->uplayticket(false);
    }else if($check) {
      $time = strtotime($ticket["expiration"]);
      if($time < time()) {
        $this->login();
        return $this->uplayticket(false);
      }
    }
    if(!isset($ticket["ticket"])) {
      return "";
    }
    $ticket = $ticket["ticket"];

		$prefix = "Ubi_v1 t=";
		return $prefix.$ticket;
	}

	private function saveTicket($save,$ticket=""){
		if($save){
			$file_ticket = fopen("api_ticket","w") or die("Can't open ticket file");
			try{
				fwrite($file_ticket, $ticket);
				return true;
			}
			catch(Exception $e){
				return false;
			}
		}
		else{
			$ticket_file = fopen("api_ticket","r") or die("{error:true}");
			$ticket = fgets($ticket_file);
			return $ticket;
		}
	}

}

?>
