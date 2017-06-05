<?php
class Genders extends MY_Controller {
	
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
               'getAll' => 'Administrator',
            )//secured action
        );

        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );

        // loading model gender
        $this->load->model('gender');

    }

    /**
     * get genders collection
     * GET api/genders/getAll
     */
    public function getAll_get() 
    {
        // result return by function getAll genders from model gender
        $result = $this->gender->getAll();

        // format result return
        $this->formatResponse( $result );
    }
    
    public function getAll_post() 
    {
        $this->getAll_get();
    }
}
