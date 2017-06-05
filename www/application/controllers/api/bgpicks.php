<?php

class BGPicks extends MY_Controller {
    
    public function __construct() {

        // set token to in MY_Controller class use this variable
        if ( isset( $_SERVER['HTTP_TOKEN'] ) )
            $this->token = $_SERVER['HTTP_TOKEN'];
        
        parent::__construct(
            TRUE, // Controller secured
            array(
                'evaluation' => 'Administrator',
            )//secured action
        );

        //loading model bgpick
        $this->load->model('bgpick');
    }

    /**
     * evaluate by date
     * GET api/biggame21/evaluation
     * @param  date $date
     * @return json
     */
    public function evaluation_get( $date ) {

        $result = $this->bgpick->evaluation( $date );

        $this->formatResponse( $result );
    }

    public function evaluation_post( $date ) {

        $this->evaluation_get( $date );
    }
}