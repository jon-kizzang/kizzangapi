<?php

class Payments extends MY_Controller {
    
    public function __construct() {

        // set token to in MY_Controller class use this variable
        if ( isset( $_SERVER['HTTP_TOKEN'] ) )
            $this->token = $_SERVER['HTTP_TOKEN'];
        
        parent::__construct(
            TRUE, // Controller secured
            array(
               'payUpdate' => array( 'Administrator', 'User', 'Guest' ),
               'payInfo' => array( 'Administrator', 'User', 'Guest' ),
               // 'maxGame' => 'Administrator',
            )//secured action
        );

        $this->load->model( 'payment' );

        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get payPal
     * GET /api/players/1/payInfo
     * @return json
     */
    public function payInfo_get( $playerId ) {

        $results = $this->payment->payInfo( $playerId );

        $this->formatResponse( $results );
    }

    /**
     * get payPal
     * POST /api/1/players/1/payInfo
     * @return json
     */
    public function payInfo_post( $playerId ) {

        $this->payInfo_get( $playerId );

    }


    /**
     * update payPal
     * PUT /api/players/1/payUpdate
     * @return json
     */
    public function payUpdate_put( $playerId ) {

        if ( $_SERVER['REQUEST_METHOD'] === 'PUT' ) {

            $data = $this->put();
        }
        else {

            $data = $this->post();
        }

        $results = $this->payment->payUpdate( $playerId, $data );

        $this->formatResponse( $results );
    }

    /**
     * update payPal
     * POST /api/2/players/1/payUpdate
     * @return json
     */
    public function payUpdate_post( $playerId ) {

        $this->payUpdate_put( $playerId );
    }
	
    /**
     * confirm a win
     * POST /api/2/players/1/confirmWin
     * @return json
     */
	public function confirmWin( $playerId ) {
	}
}