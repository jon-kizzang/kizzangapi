<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GameCount extends MY_Model 
{

    // set table GameCount
    protected $_table = 'GameCount';

    protected $token = null;

    // will call convertData after get from db
    protected $after_get = array( 'convertData' );

    // set rule validation
    protected $validate = array(
        'gameType' => array(
            'field' => 'gameType',
            'label' => 'gameType',
            'rules' => 'required'
        ),
    );

    protected $public_attributes = array(
        'playerId',
        'playPeriodId',
        'foreignId',
        'gameType', 
        'theme',
        'count',
        'maxGames',
        'expirationDate'
    );

    public function setToken( $token ) 
    {
        //set token when success login
        $this->token = $token;
    }

    /**
     * convert data before return
     * @param  object $gameCount
     * @return object
     */
    public function convertData( $gameCount ) 
    {
        $intFields = array( 'id', 'playerId', 'playPeriodId', 'count' );

        // convert field in intFields array to int
        foreach ( $intFields as $field )         
            if ( isset( $gameCount->{$field} ) )
                $gameCount->{$field} = (int)$gameCount->{$field};
                    
        return $gameCount;
    }

    /**
     * get gamecount from database
     * @param  int $id
     * @return array or object
     */
    protected function getByIdFromDb( $id ) 
    {
        // get gameCount by Id
        $result = $this->get( $id );

        if ( empty($result) ) 
        {
            // return errors gameCount not found when result return empty
            return array( 'code' => 0, 'gameCount' => null, 'statusCode' => 200 );
        }
        else 
        {
            $result->code = 0;
            $result->statusCode = 200;

            // return object of gameCount by id from database 
            return $result;
        }
    }
    
    public function getByPlayPeriodId($playerPeriodId, $playerId)
    {
        // validate the id.
        if ( ! is_numeric($playerPeriodId) || $playerPeriodId <= 0 )
            return array( 'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );        
        
        return $this->getByPlayPeriodIdFromDb( $playerPeriodId, $playerId );
    }
    
    private function createZeroCounts($playPeriodId, $playerId, $slotCount = 0, $scratcherCount = 0)
    {
        $gameCounts = array();        
        $midnight = date("Y-m-d H:i:s", strtotime("tomorrow - 1 second"));
        $template = array(
                'playerId' => $playerId,                        
                'playPeriodId' => $playPeriodId,
                'count' => 0,
                'theme' => '',
                'foreignId' => 0,
                'expirationDate' => $midnight
        );
        
        $counts = array();
        $rs = $this->db->query("Select gameType, theme, max(count) as count from GameCount where playPeriodId = ? group by gameType, theme", array($playPeriodId));
        if($rs->num_rows())
        {
            foreach($rs->result() as $row)
            {                
                $counts[$row->gameType][($row->theme ? $row->theme : 'none')] = $row->count;
            }
        }
                
        $rs = $this->db->query("Select * from Game where gameType in ('Slot','ScratchCard')");
        foreach($rs->result() as $row)
        {
            switch($row->gameType)
            {
                case 'Slot': $gameCounts[] = array_merge($template, array('maxGames' => $row->maxGames, 'gameType' => 'SlotTournament', 'count' => $slotCount)); break;
                case 'ScratchCard': $gameCounts[] = array_merge($template, array('maxGames' => $row->maxGames, 'gameType' => 'ScratchCard', 'count' => $scratcherCount)); break;
            }
        }
        
        //Get the specific counts for single day parlay cards
        $rs = $this->db->query("Select p.parlayCardId, 'SportsEvent' as type, p.type as theme, p.maxCardCount as maxGames, count(c.id) as count, endDate as expirationDate 
            From SportParlayConfig p
            Left Join SportPlayerCards c on c.parlayCardId = p.parlayCardId and c.playerId = ? 
            Where cardDate in (?, ?) and isActive = 1 and p.type in ('ptbdailyshowdown','sicollegebasketball','sidailyshowdown','cheddadailyshowdown') and convert_tz(now(), 'GMT', 'US/Pacific') + INTERVAL 15 MINUTE < endDate
            Group by p.parlayCardId order by cardDate ASC
            ", array($playerId, date('Y-m-d'), date('Y-m-d', strtotime('tomorrow'))));

        $types = array();
        if($rs->num_rows())
        {
            foreach($rs->result() as $row)
            {
                if(in_array($row->theme, $types))
                    continue;
                
                if(isset($counts['SportsEvent']) && isset($counts['SportsEvent'][$row->theme]))
                    $row->count = $counts['SportsEvent'][$row->theme];
                
                $gameCounts[] = array_merge ($template, array('count' => $row->count, 'theme' => $row->theme, 'maxGames' => $row->maxGames, 'gameType' => $row->type, 'foreignId' => $row->parlayCardId, 'expirationDate' => date("Y-m-d H:i:s", strtotime($row->expirationDate) - 900)));
                $types[] = $row->theme;
            }
        }
        
        //Get the specific counts for multi day parlay cards
        $rs = $this->db->query("Select p.parlayCardId, 'SportsEvent' as type, p.type as theme, p.maxCardCount as maxGames, count(c.id) as count, endDate as expirationDate 
            From SportParlayConfig p
            Left Join SportPlayerCards c on c.parlayCardId = p.parlayCardId and c.playerId = ? and date(c.dateTime) = date(convert_tz(now(), 'GMT', 'US/Pacific')) 
            Where isActive = 1 and p.type in ('profootball','collegefootball2016','profootball2016') and convert_tz(now(), 'GMT', 'US/Pacific') between cardDate and endDate
            Group by p.parlayCardId order by cardDate ASC
            ", array($playerId));

        if($rs->num_rows())
            foreach($rs->result() as $row)
                $gameCounts[] = array_merge ($template, array('count' => $row->count, 'theme' => $row->theme, 'maxGames' => $row->maxGames, 'gameType' => $row->type, 'foreignId' => $row->parlayCardId, 'expirationDate' => $row->expirationDate));            
            
        $rs = $this->db->query("Select l.id as configId, 'Lottery' as type, l.numCards as maxGames, 
            if(l.cardLimit = 'Per Game', count(c.id), sum(if(date(convert_tz(c.created, 'GMT', 'US/Pacific')) = date(convert_tz(now(), 'GMT', 'US/Pacific')), 1, 0))) as count
            From LotteryConfigs l
            Left join LotteryCards c on l.id = c.lotteryConfigId and playerId = ?
            Where convert_tz(now(), 'GMT', 'US/Pacific') between startDate and endDate
            Group by l.id", array($playerId));        
        
        if($rs->num_rows())
            foreach($rs->result() as $row)
                $gameCounts[] = array_merge ($template, array('count' => $row->count, 'maxGames' => $row->maxGames, 'gameType' => $row->type, 'foreignId' => $row->configId));
        
        $rs = $this->db->query("Select c.id as configId, 'ROAL' as type, theme, 1 as maxGames, count(a.playerId) as count
            From ROALConfigs c
            Left join  ROALAnswers a on c.id = a.ROALConfigId and a.playerId = ? 
            Where c.cardDate = ?
            Group by c.id", array($playerId, date("Y-m-d")));
        
        if($rs->num_rows())
            foreach($rs->result() as $row)
                $gameCounts[] = array_merge ($template, array('count' => $row->count, 'maxGames' => $row->maxGames, 'theme' => $row->theme, 'gameType' => $row->type, 'foreignId' => $row->configId));
                
        //Check for duplicate calls because the APP isn't doing callback correctly
        $rs = $this->db->query("Select count(*) as cnt from GameCount where playPeriodId = ?", array($playPeriodId));
        if(!$rs->row()->cnt)
            $this->db->insert_batch("GameCount", $gameCounts);
    }
    
    private function getByPlayPeriodIdFromDb($playPeriodId, $playerId)
    {
        // get list gamecount by player id anf date time has create gameCount
        $gameCounts = $this->db->select('GameCount.*')
                        ->from('GameCount')
                        ->join('PlayPeriod', 'GameCount.playPeriodId = PlayPeriod.id', 'left')
                        ->join('Game', 'Game.gameType = GameCount.gameType', 'left')
                        ->where( 'GameCount.playPeriodId', $playPeriodId )
                        ->get()->result();
        
        if ( empty( $gameCounts ) ) 
        {

            // If not game counts are found in the db, then the player has not player a game yet
            // Return a game count of 0 in this case
            $this->createZeroCounts($playPeriodId, $playerId);
            $gameCounts = $this->db->select('GameCount.*')
                        ->from('GameCount')
                        ->join('PlayPeriod', 'GameCount.playPeriodId = PlayPeriod.id', 'left')
                        ->join('Game', 'Game.gameType = GameCount.gameType', 'left')
                        ->where( 'GameCount.playPeriodId', $playPeriodId )
                        ->get()->result();
        }                

        $intFields = array( 'id', 'playerId', 'playPeriodId', 'count', 'maxGames', 'foreignId' );

        foreach ( $gameCounts as &$gameCount ) 
        {
            if(!$gameCount->maxGames)
                $gameCount->maxGames = (int) 0;
            
            foreach ( $intFields as $field )
                if ( isset( $gameCount->{$field} ) )
                        $gameCount->{$field} = (int)$gameCount->{$field};
        }
        
        $result = array( 'code' => 0, 'tax_status' => $this->getCode($playerId), 'gameCounts' => $gameCounts, 'statusCode' => 200 );
        $key = "KEY-GameCount-$playerId-" . date("Y-m-d");
        $this->user->updateMemcache( $key, $result);
        
        return $result;        
    }
    
    private function getCode($playerId)
    {
        $code = 0;
        $rs = $this->db->query("Select accountStatus from Users where id = ?", array($playerId));
        if($rs->row()->accountStatus == "W2 Blocked")
        {
            $rs = $this->db->query("Select status from Winners where id in (Select max(id) from Winners where player_id = ?)", array($playerId));
            if($rs->num_rows())
            {
                $status = $rs->row()->status;
                if($status == "New")
                    $code = 1;
                elseif($status == "Document")
                    $code = 2;
            }
        }
        return $code;
    }
    
    public function getById( $id ) 
    {
        // validate the id.
        if ( ! is_numeric($id) || $id <= 0 )
            return array( 'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );        
        
        return $this->getByIdFromDb( $id );
    }
    
    public function getByPlayerIdFromDb( $playerId, $dateCreated ) 
    {    
        $rs = $this->db->query("Select playPeriodId from GameCount where date(expirationDate) = ? and expirationDate < convert_tz(now(), 'GMT', 'US/Pacific') and playerId = ?", array($dateCreated, $playerId));
        if($rs->num_rows())
        {
            $playPeriodId = $rs->row()->playPeriodId;
            $rs = $this->db->query("Select count from GameCount where playPeriodId = ? and gameType = 'SlotTournament'", array($playPeriodId));
            $slotCount = 0;
            if($rs->num_rows())
                $slotCount = $rs->row()->count;
            
            $rs = $this->db->query("Select count from GameCount where playPeriodId = ? and gameType = 'ScratchCard'", array($playPeriodId));
            $scratcher = 0;
            if($rs->num_rows())
                $scratcher = $rs->row()->count;
            
            $this->db->query("Delete from GameCount where playPeriodId = ?", array($playPeriodId));
            $this->createZeroCounts($playPeriodId, $playerId, $slotCount, $scratcher);
        }
        
        // get list gamecount by player id anf date time has create gameCount
        $rs = $this->db->query("Select g.* from GameCount g
            Inner join PlayPeriod p on g.playPeriodId = p.id and p.playDate = ?
            where date(expirationDate) >= ? and convert_tz(now(), 'GMT', 'US/Pacific') < expirationDate and g.playerId = ?", array($dateCreated, $dateCreated, $playerId));        
        
        if ( !$rs->num_rows() )
        {
            $rs = $this->db->query("Select id from PlayPeriod where playerId = ? and date(startDate) = ?", array($playerId, $dateCreated));
            if(!$rs->num_rows())
            {
                $this->load->model('playperiod');
                $playPeriod = $this->playperiod->add($playerId);                
            }
            else
            {
                $playPeriod = $rs->row();
            }
            $this->createZeroCounts($playPeriod->id, $playerId);
            $rs = $this->db->query("Select * from GameCount 
                where date(expirationDate) = ? and convert_tz(now(), 'GMT', 'US/Pacific') < expirationDate and playerId = ?", array($dateCreated, $playerId)); 
        }
        $gameCounts = $rs->result();
        
        $intFields = array( 'id', 'playerId', 'playPeriodId', 'count', 'maxGames' );

        foreach ( $gameCounts as &$gameCount ) 
        {
            if(!$gameCount->maxGames)
                $gameCount->maxGames = 0;

            foreach ( $intFields as $field )             
                if ( isset( $gameCount->{$field} ) )
                        $gameCount->{$field} = (int)$gameCount->{$field};                           
        }
        
        $result = array( 'code' => 0, 'tax_status' => $this->getCode($playerId), 'gameCounts' => $gameCounts, 'statusCode' => 200 );
        
        if($this->user->memcacheEnable)        
            $this->user->updateMemcache("Key-GameCount-$playerId-$dateCreated", $result);
        
        return $result;
    }

    public function  getByPlayerId( $playerId) 
    {
        $result = NULL;
        $dateCreated = date( 'Y-m-d' );
        return $this->getByPlayerIdFromDb( $playerId, $dateCreated );
    }

    protected function getCountCurrent( $playerId, $playPeriodId ) 
    {
        $query = $this->db->select('SUM(count) AS count', FALSE )
                ->where( 'playerId', $playerId )
                ->where( 'playPeriodId', $playPeriodId )
                ->get( 'GameCount' )
                ->row();

        $count = isset( $query->count ) ? $query->count : 0;

        return $count;
    }

    public function add( $playerId, $data ) 
    {
            $this->load->model('playperiod');
            
            $isValid = $this->user->checkActionOwner( $playerId );

            if ( is_array( $isValid ) )
                return $isValid;            

            if ( ! isset( $data['gameType'] ) || ! $data['gameType'] ) 
                return array( 'code' => 1, 'message' => 'The gameType is required', 'statusCode' => 400 );
                
            $id = 0;
            $result = array();            

            // init game count data
            $gameCountData = array(
                    'playerId' => $playerId,                    
                    'gameType' => $data['gameType'],
                    'theme' => isset($data['theme']) ? $data['theme'] : '',
                    'foreignId' => isset($data['foreignId']) ? $data['foreignId'] : 0,
                    'maxGames' => isset($data['maxGames']) ? $data['maxGames'] : 0,
                    'expirationDate' => isset($data['expirationDate']) ? $data['expirationDate'] : date("Y-m-d H:i:s", strtotime("tomorrow - 1 second"))
            );

            // check playperiod was created on current day
            $playPeriod = $this->playperiod->getByPlayerId( $playerId, 1, 0);

            // if play period not exists on current day
            if ( is_array( $playPeriod ) ) 
            {
                // create play period for current day
                $playPeriod = $this->playperiod->add( $playerId );

                if ( is_object( $playPeriod ) )     // in the case created successfully
                    $gameCountData['playPeriodId'] = $playPeriod->id;                                        
                else if ( isset( $playPeriod['id'] ) )  // in the case is exists playperiod on current day
                    $gameCountData['playPeriodId'] = $playPeriod['id'];                    

                $gameCountData['maxGames'] = $this->getMaxGames($gameCountData['gameType'], $gameCountData['foreignId']);
                $id = $this->insert( $gameCountData, TRUE );
            }           
            else 
            {
                // get gameCount by filed token, field dateCreate and gameType from databaase
                $result = $this->get_by( array( 'playPeriodId' => $playPeriod->id, 'gameType' => $data['gameType'], 'theme' => $gameCountData['theme'], 'foreignId' => $gameCountData['foreignId'] ) );                

                // exists game count with token in day
                if ( ! empty( $result ) && is_object( $result ) ) 
                {
                    $id = $result->id;                    
                    $isUpdated = $this->update( $id, array( 'count' => $result->count + 1 ), TRUE );
                    
                    if ( $isUpdated ) 
                    {
                        $result->statusCode = 200;
                        $result->count = $result->count + 1;
                        //$this->checkIfTimeForWheelAward( $playerId, $playPeriod );
                    }
                    else 
                    {
                        // get and log error message
                        $errorMessage = $this->db->_error_message();                           

                        // errors log return when update gameCount unsuccessful
                        $result = array( 'code' => 3, 'message' => $errorMessage, 'statusCode' => 400 );
                    }
                }
                else 
                {
                    $rs = $this->db->query("Select * from GameCount where gameType = ? and playPeriodId = ? and theme = ?", array($gameCountData['gameType'], $playPeriod->id, $gameCountData['theme']));
                    if($rs->num_rows())
                    {
                        $row = $rs->row();
                        $gameCountData['count'] = $row->count + 1;
                    }
                    else
                    {
                        $gameCountData['count'] = 1;
                    }
                    
                    $gameCountData['playPeriodId'] = $playPeriod->id;                    
                    $id = $this->insert( $gameCountData, TRUE );
                    if($this->user->memcacheEnable && $gameCountData['gameType'] == 'SportsEvent')
                        $this->user->memcacheInstance->delete('Key-Lobby-Parlay');
                }
            }
            
            $this->user->memcacheInstance->delete("Key-GameCount-$playerId-" . date("Y-m-d"));

            if ( $id ) 
            {
                if ( is_array( $result ) ) 
                {                    
                    $result = $this->getById( $id );
                    $result->statusCode = 201;
                }

                $count = $this->getCountCurrent( $playerId, $playPeriod->id );
                $this->playperiod->edit( $playerId, $playPeriod->id, $count );
            }
            else 
            {
                $result = array( 'code' => 4, 'message' => $this->db->_error_message(), 'statusCode' => 400 );
            }            

            // return object after add success/ array log errors
            return $result;

    }
    
    private function getMaxGames($type, $foreignId)
    {
        switch($type)
        {
            case 'SlotTournament':
            case 'ScratchCard':
            case 'SportsEvent':
                if($foreignId)
                    continue;
                $rs = $this->db->query("Select maxGames from Game where gameType = ?", array($type)); break;
            
            case 'SportsEvent':           
                $rs = $this->db->query("Select maxCardCount as maxGames from SportParlayConfig where parlayCardId = ?", array($foreignId)); break;
            
            case 'lottery':
                $rs = $this->db->query("Select if(cardLimit = 'Per Day', numCards, numCards - count(c.id)) as maxGames 
                    from LotteryConfigs l
                    left join LotteryCards c on c.lotteryConfigId = l.id
                    where l.id = ? group by l.id", array($foreignId));
                break;
            default: $rs = $this->db->query("Select 0 as maxGames"); break;
        }
        if($rs->num_rows())
            return $rs->row()->maxGames;
        return 0;
    }
   
    protected function getGameCountToken() 
    {
        if ( $this->token ) 
        {
            $decodeTokenSession = base64_decode( $this->token );
            $tokenSessionArray = explode( User::ENCRYPTION_KEY, $decodeTokenSession );

            if ( count( $tokenSessionArray ) == 3 ) 
            {
                $tokenGameCount = $this->user->base64Encode( array( $tokenSessionArray[0], $tokenSessionArray[1] ) );
                return $tokenGameCount;
            }
        }
        return null;
    }

    public function getFavoriteGame( $playerId ) 
    {     
            $gameType = '';

            $gameCount = $this->db->select( 'sum(count) AS count, gameType', FALSE)
                                    ->where( 'playerId', $playerId )
                                    ->group_by( 'gameType' )
                                    ->order_by( 'count', 'DESC' )
                                    ->limit( 1 )
                                    ->get( $this->_table )
                                    ->row();

            // if found game count
            if ( ! empty( $gameCount ) ) 
                $gameType = $gameCount->gameType;

            return $gameType;
    }
  
    public function playerFavoriteGame( $playerId ) 
    {
        $favoriteGame = $this->getFavoriteGame( $playerId );
        return array( 'code' => 0, 'favoriteGame' => $favoriteGame, 'statusCode' => 200 );
    }
}