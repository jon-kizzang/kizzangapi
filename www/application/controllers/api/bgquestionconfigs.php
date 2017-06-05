<?php

class BGQuestionConfigs extends MY_Controller {
    
    public function __construct() {

        // set token to in MY_Controller class use this variable
        if ( isset( $_SERVER['HTTP_TOKEN'] ) )
            $this->token = $_SERVER['HTTP_TOKEN'];
        
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getAll' => array( 'Administrator', 'User', 'Guest' ),
                'getOne' => array( 'Administrator', 'User', 'Guest' ),
                'add' => 'Administrator',
                'update' => 'Administrator',
                'destroy' => 'Administrator',
            )//secured action
        );

        // loading model bgquestionconfig
        $this->load->model('bgquestionconfig');

    }

    /**
     * get list of final3 config
     * GET /api/1/final3/configs
     *   or  /api/1/final3/configs/<$limit>/<$offset>
     * @return json     
     */
    public function getAll_get( $limit = 10, $offset = 0 ) {
        
        $result = $this->bgquestionconfig->getAll( $limit, $offset );
        
        // format result
        $this->formatResponse( $result );
    }

    /**
     * get list of final3 config
     * POST /api/1/final3/configs
     *   or  /api/1/final3/configs/<$limit>/<$offset>
     * @return json     
     */
    public function getAll_post( $limit = 10, $offset = 0 ) {
        
        $result = $this->getAll_get( $limit, $offset );
    }

    /**
     * get a specific final3 over under config
     * GET /api/1/final3/configs/<$id>
     * @return json     
     */
    public function getOne_get( $id ) {
        
        // get list of final3 config by $id
        $result = $this->bgquestionconfig->getById( $id );

        // format result
        $this->formatResponse( $result );
    }

    /**
     * get a specific final3 over under config
     * POST /api/1/final3/configs/<$id>
     * @return json     
     */
    public function getOne_post( $id ) {
        
        $result = $this->getOne_get( $id );
    }

    /**
     * add final3 over under config
     * POST /api/bgquestionconfigs
     * @return json    
     */
    public function add_post(){

        // update organzation by id
        $result = $this->bgquestionconfig->add( $this->post() );

        // format result
        $this->formatResponse( $result );

    }

    /**
     * update final3 over under config by id
     * PUT /api/2/bgquestionconfigs/$1
     * @return json    
     */
    public function update_put( $id ){
        
        $data = ( $_SERVER['REQUEST_METHOD'] === 'PUT' ) ? $data = $this->put() : $this->post();

        // update organzation by id
        $result = $this->bgquestionconfig->edit( $id, $data );

        // format result
        $this->formatResponse( $result );

    }

    /**
     * update final3 over under config by id
     * PUT /api/2/bgquestionconfigs/$1
     * @return json    
     */
    public function update_post( $id ){

        $this->update_put( $id );
    }

    public function destroy_delete( $id ) {

        $result = $this->bgquestionconfig->destroy( $id );

        $this->formatResponse( $result );

    }

    public function destroy_post( $id ) {

        $this->destroy_delete( $id );
    }
}