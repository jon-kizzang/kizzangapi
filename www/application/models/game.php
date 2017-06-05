<?php

class Game extends MY_Model {
    
    
    
    // set table is games
    protected $_table = 'Game';

    // set validations rules
    protected $validate = array(
    );

    protected $public_attributes = array(
        'id',
        'gameType',
        'maxGames',
        'baseRulesURL'
        );
    
    protected function getAllFromDatabase( $playerId ) 
    {    
        $parlays = array('profootball','collegefootball','ptbdailyshowdown','sicollegebasketball','sidailyshowdown','cheddadailyshowdown','profootball2016','collegefootball2016');
        $lottery = array('lottery');
        $roal = array('siroal');
        // get all game is not deleted from database by offset and limit
        $games = $this->get_all();
        foreach($games as &$game)
        {
            $game->prize = NULL;
            if($game->theme)
            {
                $rs = $this->db->query("Select * from Payouts where startRank = 1 and gameType = ?", array($game->theme));
                if($rs->num_rows())
                    $game->prize = '$' . number_format ($rs->row()->amount, 0);
            }
        }
        
        foreach($games as $index => $temp)
        {
            if($game->theme)
            {
                if(in_array($temp->theme, $parlays))
                {
                    $rs = $this->db->query("Select * from SportParlayConfig where type = ? and convert_tz(now(), 'GMT', 'US/Pacific') between cardDate and endDate", array($temp->theme));
                    if(!$rs->num_rows())
                        unset($games[$index]);
                }
            }
        }
        
        if ( empty( $games ) )            
            $results = array( 'code' => 1, 'message' => 'Games Not Found', 'statusCode' => 404 );
        else        
            $results = array( 'code' => 0, 'games' => $games, 'statusCode' => 200 );        

        return $results;
    }

    /**
    * get all games
    * @return array
    */
    public function getAll( $playerId ) 
    {
        // load gamecount model
        $this->load->model( 'gamecount' );

        // Get current games played
        $gameCountCurrent = $this->gamecount->getByPlayerId( $playerId );
        if($gameCountCurrent['code'])
            return $gameCountCurrent;
        
        $result = $this->getAllFromDatabase( $playerId );

        return array( 'code' => 0, 'counts' => $gameCountCurrent, 'games' => $result, 'statusCode' => 200 );
    }
    
    public function getMaxGameFromDB( $gameType ) 
    {
        $result = $this->get_by( 'gameType' , $gameType );

        if ( empty( $result ) )        
            return array( 'code' => 1, 'message' => 'Game Not Found', 'statusCode' => 200 );
        
        return array('code' => 0, 'maxGames' => (int)$result->maxGames, 'statusCode' => 200);
        
    }
    
    public function getMaxGame( $gameType ) 
    {
        // validate the game type.
        if ( is_null( $gameType ) ) 
            return array( 'code' => 1, 'message' => 'GameType must be not null', 'statusCode' => 200 );
                    
        return $this->getMaxGameFromDB( $gameType );        
    }

    public function getRemaining( $gameType ) 
    {
        $this->load->model( 'gamecount' );

        $maxGames = 0;
        $remaining = 0;

        $gameCount = $this->db->select('SUM(count) AS count', FALSE )
            ->where( 'gameType', $gameType )
            ->get( $this->gamecount->_table )
            ->row();
        
        $countGameCount = isset( $gameCount->count ) ? (int)$gameCount->count : 0;
        
        $game = $this->getMaxGame( $gameType );

        if ( isset($game->maxGames) ) 
        {
            if ( $maxGames == (int)$game->maxGame && $maxGames >= $countGameCount ) 
                return $maxGames - $countGameCount;
                         
            $result = array( 'code' => 0, 'remaining' => $remaining, 'statusCode' => 200 );
        }
        else 
        {
            $result = array( 'code' => 1, 'message' => 'Not Found', 'statusCode' => 404 );
        }

        return $result;
    }
}