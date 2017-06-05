<?php
 
class Bingos extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'initPlayer' => array('Administrator', 'User', 'Guest'),
                'getBall' => array('Administrator', 'User', 'Guest'),
            )//secured action
        );

        //loading model
        $this->load->model('bingo');

        // set token to player model use this variable
        if ($this->token) {
            $this->user->setToken($this->token);
        }
    }

    public function initPlayer_post()
    {
        $playerId = $this->_get_player_memcache("playerId");
        $result = $this->bingo->initPlayer($playerId);
        $this->formatResponse( $result );
    }
    
    public function checkBingo_post()
    {
        $playerId = $this->_get_player_memcache("playerId");
        $result = $this->bingo->checkBingo($playerId);
        $this->formatResponse( $result );
    }
    
}