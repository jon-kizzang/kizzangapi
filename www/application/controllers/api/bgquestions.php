<?php
class BGQuestions extends MY_Controller {
	
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(               
               'getAll' => array('Administrator', 'User', 'Guest')
            )//secured action
        );

        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );

        // loading model gender
        $this->load->model( 'bgquestion' );

    }    

    /**
     * get all question by start and end date
     * GET api/1/bgquestions
     * @return json
     */
    public function getAll_get() 
    {
        $result = $this->bgquestion->getAll();
        // format result return
        $this->formatResponse( $result );
    }

    public function getAll_post() 
    {
        $this->getAll_get();
    }	
}
