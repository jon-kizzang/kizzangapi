<?php

class Roals extends MY_Controller {
	
    public function __construct() 
    {        
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getById' => array('User','Guest','Administrator')
            )//secured action
        );

        $this->load->model('roal');
        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get all games
     * GET /api/games
     * @return json
     */
    public function getById_get($id) 
    {        
        $playerId = $this->_get_player_memcache('playerId');
        $results = $this->roal->getById($id, $playerId);
        $this->formatResponse( $results );
    }
    
    public function getById_post($id) 
    {
        $this->getById_get($id);
    }
    
    public function save_post()
    {
        $data = $this->post();
        $data['playerId'] = $this->_get_player_memcache('playerId');
        $results = $this->roal->save($data);
        $this->formatResponse( $results );
    }
}