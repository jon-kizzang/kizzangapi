<?php
 
class Handshake extends MY_Controller {
	// set variable player->memcacheInstance
	public function __construct() {
 	
		parent::__construct(
            FALSE, // Controller secured
            array( 
            )//secured action
        );

	}

 
  public function endpoints_post()
  {
   
       $endpoints = array(  

         'code' => 0, 
         'main_api' => MAIN_API_URL,
         'slot_api' => SLOT_API_URL,
         'rest_version' => getenv("REST_VERSION_NUMBER"),
         'app_version' => getenv("APP_VERSION_NUMBER"),
         'statusCode' => 200 

        );
      
       $this->formatResponse( $endpoints );

  }

}
 