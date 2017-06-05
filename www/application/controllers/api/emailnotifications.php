<?php

class EmailNotifications extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getAll' => 'Administrator',
                'getOne' => array( 'Administrator', 'User', 'Guest' ),
                'add' => 'Administrator',
                'update' => 'Administrator',
                'destroy' => 'Administrator',
            )//secured action
        );

        // loading model emailnotification
        $this->load->model( 'emailnotification' );

    }

    /**
     * get list of email notifications
     * GET /api/1/emailnotifications
     * @return json     
     */
    public function getAll_get() 
    {    
        $result = $this->emailnotification->getAll();		
        // format result
        $this->formatResponse( $result );
    }

    /**
     * get list of email notifications
     * POST /api/1/emailnotifications
     * @return json     
     */
    public function getAll_post() 
    {
        $result = $this->getAll_get();
    }

    /**
     * get a specific email notification
     * GET /api/1/emailnotifications/<$id>
     * @return json     
     */
    public function getOne_get( $id ) 
    {
        // get list of email notification by $id
        $result = $this->emailnotification->getById( $id );
        // format result
        $this->formatResponse( $result );
    }

    /**
     * get a specific email notification
     * POST /api/1/emailnotifications/<$id>
     * @return json     
     */
    public function getOne_post( $id ) 
    {
        $result = $this->getOne_get( $id );
    }

    /**
     * add email notification
     * POST /api/emailnotifictions
     * @return json    
     */
    public function add_post()
    {
        // update email notification by id
        $result = $this->emailnotification->add( $this->post() );
        // format result
        $this->formatResponse( $result );
    }

    /**
     * update email notification by id
     * PUT /api/2/emailnotifictions/$1
     * @return json    
     */
    public function update_put( $organizationId )
    {    
        $data = $this->post();
        // update email notification by id
        $result = $this->emailnotification->edit( $organizationId, $data );
        // format result
        $this->formatResponse( $result );
    }

    /**
     * update email notification by id
     * PUT /api/2/emailnotifictions/$1
     * @return json    
     */
    public function update_post( $organizationId )
    {
    	$this->update_put( $organizationId );
    }   
}