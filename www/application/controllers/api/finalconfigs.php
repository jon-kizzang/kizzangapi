<?php

class FinalConfigs extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getAll' => array( 'Administrator', 'User', 'Guest' ),
                'getOne' => array( 'Administrator', 'User', 'Guest' ),
                'save' => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );
        
        $this->load->model('finalconfig');

    }

    public function getAll_get( $limit = 10, $offset = 0 ) 
    {    
        $result = $this->finalconfig->getAll( $limit, $offset );           
        $this->formatResponse( $result );
    }

    public function getAll_post( $limit = 10, $offset = 0 ) 
    {    
        $result = $this->getAll_get( $limit, $offset );
    }

    public function getOne_get( $id ) 
    {            
        $result = $this->finalconfig->getOne( $id );
        $this->formatResponse( $result );
    }
    
    public function getOne_post( $id ) 
    {    
        $result = $this->getOne_get( $id );
    }
        
    public function save_post() 
    {    
        $this->load->model("finalanswer");
        $this->finalanswer->setToken($this->token);
        $post = $this->post();
        $playerId = $this->_get_player_memcache('playerId');
        if(!$playerId)
            $this->formatResponse( array('code' => 1, 'message' => 'Invalid Player ID', 'statusCode' => 200) );
        $post['playerId'] = $playerId;
        $result = $this->finalanswer->save($post);
        $this->formatResponse( $result );
    }
}