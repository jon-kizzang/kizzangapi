<?php

class LeaderBoards extends MY_Controller 
{
	
    public function __construct() 
        {		
        parent::__construct(
            TRUE, // Controller secured
            array(               
               'getById' => array( 'Administrator', 'User', 'Guest' ),
                'get' => array( 'Administrator', 'User', 'Guest' ),
                'menu' => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );

        // loading model leaderboard
        $this->load->model( 'leaderboard' );
    }
    
    public function get_get($type, $sub_type = NULL)
    {
        $this->get_post($type, $sub_type);
    }
    
    public function get_post($type, $sub_type = NULL)
    {
        $playerId = $this->_get_player_memcache("playerId");
        $result = $this->leaderboard->getByType($type, $sub_type, $playerId);
        $this->formatResponse( $result );
    }
    
    public function menu_get()
    {
        $this->menu_post();
    }
    
    public function menu_post()
    {
        $result = $this->leaderboard->getMenus();
        $this->formatResponse( $result );
    }

    /**
     * get all leaderBoard
     * GET /api/leaderboard/getall
     */
    public function getAll_get( $limit = 10, $offset = 0 ) 
    {
        // return result leaderboards list
        $result = $this->leaderboard->getAll( $limit, $offset );
        // format response result return
        $this->formatResponse( $result );
    }

    public function getAll_post( $limit = 10, $offset = 0 ) 
    {
        $this->getAll_get( $limit, $offset );
    }
    /**
     * get all leaderBoard by leaderboard id
     * GET /api/leaderboard/getall/1
     */
    public function getById_get( $leaderboardId ) 
    {
        // return result leaderboards list
        $result = $this->leaderboard->getByLeaderboardId( $leaderboardId );
        // format response result return
        $this->formatResponse( $result );
    }

    public function getById_post( $leaderboardId ) 
    {
        $this->getById_get( $leaderboardId );
    }

    public function getByLimitOffset_get( $leaderboardId, $limit, $offset ) 
    {
        // return result leaderboards list
        $result = $this->leaderboard->getByLeaderboardId( $leaderboardId, $limit, $offset );

        // format response result return
        $this->formatResponse( $result );
    }

    public function getByLimitOffset_post( $leaderboardId, $limit, $offset ) 
    {
        $this->getByLimitOffset_get( $leaderboardId, $limit, $offset );
    }    
}