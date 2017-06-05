<?php

class Wedges extends MY_Controller 
{	
    public function __construct() 
    {

        parent::__construct(
            TRUE, // Controller secured
            array(
               'getOne'         => array( 'Administrator', 'User', 'Guest' ),
               'getAll'         => array( 'Administrator', 'User', 'Guest' ),
               'spin'           => array( 'Administrator', 'User', 'Guest' ),
               'disposeEvent'   => array( 'Administrator', 'User', 'Guest' ),
                'sponsors'   => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );

        // loading model wedge
        $this->load->model( 'wedge' );

        // set token to player model use this variable
        if ( $this->token )
                $this->wedge->setToken( $this->token );

    }
	
    /**
     * get all wedge
     * GET /api/wheels/1/wedges
     *  @param int $wheelId
     */
    public function sponsors_post( $eventId, $spinKey ) 
    {        
        $id = $this->_get_player_memcache("playerId");
        $results = $this->wedge->getSponsorWheel($eventId, $spinKey, $id);

        // format response results
        $this->formatResponse( $results );
    }
    
    public function sponsors_get($eventId, $spinKey) 
    {
        $this->sponsors_post($eventId, $spinKey);
    }
    
    /**
     * get all wedge
     * GET /api/wheels/1/wedges
     *  @param int $wheelId
     */
    public function getAll_get( $wheelId ) 
    {
        // get all list wedges from function getAll of model wedge
        $results = $this->wedge->getAll( $wheelId );

        // format response results
        $this->formatResponse( $results );
    }
    
    public function getAll_post( $wheelId ) 
    {
        $this->getAll_get($wheelId);
    }
	
    /**
     * get One wedge by id
     * GET /api/wheels/1/wedges/1
     */
    public function getOne_get( $wheelId, $id ) 
    {
        // Get playerRole from token
        $playerRole = $this->_get_player_memcache( 'playerRole' );

        // get list object of wedge by Id from function getById of model sweekstake
        $result = $this->wedge->getById( $wheelId, $id, $playerRole );

        // format response result
        $this->formatResponse( $result );
    }
    
    public function getOne_post( $wheelId, $id ) 
    {
        $this->getOne_get($wheelId, $id);
    }

    /**
     * get random wheel wedge by wheelId and gameToke
     * GET /api/wheels/1/spin/[add][1][Ticket]
     */
    public function spin_get( $wheelId, $eventId, $spinKey ) 
    {
        // Get playerId from token
        $playerId = $this->_get_player_memcache( 'playerId' );

        // Get playerRole from token
        $playerRole = $this->_get_player_memcache( 'playerRole' );

        $result = $this->wedge->spin( $wheelId, $eventId, $spinKey, $playerId, $playerRole );

        // format response result
        $this->formatResponse( $result );
    }
    public function spin_post( $wheelId, $eventId, $spinKey ) 
    {
        $this->spin_get($wheelId, $eventId, $spinKey);
    }

    /**
     * Dispose of a wheel event. Used by the app when the app receives an event notification
         * for a wheel that it does not support.
     * GET /api/wheels/1/dispose
     */
    public function disposeEvent_delete( $eventId, $spinKey ) 
    {
            // Get playerId from token
            $playerId = $this->_get_player_memcache( 'playerId' );

            // Get playerRole from token
            $playerRole = $this->_get_player_memcache( 'playerRole' );

            $result = $this->wedge->disposeEvent( $eventId, $spinKey, $playerId, $playerRole );

            // format response result
            $this->formatResponse( $result );
    }

    public function disposeEvent_post( $eventId, $spinKey ) 
    {
            $this->disposeEvent_delete($eventId, $spinKey);
    }

    /**
     * create a wheel spin event
     * GET /api/wheels/$wheelId/addSpinEvent
     */
    public function addSpinEvent_post( $wheelId ) 
    {

            $gameInfo = $this->post();

            // Get playerRole from token
            $playerId = $this->_get_player_memcache( 'playerId' );

            $result = $this->wedge->addSpinEvent( $wheelId, $playerId, $gameInfo );

            // format response result
            $this->formatResponse( $result );
    }        	
}
