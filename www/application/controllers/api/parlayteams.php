<?php

class ParlayTeams extends MY_Controller {
    
    public function __construct() 
    {       
        parent::__construct(
            TRUE, // Controller secured
            array(                
                'getAll' => array('Administrator', 'User', 'Guest'),
                'getByCategoryId' => array('Administrator', 'User', 'Guest')                
            )//secured action
        );

        //loading model finalplayercard
        $this->load->model('parlayteam');

        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get all sport team by category id
     * GET /api/1/parlay/teams/<:num>
     * @return json    
     */
    public function getByCategoryId_get( $categoryId )
    {
        $result = $this->parlayteam->getAllByCategory( $categoryId );

        // format result
        $this->formatResponse( $result );
    }

    public function getByCategoryId_post( $categoryId )
    {
        $this->getByCategoryId_get( $categoryId );
    }

    /**
     * get all sport team 
     * GET /api/1/parlay/teams/
     * @return json    
     */
    public function getAll_get( $limit = 10, $offset = 0 ) 
    {
        $result = $this->parlayteam->getAll( $limit, $offset );

        // format result
        $this->formatResponse( $result );
    }

    public function getAll_post( $limit = 10, $offset = 0 ) 
    {
        $this->getAll_get( $limit, $offset );
    }    
}