<?php
class Sweepstakes extends MY_Controller {
	
    public function __construct() {

        // set token to in MY_Controller class use this variable
        if ( isset( $_SERVER['HTTP_TOKEN'] ) )
            $this->token = $_SERVER['HTTP_TOKEN'];

        parent::__construct(
            TRUE, // Controller secured
            array(
               'getOne' => array( 'Administrator', 'User', 'Guest' ),
               'getAll' => array( 'Administrator', 'User', 'Guest' ),               
               'getListAll' => array( 'Administrator', 'User', 'Guest' ),
               'getAllActive' => array( 'Administrator', 'User', 'Guest' ),
               'getListBySweepstake' => array( 'Administrator', 'User', 'Guest' ),
               'raffle' => array( 'Administrator' ),
            )//secured action
        );

        // loading model sweepstake
        $this->load->model( 'sweepstake' );

        if( $this->token ) {

            $this->user->setToken( $this->token );
        }
    }
	
    /**
     * get all sweepstake
     * GET /api/sweepstakes
     * 	or /api/sweepstakes/5/10
     *  @param int $limit
     *  @param int $offset
     */
    public function getAllActive_get() 
    {
        $playerId = $this->_get_player_memcache("playerId");
        $results = $this->sweepstake->getAllActive($playerId);
        // format response results
        $this->formatResponse( $results );
    }

    public function getAllActive_post() 
    {
        $this->getAllActive_get();
    }

    /**
     * get all sweepstake
     * GET /api/sweepstakes
     * 	or /api/sweepstakes/5/10
     *  @param int $limit
     *  @param int $offset
     */
    public function getAll_get( $limit = 10, $offset = 0 ) 
    {
        // get all list sweepstakes from function getAll of model sweepstake
        $results = $this->sweepstake->getAll( $limit, $offset );

        // format response results
        $this->formatResponse( $results );
    }
    public function getAll_post( $limit = 10, $offset = 0 ) 
    {
        $this->getAll_get($limit, $offset);
    }
        
    /**
     * get One sweepstake by id
     * GET /api/sweepstakes/1
     */
    public function getOne_get( $id ) 
    {
        // get list object of sweepstake by Id from function getById of model sweekstake
        $result = $this->sweepstake->getById( $id );

        // format response result
        $this->formatResponse( $result );
    }
    public function getOne_post( $id ) 
    {
        $this->getOne_get($id);
    }

    /**
     * Insert a sweepstakes
     * POST /API/sweepstakes
     */
    public function add_post() 
    {
        $result = $this->sweepstake->add( $this->post() );

        // format response result
        $this->formatResponse( $result );

    }

    /**
     * Update a sweepstake
     * PUT /API/sweepstakes/1
     */
    public function update_put( $id ) 
    {
        if ( $_SERVER['REQUEST_METHOD'] === 'PUT' )
            $data = $this->put();        
        else 
            $data = $this->post();        

        // update sweepstake from function edit of model sweepstake
        $result = $this->sweepstake->edit( $id, $data );

        // format response result return
        $this->formatResponse( $result );
    }

    public function update_post( $id ) 
    {
        $this->update_put($id);
    }

    /**
     * delete a sweepstake by id
     * @param  int $id
     */
    public function destroy_delete( $id ) 
    {
        // destroy sweeptake from function destroy of model sweepstake
        $result = $this->sweepstake->destroy( $id );

        // format response return
        $this->formatResponse( $result );
    }

    public function destroy_post( $id ) 
    {
        $this->destroy_delete($id);
    }

    /**
     * lists_get  get list sweepstake ticket of player had been entered ticket
     * @param  int $playerId     
     * @param  int  $sweepstakeId 
     * @param  int $limit        
     * @param  int $offset
     *   GET /api/1/sweepstakes/list/<sweepstakeId>/<limit>/<offset>       
     */
    public function getListBySweepstake_get( $sweepstakeId, $limit = 10, $offset = 0 ) 
    {
        $playerId = $this->_get_player_memcache( 'playerId' );

        // get all sweepstake ticket of player had been entered ticket 
        $result = $this->sweepstake->listEnteredBySweepstake( $playerId, $sweepstakeId, $limit, $offset );

        // format result
        $this->formatResponse( $result );
    }

    public function getListBySweepstake_post( $sweepstakeId, $limit = 10, $offset = 0 ) 
    {	
    	$this->getListBySweepstake_get( $sweepstakeId, $limit, $offset );
    }

    /**
     * get all list sweepstake and count ticket has been entered
     * GET /api/1/sweepstakes/list/<$limit>/<$offset>
     */
    public function getListAll_get( $limit = 10, $offset = 0 ) 
    {

        $playerId = $this->_get_player_memcache( 'playerId' );

        $result = $this->sweepstake->listEntered( $playerId, $limit, $offset );

        // format result
        $this->formatResponse( $result );
    }

    public function getListAll_post( $limit = 10, $offset = 0 ) {

        $this->getListAll_get( $limit, $offset );
    }

    /**
     * raffle_get get ticket had entered of player for a sweeptakes
     * @param  int $sweepstakeId 
     * @return object
     * 
     *    GET /api/sweepstakes/<sweepstakesId>/raffle        
     */
    public function raffle_get( $sweepstakeId ) 
    {
        // get ojbect of ticket had entered into sweepstake
        $result = $this->sweepstake->raffleTickets( $sweepstakeId );

        // format result
        $this->formatResponse( $result );
    }
    public function raffle_post( $sweepstakeId ) 
    {
        $this->raffle_get($sweepstakeId);
    }
}
