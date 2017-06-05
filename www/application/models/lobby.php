<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lobby extends MY_Model {

    // set table to Lobbys
    protected $_table = 'Lobbys';

    // set validations rules
    protected $validate = array(
        'gameTypeId' => array( 
            'field' => 'gameTypeId', 
            'label' => 'gameTypeId',
            'rules' => 'required'
        ),
        'name' => array( 
            'field' => 'name', 
            'label' => 'name',
            'rules' => 'required'
        ),
    );

    protected $public_attributes = array(
            'gameTypeId',
            'name'
        );

    /**
     * getAllLobbysFromDB get all lobbies from database
     * @param  int $limit  
     * @param  int $offset 
     * @return array         
     */
    protected function getAllLobbysFromDB( $limit, $offset ) 
    {
        // get all organizations from database by offset and limit
        $lobbys = $this->limit( $limit, $offset )->get_all();

        if ( empty( $lobbys ) )
            $result = array( 'code' => 1, 'message' => 'Lobbys Not Found', 'statusCode' => 404 );        
        else
            $result = array( 'code' => 0, 'lobbys' => $lobbys, 'statusCode' => 200 );        

        return $result;
    } 
	    
    public function getAll( $limit, $offset ) 
    {        
        return $this->getAllLobbysFromDB( $limit, $offset );
    }
	
    protected function getByIdFromDb( $typeId ) 
    {        
        $lobby = $this->get( $typeId );

        if ( empty($lobby) ) 
            return array( 'code' => 1, 'message' => 'Lobby not found', 'statusCode' => 404 );             
        else         
            return array( 'code' => 0, 'lobby' => $lobby->name, 'statusCode' => 200 );            
    }

    public function getById( $typeId, $playerId, $userType ) 
    {
        $this->load->model( array( 'bgquestionconfig', 'parlaycard', 'bgquestionsplace', 'parlayplace', 'parlaycard', 'finalconfig','roal' ) );

        // validate the id.
        if ( ! is_numeric( $typeId ) || $typeId <= 0 ) 
            return array( 'code' => 1, 'message' => 'Id must be a numeric and greater than zero', 'statusCode' => 200 );   
            
        $currentDate = date( 'Y-m-d H:i:s' );
        if($this->user->memcacheEnable)
        {
            $games = $this->user->memcacheInstance->get("Key-Lobby-Parlay-" . $userType);
            if($games)
                return $games;
        }
        $games = array ();
	
        if($userType != "Guest")
        {
            $roal = array( 'gameId' => 4, 'name' => "Run of a Lifetime" );
            $row = $this->roal->getCurrent();
            if($row)
            {
                $roal['cardDate'] = $row->cardDate;
                $roal['endTime'] = date("m/d/Y g:i A", strtotime($row->endDate));
                $roal['theme'] = $row->theme;
                $roal['disclaimer'] = $row->disclaimer;
                $roal['id'] = (int) $row->id;
                $roal['adPlacement'] = 'After';
                if(strstr($row->payouts, ","))
                    $roal['prizeList'] = implode(",", $row->payouts);
                else
                    $roal['prizeList'][] = $row->payouts;
                foreach($roal['prizeList'] as &$prize)
                    $prize = '$' . number_format ($prize, $prize < 1000 ? 2 : 0);
                $games[] = $roal;
            }
        
            $bgquestions = $this->bgquestionconfig->getByDate( $currentDate, $playerId );

            $bgGame = array( 'gameId' => 1, 'name' => "Big Game 21" );

            if ( (int)$bgquestions['code'] === 0) 
            {
                $prizeList = array();
                $bgPlaces = $this->bgquestionsplace->as_array()->get_many_by( 'parlayCardId', $bgquestions['items']->parlayCardId );

                if ( ! empty( $bgPlaces) ) 
                {
                    // convert prize to array
                    $prizeList = array_column( $bgPlaces, 'prize' );
                }

                //$questions = $this->bgquestion->order_by( 'startDate' )->get_by( array( 'startDate <=' => $currentDate, 'endDate >=' => $currentDate ) );
                $bgGame['startTime']   = isset( $bgquestions['items']->startDate ) ? date( 'm/d/Y g:i A', strtotime( $bgquestions['items']->startDate ) ) : null;
                $bgGame['endTime']     = isset( $bgquestions['items']->endDate ) ? date( 'm/d/Y g:i A', strtotime( $bgquestions['items']->endDate ) ) : null;
                $bgGame['prizeList']   = $prizeList;
                $bgGame['disclaimer'] = $bgquestions['items']->disclaimer;
                $bgGame['theme']   = "sibiggame";
                $bgGame['adPlacement'] = 'After';

                $games[] = $bgGame;
            }

            $finalResult = $this->finalconfig->getAllByDate( $currentDate, $playerId );

            $finalGame = array( 'gameId' => 2, 'name' => "Final 3" );

            if ( (int)$finalResult['code'] === 0)      
            {
                foreach($finalResult['games'] as $game)
                {
                    $finalGame['id'] =  (int) $game->id;
                    $finalGame['endTime'] = $game->endDate;
                    $finalGame['startTime'] = $game->startDate;
                    $finalGame['prizeList'] =  strstr($game->prizes, "|") ? explode("|", $game->prizes) : array($game->prizes);
                    $finalGame['theme'] = $game->theme;
                    $finalGame['disclaimer'] = $game->disclaimer;
                    $finalGame['adPlacement'] = 'After';
                }
                $games[] = $finalGame;
            }
        }

        $today = date( 'm-d-Y' );
        
        $parlayResult = $this->parlaycard->getAllTypes( $today, $userType );
        $ids = array();
        $rs = $this->db->query("Select foreignId, count from GameCount where date(expirationDate) = ? and playerId = ? and gameType = 'SportsEvent'", array(date("Y-m-d"), $playerId));
        foreach($rs->result() as $row)
            $ids[$row->foreignId] = $row->count;
        
        $parlayGame = array( 'gameId' => 3, 'name' => "Daily Showdown" );       
        
        if ( (int)$parlayResult['code'] === 0 ) 
        {
            foreach($parlayResult['games'] as $game)
            {
                $usedCards = 0;
                if(in_array($game->parlayCardId, array_keys($ids)))
                {
                    if($ids[$game->parlayCardId] >= $game->maxCardCount)
                        continue;
                    else
                        $usedCards = $ids[$game->parlayCardId];
                }
                $parlayGame = array( 'gameId' => 3, 'name' => "Daily Showdown" );
                $parlayCardId = $game->parlayCardId;

                $prizeList = array();                                
                $parlayCard = $this->parlaycard->order_by( 'dateTime' )->get_by( 'parlayCardId', $parlayCardId );
                
                $rs = $this->db->query("Select * from Payouts order by gameType, startRank");
                foreach($rs->result() as $row)
                {
                    if($row->gameType == $game->type)
                    {
                        switch($row->payType)
                        {
                            case 'Money': $prizeList[] = '$' . ($row->amount < 10000 ? number_format($row->amount, 2) : number_format($row->amount, 0)); break;
                            case 'Chedda': $prizeList[] = number_format($row->amount, 0); break;
                        }
                    }
                }

                $parlayGame['startTime'] = isset( $game->endDate ) ? date( 'm/d/Y g:i A', strtotime( '-15 minutes', strtotime( $game->endDate ) ) ) : null;
                $parlayGame['endTime']   = $parlayGame['startTime'];
                $parlayGame['prizeList'] = $prizeList;
                $parlayGame['theme'] = $game->type;
                $parlayGame['maxCards'] = $game->maxCardCount;
                $parlayGame['usedCards'] = $usedCards;
                $parlayGame['disclaimer'] = $game->disclaimer;
                $parlayGame['parlayCardId'] = $game->parlayCardId;
                $parlayGame['adPlacement'] = $game->adPlacement;
                $games[] = $parlayGame;                
            }
        }                
                
        if ( count ( $games ) > 0 )
        {
            $result = array( 'code' => 0, 'response' => $games, 'statusCode' => 200 );
            if($this->user->memcacheEnable)
            {
                $expire = strtotime("tomorrow -1 second");
                foreach($games as $game)
                    if(strtotime($game['endTime']) < $expire)
                        $expire = strtotime($game['endTime']);
                $this->user->updateMemcache('Key-Lobby-Parlay-' . $userType, $result, $expire - strtotime("now"));
            }
        }
        else
        {
            $result = array( 'code' => 1, 'response' => null, 'statusCode' => 200 );
        }
        
        // return object of lobby
        return $result;

    }    
}