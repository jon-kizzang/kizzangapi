<?php

class Campaigns extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            FALSE, // Controller secured
            array(
            )//secured action
        );

        //loading model winodometer
        $this->load->model('campaign');
    }

    /**
     * get list of organizations for donations
     * GET /api/1/donations/getAll
     * @return json     
     */
    public function getById_get( $id ) 
    {
        // get winners pending by playerId
        $result = $this->campaign->getById( $id );
        // format result
        $this->formatResponse( $result );
    }

    /**
     * get list of organizations for donations
     * GET /api/1/donations/getAll
     * @return json     
     */
    public function getById_post( $id ) 
    {
        $this->getById_get( $id );
    }
}