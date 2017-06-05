<?php

class Donations extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getAll' => array( 'Administrator', 'User', 'Guest' ),
                'getOne' => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );

        //loading model winodometer
        $this->load->model('donation');

        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get list of organizations for donations
     * GET /api/1/donations/getAll
     * @return json     
     */
    public function getAll_get( $limit = 10, $offset = 0 ) 
    {
        // get winners pending by playerId
        $result = $this->donation->getAll( $limit, $offset );		
        // format result
        $this->formatResponse( $result );
    }

    /**
     * get list of organizations for donations
     * GET /api/1/donations/getAll
     * @return json     
     */
    public function getAll_post( $limit = 10, $offset = 0 ) 
    {
        $result = $this->getAll_get($limit, $offset);
    }

    /**
     * get a specific organization
     * GET /api/1/donations/getOne/$1
     * @return json     
     */
    public function getOne_get( $organizationId ) 
    {
        // get list of donation organizations by $organizationId
        $result = $this->donation->getOne( $organizationId );
        // format result
        $this->formatResponse( $result );
    }

    /**
     * get a specific organization
     * GET /api/1/donations/getOne/$1
     * @return json     
     */
    public function getOne_post( $organizationId ) 
    {
        $result = $this->getOne_get( $organizationId );
    }    
}