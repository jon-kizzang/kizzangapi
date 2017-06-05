<?php

class MarketingEmails extends MY_Controller
{        
        public function __construct()
        {
            // set token to in MY_Controller class use this variable
            if ( isset( $_SERVER['HTTP_TOKEN'] ) )
                $this->token = $_SERVER['HTTP_TOKEN'];
		
            parent::__construct(
                false, // Controller secured
                array() //secured action
            );

            // set token to player model use this variable
            if ( $this->token )
                $this->user->setToken( $this->token );

            $this->load->model("marketingemail");
        }
	        
        public function impression_post()
        {
            $data = $_POST;
            $result = $this->marketingemail->impression($data);
            $this->formatResponse($result);
        }
}
