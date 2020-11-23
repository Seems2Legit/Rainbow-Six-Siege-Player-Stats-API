<?php

require_once("Operators.php");

class UbiAPI{
	private $b64authcreds;

	function __construct($email, $password) {
		$this->b64authcreds=$this->generateB64Creds($email . ":" . $password);
	}

	public function getErrorMessage() {
		$ticket = json_decode($this->saveTicket(false), true);
		if(isset($ticket["errorCode"]))
			return $ticket;
		return false;
	}

	public function generateB64Creds($emailandpassword) {
		return base64_encode($emailandpassword);
	}

	function parseHeaders($headers) {
		$head = array();
		foreach ($headers as $k=>$v) {
			$t = explode(':',$v,2);
			if (isset($t[1])) {
				$head[ trim($t[0])] = trim($t[1]);
			} else {
				$head[] = $v;
				if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out))
					$head['reponse_code'] = intval($out[1]);
			}
		}
		return $head;
	}

	public function searchUser($mode,$content, $platform) {
		$prefixUrl = "https://api-ubiservices.ubi.com/v3/profiles?";
		if($mode == 1 || $mode == "bynick"){
			$content = urlencode($content);
			$request_url = $prefixUrl . "nameOnPlatform=" . $content . "&platformType=" . $platform;
		}
		if($mode == 2 || $mode == "byid")
			$request_url = $prefixUrl . "profileId=" . $content;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers = [
			"ubi-appid: 314d4fef-e568-454a-ae06-43e3bece12a6",
			"authorization: " . $this->uplayticket(),
			"Content-Type: application/json",
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		curl_close($ch);
		$orginaloutput=$ubioutput;
		$jsonoutput = json_decode($ubioutput,true);
		if(!isset($jsonoutput['profiles']) || count($jsonoutput["profiles"]) == 0) {
			return array("error" => true,
						 "content" => "Ubi Response is empty!");
		}
		return array("error" => false,
					 "raw" => $ubioutput,
					 "json" => $jsonoutput,
					 "nick" => $jsonoutput['profiles'][0]['nameOnPlatform'],
					 "pid" => $jsonoutput['profiles'][0]['profileId'],
					 "uid" => $jsonoutput['profiles'][0]['userId']);
	}

	public function getOperators($users, $platform) {
		global $operators;
		
		$normalStats = array("operatorpvp_kills", "operatorpvp_death", "operatorpvp_roundwon", "operatorpvp_roundlost", "operatorpvp_meleekills", "operatorpvp_headshot", "operatorpvp_dbno", "operatorpvp_timeplayed");
		
		$stats = implode(",", $normalStats) . ",";
		foreach($operators as $operator => $info) {
			if (array_key_exists("pvp", $info["stats"])) {
				$pvp_ability = explode(":", $info["stats"]["pvp"])[0];
				$stats .= $pvp_ability . ",";
			}
			if (array_key_exists("pve", $info["stats"])) {
				$pve_ability = explode(":", $info["stats"]["pve"])[0];
				$stats .= $pve_ability . ",";
			}
		}
		
		$stats = substr($stats, 0, -1);
		
		$stats = $this->getStatsRaw($users, $stats, $platform);
		$stats = json_decode($stats, true)["results"];
		$final = array();
		foreach($stats as $id => $value) {
			$final[$id] = array();
			foreach($operators as $operator => $info) {
				$index = $info["index"];
				$final[$id][$operator] = array();
				$info = $info["stats"];
				
				foreach($normalStats as $stat) {
					if ($index == "1:8")
						$rstat = $stat . ":3:8";
					else
						$rstat = $stat . ":" . $index;

					if (isset($value[$rstat]))
						$final[$id][$operator][$stat] = $value[$rstat];
					else
						$final[$id][$operator][$stat] = 0;
				}
				
				foreach ($info as $stat) {
					$rstat = explode(":", $stat)[0];
					
					if (strpos($stat, ":1:8") !== FALSE)
						$stat = str_replace(":1:8", ":3:8", $stat);

					if (isset($value[$stat]))
						$final[$id][$operator][$rstat] = $value[$stat];
					else
						$final[$id][$operator][$rstat] = 0;
				}
			}
		}
		return json_encode($final);
	}

	public function getStats($users, $stats, $platform) {
		$array = array_chunk(explode(",", $stats), 19, true);
		$final = array();
		
		foreach($array as $row) {
			$stats = implode(",", $row);
			$stats = $this->getStatsRaw($users, $stats, $platform);
			$stats = json_decode($stats, true);
			$final[] = $stats;
		}

		$result = array();

		foreach ($final as $key => $val) {
			if ((!is_null($val)) && (array_key_exists("results", $val))){
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

	private function getStatsRaw($users, $stats, $platform) {
		$user = explode(",", $users)[0];
		$request_urls = array("uplay" => 
							  "https://public-ubiservices.ubi.com/v1/spaces/5172a557-50b5-4665-b7db-e3f2e8c5041d/sandboxes/OSBOR_PC_LNCH_A/playerstats2/statistics"
							  ,"xbl" => 
							  "https://public-ubiservices.ubi.com/v1/spaces/98a601e5-ca91-4440-b1c5-753f601a2c90/sandboxes/OSBOR_XBOXONE_LNCH_A/playerstats2/statistics"
							  ,"psn" => 
							  "https://public-ubiservices.ubi.com/v1/spaces/05bfb3f7-6c21-4c42-be1f-97a33fb5cf66/sandboxes/OSBOR_PS4_LNCH_A/playerstats2/statistics"
							 );
		
		$request_url = $request_urls[$platform] . "?populations=" . $users . "&statistics=" . $stats;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers = [
			"Authorization: " . $this->uplayticket(),
			"Content-Type: application/json",
			"Ubi-AppId: 39baebad-39e5-4552-8c25-2c9b919064e2"
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		curl_close($ch);
		return str_replace(":infinite", "", $ubioutput);
	}

	public function getRanking($users, $season, $region, $platform) {
		$user = explode(",", $users)[0];
		$request_urls = array(
			"uplay" =>
			"https://public-ubiservices.ubi.com/v1/spaces/5172a557-50b5-4665-b7db-e3f2e8c5041d/sandboxes/OSBOR_PC_LNCH_A/r6karma/players"
			,"xbl" =>
			"https://public-ubiservices.ubi.com/v1/spaces/98a601e5-ca91-4440-b1c5-753f601a2c90/sandboxes/OSBOR_XBOXONE_LNCH_A/r6karma/players"
			,"psn" =>
			"https://public-ubiservices.ubi.com/v1/spaces/05bfb3f7-6c21-4c42-be1f-97a33fb5cf66/sandboxes/OSBOR_PS4_LNCH_A/r6karma/players"
		);
		$request_url = $request_urls[$platform] . "?board_id=pvp_ranked&profile_ids=" . $users . "&region_id=" . $region . "&season_id=" . $season;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers =[
			"Authorization: " . $this->uplayticket(),
			"Content-Type: application/json",
			"Ubi-AppId: 39baebad-39e5-4552-8c25-2c9b919064e2"
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		curl_close($ch);
		return $ubioutput;
	}

	public function getProgression($users, $platform) {
		$user = explode(",", $users)[0];
		$request_urls = array("uplay" =>
							  "https://public-ubiservices.ubi.com/v1/spaces/5172a557-50b5-4665-b7db-e3f2e8c5041d/sandboxes/OSBOR_PC_LNCH_A/r6playerprofile/playerprofile/progressions"
							  ,"xbl" =>
							  "https://public-ubiservices.ubi.com/v1/spaces/98a601e5-ca91-4440-b1c5-753f601a2c90/sandboxes/OSBOR_XBOXONE_LNCH_A/r6playerprofile/playerprofile/progressions"
							  ,"psn" =>
							  "https://public-ubiservices.ubi.com/v1/spaces/05bfb3f7-6c21-4c42-be1f-97a33fb5cf66/sandboxes/OSBOR_PS4_LNCH_A/r6playerprofile/playerprofile/progressions"
							 );

		$request_url = $request_urls[$platform] . "?profile_ids=" . $users;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers = [
			"Authorization: " . $this->uplayticket(),
			"Content-Type: application/json",
			"Ubi-AppId: 39baebad-39e5-4552-8c25-2c9b919064e2"
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		curl_close($ch);
		return $ubioutput;
	}

	public function login() {
		$request_url = "https://public-ubiservices.ubi.com/v3/profiles/sessions";
		$request_header_authbasic = $this->b64authcreds;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, '{"rememberMe": true}');
		$headers = [
			"Content-Type: application/json",
			"Ubi-AppId: 39baebad-39e5-4552-8c25-2c9b919064e2",
			"Authorization: Basic " . $request_header_authbasic
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		$orginaloutput = $ubioutput;
		curl_close($ch);
		$test_beforeSave = $this->saveTicket(false);
		$this->saveTicket(true, $ubioutput);
		$test_afterSave = $this->saveTicket(false);
		$test_fileUpdated = false;
		if($test_beforeSave != $test_afterSave)
			$test_fileUpdated=true;
		return array("error" => false,
					 "content" => "Ticket Updated? (1==true):" . $test_fileUpdated);
	}

	public function uplayticket($check = true) {
		$ticket = json_decode($this->saveTicket(false), true);
		if ((!isset($ticket["expiration"]) || isset($ticket["error"]) && $ticket["error"] == true || isset($ticket["errorCode"])) && $check) {
			$this->login();
			return $this->uplayticket(false);
		} else if ($check) {
			$time = strtotime($ticket["expiration"]);
			if ($time < time()) {
				$this->login();
				return $this->uplayticket(false);
			}
		}
		if(!isset($ticket["ticket"]))
			return "";
		$ticket = $ticket["ticket"];

		$prefix = "Ubi_v1 t=";
		return $prefix.$ticket;
	}

	private function saveTicket($save, $ticket = "") {
		if ($save) {
			$file_ticket = fopen("api_ticket", "w") or die("Can't open ticket file");
			try {
				fwrite($file_ticket, $ticket);
				return true;
			} catch(Exception $e) {
				return false;
			}
		} else {
			$ticket_file = fopen("api_ticket", "r") or die("{error:true}");
			$ticket = fgets($ticket_file);
			return $ticket;
		}
	}
}

?>
