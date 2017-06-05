<?php

class Cheddas extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getStatus' => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );

        //loading model
        $this->load->model('chedda');

        // set token to player model use this variable
        if ($this->token) {
            $this->user->setToken($this->token);
        }
    }
    
    public function getEN_post()
    {
        $playerId = $this->_get_player_memcache( 'playerId' );
        $result = $this->chedda->getEventNotifications($playerId);
        $this->formatResponse( $result );
    }
    
    public function addEN_post()
    {
        $gameInfo = $this->post();
        $playerId = $this->_get_player_memcache( 'playerId' );
        $result = $this->chedda->addEventNotification($playerId, $gameInfo);
        $this->formatResponse( $result );
    }

    public function getStatus_get() 
    {
        $playerId = $this->_get_player_memcache("playerId");
        $result = $this->chedda->getStatus($playerId);
        $this->formatResponse( $result );
    }

    public function getStatus_post() 
    {
        $this->getStatus_get();        
    }

}