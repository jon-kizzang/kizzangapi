<?php

class Games extends MY_Controller {
    
    public function __construct() 
    {            
        parent::__construct(
            TRUE, // Controller secured
            array(
               'maxGame' => array( 'Administrator'),
               'getAll'  => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );
        
        $this->load->model('game');
        
        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get all games
     * GET /api/games
     * @return json
     */
    public function getAll_get( $playerId ) 
    {        
        $results = $this->game->getAll( $playerId );

        if( isset( $results['counts']['statusCode'] ) && $results['counts']['statusCode'] === 200 )
            unset($results['counts']['statusCode']);        

        $this->formatResponse( $results );
    }

    public function getAll_post( $playerId ) 
    {
        $this->getAll_get($playerId);
    }
    
    /**
     * maxGame get maxGame by game type
     * @param  string $gameType 
     * GET /api/game/<gameType>/maxGame
     *
     * @return json
     */
    public function maxGame_get( $gameType ) 
    {
        $results = $this->game->getMaxGame( $gameType );
        $this->formatResponse( $results );
    }

    public function maxGame_post( $gameType ) 
    {
        $this->maxGame_get( $gameType );
    }

}