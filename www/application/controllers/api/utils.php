<?php

class Utils extends MY_Controller {
	
    public function __construct() {

        // set token to in MY_Controller class use this variable
        if ( isset( $_SERVER['HTTP_TOKEN'] ) )
            $this->token = $_SERVER['HTTP_TOKEN'];

        parent::__construct(
            FALSE, // Controller secured
            array(
            )//secured action
        );

    }
	
    /**
     * Get IP Address of client
     * @param  
     * @return json
     */
    public function ipAddress_get() {

        $ipAddress = null;

        // check for shared internet/ISP IP
        $ipAddress = getenv('HTTP_CLIENT_IP')?:
        getenv('HTTP_X_FORWARDED_FOR')?:
        getenv('HTTP_X_FORWARDED')?:
        getenv('HTTP_FORWARDED_FOR')?:
        getenv('HTTP_FORWARDED')?:
        getenv('REMOTE_ADDR');
        if(!$ipAddress)
        {
            $ipAddress = "None";
        }
        
    	$result = array( 'ipAddress' => $ipAddress, 'statusCode' => 200);

    	// format result before return
    	$this->formatResponse( $result );
    }

    public function ipAddress_post() {
        $this->ipAddress_get();
    }

 
}