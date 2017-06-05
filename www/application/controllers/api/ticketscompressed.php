<?php
class Ticketscompressed extends MY_Controller {

    function __construct() {

        // set token to in MY_Controller class use this variable
        if ( isset( $_SERVER['HTTP_TOKEN'] ) )
            $this->token = $_SERVER['HTTP_TOKEN'];
        
        parent::__construct(
            TRUE, // Controller secured
            array(
               'getAll' => array( 'Administrator', 'Player' ),
               'destroy' => 'Administrator',
               'update' => 'Administrator',
               'add' => 'Administrator',
               'status' => array( 'Administrator', 'Player' )
            )//secured action
        );

        // loading model gender
        $this->load->model('ticket');
        
        //set token to player model use this variable
        if ( $this->token ) {

            $this->player->setToken( $this->token );
        }
        

    }

    /**
     * get tickets collection
     *  GET api/players/1/tickets/
     *      or api/players/1/tickets/10/0
     */
    public function getAll_get( $playerId, $limit = 10, $offset = 0 ) {

        // get result tickets by playerId
        $result = $this->ticket->getAll( $playerId, $limit, $offset );

        // format reponse result return
        $this->formatResponse( $result );
    }
    public function getAll_post( $playerId, $limit = 10, $offset = 0 ) {
    	$this->getAll_get($playerId, $limit, $offset);
	}
	
    /**
     * get ticket by Id
     *  GET api/players/1/tickets/1
     */
    public function getById_get( $playerId, $ticketId ) {

        // get result tickets by playerId
        $result = $this->ticket->getById( $playerId, $ticketId );

        // format reponse result return
        $this->formatResponse( $result );
    }
    public function getById_post( $playerId, $ticketId ) {
    	$this->getById_get($playerId, $ticketId);
	}
	
    /**
     * Update a ticket
     * PUT /API/players/1/tickets/1
     */
    public function update_put( $playerId, $id ) {

        if ( $_SERVER['REQUEST_METHOD'] === 'PUT' ) {

            $data = $this->put();
        }
        else {

            $data = $this->post();
        }

        // update ticket from function edit of model ticket
        $result = $this->ticket->edit( $playerId, $id, $data );

        // format response result return
        $this->formatResponse( $result );
    }
    
    public function update_post( $playerId, $id ) {

    	$this->update_put($playerId, $id);
	}

    /**
     * delete a ticket by id
     * DELETE /api/players/1/tickets/1
     */
    public function destroy_delete( $playerId, $id ) {

        // destroy ticket from function destroy of model ticket
        $result = $this->ticket->destroy( $playerId, $id );

        // format response return
        $this->formatResponse( $result );
    }
    public function destroy_post( $playerId, $id ) {
    	$this->destroy_delete($playerId, $id);
	}
	

    /**
     * enter_post enter a number ticket into sweepstake
     * @param  int $playerId
     * @param  int $sweekstakeId 
     */
    public function enter_post( $sweekstakeId ) {

        // update ticket when enter number into sweepstake
        $result = $this->ticket->enterTicket( $sweekstakeId, $this->post() );

        // format reponse return
        $this->formatResponse( $result );
    }

    /**
     * add new tickets
     * This just only available for Administrator.
     * @param  int $playerId
     * @param  int $sweekstakeId 
     */
    public function add_post( $playerId ) {

        $data = $this->post();
        
        // defined rule validation
        $validate = array(
            'gameToken' => array(
                'field' => 'gameToken', 
                'label' => 'Game Token',
                'rules' => 'required'
            ),
            'number' => array(
                'field' => 'number',
                'label' => 'Number',
                'rules' => 'required|numeric|trim|greater_than[0]'
            )
        );

        // reset error messages
        $this->ticket->form_validation->reset_validation();
        
        // set form data to validate
        $this->ticket->form_validation->set_params( $data );

        // set rules validation
        $this->ticket->form_validation->set_rules( $validate );

        // if validation fail
        if ( $this->ticket->form_validation->run() === FALSE ) {

             $result = array( 'errors' => $this->ticket->form_validation->validation_errors(), 'statusCode' => 400 );
        }
        else {

            // update ticket when enter number into sweepstake
            $result = $this->ticket->add( $playerId, $data['number'], $data['gameToken'] );
        }
        

        // format response return
        $this->formatResponse( $result );
    }

    /**
     * status get count tickets and count tickets had used
     * @param int $playerId 
     */
    public function status_get( $playerId ) {
        
        // get result count tickets
        $result = $this->ticket->status( $playerId );

        // format response return
        $this->formatResponse ( $result );

    }
    public function status_post( $playerId ) {
    	$this->status_get($playerId);
	}

}