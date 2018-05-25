<?php
/**
 * @author Kacper Serewis (k4czp3r.dev@gmail.com)
 * @copyright 2017
 * @version 2.0.1.2
 * github.com/K4CZP3R
 * Updated at 01-Mar-2017
 */
class ubiapi{
	private $b64authcreds;
	public $http_useragent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36";
	public $http_encoding="gzip";
	public $debug=false;
    /**
     * @param string $location - From which function is this message executed
     * @param string $content - Content of debug message
     * @param string $color - Background Color of message
     */
	public function debugReport($location,$content,$color){
		$color_array = array("red"=>"E57373","green"=>"4DB6AC","blue"=>"4FC3F7","grey"=>"E0E0E0");
		if($this->debug){
			print '<span style="background:#'.$color_array[$color].';text-align:center;">'.date("h:i:s").' - ['.$location.' ] - '.$content.'</span><br>';
		}
	}
    /**
     * @param string $emailandpassword - email:password
     * @return string - email:password in b64
     */
    public function generateB64Creds($emailandpassword){
		$this->debugReport(__FUNCTION__,"B64: <hidden> uncomment line below to see this","grey");
		//$this->debugReport(__FUNCTION__,"B64: ".base64_encode($emailandpassword),"grey");
		return base64_encode($emailandpassword);
	}
    /**
     * ubiapi constructor.
     * You can use email and password OR already encoded b64 string
     * If you gonna use encoded string, enter 'null' in other places
     * @param string $credsemail Uplay Account Email
     * @param string $credspass Uplay Account Password
     * @param string $credsb64 B64 Encoded string (email:password)
     */
	function __construct($credsemail,$credspass,$credsb64){
		if($credspass == null && $credsb64 != null){
			$this->debugReport(__FUNCTION__,"Using b64 string to login","green");
			$this->b64authcreds=$credsb64;
		}
		else{
			$this->debugReport(__FUNCTION__,"Using creds to login","green");
			$this->b64authcreds=$this->generateB64Creds($credsemail.":".$credspass);
		}
	}
    /**
     * If you gonna use this API on website, it is smart to use this.
     * Ubisoft is going to temp ban your account if you gonna login every time you refresh page.
     * And this function logins only when its needed (see examples on my github.com/K4CZP3R
     *
     * @param string $searchWith How to search (bynick,byid)
     * @param string $userToCheck What to search (id,nick)
     * @return array error,content
     */
	public function refreshTicket($searchWith,$userToCheck){
		$this->debugReport("begin ".__FUNCTION__,"Checking if ubi gonna return normal answer","green");
		$firstResponse = $this->searchUser($searchWith,$userToCheck);
		if($firstResponse["error"]){
			$this->debugReport(__FUNCTION__,"strike1, ubi returned null. Trying to refresh ticket","red");
			$this->login(1);
			$this->debugReport(__FUNCTION__,"Checking if after logging in, ubi will return normal answer","grey");
			$secondResponse=$this->searchUser($searchWith,$userToCheck);
			if($secondResponse["error"]){
				$this->debugReport(__FUNCTION__,"strike2, ubi returned null","red");
				return array("error"=>true,
					"content"=>"After logging in still empty response!");
			}
			else{
				$this->debugReport(__FUNCTION__,"Success! Ubi returned normal answer!","green");
				return array("error"=>false,
					"content"=>"Ticket has been refreshed");
			}
		}
		else{
			$this->debugReport(__FUNCTION__,"Ticket is still up-to-date","green");
			return array("error"=>false,
				"content"=>"No action has been taken, ticket up-to-date");
		}
	}
    /**
     * @param string|int $mode 1-bynick, 2-byid
     * @param string $content id or nick you want to search
     * @return array raw-raw answer from ubi, json-array formated json, nick- nickname, uid- user id
     */
	public function searchUser($mode,$content){
		$prefixUrl = "https://api-ubiservices.ubi.com/v2/profiles?";
		if($mode == 1 || $mode == "bynick"){
			$request_url = $prefixUrl."nameOnPlatform=".$content."&platformType=uplay";
		}
		if($mode == 2 || $mode == "byid"){
			$request_url = $prefixUrl."profileId=".$content;
		}
		$this->debugReport(__FUNCTION__,"Request URL: ".$request_url,"grey");
		$request_header_ubiappid = "314d4fef-e568-454a-ae06-43e3bece12a6";
		$request_header_ubisessionid = "a651a618-bead-4732-b929-4a9488a21d27";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers =[
		"Accept: */*",
		"ubi-appid: ".$request_header_ubiappid,
		"ubi-sessionid: ".$request_header_ubisessionid,
		"authorization: ".$this->uplayticket(false),
		"Referer: https://club.ubisoft.com/en-US/friends",
		"Accept-Language: en-US",
		"Origin: https://club.ubisoft.com",
		"Accept-Encoding: ".$this->http_encoding,
		"User-Agent: ".$this->http_useragent,
		"Host: api-ubiservices.ubi.com",
		"Cache-Control: no-cache"];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		curl_close($ch);
		$orginaloutput=$ubioutput;
		if($this->http_encoding == "gzip"){
			$ubioutput = gzdecode($ubioutput);
		}
		$this->debugReport(__FUNCTION__,"Executed request (to see it, uncomment log line)","blue");
		$this->debugReport(__FUNCTION__,$ubioutput,"grey");
		//idk why but sometimes gzdecoded returns null
		if(empty($ubioutput)){
			$this->debugReport(__FUNCTION__,"After making use of ".$this->http_encoding. "decode, string is empty, using orginal one...","red");
			$ubioutput=$orginaloutput;
			if(empty($ubioutput)){
			return array("error"=>true,
				"content"=>"Ubi Response is empty!");
		}
		}
		$jsonoutput = json_decode($ubioutput,true);
		if(!isset($jsonoutput['profiles'])) {
			return array("error"=>true,
				"content"=>"Ubi Response is empty!");
		}
		return array("error"=>false,
			"raw"=>$ubioutput,
			"json"=>$jsonoutput,
			"nick"=>$jsonoutput['profiles'][0]['nameOnPlatform'],
			"uid"=>$jsonoutput['profiles'][0]['profileId']);
		
	}
	
	
	function parseHeaders( $headers )
{
		$head = array();
		foreach( $headers as $k=>$v )
		{
			$t = explode( ':', $v, 2 );
			if( isset( $t[1] ) )
				$head[ trim($t[0]) ] = trim( $t[1] );
			else
			{
				$head[] = $v;
				if( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$v, $out ) )
					$head['reponse_code'] = intval($out[1]);
			}
		}
		return $head;
	}
	
	    /**
     * @return array raw-raw answer from ubi, json-array formated json
     */
	public function getStats($users){
		$user = explode(",",$users)[0];
		$request_url = "https://public-ubiservices.ubi.com/v1/spaces/5172a557-50b5-4665-b7db-e3f2e8c5041d/sandboxes/OSBOR_PC_LNCH_A/r6karma/players?board_id=pvp_ranked&profile_ids=$users&region_id=emea&season_id=-1";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$headers =[
		"Authorization: ".$this->uplayticket(false),
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
	
    /**
     * @param int $showraw outputs (1) raw response or (2) raw decoded response
     * @return array returns true when ticket updated (or not)
     * todo: catch errors
     */
	public function login($showraw){
		$this->debugReport(__FUNCTION__,"Going to login...","green");
		$request_url = "https://connect.ubi.com/ubiservices/v2/profiles/sessions";
		#$request_header_ubiappid="314d4fef-e568-454a-ae06-43e3bece12a6";
		$request_header_ubiappid="39baebad-39e5-4552-8c25-2c9b919064e2";
		$request_header_authbasic=$this->b64authcreds;
		$this->debugReport(__FUNCTION__,"<br>url:".$request_url."<br>appid:".$request_header_ubiappid."<br>authbasic:".$request_header_authbasic,"grey");
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
		"Accept-Encoding: ".$this->http_encoding,
		"User-Agent: ".$this->http_useragent,
		"Host: connect.ubi.com",
		"Content-Lenght: 19", //change this when you are changing CURLOPT_POSTFIELDS!!!!
		"Cache-Control: no-cache",
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$ubioutput = curl_exec($ch);
		$orginaloutput=$ubioutput;
		curl_close($ch);
		if($this->http_encoding == "gzip"){
			$ubioutput = gzdecode($ubioutput);
		}
		if($showraw == 1){
			$this->debugReport(__FUNCTION__,"Raw response (not decoded):<br>".$orginaloutput."<br>","blue");
		}
		if($showraw == 2){
			$this->debugReport(__FUNCTION__,"Raw Response (decoded):<br>".$ubioutput."<br>","blue");
		}
		$this->debugReport(__FUNCTION__,"Executed request (to see it, uncomment log line)","blue");
		//$this->debugReport(__FUNCTION__,__FUNCTION__,$ubioutput,"grey");
		//idk why but sometimes gzdecoded returns null todo: check it
		if(empty($ubioutput)){
			$this->debugReport(__FUNCTION__,"After making use of ".$this->http_encoding. "decode, string is empty, using orginal one...","red");
			$ubioutput=$orginaloutput;}
		$json = json_decode($ubioutput,true);
		$this->debugReport(__FUNCTION__,"Your authstring (to see it, uncomment next line of code)","green");
		//$this->debugReport($json['ticket']);
		$this->debugReport(__FUNCTION__,"Welcome, ".$json['username'],"green");
		$this->debugReport(__FUNCTION__,"Your UserId is ".$json['userId'],"green");
		$this->debugReport(__FUNCTION__,"You can ignore last function for saving authstring but you'll need to edit some lines of codes to disable it","grey");
		$test_beforeSave=$this->uplayticket(false);
		$this->uplayticket(true,$json['ticket']);
		$test_afterSave=$this->uplayticket(false);
		$test_fileUpdated=false;
		if($test_beforeSave != $test_afterSave){
			$test_fileUpdated=true;
		}
		return array("error"=>false,
			"content"=>"Ticket Updated? (1==true):".$test_fileUpdated);
	}
    /**
     * @param bool $save when false, returns ticket otherwise saves ticket
     * @param string $ticket when $save is true, this will be ticket to save
     * @return string Ticket
     */
	public function uplayticket($save,$ticket=""){
		if($save){
			$this->debugReport(__FUNCTION__,"Saving ticket","green");
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
			$this->debugReport(__FUNCTION__,"Returning Ticket","green");
			$prefix = "Ubi_v1 t=";
			$ticket_file = fopen("api_ticket","r") or die("Can't open ticket file");
			$ticket = fgets($ticket_file);
			return $prefix.$ticket;
		}
	}
	
}
