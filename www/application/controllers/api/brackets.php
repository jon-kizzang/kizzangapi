<?php
class Brackets extends MY_Controller {
	
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(               
               'get' => array('Administrator', 'User', 'Guest'),
                'save' => array('Administrator', 'User', 'Guest'),
                'email' => array('Administrator', 'User', 'Guest'),
                'cards' => array('Administrator', 'User', 'Guest'),
                'celebrities' => array('Administrator', 'User', 'Guest')
            )//secured action
        );

        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );

        // loading model gender
        $this->load->model( 'bracket' );

    }
    
    public function button_post()
    {        
        $result = $this->bracket->button();
        $this->formatResponse( $result );
    }
    
    public function image_post($id)
    {        
        $result = $this->bracket->image($id);
        die();
    }
    
    public function image_get($id)
    {
        $this->image_post($id);
    }
        
    public function celebrities_post($id)
    {        
        $result = $this->bracket->celebrities($id);     
        $this->formatResponse( $result );
    }
    
    public function card_post($id)
    {
        $playerId = $this->_get_player_memcache("playerId");
        if($playerId)
            $result = $this->bracket->card($playerId, $id);
        else
            $result = array('code' => 1, 'message' => 'Error finding player session', 'statusCode' => 200);
        $this->formatResponse( $result );
    }
    
    public function cards_post($id)
    {
        $playerId = $this->_get_player_memcache("playerId");
        if($playerId)
            $result = $this->bracket->cards($playerId, $id);
        else
            $result = array('code' => 1, 'message' => 'Error finding player session', 'statusCode' => 200);
        $this->formatResponse( $result );
    }
    
    public function email_post()
    {
        $playerId = $this->_get_player_memcache("playerId");
        $data = $this->post();
        if($playerId)
            $result = $this->bracket->email($playerId, $data);
        else
            $result = array('code' => 1, 'message' => 'Error finding player session', 'statusCode' => 200);
        $this->formatResponse( $result );
    }
    
    public function save_post() 
    {
        $playerId = $this->_get_player_memcache("playerId");
        $data = $this->post();
        if($playerId)
            $result = $this->bracket->save($playerId, $data);
        else
            $result = array('code' => 1, 'message' => 'Error finding player session', 'statusCode' => 200);
        $this->formatResponse( $result );
    }

    public function get_post($id) 
    {
        $playerId = $this->_get_player_memcache("playerId");
        if($playerId)
            $result = $this->bracket->getById($id, $playerId);
        else
            $result = array('code' => 1, 'message' => 'Error finding player session', 'statusCode' => 200);
        $this->formatResponse( $result );
    }
    
    public function get_get($id)
    {
        $this->get_post($id);
    }
}
