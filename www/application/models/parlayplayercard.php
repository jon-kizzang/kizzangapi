<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ParlayPlayerCard extends MY_Model {

	// Use for fetching values from the db and updating memcache instead of
	// using memcache directly if a key already exists. Helpful for testing.
	private $testing = FALSE;
	
    // set table is Sport Schedule
    protected $_table = 'SportPlayerCards';

    // a ParlayPlayerCard belong player
    // protected $belongs_to = array( 
    //     'player' => array( 'model' => 'Player', 'primary_key' => 'playerId' ),
    // );

    // set validations rules
    protected $validate = array(
        'playerId' => array( 
            'field' => 'playerId', 
            'label' => 'playerId',
            'rules' => 'greater_than[0]'
        ),
        'picksHash' => array( 
            'field' => 'picksHash', 
            'label' => 'picksHash',
            'rules' => 'required'
        ),
        'parlayCardId' => array( 
            'field' => 'parlayCardId', 
            'label' => 'parlayCardId',
            'rules' => 'required|greater_than[0]'
        ),
        'rank' => array( 
            'field' => 'rank', 
            'label' => 'rank',
            'rules' => 'greater_than[-1]'
        ),
        'wins' => array( 
            'field' => 'wins', 
            'label' => 'wins',
            'rules' => 'greater_than[-1]'
        ),
        'losses' => array( 
            'field' => 'losses', 
            'label' => 'losses',
            'rules' => 'greater_than[-1]'
        ),
        'isQuickpick' => array( 
            'field' => 'isQuickpick', 
            'label' => 'isQuickpick',
            'rules' => 'greater_than[-1]'
        )
    );

    protected $public_attributes = array(
            'id',
            'playerId',
            'dateTime',
            'picksHash',
            'wins',
            'losses',
            'rank',
            'sportCategoryId',
            'parlayCardId',
            'isQuickpick'
        );

    // mark execute function from unit test
    public $executeTesting = false;

    function __construct() {

        parent::__construct();

        //loading model bgplayercard
        $this->load->model('bgplayercard');
        $this->load->model( 'parlaycard' );
    }
    
    /**
     * get Sport Schedule from database
     * @param  int $id
     * @return array or object
     */
    protected function getByIdFromDb( $id ) 
    {
        // get object Sport Schedule by id from database
        $result = $this->get( $id );

        if ( empty($result) ) 
        {
            // return log errors when return empty result
            $errors = array( 'code' => 1, 'message' => 'Sport Player Card Not Found', 'statusCode' => 404 ); 
            return $errors; 
        } 
        else 
        {
            $result->statusCode = 200;
            // return object of Sport Schedule
            return $result;
        }
    }
   
    public function getById( $id ) 
    {
        // validate the id.
        if ( ! is_numeric( $id ) || $id <= 0 ) 
            return array(  'code' => 1, 'error' => 'Id must be a numeric and greater than zero', 'statusCode' => 400 );                           
        
        return $this->getByIdFromDb( $id );
    }

    public function getAll( $date ) 
    {
        if ( ! $this->form_validation->valid_date( $date ) ) 
            return array( 'code' => 1, 'message' => 'The date field must contain a valid date (m-d-Y)', 'statusCode' => 400 );
                           
        $games = $this->db->where( 'date_format(dateTime,"%m-%d-%Y")', "'$date'", FALSE )
                ->get( $this->_table )
                ->result();

        if ( empty( $games) ) 
            $result = array( 'code' => 2, 'message' => 'Games Not Found on date ' . $date, 'statusCode' => 404 );        
        else 
            $result = array( 'code' => 0, 'games' => $games, 'count' => count( $games ), 'statusCode' => 200 );

        return $result;
    }    
    
    public function getParlayCard($id)
    {
        $rs = $this->db->query("Select id , screenName from Users where id in (Select distinct playerId from SportPlayerCards where id = ?) order by screenName", array($id));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'User not found', 'statusCode' => 200);
        $name = $rs->row();

        $rs = $this->db->query("Select * from SportPlayerCards where id = ?", array($id));
        if(!$rs->num_rows())
            return array('code' => 2, 'message' => 'Parlay Card not found', 'statusCode' => 200);
        $card = $rs->row();

        $rs = $this->db->query("Select * from SportParlayConfig where parlayCardId = ?", array($card->parlayCardId));
        $config = $rs->row();

        $results = array();
        $rs = $this->db->query("Select * from SportGameResults where parlayCardId = ?", array($card->parlayCardId));
        foreach($rs->result() as $row)
            $results[$row->sportScheduleId] = $row->winner;

        $categories = array();
        $rs = $this->db->query("Select * from SportCategories");
        foreach($rs->result() as $row)
            $categories[$row->id] = $row->name;

        $answers = array();
        $rs = $this->db->query("Select * from SportParlayCards where parlayCardId = ? order by sequence", array($card->parlayCardId));
        foreach($rs->result() as $row)
        {
            if($row->overUnderScore && isset($results[$row->id]))
                $row->winner = $results[$row->id];
            elseif(!$row->overUnderScore && isset($results[$row->sportScheduleId]))
                $row->winner = $results[$row->sportScheduleId];
            else
                $row->winner = 0;

            if($row->overUnderScore)
                $answers[$row->id] = $row;
            else
                $answers[$row->sportScheduleId] = $row;
        }            

        $picks_temp = explode(":", $card->picksHash);
        foreach($picks_temp as $temp)
        {
            $key_value = explode("|", $temp);
            $card->picks[$key_value[0]] = $key_value[1];
        }

        $ret = array();
        $i = 1;
        foreach($card->picks as $index => $pick)
        {
            $rec = array('game' => $i++, 'team1' => $answers[$index]->team1Name, 'team2' => $answers[$index]->team2Name, 
                'overUnder' => $answers[$index]->overUnderScore ? $answers[$index]->overUnderScore : NULL, 
                'spread' => $answers[$index]->spread ? $answers[$index]->spread : NULL, 'question' => $answers[$index]->question ? $answers[$index]->question : NULL,
                'type' => $answers[$index]->spread ? 'spread' : ($answers[$index]->overUnderScore ? 'ou' : ($answers[$index]->question ? 'question' : 'normal')));
            
            $rec['result'] = !$answers[$index]->winner ? 'Unknown' : ($answers[$index]->winner == $pick ? "Win" : "Lose");
            if(!$answers[$index]->overUnderScore)
                $rec['teamSelected'] = $answers[$index]->team1 == $pick ? $answers[$index]->team1Name : $answers[$index]->team2Name;
            else
                $rec['teamSelected'] = $answers[$index]->team1 == $pick ? "Under" : "Over";
            $rec['category'] = $categories[$answers[$index]->sportCategoryId];
            $ret[] = $rec;
        }

        return array('code' => 0, 'name' => $name->screenName, 'date' => $config->cardDate, 'results' => $ret, 'statusCode' => 200);
    }
    
    public function add( $playerId, $data ) 
    {                
        if ( empty( $data ) ) 
            return array( 'code' => 1, 'message' => 'Please enter the required data', 'statusCode' => 200 );
                 
       $this->form_validation->reset_validation();
       $this->form_validation->set_params( $data );
       $this->form_validation->set_rules( $this->validate );

       if ( $this->form_validation->run() === FALSE )        
           return array( 'code' => 2, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );
            
        // get time of match 1st by parlayCard ID
        $timeMatchFirst = $this->parlaycard->getTimeMatchFirst( $data['parlayCardId'] );

        if ( $timeMatchFirst )         
            if ( ( $timeMatchFirst - strtotime( 'now' )  ) < ( 60 * 15 ) ) 
                return array( 'code' => 4, 'message' => 'Time up! You cannot enter any new cards', 'statusCode' => 200 );
                

        $picksArray         = explode( ':', $data['picksHash'] );
        $scheduleNotExist   = array();
        $teamNotExist       = array();
        $scheduleTeamIds    = array();
        $scheduleTeams      = $this->parlaycard->getScheduleAndTeamByParlayId( $data['parlayCardId'] );

        if ( (int)$scheduleTeams['code'] === 1 ) 
            return $scheduleTeams;

        $scheduleIds        = $scheduleTeams['scheduleIds'];
        $teamIds            = $scheduleTeams['teamIds'];
        $teamPicks          = array();

        if ( count( $picksArray ) !== ( count( $teamIds ) / 2 ) )         
            return array( 'code' => 3, 'message' => 'Please select picks for all matches', 'statusCode' => 200 );
            

        if ( ! empty( $picksArray ) ) 
        {
            foreach ( $picksArray as $pick ) 
            {   
                $scheduleTeam = explode( '|', $pick );
                if ( count( $scheduleTeam ) == 2 ) 
                {
                    // check exists of schedule id 
                    if ( ! in_array( $scheduleTeam[0] , $scheduleIds ) )
                        array_push( $scheduleNotExist, $scheduleTeam[0] );                            

                    if ( ! in_array( $scheduleTeam[1] , $teamIds ) )
                        array_push( $teamNotExist, $scheduleTeam[1] );                            

                    $scheduleTeamIds[ $scheduleTeam[0] ] = $scheduleTeam[1];
                    array_push( $teamPicks, $scheduleTeam[0] . '-' . $scheduleTeam[1] );
                }
            }
        }

        $messageNotExist = '';

        if ( ! empty( $scheduleNotExist ) )
            $messageNotExist .= 'Schedule in (' . implode( ',', $scheduleNotExist ) . '), ';                

        if ( ! empty( $teamNotExist ) )
            $messageNotExist .= 'Team in (' . implode( ',', $teamNotExist ) . ') ';               

        if ( $messageNotExist ) 
            return array( 'code' => 5, 'message' => $messageNotExist . ' does not exist', 'statusCode' => 200 );
            
        $data['dateTime'] = date( 'Y-m-d H:i:s' );
        $countPlayerCard  = 0;
        $playerId         = $playerId;        

        if ( ! $countPlayerCard ) 
        {
            // check to not duplicate
            $where = array( 
                'playerId' => $playerId,
                'picksHash' => $data['picksHash']
            );
            $countPlayerCard = $this->count_by( $where );
        }

        if ( $countPlayerCard ) 
            return array( 'code' => 6, 'message' => 'You have already made these picks.||Please submit a new entry.', 'statusCode' => 200 );
        
        $rs = $this->db->query("Select c.id, maxCardCount, count(p.id) as cnt from SportParlayConfig c
            Left join SportPlayerCards p on c.parlayCardId = p.parlayCardId and date(p.dateTime) = ? and playerId = ?
            where c.parlayCardId = ?
            group by c.id", array(date("Y-m-d"), $playerId, $data['parlayCardId']));
        
        $row = $rs->row();
        if($row->cnt + 1 > $row->maxCardCount)
            return array( 'code' => 7, 'message' => 'Max Picks reached for the day.', 'statusCode' => 200 );
                
        $insertId = $this->insert( $data, TRUE );                                
                
        if ( $insertId ) 
        {                
            $result = $this->getById( $insertId );
            $result->endDate = NULL;
            $parlayConfig = $this->parlayconfig->getByParlayCardId( $data['parlayCardId'] );

            // return end date if object returned
            if ( is_object( $parlayConfig ) )
                $result->endDate = $parlayConfig->endDate;

            $this->load->model("chedda");
            $gameInfo = array('serialNumber' => $parlayConfig->serialNumber, 'entry' => $insertId, 'type' => 'dailyShowdown');
            $this->chedda->addEventNotification($playerId, $gameInfo);

            $this->load->model('gamecount');
            $rs = $this->db->query("Select count(*) as cnt from SportPlayerCards where parlayCardId = ? and playerId = ?", array($parlayConfig->parlayCardId, $playerId));
            $this->gamecount->add($playerId, array('gameType' => 'SportsEvent', 'foreignId' => $parlayConfig->parlayCardId, 'theme' => $parlayConfig->type, 
                'maxGames' => $parlayConfig->maxCardCount, 'count' => $rs->row()->cnt, 'expirationDate' => $parlayConfig->endDate));
            
            $result->eventId = -1;
            $result->code = 0;
            $result->statusCode = 201;
        } 
        else 
        {            
            $errorMessage = $this->db->_error_message();
            log_message( 'error', 'Insert Parlay Player Card: ' . $errorMessage );
            $result = array( 'code' => 8, 'message' => $errorMessage, 'statusCode' => 200 );
        }           
        return $result;
    }    
}
