<?php

class PlayPeriods extends MY_Controller 
{
	
    public function __construct() 
    {	

        parent::__construct(
            TRUE, // Controller secured
            array(
               'getOne' => array( 'Administrator', 'User', 'Guest' ),
               'add' => array( 'Administrator', 'User', 'Guest' ),
               'update' => array( 'Administrator', 'User', 'Guest' ),
               'getCurrent' => array( 'Administrator', 'User', 'Guest' ),
               'getAllByPlayerId' => 'Administrator',
               'getAll' => 'Administrator',
            )//secured action
        );

        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get One play period by id
     * GET /api/players/{playerId}/playperiod/{id}
     */
    public function getOne_get( $playerId, $id ) 
    {
        $result = $this->playperiod->getById( $playerId, $id );
        $this->formatResponse( $result );
    }
    
    public function getOne_post( $playerId, $id ) 
    {
        $this->getOne_get($playerId, $id);
    }

    /**
     * get all play period by player id
     * @param  int $playerid  player id
     * GET /api/players/{playerId}/playperiod
     * or GET /api/players/{playerId}/playperiod/{limit}/{offset}
     */
    public function getAllByPlayerId_get( $playerId, $limit = 10, $offset = 0) 
    {
        $results = $this->playperiod->getByPlayerId( $playerId, $limit, $offset );
        $this->formatResponse( $results );
    }
    public function getAllByPlayerId_post( $playerId, $limit = 10, $offset = 0) 
    {
        $this->getAllByPlayerId_get($playerId, $limit, $offset);
    }
	
    /**
     * get current play period by player id
     * @param  int $playerid  player id
     * GET /api/players/1/playperiods/current
     */
    public function getCurrent_get( $playerId ) 
    {
        $result = $this->playperiod->getByPlayerId( $playerId, 1, 0, true );
        $this->formatResponse( $result );
    }
    public function getCurrent_post( $playerId ) 
    {
        $this->getCurrent_get($playerId);
    }

    /**
     * get all play period by id, startTime, endTiem
     * GET /api/playperiod/1/08-01-2014 10:00:00/08-01-2014 23:59:59
     * @param  int $playerid  player id
     * @param  date $startdate
     * @param  date $enddate
     * @return json
     */
    public function getAll_get( $playerid, $limit = 10, $offset = 0, $startDate = null, $endDate = null ) 
    {
        $results = $this->playperiod->getAll( $playerid, $limit, $offset, urldecode( $startDate ), urldecode( $endDate ) );
        $this->formatResponse( $results );
    }
    public function getAll_post( $playerid, $limit = 10, $offset = 0, $startDate = null, $endDate = null ) 
    {
        $this->getAll_get($playerid, $limit, $offset, $startDate, $endDate);
    }

    /**
     * add a play period
     * @param int $playerId id of players
     * @return json
     */
    public function add_post( $playerId ) 
    {
        $result = $this->playperiod->add( $playerId );
        $this->formatResponse( $result );
    }

    /**
     * edit a play period
     * @param int $playerId id of player
     * @param int $id id of play period
     * @return json
     */
    public function update_put( $playerId, $id ) {

        if ( $_SERVER['REQUEST_METHOD'] === 'PUT' )
            $data = $this->put();        
        else 
            $data = $this->post();        

        // if exists status variable will call update status
        if ( isset( $data['status'] ) ) 
            $result = $this->playperiod->editStatus( $playerId, $id, $data['status'] );        
        else 
            $result = $this->playperiod->edit( $playerId, $id, $this->put('gamesPlayed') );        

        $this->formatResponse( $result );
    }
    public function update_post( $playerId, $id ) 
    {
        $this->update_put($playerId, $id);
    }	
}