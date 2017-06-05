<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Winner extends MY_Model {

	// Use for fetching values from the db and updating memcache instead of
	// using memcache directly if a key already exists. Helpful for testing.
	private $testing = FALSE;
    
    // set table is Winners
    protected $_table = 'Winners';

    // set validations rules
    protected $validate = array(
        'player_id' => array( 
            'field' => 'player_id', 
            'label' => 'player_id',
            'rules' => 'required|greater_than[0]'
        ),
        'foreign_id' => array( 
            'field' => 'foreign_id', 
            'label' => 'foreign_id',
            'rules' => 'required|greater_than[0]'
        ),
        'game_type' => array( 
            'field' => 'game_type', 
            'label' => 'game_type',
            'rules' => 'required|valid_game_type'
        ),
        'serial_number' => array( 
            'field' => 'serial_number', 
            'label' => 'serial_number',
            'rules' => 'required'
        ),
        'amount' => array( 
            'field' => 'amount', 
            'label' => 'amount',
            'rules' => 'required|decimal'
        ),
        'processed' => array( 
            'field' => 'processed', 
            'label' => 'processed',
            'rules' => 'required|numeric'
        ),
    );

    protected $public_attributes = array(
            'id',
            'player_id',
            'foreign_id',
            'game_type',
            'serial_number',
            'prize_name',
            'amount',
            'processed',
            'order_num',
            'game_name',
            'expirationDate',
            'created',
            'updated'
        );

    /**
     * get Winner from database
     * @param  int $id
     * @return array or object
     */
    protected function getByIdFromDb( $id ) 
    {
        // get object Winner by id from database
        $result = $this->get( $id );

        if ( empty($result) ) 
        {
            // return log errors when return empty result
            $errors = array( 'code' => 1, 'message' => 'Winner Not Found', 'statusCode' => 200 ); 
            return $errors; 
        } 
        else 
        {
            $result->statusCode = 200;            
            return $result;
        }
    }

    /**
    * get Winner by id
    * @param  int $id Winner id
    * @return array
    */
    public function getById( $id ) 
    {
        // validate the id.
        if ( ! is_numeric( $id ) || $id <= 0 ) 
        {    
            // return log errors when id input is invalid
            $error = array(  'code' => 1, 'message' => 'Id must be a numeric and greater than zero', 'statusCode' => 400 );
            
            // array errors
            return $error; 
        }

        if ( $this->memcacheEnable ) 
        {
            $key = "KEY-Id-Winner-$id";

            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );
            
            if ( ! $this->testing && $result ) return $result;
        }

        // get Winner from database if empty on memcache
        $result = $this->getByIdFromDb( $id );

        if ( $this->memcacheEnable && is_object( $result ) ) 
        {
            // set the result to memcache for use later
            $this->user->updateMemcache( $key, $result );
        }

        // return object of Winner
        return $result;

    }

    /**
    * get all teams from database
    * @param  int $limit
    * @param  int $offset
    * @param  int $playerId
    * @return array
    */
    protected function getAllFromDatabase( $playerId, $limit, $offset ) 
    {       
        // get all sport teams is not deleted from database by offset and limit
        $this->db->query("SET @@session.time_zone = 'US/Pacific';");
        $winners = $this->limit( $limit, $offset )->order_by('created', 'DESC')->get_many_by( array('player_id' =>$playerId, 'processed' => 1) );

        if ( empty( $winners ) ) 
        {
            // return log errors when sport teams return null
            $result = array( 'code' => 1, 'message' => 'Winner Not Found with Player Id ' . $playerId, 'statusCode' => 200 );
        }
        else 
        {    
            $count = $this->count_all();
            //$count = count($winners);

            // return all list of winners
            $result = array( 'code' => 0, 'count' => $count, 'winners' => $winners, 'limit' => (int)$limit, 'offset' => (int)$offset ,'statusCode' => 200 );
        }

        return $result;
    }

    /**
    * get all winner
    * @param int $limit
    * @param int $offset
    * @param int $playerId
    * @return array
    */
    public function getAll( $playerId, $limit, $offset ) 
    {

        if ( $this->memcacheEnable ) 
        {            
            $key = "KEY-Winner-$playerId-$limit-$offset";

            // the first at all, get the result from memcache
            $result = NULL; //$this->memcacheInstance->get( $key );

            if ( ! $this->testing && $result ) return $result;

        }

        // return an array
        $result = $this->getAllFromDatabase( $playerId, $limit, $offset );

        if ( $this->memcacheEnable && (int)$result['code'] === 0 ) {

            $this->user->updateMemcache( $key, $result );
        }

        return $result;
    }

    public function add( $data ) {

        // validate data insert 
        if ( empty( $data ) ) {

            // return log error when data miss/ invalid
            $errors = array( 'code' => 1, 'message' => 'Please the required enter data', 'statusCode' => 400 );

            return $errors;
        } 
        else 
        {
             // reset errors messages
            $this->form_validation->reset_validation();

            // set data for all field to validation
            $this->form_validation->set_params( $data );

            // set rules validation
            $this->form_validation->set_rules( $this->validate );

            if ( $this->form_validation->run() === FALSE ) 
            {
                $errors = $this->form_validation->validation_errors();

                // return result errors log
                $result = array( 'code' => 2, 'message' => $errors, 'statusCode' => 400 );
            } 
            else 
            {                
                // set skip_validation = TRUE in 2nd parameter
                $insertId = $this->insert( $data, TRUE );

                if ( $insertId ) 
                {                    
                    // get object Winner by id 
                    $result = $this->getById( $insertId );
                    $result->statusCode = 201;
                } 
                else 
                {
                    // get and log error message
                    $result = array( 'code' => 3, 'message' => $errorMessage, 'statusCode' => 400 );
                }   
            }
        }

        // return object Winner
        return $result;
    }   
    
    /**
    * get instant winner
    * @param int $playerId
    * @return array
    */
    public function getInstantWinner( $playerId ) 
    {
        $list = NULL;
        $today = date("Y-m-d");
        if ( $this->memcacheEnable ) 
        {            
            $key = "KEY-InstantWinner-$playerId-$today";

            // the first at all, get the result from memcache
            $list = $this->memcacheInstance->get( $key );            
        }
        
        if(!$list)
        {
            $this->load->model("player");
            $rs = $this->db->query("Select p.screenName, w.* 
                from Winners w
                Inner join Players p on p.id = w.player_id
                where date(created) = ? and processed = 1 order by created", array($today));
            $list = array();
            $winners = $rs->result();            

            foreach($winners as $winner)        
            {                   
                $rec = array('playerName' => $winner->screenName, 'prize' => $winner->prize_name, 
                    'gameName' => $winner->game_name, 'gameType' => $winner->game_type, 'mapDay' => 0);
                $player = $this->user->getByIdFromDb($winner->player_id);                
                
                $rec['state'] = isset($player->accountData['state']) ? $player->accountData['state'] : "";
                $rec['city'] = isset($player->accountData['city']) ? $player->accountData['city'] : "";
                if($winner->game_type == "Slots")
                    $rec['tournamentType'] = "Daily";
                else
                    $rec['tournamentType'] = "";
                $list[] = $rec;
            }
             $this->user->updateMemcache( $key, $list, 300 );
        }
        
        //Grab the current index for this player in Redis
        $redis = self::createRedis();
        $rkey = $playerId . "-" . $today;
        
        if($redis->exists($rkey))
        {
            $index = $redis->get($rkey);
            $redis->incr($rkey);
        }
        else
        {
            $index = 0;
            $redis->set($rkey, 0);
        }
        
        // return an array
        if(isset($list[$index]))
        {
            $result = array( 'code' => 0, 
                             'winner' => $list[$index],
                             'statusCode' => 200 );
        }
        else //No more entries for the day
        {
            if($index)
                $redis->decr($rkey);
            
            $result = array('code' => 1,
                'winner' => array(),
                'statusCode' => 200);
        }
        
        return $result;
    }
    
    static function createRedis()
    {
        return new \Predis\Client(['scheme' => 'tcp', 'host'   => getenv("REDIS_HOST"), 'port'   =>  getenv("REDIS_PORT")]);
    }
}