<?php

class ParlayCategories extends MY_Controller 
{    
    public function __construct() 
    {       
        parent::__construct(
            TRUE, // Controller secured
            array(               
                'getAll' => array('Administrator', 'User', 'Guest'),
                'getOne' => array('Administrator', 'User', 'Guest')
            )//secured action
        );

        //loading model finalplayercard
        $this->load->model('parlaycategory');

        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get all parlay category by category id
     * GET /api/1/parlay/categories/<:num>
     * @return json    
     */
    public function getOne_get( $id )
    {
        $result = $this->parlaycategory->getById( $id );
        // format result
        $this->formatResponse( $result );
    }

    public function getOne_post( $id )
    {
        $this->getOne_get( $id );
    }

    /**
     * get all parlay category 
     * GET /api/1/parlay/categories/
     * @return json    
     */
    public function getAll_get($limit = 10, $offset = 0)
    {
        $result = $this->parlaycategory->getAll( $limit, $offset );
        // format result
        $this->formatResponse( $result );
    }

    public function getAll_post($limit = 10, $offset = 0)
    {
        $this->getAll_get( $limit, $offset );
    }
    
}