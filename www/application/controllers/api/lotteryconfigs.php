<?php

class Lotteryconfigs extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getCurrent' => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );

        //loading model
        $this->load->model('lotteryconfig');

        // set token to player model use this variable
        if ($this->token) {
            $this->user->setToken($this->token);
        }
    }
    
    public function getCurrent_post()
    {
        $playerId = $this->_get_player_memcache( 'playerId' );
        $result = $this->lotteryconfig->getCurrent($playerId);
        $this->formatResponse( $result );
    }
    
    public function getRandom_post($id)
    {
        $result = $this->lotteryconfig->getRandomNumbers($id);
        $this->formatResponse( $result );
    }
    
}