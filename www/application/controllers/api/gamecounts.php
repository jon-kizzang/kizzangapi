<?php

class GameCounts extends MY_Controller 
{
	  
    public function __construct() {
        parent::__construct(
            TRUE, // Controller secured
            array(
               'add' => array( 'Administrator', 'User', 'Guest' ),
               'getByPlayerId' => array( 'Administrator', 'User', 'Guest' ),
               'getFavoriteGame' => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );

        // loading model gamecount
        $this->load->model( 'gamecount' );

        // set token
        if ( $this->token ) 
        {
            $this->gamecount->setToken( $this->token );
            $this->user->setToken( $this->token );
        }
    }

        /**
         * get all game count by player id
         * GET api/players/5/gamecounts
         * 	or api/players/5/gamecounts/10-01-2014
         * @param int $playerId
         * @param datetime $dateCreated
         */
    public function getByPlayerId_post( $playerId) 
    {
        // return result gameCount by player Id and dateCreated
        $result = $this->gamecount->getByPlayerId($playerId);

        // format response result return
        $this->formatResponse( $result );
    }
    
        /**
         * get favorite game by player id
         * GET api/1/players/favoritegame
         */
    public function getFavoriteGame_get() 
    {
            // Get playerId from token
            $playerId = $this->_get_player_memcache( 'playerId' );

            // return result gameCount by player Id and dateCreated
            $result = $this->gamecount->playerFavoriteGame ( $playerId );

            // format response result return
            $this->formatResponse( $result );
    }
    
    public function getFavoriteGame_post() 
    {
            $this->getFavoriteGame_get();
    }

    /**
     * Insert a game count
     * POST /api/players/gamecounts
     */
    public function add_post( $playerId ) 
    {
        // get result when add new gamecount
        $result = $this->gamecount->add( $playerId, $this->post() );

        // format response result return
        $this->formatResponse( $result );
    }

}