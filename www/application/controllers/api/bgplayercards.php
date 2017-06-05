<?php

class BGPlayerCards extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'add' => array('Administrator', 'User', 'Guest'),
                'getHistory' => array('Administrator', 'User', 'Guest'),
                'getWinner' => array('Administrator', 'User', 'Guest'),
            )//secured action
        );

        //loading model bgplayercard
        $this->load->model('bgplayercard');

        if ( $this->token )
            $this->bgplayercard->setToken( $this->token );
    }

    
    /**
     * add sportschedule
     * POST /api/biggame21/save
     * @return json    
     */
    public function add_post()
    {
        $this->load->model( 'gamecount' );
        
        if ( $this->token ) 
        {
            $this->user->setToken( $this->token );
            $this->gamecount->setToken( $this->token );
        }

        $result = $this->bgplayercard->add( $this->post() );
			
        // format result
        $this->formatResponse( $result );
    }

    /**
     * get current player card by player id
     * GET api/1/biggame21/<$playerId>/current
     * @param  int $playerId
     * @return json
     */
    public function getCurrent_get( $playerId ) 
    {
        $result = $this->bgplayercard->getCurrent( $playerId );
        // format result
        $this->formatResponse( $result );
    }

    public function getCurrent_post( $playerId ) 
    {
        $this->getCurrent_get( $playerId );
    }

    /**
     * get all card save by player but not evaluation
     * GET api/1/biggame21/history
     * or api/1/biggame21/history/<$limit>/<$offset>
     * @return json    
     */
    public function getHistory_get( $limit = 10, $offset = 0 ) 
    {
        $playerId = $this->_get_player_memcache( 'playerId' );
        $result = $this->bgplayercard->getHistory( $playerId, $limit, $offset );
        // format result
        $this->formatResponse( $result );
    }    

    public function getHistory_post( $limit = 10, $offset = 0 ) 
    {
        $this->getHistory_get( $limit, $offset );
    }

    /**
     * get list winner by player id
     * GET api/1/biggame21/winner
     * or api/1/biggame21/winner/<$limit>/<$offset>
     * @return json    
     */
    public function getWinner_get( $limit = 10, $offset = 0 ) 
    {
        $playerId = $this->_get_player_memcache( 'playerId' );
        $result = $this->bgplayercard->getHistoryWinner( $playerId, $limit, $offset );
        // format result
        $this->formatResponse( $result );
    }    

    public function getWinner_post( $limit = 10, $offset = 0 ) 
    {
        $this->getWinner_get( $limit, $offset );
    }
}