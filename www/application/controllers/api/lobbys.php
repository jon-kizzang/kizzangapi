<?php

class Lobbys extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getAll' => array( 'Administrator', 'User', 'Guest' ),
                'getById' => array( 'Administrator', 'User', 'Guest' ),
            )//secured action
        );

        //loading model winodometer
        $this->load->model('lobby');

        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get list of all lobbys
     * GET /api/1/lobbys/getAll
     * @return json     
     */
    public function getAll_get( $limit = 10, $offset = 0 ) 
    {    
        // get winners pending by playerId
        $result = $this->lobby->getAll( $limit, $offset );		
        // format result
        $this->formatResponse( $result );
    }

    /**
     * get list of lobbys
     * GET /api/1/lobbys/getAll
     * @return json     
     */
    public function getAll_post( $limit = 10, $offset = 0 ) 
    {    
        $result = $this->getAll_get($limit, $offset);
    }

    /**
     * get a specific lobby
     * GET /api/1/lobby/getOne/$1
     * @return json     
     */
    public function getById_get( $typeId ) 
    {    
        // get list of lobbys by $lobbyId
        $playerId = $this->_get_player_memcache('playerId');
        $userType = $this->_get_player_memcache('playerRole');
        $result = $this->lobby->getById( $typeId, $playerId, $userType );
        // format result
        $this->formatResponse( $result );
    }

    /**
     * get a specific lobby
     * GET /api/1/lobbys/getOne/$1
     * @return json     
     */
    public function getById_post( $typeId ) 
    {    
        $result = $this->getById_get( $typeId );
    }
}