<?php

class Operations extends MY_Controller {
	 

    public function __construct() {

   
        $this->token = "00d726b30a1bf7169357c56b92753b80";
 
        parent::__construct(
            false, // Controller secured
            array(
            )//secured action
        );
 
    }
    



    public function healthCheck_get() {
 
        //check database
        $msg = "Could not grab a player db";
        $dbCheck = $this->db->query("SELECT * FROM Users LIMIT 1;") ;

 
        if ( $dbCheck->num_rows < 1 ) {

            $this->Unhealthy($msg);

        }
                 
  

       // check memcache
        //load the player mode
        $this->load->model('User');

        $randomValue = uniqid();

        $result = $this->User->memcacheInstance->set('healthCheck', $randomValue, 5);

        if ($result != true) $this->Unhealthy("Could not set memcache healthCheck key");

        $result = $this->User->memcacheInstance->get('healthCheck' );

        if ($result != $randomValue) $this->Unhealthy("Could not set health check");

        die("OK");
   
 
    } //end status
    



    public function Unhealthy($msg) {


        echo $msg;
        http_response_code(500);



    }

}