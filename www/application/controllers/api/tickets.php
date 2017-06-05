<?php
class Tickets extends MY_Controller {

    function __construct() {

        // set token to in MY_Controller class use this variable
        if ( isset( $_SERVER['HTTP_TOKEN'] ) )
            $this->token = $_SERVER['HTTP_TOKEN'];
        
        parent::__construct(
            TRUE, // Controller secured
            array(               
               'add' => array( 'Administrator', 'User' ),               
            )//secured action
        );
        
        $this->load->model('ticket');
                
        if ( $this->token )
            $this->user->setToken( $this->token );
    }
    
    public function add_post($sweepstakesId)
    {
        $playerId = $this->_get_player_memcache("playerId");
        $result = $this->ticket->add( $sweepstakesId, $playerId );		        
        $this->formatResponse( $result );
    }

}