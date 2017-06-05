<?php

class Positions extends MY_Controller 
{
    
        public function __construct() 
        {		

            parent::__construct(
                TRUE, // Controller secured
                array(
                   'getAll' => array( 'Administrator', 'User', 'Guest' ),
                   'setAck' => array( 'Administrator', 'User', 'Guest' ),
                )//secured action
            );

        // set token to player model use this variable
            if ( $this->token )
                $this->user->setToken( $this->token );

                // loading postion model
                $this->load->model( 'position' );

        }

        /**
         * get position collection
         * GET /api/maps/1/panels/1/positions
         * @param int $playerId id of player
         * @param int $limit
         * @param int $offset 
         * @return array list postions 
         */

        public function getAll_get( $playerId, $limit = 10, $offset = 0 ) 
        {
                // return list all position from function getAll of model postition
                $result = $this->position->getAll( $playerId, $limit, $offset );

                // format reponse return 
                $this->formatResponse( $result );
        }
        public function getAll_post( $playerId, $limit = 10, $offset = 0 ) 
        {
                $this->getAll_get($playerId, $limit, $offset);
        }


        public function setAck_put( $positionId ) 
        {
            // Get playerId from token
            $playerId = $this->_get_player_memcache( 'playerId' );

            $result = $this->position->setAck( $playerId, $positionId );

            $this->formatResponse( $result );
        }

        public function setAck_post( $positionId ) 
        {
            $this->setAck_put( $positionId );
        }
}
