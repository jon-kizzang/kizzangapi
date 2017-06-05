<?php
class GameRules extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            FALSE, // Controller secured
            array(
            )//secured action
        );

        $this->load->model('gamerule');

        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get all games
     * GET /api/games
     * @return json
     */
    public function getAll_get( $limit = 10, $offset = 0 ) 
    {
        $results = $this->gamerule->getAll( $limit, $offset );
        $this->formatResponse( $results );
    }

    public function getAll_post( $limit = 10, $offset = 0 ) 
    {
        $this->getAll_get( $limit, $offset );
    }

    /**
     * getRule by gameSerialNumber
     * @param  string $gameSerialNumber
     * GET api/game/<gameSerialNumber>/rules 
     * @return json
     */
    public function getGameRules_get( $gameSerialNumber ) 
    {
        // get rules by gameSerialNumber
        $result = $this->gamerule->getGameRules( $gameSerialNumber );

        // format result
        $this->formatResponse($result); 
    }

    public function getGameRules_post( $gameSerialNumber ) 
    {
        $this->getGameRules_get( $gameSerialNumber ); 
    }

    /**
     * getRule by gameSerialNumber
     * @param  string $gameSerialNumber
     * GET api/game/<gameSerialNumber>/rules 
     * @return json
     */
    public function getGameRulesCat_get( $gameType, $limit = 10, $offset = 0 ) 
    {
        // get rules by gameSerialNumber
        $result = $this->gamerule->getGameRulesCat( $gameType, $limit, $offset );

        // format result
        $this->formatResponse($result); 
    }

    public function getGameRulesCat_post( $gameType, $limit = 10, $offset = 0 ) 
    {
        $this->getGameRulesCat_get( $gameType, $limit, $offset ); 
    }
}