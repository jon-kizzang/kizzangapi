<?php

class ParlayConfigs extends MY_Controller {
    
    public function __construct() 
    {        
        
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getAll' => array( 'Administrator', 'User', 'Guest' ),
                'getOne' => array( 'Administrator', 'User', 'Guest' )                
            )//secured action
        );

        // loading model parlayconfig
        $this->load->model('parlayconfig');

    }

    /**
     * get list of parlay config
     * GET /api/1/parlay/configs
     *   or  /api/1/parlay/configs/<$limit>/<$offset>
     * @return json     
     */
    public function getAll_get( $limit = 10, $offset = 0 ) 
    {
        $result = $this->parlayconfig->getAll( $limit, $offset );
        // format result
        $this->formatResponse( $result );
    }

    /**
     * get list of parlay config
     * POST /api/1/parlay/configs
     *   or  /api/1/parlay/configs/<$limit>/<$offset>
     * @return json     
     */
    public function getAll_post( $limit = 10, $offset = 0 ) 
    {
        $this->getAll_get( $limit, $offset );
    }

    /**
     * get a specific organization
     * GET /api/1/parlay/configs/<$id>
     * @return json     
     */
    public function getOne_get( $id ) 
    {
        // get list of parlay config by $id
        $result = $this->parlayconfig->getById( $id );
        // format result
        $this->formatResponse( $result );
    }

    /**
     * get a specific organization
     * POST /api/1/parlay/configs/<$id>
     * @return json     
     */
    public function getOne_post( $id ) 
    {
        $this->getOne_get( $id );
    }

    /**
     * add organization
     * POST /api/parlayconfigs
     * @return json    
     */
    public function add_post(){

        // update organzation by id
        $result = $this->parlayconfig->add( $this->post() );

        // format result
        $this->formatResponse( $result );

    }

    /**
     * update organization by id
     * PUT /api/2/parlayconfigs/$1
     * @return json    
     */
    public function update_put( $organizationId ){
        
        if ( $_SERVER['REQUEST_METHOD'] === 'PUT' ) {

            $data = $this->put();
        }
        else {

            $data = $this->post();
        }

        // update organzation by id
        $result = $this->parlayconfig->edit( $organizationId, $data );

        // format result
        $this->formatResponse( $result );

    }

    /**
     * update organization by id
     * PUT /api/2/parlayconfigs/$1
     * @return json    
     */
    public function update_post( $organizationId ){

    	$this->update_put( $organizationId );
	}

    public function destroy_delete( $organizationId ) {

        $result = $this->parlayconfig->destroy( $organizationId );

        $this->formatResponse( $result );

    }

    public function destroy_post( $organizationId ) {

        $this->destroy_delete( $organizationId );
    }
}