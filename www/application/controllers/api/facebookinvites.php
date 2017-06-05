<?php

class FacebookInvites extends MY_Controller 
{
     function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
            	'add'           => array('Administrator', 'User', 'Guest'),
            	'getFriendList' => array('Administrator', 'User', 'Guest'),
            )//secured action
        );

        // loading model wheel
        $this->load->model('facebookinvite');

        // set token
        if ( $this->token ) {
            $this->user->setToken( $this->token );
        }
    }

    /**
    * add new facebook invite
    * POST /api/players/$1/facebookInvites
    */
    public function add_post() 
    {
        $playerId = $this->_get_player_memcache( 'playerId' );
        //get result facebook invite when add new wheel
        $result = $this->facebookinvite->add( $playerId, $this->post() );    
        //format reponse result return
        $this->formatResponse( $result );
    }

    /**
    * get facebook invite
    * POST /api/1/players/$1/facebookInvites
    */
    public function getFriendList_get( $limit = 10, $offset = 0) 
    {
        $playerId = $this->_get_player_memcache( 'playerId' );
        $result = $this->facebookinvite->getFriendList( $playerId, $limit, $offset );
        $this->formatResponse( $result );
    }
	
    public function getFriendList_post( $limit, $offset ) 
    {	
    	$this->getFriendList_get( $limit, $offset );
    }
}