<?php

class Storeitems extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getAll' => array( 'Administrator', 'User', 'Guest' ),
                'buy' => array( 'Administrator', 'User')
            )//secured action
        );

        //loading model
        $this->load->model('storeitem');

        // set token to player model use this variable
        if ($this->token) {
            $this->user->setToken($this->token);
        }
    }

    public function getAll_get($limit = 20) 
    {
        $playerId = $this->_get_player_memcache("playerId");
        $result = $this->storeitem->getAll( $limit , $playerId);		        
        $this->formatResponse( $result );
    }

    public function getAll_post( $limit = 20) 
    {
        $this->getAll_get($limit);        
    }
    
    public function buy_post( $storeItemId) 
    {
        $playerId = $this->_get_player_memcache("playerId");
        $result = $this->storeitem->buy( $storeItemId, $playerId );		        
        $this->formatResponse( $result );
    }

}