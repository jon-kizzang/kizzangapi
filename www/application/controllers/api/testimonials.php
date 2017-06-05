<?php

class Testimonials extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getAll' => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );

        //loading model
        $this->load->model('testimonial');

        // set token to player model use this variable
        if ($this->token) {
            $this->user->setToken($this->token);
        }
    }

    public function getAll_get($limit = 20) 
    {
        $result = $this->testimonial->getAll( $limit );		        
        $this->formatResponse( $result );
    }

    public function getAll_post( $limit = 20) 
    {
        $this->getAll_get($limit);        
    }

}