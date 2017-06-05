<?php

class Rules extends MY_Controller {
	
    public function __construct() 
    {        
        parent::__construct(
            FALSE, // Controller secured
            array(
            )//secured action
        );

        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get all games
     * GET /api/games
     * @return json
     */
    public function getAll_get() 
    {
        $this->load->model('rule');
        $results = $this->rule->getAll();
        $this->formatResponse( $results );
    }
    
    public function getAll_post() 
    {
        $this->getAll_get();
    }
}