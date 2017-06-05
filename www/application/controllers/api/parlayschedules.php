<?php

class ParlaySchedules extends MY_Controller {
    
    public function __construct() 
    {

        parent::__construct(
            TRUE, // Controller secured
            array(                
                'getAllByDate' => 'Administrator',
                'getAllByCategory' => 'Administrator'
            )//secured action
        );

        //loading model winodometer
        $this->load->model('parlayschedule');
    }

    /**
     * get all list game by date
     * GET /api/parlay/schedules/<$date>
     *    or /api/parlay/schedule/<$date>/<$limit>/<$offset>
     * @return json    
     */
    public function getAllByDate_get( $date, $limit = 10, $offset = 0 ) 
    {
        // update organzation by id
        $result = $this->parlayschedule->getAllByDate( $date, $limit, $offset );

        // format result
        $this->formatResponse( $result );
    }    

    public function getAllByDate_post( $date, $limit = 10, $offset = 0 ) 
    {
        $this->getAllByDate_get( $date, $limit, $offset );
    }

    /**
     * get all list game by date
     * GET /api/parlay/schedules/<$categoryId>
     *    or /api/parlay/schedule/<$categoryId>/<$limit>/<$offset>
     * @return json    
     */
    public function getAllByCategory_get( $categoryId, $limit = 10, $offset = 0 ) 
    {
        // update organzation by id
        $result = $this->parlayschedule->getAllByCategory( $categoryId, $limit, $offset );

        // format result
        $this->formatResponse( $result );
    }    

    public function getAllByCategory_post( $categoryId, $limit = 10, $offset = 0 ) 
    {
        $this->getAllByCategory_get( $categoryId, $limit, $offset );
    }        
}