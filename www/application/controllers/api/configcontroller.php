<?php

class Configcontroller extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getFile' => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );

        //loading model
        $this->load->model('configs');

        // set token to player model use this variable
        if ($this->token) {
            $this->user->setToken($this->token);
        }
    }

    public function getFile_post( $type ) 
    {        
        $result = $this->configs->getFile($type);
        $this->formatResponse( $result );
    }

}