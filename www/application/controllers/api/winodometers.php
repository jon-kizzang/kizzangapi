<?php

class WinOdometers extends MY_Controller {
    
    public function __construct() {

        // set token to in MY_Controller class use this variable
        if ( isset( $_SERVER['HTTP_TOKEN'] ) )
            $this->token = $_SERVER['HTTP_TOKEN'];
        
        parent::__construct(
            TRUE, // Controller secured
            array(
                'update' => 'Administrator',
            )//secured action
        );

        //loading model winodometer
        $this->load->model('winodometer');

        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get One odometer
     * @param int $id 
     * GET /api/odometer/<odometerId>
     * @return json     
     */
    public function getOne_get( $id ) {
        
        // get win odometer by id
        $result = $this->winodometer->getById( $id );

        // format result
        $this->formatResponse( $result );
    }

    public function getOne_post( $id ) {

        $this->getOne_get( $id );
    }
    
}