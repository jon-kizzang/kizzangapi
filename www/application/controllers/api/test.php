<?php

class Test extends CI_Controller {
	protected $token;
	
	public function __construct() {		
		// set token to in MY_Controller class use this variable
		if ( isset( $_SERVER['HTTP_TOKEN'] ) )
			$this->token = $_SERVER['HTTP_TOKEN'];
		
		parent::__construct(
            FALSE, // Controller secured
            array(
               'login'          => array( 'Administrator', 'User', 'Guest' ),              
               'logout'         => array( 'Administrator', 'User', 'Guest' ),
               'myfriends'      => array( 'Administrator', 'User', 'Guest' ),
               'confirm'        => array( 'Administrator', 'User', 'Guest' ),
               'addFriendPost'  => array( 'Administrator', 'User', 'Guest' ),              
            )//secured action
        );

		$this->load->model('Player', 'player');
		$this->load->model('PlayerInvites', 'playerinvites');
        // load email library
        $this->load->library('email');

		// set token to player model use this variable
        if ( $this->token )
			$this->user->setToken( $this->token );
	}
	
	// testing the login post call
	public function login(){		
		$this->post->isContinue=1;
		$this->post->password="seantest";
		$this->post->email="qaz@mailinator.com";
		$this->post->appid="d2405";
		$this->post->isRegistration=0;
		$this->post->forceUpdate=false;
		$this->post->deviceId=4492406;
		
		$url = 'http://local.kizzangchef.com/api/players/login/';
		$params = $this->post;
		$method = 'POST';
		$api_key = $this->getKey();
		$token = null;
		
		$response = $this->doCurl($url, $params, $api_key, $token, $method);
		$res = json_decode($response);	
		var_dump($res); exit; 
		$this->addFriendPost();
	}
	
	/// testing the logoout
	public function logout(){
		$logout = $this->user->logout();
		var_dump($logout); exit;
	}
	
	// testing the confirm  get call
	public function confirm(){
		$url = 'http://local.kizzangchef.com/api/playerinvite/confirm/hFh9DlTcHDQz';
		$token = null;
		$method = 'get';
		$api_key = $this->getKey();
		$params = array();
		$response = $this->doCurl($url, $params, $api_key, $token, $method);
		var_dump($response);
		exit;
	}
	
	// testing the myfriends get call
	public function myfriends(){
		$url = 'http://local.kizzangchef.com/api/playerinvite/myfriends/10/0';
		$token = null;		
		$method = 'get';
		$api_key = $this->getKey();
		$params = array();
		$response = $this->doCurl($url, $params, $api_key, $token, $method);
		var_dump($response);
		exit;
	}
	
	// testing the addFriend post call
	public function addFriendPost(){
		$email = 'nick.romano@kizzang.com';	//test
		
		$url = 'http://local.kizzangchef.com/api/playerinvite/addFriend';
		$params = array('email' => $email);
		$method = 'post';
		$api_key = $this->getKey();
		$token = $this->token; 
		//var_dump($url, $params, $method, $api_key, $token); exit;
		$response = $this->doCurl($url, $params, $api_key, $token, $method);
		
		$res = json_decode($response);	
		
		var_dump($response, $res); exit;
	}
	
	public function doCurl($url, $params, $api_key, $token = null, $method = 'get'){				
		$curl = curl_init();
		if(!empty($params)){ 			
	    	$pStr = http_build_query($params, null, '&', null);
	    	curl_setopt($curl, CURLOPT_POST, count($params));
	    	curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
			$uStr = trim($url, '/'); 
		}else{
			$uStr = $url;
		}
		curl_setopt($curl, CURLOPT_URL, $uStr.'/?'.$pStr);
		//var_dump($url, $params, $api_key, $token, $method, $pStr, $uStr.'/?'.$pStr); exit;
		$headers [] = "cache-control: no-cache";
		if($api_key)
			$headers[] = "x-api-key: $api_key";		    
		if($token)
			$headers[] = "token: $token";
		
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$a = curl_setopt_array($curl, array(
				CURLOPT_VERBOSE => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "utf-8",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "$method",												
		));
	
		//var_dump($url, $params, $pStr, $uStr, $api_key, $method); exit;
		$res = curl_exec($curl); 
		$err = curl_error($curl);
		return $res;
	}
	
	public function getKey(){
		$query = $this->db->query('select * from kizzang.keys');
		return $key = $query->result()[0]->key;
	}
	
	/////JUST TESTING/////
	public function getProfile($email = 'nicolino101@gmail.com'){
		$url = 'http://local.kizzangchef.com/api/players/getOne/1';
		$token = null;
		$params = array('email' => $email);
		$method = 'get';
		$api_key = $this->getKey();
		$response = $this->doCurl($url, $params, $api_key, $token, $method);
		var_dump($response);
		exit;
	}
	
	public function getOne($id){
		$url = 'http://local.kizzangchef.com/api/players/getOne/'.$id;
		$params = array();
		$method = 'get';
		$api_key = $this->getKey();
		$token = null;
		$response = $this->doCurl($url, $params, $api_key, $token, $method);
		var_dump($response);
		exit;
	}
	
	public function getplayer($playerId){
		$query = $this->db->from('Players')->where('id', $playerId)->get();
		$player = $this->user->decryptData($query->result()[0]);
		var_dump($player, $player->accountData); exit;
	}
	
	public function testMemcache(){
		var_dump($this->playerinvites->memcacheInstance); exit;
		$this->load->driver('cache', array('adapter' => 'memcached', 'backup' => 'file'), '_memcache');
		var_dump($this->_memcache->memcached); exit;
	} 
}