<?php
class Scratchcards extends MY_Controller {

    function __construct() {

        // set token to in MY_Controller class use this variable
        if ( isset( $_SERVER['HTTP_TOKEN'] ) )
            $this->token = $_SERVER['HTTP_TOKEN'];
        
        parent::__construct(
            TRUE, // Controller secured
            array(               
               'getAll' => array( 'Administrator', 'User', 'Guest' ),
               'getBySerialNumber' => array( 'Administrator', 'User', 'Guest' ),               
               'killKey' => 'Administrator',               
               'card' => array( 'Administrator', 'User', 'Guest' ),
            )//secured action
        );

        // loading model gender
        $this->load->model('scratchcard');
        
        //set token to player model use this variable
        if ( $this->token ) {
            $this->user->setToken( $this->token );
        }
    }
    
    public function killKey_get($serial_number)
    {
        $result = $this->scratchcard->killKey($serial_number);
        $result->statusCode = 200;
        $this->formatResponse( $result );
    }

    /**
     * get next scratch card
     *  GET api/1/scratchcards/card
     */
        public function card_get() 
        {

            $this->scratchcard->setToken( $this->token );

            // get result scratchcards
            $result = $this->scratchcard->getCard( $this->post() );

            // format reponse result return
            $this->formatResponse( $result );
        }
	
        /**
         * get next scratch card
         *  POST api/1/scratchcards/card
         */
        public function card_post() 
        {	
            $this->card_get();
        }
	
    /**
     * get scratchcards collection
     *  GET api/1/scratchcards
     */
    public function getAll_get( $limit = 10, $offset = 0 ) 
    {
        // get result scratchcards
        $result = $this->scratchcard->getAll( $limit, $offset );

        // format reponse result return
        $this->formatResponse( $result );
    }

    public function getAll_post( $limit = 10, $offset = 0 ) 
    {
        $this->getAll_get($limit, $offset);
    }
   
    /**
     * get scratchcard by serial Number
     *  GET api/1/scratchcards/serialnumber/$1
     */
    public function getBySerialNumber_get( $serialNumber ) 
    {
        // get result scratchcard by serialNumber
        $result = $this->scratchcard->getBySerialNumber( $serialNumber );

        // format reponse result return
        $this->formatResponse( $result );
    }

    public function getBySerialNumber_post( $serialNumber ) 
    {
        $this->getBySerialNumber_get( $serialNumber );
    }
}