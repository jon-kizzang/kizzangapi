<?php

class ParlayPlayerCards extends MY_Controller {
    
    public function __construct() 
    {        
        parent::__construct(
            TRUE, // Controller secured
            array(
                'add' => array( 'Administrator', 'User', 'Guest' ),
                'getOne' => array( 'Administrator', 'User', 'Guest' ),
                'getAll' => 'Administrator',
            )//secured action
        );

        //loading model winodometer
        $this->load->model('parlayplayercard');
        $this->load->model('parlayconfig' );
        
        //loading model bgplayercard
        $this->load->model('bgplayercard');

        if ( $this->token )
            $this->bgplayercard->setToken( $this->token );
    }
    
    public function getOne_post($id)
    {
        $result = $this->parlayplayercard->getParlayCard($id);
        $this->formatResponse( $result );
    }

    /**
     * get all list game by date
     * GET /api/sportschedules/$date
     * @return json    
     */
    public function getAll_get( $date ) 
    {
        // update organzation by id
        $result = $this->parlayplayercard->getAll( $date );

        // format result
        $this->formatResponse( $result );
    }    

    public function getAll_post( $date ) 
    {
        $this->getAll_get( $date );
    }    
    
    /**
     * add sportschedule
     * POST /api/parlay/save
     * @return json    
     */
    public function add_post()
    {
        $data = $this->post();
		
    	// Increment the sports count event - even if the email doesn't get sent
        $this->load->model( 'gamecount' );
        if ( $this->token )
        {
            $this->user->setToken( $this->token );
            $this->gamecount->setToken( $this->token );
        }

        $playerId = $this->_get_player_memcache( 'playerId' );
        $result = $this->parlayplayercard->add( $playerId, $data );        

        $this->formatResponse( $result );

    }
    
}