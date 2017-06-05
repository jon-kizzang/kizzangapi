<?php

class Wheels extends MY_Controller {

     function __construct() 
    {                   
        parent::__construct(
            TRUE, // Controller secured
            array(              
               'getAll' 	=> 'Administrator',
               'getOne' 	=> 'Administrator',
               'destroy' 	=> 'Administrator',
            )//secured action
        );        
        $this->load->model('wheel');
    }
    
    /**
     * get all wheels
     * GET api/wheels
     *     or api/wheels/10/0
     */
    public function getAll_get( $limit = 10, $offset = 0 ) 
    {
        $result = $this->wheel->getAll( $limit, $offset );
        $this->formatResponse( $result );
    }
    public function getAll_post( $limit = 10, $offset = 0 ) 
    {
        $this->getAll_get($limit, $offset);
    }
	
    /**
     * get a wheel by id
     * GET api/wheels/1
     */
    public function getOne_get( $id ) 
    {
        $result = $this->wheel->getById( $id );
        $this->formatResponse( $result );
    }
    public function getOne_post( $id ) 
    {
        $this->getOne_get($id);
    }
	
    /**
     * delete a wheel by id
     * @param  int $id
     */
    public function destroy_delete( $id ) 
    {
        // call destroy funciton from player model
        $result = $this->wheel->destroy( $id );

        // format result before return
        $this->formatResponse( $result );
    }
    
    public function destroy_post( $id ) 
    {
        $this->destroy_delete($id);
    }
}