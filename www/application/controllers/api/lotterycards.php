<?php

class Lotterycards extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'add' => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );

        //loading model
        $this->load->model('lotterycard');

        // set token to player model use this variable
        if ($this->token) {
            $this->user->setToken($this->token);
        }
    }
    
    public function add_post()
    {
        $data = $this->post();
        $data['playerId'] = $this->_get_player_memcache( 'playerId' );
        $result = $this->lotterycard->add($data);
        $this->formatResponse( $result );
    }
    
}