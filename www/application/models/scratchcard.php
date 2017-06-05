<?php

use Aws\Common\Aws;

class Scratchcard extends MY_Model {
    
    // set table
    protected $_table = 'Scratch_GPGames';

	// set scratch card db
	protected $_scratch_card_db = "ebdb";
	
    protected $token = null;

	// set db group
	protected $_scratch_card_group = "scratchcardsdb";
	
    // set validations rules
    protected $validate = array(

        // verify what is required
        'TotalCards' => array(
            'field' => 'TotalCards',
            'label' => 'Total Cards',
            'rules' => 'required|greater_than[0]'
        ),
        'EndDate' => array(
            'field' => 'EndDate',
            'label' => 'End Date',
            'rules' => 'valid_datetime'
        ),
        'SerialNumber' => array(
            'field' => 'SerialNumber',
            'label' => 'Serial Number',
            'rules' => 'required'
        ),
        'WinAmount' => array(
            'field' => 'WinAmount',
            'label' => 'Win Amount',
            'rules' => 'regex_match["^[\-+]?\d+\.\d+$"]'
        ),
    );

    protected $public_attributes = array(
        'ID',
        'TotalCards',
        'EndDate',
        'SerialNumber',
        'WinAmount'
    );
    
    private $redis;

    function __construct() {

        parent::__construct();
        
        // load database utility
        $this->load->dbutil();
        $this->db = $this->load->database("scratchcardsdb", true);
        $this->redis = self::createRedis();

    }
    
    static function createRedis()
    {
        return new \Predis\Client(['scheme' => 'tcp', 'host'   => getenv("REDIS_HOST"), 'port'   =>  getenv("REDIS_PORT")]);
    }

    /**
     * set token to get email
     * @param string $token
     * @return none
     */
    public function setToken( $token ) {

        $this->token = $token;
    }
       
    /**
    * create a key for use by the scratch card server
    * @param  $data post data
    * @return array
    */
	public function getCard( $data )
	{

            if ( array_key_exists( 'playerId', $data ) && array_key_exists( 'serialNumber', $data ) && array_key_exists( 'wheelId', $data ) ) 
            {
                $playerId = $data['playerId'];
                $serialNumber = $data['serialNumber'];

                if ( $this->memcacheEnable && $this->token ) 
                {

                    $this->load->model( 'gamecount' );

                    // Set tokens for needed models
                    $this->user->setToken( $this->token );
                    $this->gamecount->setToken( $this->token );

                    $scratchCardResult = $this->getACard($serialNumber, $playerId);

                    if ( $scratchCardResult !== FALSE )
                    {

                        // JSON encode the return string
                        $orig = $scratchCardResult;

                        $scratchCardResult = json_decode( $scratchCardResult );
                        if ($scratchCardResult->status == "Error") 
                        {
                           return array( 'code' => 2, 'message' => $scratchCardResult->message, 'statusCode' => $scratchCardResult->statusCode );
                        }
                        
                        $config = $data;
                        $data = $scratchCardResult->data;

                        //added null check for invalid json reponses from Scratch Server
                        if ( is_object( $data ) && ( $data->CanPlay == true ) )
                        {
                            // Increment game count for scratch card game type. Each key retrieved is considered a game
                            // count increment.
                            $countData = array( 'gameType' => 'ScratchCard' );
                            $countResponse = $this->gamecount->add( $playerId, $countData );
                            if ( is_array($countResponse ) )
                            {
                                // Error incrementing count
                                $result = array( 'code' => 1, 'message' => 'Valid card received, unable to increment game count', 'cardId' => $data->CardNumber, 'statusCode' => 400 );
                            }
                            else {

                                if ( ( $data->WinAmount > 0 ) && ( $data->WinRank > 0 ) )
                                        $eventResult = $this->addEvent ( $data, $playerId );
                                else 
                                        $eventResult = NULL;
                                
                                // Add the eventId to the response
                                if ( is_object( $eventResult ) )
                                        $eventId = $eventResult->id;
                                else
                                        $eventId = -1;

                                $result = array( 'code' => 0, 'scratchcard' => $data, 'eventId' => $eventId, 'statusCode' => 200 );

                            }
                        }
                        else
                            $result = array( 'code' => 2, 'message' => 'This game is not available or has expired:'.$orig, 'statusCode' => 400 );
                    }
                    else {
                        $result = array( 'code' => 3, 'message' => 'Unable to get a scratch card for this game', 'statusCode' => 400 );
                    }
                }
                else {
                    $result = array( 'code' => 4, 'message' => 'memcache not enabled', 'statusCode' => 400 );
                }
            }
            else
                $result = array( 'code' => 5, 'message' => 'missing post data', 'statusCode' => 400 );

            return $result;
	}
    
