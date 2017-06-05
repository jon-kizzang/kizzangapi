<?php

class Winners extends MY_Controller {
    
    public function __construct() {

        // set token to in MY_Controller class use this variable
        if ( isset( $_SERVER['HTTP_TOKEN'] ) )
            $this->token = $_SERVER['HTTP_TOKEN'];
        
        parent::__construct(
            TRUE, // Controller secured
            array(                
                'getOne'        => array( 'Administrator', 'User', 'Guest' ),
                'getAll'        => array( 'Administrator', 'User', 'Guest' ),
                'getInstantWin' => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );

        //loading model winodometer
        $this->load->model('winner');
    }

    /**
     * get a winner
     * GET /api/winner/<$id>
     * @return json    
     */
    public function getOne_get( $id ) {

        // update organzation by id
        $result = $this->winner->getById( $id );

        // format result
        $this->formatResponse( $result );
    }    

    public function getOne_post( $id ) {

        $this->getOne_get( $id );
    }

    /**
     * get all list winners
     * GET /api/winner
     *    or /api/winner/<$limit>/<$offset>
     * @return json    
     */
    public function getAll_get( $limit = 10, $offset = 0 ) {

        $playerId = $this->_get_player_memcache( 'playerId' );
        
        // update organzation by id
        $result = $this->winner->getAll( $playerId, $limit, $offset );

        // format result
        $this->formatResponse( $result );
    }    

    public function getAll_post( $limit = 10, $offset = 0 ) {

        $this->getAll_get( $limit, $offset );
    }                
    
    /**
     * get instant winner
     * GET /api/winner/instant
     *    or /api/winner/instant
     * @return json    
     */
    public function getInstantWinner_get() {

        $playerId = $this->_get_player_memcache( 'playerId' );
        
        // update organzation by id
        $result = $this->winner->getInstantWinner( $playerId );

        // format result
        $this->formatResponse( $result );
    }    

    public function getInstantWinner_post() {

        $this->getInstantWinner_get();
    }                
}