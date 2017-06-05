<?php

class EventNotifications extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'add' => 'Administrator',
                'update' => array( 'Administrator', 'User', 'Guest' ),
                'ack' => array( 'Administrator', 'User', 'Guest' ),
                'getAll' => 'Administrator',
                'getOne' => array( 'Administrator', 'User', 'Guest' ),
                'getByPlayerId' => array( 'Administrator', 'User', 'Guest' ),
            )//secured action
        );

        //loading model winodometer
        $this->load->model('eventnotification');
    }

    /**
     * get all event notifications
     * GET /api/1/eventnotification/
     *    or /api/1/eventnotification
     * @return json    
     */
    public function getByPlayerId_get() 
    {
        // Get playerId from token
        $playerId = $this->_get_player_memcache( 'playerId' );
		
        // update organzation by id
        $result = $this->eventnotification->getByPlayerId( $playerId );

        // format result
        $this->formatResponse( $result );
    }    

    public function getByPlayerId_post() 
    {
        $this->getByPlayerId_get();
    }
    
    public function ack_post($id) 
    {
        $playerId = $this->_get_player_memcache( 'playerId' );
        $result = $this->eventnotification->ack($playerId, $id);
        $this->formatResponse( $result );
    }
	
    /**
     * get all event notifications
     * GET /api/1/eventnotification/
     *    or /api/1/eventnotification
     * @return json    
     */
    public function getAll_get() 
    {
        // update organzation by id
        $result = $this->eventnotification->getAll();
        // format result
        $this->formatResponse( $result );
    }    

    public function getAll_post() 
    {
        $this->getAll_get();
    }
    
    /**
     * get one event notification
     * GET /api/1/eventnotification/<$id>
     * @return json    
     */
    public function getOne_get( $id ) 
    {
        // update organzation by id
        $result = $this->eventnotification->getById( $id );
        // format result
        $this->formatResponse( $result );
    }    

    public function getOne_post( $id ) 
    {
        $this->getOne_get( $id );
    }

    /**
     * add eventnotification
     * POST /api/eventnotification
     * @return json    
     */
    public function add_post()
    {
        // Get playerRole from token
        $playerRole = $this->_get_player_memcache( 'playerRole' );
        
        if ( $playerRole == "Administrator" )
        {
            // Get player id from post data if called by an Administrator
            $data = $this->post();
            $playerId = $data['playerId'];
            // update organzation by id
            $result = $this->eventnotification->add( $this->post(), $playerId );
        }
        else
        {
            // Get playerId from token if this is called by a Player
            $playerId = $this->_get_player_memcache( 'playerId' );
            // update organzation by id
            $result = $this->eventnotification->add( $this->post(), $playerId );
        }

        // format result
        $this->formatResponse( $result );

    }

    /**
     * update eventnotification by id
     * PUT /api/2/eventnotification/<$id>
     * @return json    
     */
    public function update_put( $id )
    {    
        $data = $this->post();

        // Get playerId from token
        $playerId = $this->_get_player_memcache( 'playerId' );

        // Get playerRole from token
        $playerRole = $this->_get_player_memcache( 'playerRole' );

        $this->eventnotification->setToken( $this->token );
		
        // update sports chedule by id
        $result = $this->eventnotification->edit( $id, $playerId, $playerRole, $data );

        // format result
        $this->formatResponse( $result );
    }

    /**
     * update eventnotification by id
     * POST /api/2/eventnotification/<$id>
     * @return json    
     */
    public function update_post( $id ) 
    {
    	$this->update_put( $id );
    }

}