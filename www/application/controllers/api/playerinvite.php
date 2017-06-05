<?php

class PlayerInvite extends MY_Controller 
{	
     public function __construct(){

        parent::__construct(
            TRUE, 
            array(
            	'addFriend_post'  => array('Administrator', 'User', 'Guest'),
            	'myfriends_get'   => array('Administrator', 'User', 'Guest'),
            	'confirm_get'     => array('Administrator', 'User', 'Guest'),
            )
        );
        
        $this->load->model('PlayerInvites', 'playerinvites');
        
        if ( $this->token ) {
            $this->user->setToken( $this->token );
        }
    }

    public function addFriend_post()
    {   
    	$email = urlencode($_REQUEST['email']);  
    	//$emailCode = md5('nicolino101@gmail.com');
    	$playerId = 46;
    	$player = $this->playerinvites->getPlayerByEmail($email);
    	//$player = $this->user->get_by( 'emailCode', $emailCode );
    	//var_dump($player); exit;
    	if(!($player->accountData)){
    		//no current results from Player table
    	}
    	$result = $this->playerinvites->add( $playerId, $email );
    	if($result)
    		$result = array( 'code' => 0, 'message' => 'Email Notification was sent to Friend', 'statusCode' => 200 );
    	else 
    		$result = array( 'code' => 1, 'message' => 'Email Failed to Send', 'statusCode' => 200 );
    	
    	//var_dump($result);  exit;
    	$this->formatResponse( $result );
    }
    
    public function myfriends_get($num_per_page, $page){
    	$playerId = 46;
    	// get all friends and requests where playerId = now player comfirmed accepted invite
    	$friends = $this->playerinvites->fetchAll('playerId = '.$playerId.' AND confirmed > 0', array('limit' => $num_per_page, 'offset' => $page, 'orderby' => 'modified DESC'));
    	//var_dump($this->db->last_query(), $friends); exit; 
    	
    	if(!is_null($friends))
    		$result = array( 'code' => 0, 'message' => array('friends' => $friends), 'statusCode' => 200 );
    	else{
    		$result = array( 'code' => 1, 'message' => 'You currently have 0 friend invites', 'statusCode' => 200 );
    	}
    	
    	$this->formatResponse( $result );
    }
    
    public function confirm_get($cCode){
    	$playerId = 46;
    	if($cCode){
    		$cc = $this->playerinvites->fetchAll('playerId = '.$playerId.' AND confirmationCode = "'.$cCode.'"')[0];
    		/* var_dump($cc->confirmationCode == $cCode); exit; */
    		if($cc->confirmationCode == $cCode){
    			$data['playerId'] = $playerId;
    			$data['id'] = $cc->id;
    			$data['confirmed'] = 1;
    			$data['modified'] = time();
    			$this->playerinvites->save($data);
    			$result = array( 'code' => 0, 'message' => 'Thank you for confirming your email.', 'statusCode' => 200 );
    		}else{
    			$result = array( 'code' => 1, 'message' => 'That code does not match our records.', 'statusCode' => 200 );
    		}    		
    	}else
    		$result = array( 'code' => 1, 'message' => 'You must supply a valid Confirmation Code', 'statusCode' => 200 );
    	
    	$this->formatResponse( $result );
    }
}