        public function getAll($limit, $offset)
        {
            //Code to get the player ID
            if ( isset( $_SERVER['HTTP_TOKEN'] ) )
                $this->token = $_SERVER['HTTP_TOKEN'];
                        
            $player = $this->sessions->getPlayerData($this->token);
            if(!$player)
                return array('code' => 1, 'message' => 'Invalid Player Token', 'statusCode' => 500);
            
            $playerId = $player['playerId'];
            
            if ( $this->memcacheEnable ) 
            {
                $date = date("Y-m-d");
                $key = "KEY-ScratchCardGames-$date-$playerId";

                // the first at all, get the result from memcache
                $result = $this->memcacheInstance->get( $key );
                $result = NULL;
                if ( ! $result ) 
                {
                    // if empty result, will get all sweepstale from database
                    $result = $this->getAllFromDatabase( $playerId, $limit, $offset );

                    // set the result to memcache
                    $this->user->updateMemcache( $key, $result );   
                }
            } 
            else 
            {
                // return an array
                $result = $this->getAllFromDatabase( $limit, $offset );
            }

            return $result;
        }
                        
	private function addEvent( $data, $playerId )
	{
               $this->load->model( 'eventnotification' );
               $this->load->model('winner');
               $db = $this->load->database("default", true);
               
               $previous_win = 0;
               $win = $data->WinAmount;
               $rs = $db->query("Select sum(amount) as amount from Winners where player_id = ? and year(created) = year(now())", array($playerId));
               if($rs->num_rows())
                   $previous_win = $rs->row()->amount;
                
               $time = 2880;
               $rs = $db->query("Select numMinutes from GameExpireTimes where game = 'scratchCard' and ? between lowAmount and highAmount LIMIT 1", array($win));
               if($rs->num_rows())
                   $time = $rs->row()->numMinutes;
               $config = $this->getConfigDB ($data->SerialNumber);
		
               $winner_data = array('player_id' => $playerId,
                   'game_type' => 'Scratchers',
                   'foreign_id' => $data->CardNumber,
                   'serial_number' => $data->SerialNumber,
                   'prize_name' => $data->WinName,
                   'amount' => $data->WinAmount,
                   'processed' => 1,
                   'game_name' => $config->Name,
                   'order_num' => $data->CardNumber,
                   'expirationDate' => date("Y-m-d H:i:s", strtotime("+" . $time . " minutes")));
               
               $w2Blocked = false;
               if($previous_win + $win >= 600)
               {
                    $rs = $db->query("Select s.id from rightSignature.signins s
                        Inner join rightSignature.templates t on t.id = s.templateId and t.type in ('W9','Notarize') 
                        Where s.playerId = ? and YEAR(now()) = YEAR(s.created) and status = 'Complete'", array($playerId));
                    if(!$rs->num_rows())
                        $w2Blocked = true;                                      
               }
               
               if($w2Blocked)
                   $db->query("Update Users set accountStatus = 'W2 Blocked' where id = ?", array($playerId));
               
               $winnerResult = $this->winner->add($winner_data);
               
                // Append the app version number to each data event
                $eventData = array(
                    'data' => json_encode( array( 'serialNumber' => $data->SerialNumber, 
                                                              'entry' => $winnerResult->id, 
                                                              'prizeAmount' => $data->WinAmount,
                                                              'prizeName' => $data->WinName,
                                                              'gameName' => $data->Name
                                                        ) ),
                    'type' => 'scratchCard',
                    'buttonType' => 1,
                    'pending' => 1,
                    'playerId' => $playerId,
                    'expireDate' => date("Y-m-d H:i:s", strtotime("+" . $time . " minutes"))
                );

               $eventResult = $this->eventnotification->add( $eventData, $playerId );                              
		
               return $eventResult;
	}
		
    protected function getAllFromDatabase( $playerId, $limit, $offset ) 
    {            		
            $date = date("Y-m-d");
            $key = "KEY-Scratchcard-Wins-$date-$playerId";
            // Get all games that are deployed to the app
            $rs = $this->db->query("Select g.*, max(p.PrizeAmount) as PrizeAmount, 'Open' as Status from Scratch_GPGames g
                Inner join Scratch_GPPayout p on g.PayoutID = p.PayoutID
                where convert_tz(now(), 'GMT', 'US/Pacific') between StartDate and EndDate AND (DeployWeb = 1 OR DeployMobile = 1)
                Group by g.ID 
                order by max(p.PrizeAmount) ASC");
            $games = $rs->result();
            
            if(!$games)
                return array ( 'code' => 1, 'message' => 'No Records Found', 'statusCode' => 404);

            $themes = $campaign = NULL;
            
            $campaign = $this->_get_session_memcache('referralCode');
            if($campaign)
            {
                $conn = $this->load->database('default', true);
                $rs = $conn->query("SELECT group_concat(Theme) as themes FROM kizzang.AffiliateGames where Sponsor_Advertising_Campaign_Id = ?", array($campaign));
                $themes = $rs->row()->themes;
            }
            
            foreach($games as &$game)
            {
                if(is_numeric($game->WinAmount))
                {
                    if($game->WinAmount < 1000)
                        $game->WinAmount = '$' . number_format ($game->WinAmount, 2);
                    else
                        $game->WinAmount = '$' . number_format ($game->WinAmount, 0);
                }
                
                //Affiliate Code
                if($game->CardType == 'Affiliate')
                {
                    if($themes && stristr($themes, $game->Theme) !== false)
                        $game->Status = 'Open';
                    else
                        $game->Status = 'Locked';
                }
            }
            
            $wins = $this->memcacheInstance->get($key);
            if($wins)
            {
                $temp = array();                
                foreach($games as $key => $row)
                {
                    if(!in_array ($row->SerialNumber, $wins))
                        $temp[] = $row;
                }                
                $games = $temp;
            }

            $results = array ('game_list' => $games, 'limit' => (int)$limit, 'offset' => (int)$offset, 'statusCode' => 200);

            return $results;
    }
    
    private function getACard($serial_number, $player_id)
    {
         //Get the Redis counts for this serial number
        $date = date("Y-m-d");
        $config_key = md5("ScratchConfiguration" . $serial_number);
        if($this->redis->exists($config_key.":CardCount"))
        {
            $card_count = $this->redis->get($config_key.":CardCount");
        }
        else
        {
            $card_count = 1;
            $this->redis->set($config_key.":CardCount", 1);
        }
        
        if($this->redis->exists($config_key.":CardCount"))
        {
            $win_count = $this->redis->get($config_key.":WinCount");
        }
        else
        {
            $win_count = 1;
            $this->redis->set($config_key.":WinCount", 1);
        }
        
        if($this->redis->exists("PlayerCardCount:" . $date . "-" . $player_id ))
        {
            $cards_played = $this->redis->exists("PlayerCardCount:" .$date . "-" . $player_id );
            if($cards_played > 49)
                return json_encode(array('status' => 'Error', 'message' => '50 Card Limit Reached', 'statusCode' => 200));
        }
        else
        {
            $this->redis->set("PlayerCardCount:" . $date . "-" . $player_id, 1);
        }
        
        //Check to see if config is in memcache / DB
        if($this->memcacheEnable)
        {            
            $config = $this->memcacheInstance->get($config_key);
            if(!$config)
            {
                $config = $this->getConfigDB ($serial_number);
                if(!$config)
                    return json_encode(array('status' => 'Error', 'message' => 'SerialNumber does not exist in the DB', 'statusCode' => 200));
                $this->memcacheInstance->set($config_key, $config, 43200);
                $this->generateMemcacheWinners($serial_number, $config->PayoutID, $card_count);
            }
        }
        else
        {
            return json_encode(array('status' => 'Error', 'message' => 'Memcache failed and config not found.', 'statusCode' => 200));
        }
        
        //Check to see if the card is currently valid
        if(strtotime($config->StartDate) > strtotime("now") && strtotime($config->EndDate) < strtotime("now"))
            return json_encode(array('status' => 'Error', 'message' => 'SerialNumber as Expired', 'statusCode' => 200));               
       
        if($card_count > $config->TotalCards)
            return json_encode(array('status' => 'Error', 'message' => 'Card Supply has been Exhausted', 'statusCode' => 200));
        
        //Check for the loser ticket and if it isn't there, then recreate all the winners
        if(!$this->memcacheInstance->get("L" . $serial_number))        
            $this->generateMemcacheWinners($serial_number, $config->PayoutID, $card_count);
                
        $rec = $this->memcacheInstance->get($serial_number . "-" . $card_count);
        $this->redis->incr($config_key.":CardCount");
        //If Winner
        if($rec)
        {            
            $this->redis->incr($config_key.":WinCount");
            $card = $this->generateWinningCard($config, json_decode($rec), $player_id);
            
            //Add to wins to memcache
            $date = date("Y-m-d");
            $key = "KEY-Scratchcard-Wins-$date-$player_id";
            $wins = $this->memcacheInstance->get($key);            
            $wins[] = $serial_number;
            $this->memcacheInstance->set($key, $wins);
            $this->memcacheInstance->delete("KEY-ScratchCardGames-$date-$player_id");
        } 
        else //Else Loser
        {
            $rec = $this->memcacheInstance->get("L" . $serial_number);
            if(!$rec)
                return array('status' => 'Error', 'message' => 'L Record Not Found', 'statusCode' => 200);
            $card = $this->generateLosingCard($config, $card_count, json_decode($rec), $player_id);
        }
        
        if(!$this->logPlay($serial_number, $player_id, $card_count))
        {
            //$this->redis->decr($config_key.":CardCount");
            $row = json_decode($rec);
            if($row->PrizeRank)
                $this->redis->decr($config_key.":WinCount");
            return json_encode(array('status' => 'Error', 'message' => 'Possible Duplicate Key in Log Table', 'statusCode' => 200));
        }
        
        return json_encode(array('status' => 'Success', 'data' => $card, 'statusCode' => 200));
    }
    
    public function killKey($serial_number)
    {
        $config = $this->getConfigDB ($serial_number);
        $config_key = md5("ScratchConfiguration" . $serial_number);
        $data = $this->memcacheInstance->get("L" . $serial_number);
        
        $card_count = $this->redis->get( $config_key.":CardCount" );
        $win_count = $this->redis->get( $config_key.":WinCount" );

        $this->generateMemcacheWinners($serial_number, $config->PayoutID, $card_count);

        return array('success' => true, 'result' => $data ? true : false, 'card_count' => $card_count, 'win_count' => $win_count);
    }
    
    private function logPlay($serial_number, $player_id, $scratch_id)
    {
        $config_key = md5("ScratchConfiguration" . $serial_number);
        $rs = $this->db->query("Select * from Scratch_GPPlays where SerialNumber = ? and ScratchId = ?", array($serial_number, $scratch_id));
        if(!$rs->num_rows())
        {
            $rec = array('SerialNumber' => $serial_number, 'PlayerId' => $player_id, 'ScratchId' => $scratch_id, 'TimeStamp' => date('Y-m-d H:i:s'), 'Location' => 0);
            $this->db->insert('Scratch_GPPlays', $rec);                    
        }
        else
        {
            $rs = $this->db->query("Select max(ScratchId) as scratchId from Scratch_GPPlays where SerialNumber = ?", array($serial_number));
            if($rs->num_rows())
            {
                $row = $rs->row();
                $this->redis->set($config_key.":CardCount", $row->scratchId + 1);
            }
            return false;
        }
        return true;
    }
    
    private function generateWinningCard($game, $temp, $playerId)
    {
        $this->load->model('chedda');
        $this->load->model('configs');
        $card = new stdClass();
        $card->CanPlay = true;
        $card->PlayAgainIn = (int) $game->PlayInterval;
        $card->WinAmount = $temp->Amount;
        $card->WinRank = (int) $temp->PrizeRank;
        $card->CardNumber = (int) $temp->CardNumber;
        $card->PaySchedule = $game->payments;
        $card->WinName = $temp->Name;
        $card->SerialNumber = $game->SerialNumber;
        $card->Values = (int) $temp->Values;
        $card->Name = $game->Name;
        $chedda = 0;
         $k = rand(2,4);
        for($i = 0; $i < $k; $i++)
        {
                $bit_value = 1;
                $bit = rand(0, $game->SpotsOnCard - 1);
                $bit_value = $bit_value << $bit;
                if($bit_value & $card->Values || $bit_value & $chedda)
                        $i--;
                else
                        $chedda = $chedda | $bit_value;
        }
        $card->CheddaBF = $chedda;
        $card->CheddaArray = array();
        $card->CheddaNum = 0;
        
        $config = $this->configs->getConfigElement('File', 'Chedda');
        $config->items = json_decode($config->info, true);
        $expArray = array();
        foreach($config->items as $index => $value)        
            for($i=0; $i < $value; $i++)
                $expArray[] = $index;
        
        $totalChedda = $expArray[rand(0, count($expArray) - 1)];
        $card->CheddaNum = $totalChedda;
        for($i = 0; $i < $k; $i++)
        {
            if($i == $k - 1)
            {
                $card->CheddaArray[] = $totalChedda;
            }
            else
            {
                $rand = rand(floor(($totalChedda / ($k - $i)) / 2),floor($totalChedda / ($k - $i)));
                $totalChedda -= $rand;
                $card->CheddaArray[] = $rand;
            }
        }        
        
        $rec = array('playerId' => $playerId, 'gameKey' => 'Scratcher-' . $temp->CardNumber . '-' . $game->SerialNumber, 'isUsed' => 0, 'count' => $card->CheddaNum);
        $ret = $this->chedda->add($rec);
        
        return $card;
    }


    // This function generates a losing card based on the game
    private function generateLosingCard($game, $pick_id, $temp, $playerId)
    {
        //error check
        if (!isset($temp->Amount) || !isset($temp->PrizeRank) || !isset($temp->Name)) 
        {
            return null;
        }

        $this->load->model('chedda');
        $this->load->model('configs');
        $card = new stdClass();
        $card->CanPlay = true;
        $card->PlayAgainIn = $game->PlayInterval;
        $card->WinAmount = $temp->Amount;
        $card->WinRank = $temp->PrizeRank;
        $card->CardNumber = $pick_id;
        $card->PaySchedule = $game->payments;
        $card->WinName = $temp->Name;
        $card->SerialNumber = $game->SerialNumber;
        $value = 0;
        $chedda = 0;
        $j = 0;
        for($i = 1; $i < $game->WinningSpots; $i++)
        {
                $bit_value = 1;
                $bit = rand(0, $game->SpotsOnCard - 1);
                $bit_value = $bit_value << $bit;
                if($bit_value & $value)
                        $i--;
                else
                        $value = $value | $bit_value;
        }
        
        $k = rand(3,5);
        for($i = 0; $i < $k; $i++)
        {
                $bit_value = 1;
                $bit = rand(0, $game->SpotsOnCard - 1);
                $bit_value = $bit_value << $bit;
                if($bit_value & $value || $bit_value & $chedda)
                        $i--;
                else
                        $chedda = $chedda | $bit_value;
        }
        
        $card->Values = $value;
        $card->CheddaBF = $chedda;
        $card->CheddaArray = array();
        $card->CheddaNum = 0;
        
        $config = $this->configs->getConfigElement('File', 'Chedda');
        $config->items = json_decode($config->info, true);
        $expArray = array();
        foreach($config->items as $index => $value)        
            for($i=0; $i < $value; $i++)
                $expArray[] = $index;
                
        $totalChedda = $expArray[rand(0, count($expArray) - 1)];
        
        $card->CheddaNum = $totalChedda;
        for($i = 0; $i < $k; $i++)
        {
            if($i == $k - 1)
            {
                $card->CheddaArray[] = $totalChedda;
            }
            else
            {
                $rand = rand(floor(($totalChedda / ($k - $i)) / 2),floor($totalChedda / ($k - $i)));
                $totalChedda -= $rand;
                $card->CheddaArray[] = $rand;
            }
        }        
        
        $rec = array('playerId' => $playerId, 'gameKey' => 'Scratcher-' . $pick_id . '-' . $game->SerialNumber, 'isUsed' => 0, 'count' => $card->CheddaNum);
        $ret = $this->chedda->add($rec);        
        //print_r($ret); die();
        
        return $card;
    }
    
    private function generateMemcacheWinners($serial_number, $payout_id, $card_count)
    {
        //Create memcache key to indicate this is going on
        $this->memcacheInstance->set("Building-" . $serial_number, $serial_number);
        
        //Generate the Losing Card first
        $rs = $this->db->query("SELECT PrizeAmount as Amount, Rank as PrizeRank, PrizeName as Name 
            FROM  Scratch_GPPayout
            WHERE PayoutID = ? AND Rank = 0", 
            array($payout_id));
        
        if (!$rs->num_rows()) 
            return false;
             
        $memcache_key = "L".$serial_number ;
        $this->memcacheInstance->set( $memcache_key,  json_encode($rs->row()));
        
        $rs = $this->db->query("SELECT p.PrizeAmount as Amount, PrizeRank, `Values`, 
                       CardNumber, ScratchID, p.PrizeName as Name 
                FROM Scratch_GPCards c
                INNER JOIN Scratch_GPPayout p 
                ON p.Rank = c.PrizeRank AND p.PayoutID = ?
                WHERE c.SerialNumber = ? AND PrizeRank > 0 AND ScratchID between ? and ?", array($payout_id, $serial_number, $card_count - 10, $card_count + 1000000));
        
        if($rs->num_rows())        
            foreach($rs->result() as $row)
                $this->memcacheInstance->set($serial_number . "-" . $row->ScratchID, json_encode($row));
        
        $this->memcacheInstance->delete("Building-" . $serial_number);
    }
    
    private function getConfigDB($serial_number)
    {
        //Get main config
        $rs = $this->db->query("Select * from Scratch_GPGames where SerialNumber = ?", array($serial_number));
        
        if(!$rs->num_rows())
            return false;
        $config = $rs->row();
        
        //Get the payments for the game
        $rs = $this->db->query("Select * from Scratch_GPPayout where PayoutID = ? order by Rank ASC", array($config->PayoutID));
        if(!$rs->num_rows())
            return false;
        
        foreach($rs->result() as $row)
            $config->payments[$row->Rank] = $row;
        
        return $config;
    }
    
    //This function is to get the overall status of the Scratch Server
    public function getStatus()
    {
        $errors = array();
        $rs = $this->db->query("Select * from Scratch_GPGames where convert_tz(now(), 'GMT', 'US/Pacific') between StartDate and EndDate");
        foreach($rs->result() as $game)
        {
            $config_key = md5("ScratchConfiguration" . $game->SerialNumber);
            
            //Get the Redis counts for this serial number
            $card_count = $this->redis->get($config_key.":CardCount");
            $win_count = $this->redis->get($config_key.":WinCount");
            
            if(!$card_count)
                $errors[] = "Card Count for " . $game->SerialNumber . " is 0";
            
            if(!$this->memcacheInstance->get("L" . $game->SerialNumber))
                $errors[] = "Loser card not in Memcache for " . $game->SerialNumber;
            
            //Randomly select 3 winners and see if they are in Memcache
            $rs = $this->db->query("Select * Scratch_GPCards where SerialNumber = ? and ScratchID > ? order by rand() limit 3", array($game->SerialNumber, $card_count));
            if($rs->num_rows())
            {
                foreach($rs->result() as $row)
                    if(!$this->memcacheInstance->get($game->SerialNumber . "-" .  $row->ScrachID))
                        $errors[] = "Winning Card for " . $game->SerialNumber . " Found in DB and not in Memcache";
            }
            else
            {
                $errors[] = "No more winning cards for " . $game->SerialNumber;
            }
            
            $rs = $this->db->query("Select max(ScratchId) as max from Scratch_GPPlays where SerialNumber = ?", array($game->SerialNumber));
            if($rs->row()->max <= $card_count)
                $errors[] = "Possible Redis issue for " . $game->SerialNumber . ": Redis = " . $card_count . "|| DB = " . $rs->row()->max;
                        
        }
        return $errors;
    }
}   
