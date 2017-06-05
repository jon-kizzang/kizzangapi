<?php

class ParlayCards extends MY_Controller {
    
    public function __construct() 
    {    
        parent::__construct(
            TRUE, // Controller secured
            array(                
                'getAll' => array('Administrator', 'User', 'Guest'),
                'getNextCardId' => 'Administrator'
            )//secured action
        );

        //loading model winodometer
        $this->load->model('parlaycard');
    }

    /**
     * get all list game by date
     * GET /api/sportschedules/$date/$4
     * @return json    
     */
    public function getAll_get( $date, $dayLimit ) 
    {
        // update organzation by id
        $result = $this->parlaycard->getAll( $date, $dayLimit );
        // format result
        $this->formatResponse( $result );
    }    

    public function getAll_post( $date, $dayLimit ) 
    {
        $this->getAll_get( $date, $dayLimit );
    }
    
    //------------  Get By Id -----------//
    public function getById_get( $id ) 
    {
        // update organzation by id
        $result = $this->parlaycard->getAllById($id);
        // format result
        $this->formatResponse( $result );
    }    

    public function getById_post( $id ) 
    {
        $this->getById_get( $id );
    }

    /**
     * get NextCardId list game by date
     * GET /api/1/parlay/nexrCardId
     * @return json    
     */
    public function getNextCardId_get( ) 
    {
        // update organzation by id
        $result = $this->parlaycard->getNextCardId( );
        // format result
        $this->formatResponse( $result );
    }    

    public function getNextCardId_post( ) 
    {
        $this->getNextCardId_get( );
    }    
}