<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PlayerInvites {

    protected $table = 'PlayerInvites';

    public $id;
    public $playerId;
    public $friendEmail;
    public $confirmationCode;
    public $confirmed; // 0 | 1
    public $created;
    public $modified;
    private $player;
    private $body;
    private $subject;
    private $cCode;
    
    /**
     * add player invite
     * @param int $playerId
     * @param string $email // friend email
     */
    public function add( $playerId, $email ) {
    	var_dump($this->user->_memcached); exit;
    	if(!$playerId || !$email){
    		return false;
    	}
    	$this->friendEmail = $email;
    	$this->player = $this->getPlayer($playerId);// get decrypted player.accountData
    	var_dump($this->user->accountData); exit;
    	$this->cCode = $this->getRandomString();
    	$this->user->confirmationCodeUrl = 'http://local.kizzangchef.com/api/playerinvite/confirm/'.$this->cCode;
		$this->body = $this->formatTxt($this->getEmailBody());
		$this->subject = $this->formatTxt($this->getEmailSubject());
		$this->insertData();
		$sent = $this->sendMail();
		
		if($sent){				
			return true;
		}else 
			return false;
    }
    
    private function insertData(){
    	$data = new stdClass();
    	$data->playerId = $this->user->id;
    	$data->friendEmail = $this->friendEmail;
    	$data->confirmationCode = $this->cCode;
    	//var_dump((array)$data); exit;
    	$this->save((array)$data);
    }
    
    private function sendMail(){
    	$this->load->library('email');
    	$this->email->smtp_host = 'smtphost';
    	$this->email->smtp_user = 'user';
    	$this->email->smtp_pass = 'pass';
    	$this->email->to($this->friendEmail);
    	$this->email->from($this->user->accountData['email'], $this->user->accountData['firstName'].' '.$this->user->accountData['lastName']);
    	$this->email->subject($this->subject);
    	$this->email->message($this->body);
    	echo '<pre>';
    	var_dump($this->email); 
    	echo '</pre>';
    	//return $sent = $this->email->send();   
    	return true;
    }
    
    private function getRandomString(){
    	$length = 12; $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';    	
    	$str = '';
    	$count = strlen($charset);
    	while ($length--) {
    		$str .= $charset[mt_rand(0, $count-1)];
    	}
    	return $str;  	
    }
    
    private function getEmailSubject(){
    	$s = 'This will be the subject of the email and will have placeholders like {{firstName}} and {{lastName}}';
    	return $s;
    }
    
    private function getEmailBody(){
    	$b = 'This will be the body of the email and will have placeholders like {{firstName}} and {{lastName}}';
    	$b .= ' Will also contain a confirmation code url like {{confirmationCodeUrl}}';
    	return $b;
    }

    private function formatTxt($txt){
    	$data['email'] = $this->user->accountData['email'];
    	$data['firstName'] = $this->user->accountData['firstName'];
    	$data['lastName'] = $this->user->accountData['lastName'];
    	$data['confirmationCodeUrl'] = $this->user->confirmationCodeUrl;
    	
    	foreach($data as $key=>$val){ 
    		$txt = preg_replace('/{{'.$key.'}}/', $val, $txt);	
    	}
    	return $txt;
    }
    
    public function getPlayer($playerId){
    	$query = $this->db->from('Players')->where('id', $playerId)->get();
    	$this->load->model('Player', 'players');
    	$player = $this->players->decryptData($query->result()[0]);
    	return $player;	
    }
    
    public function getPlayerByEmail($email){
    	$email = md5($email);
    	$query = $this->db->from('Players')->where('email', $email);
    	if($get = $query->get()){
    		$this->load->model('Player', 'players');
    		$player = $this->players->decryptData($get->result()[0]);
    	}
    	return $player;
    }
}