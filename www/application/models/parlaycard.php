<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ParlayCard extends MY_Model {

	// Use for fetching values from the db and updating memcache instead of
	// using memcache directly if a key already exists. Helpful for testing.
	private $testing = FALSE;
	
    protected $after_get = array( 'relate', 'convertData' );

    protected $belongs_to = array( 
            'category' => array( 'model' => 'parlaycategory', 'primary_key' => 'sportCategoryId' ),
            'config' => array( 'model' => 'ParlayConfig', 'primary_key' => 'parlayCardId' )
        );

    // set table is Sport Schedule
    protected $_table = 'SportParlayCards';

    // set validations rules
    protected $validate = array(
        'parlayCardId' => array( 
            'field' => 'parlayCardId', 
            'label' => 'parlay Card Id',
            'rules' => 'required|greater_than[0]'
        ),
        'sportScheduleId' => array( 
            'field' => 'sportScheduleId', 
            'label' => 'sport Schedule Id',
            'rules' => 'required|greater_than[0]'
        ),
        'sportCategoryId' => array( 
            'field' => 'sportCategoryId', 
            'label' => 'sport Category Id',
            'rules' => 'required|greater_than[0]'
        ),
        'dateTime' => array( 
            'field' => 'dateTime', 
            'label' => 'Date Time',
            'rules' => 'required|valid_datetime'
        ),
        'team1' => array( 
            'field' => 'team1', 
            'label' => 'team1',
            'rules' => 'required|greater_than[0]'
        ),
        'team2' => array( 
            'field' => 'team2', 
            'label' => 'team2',
            'rules' => 'required|greater_than[0]'
        ),
        'team1Name' => array( 
            'field' => 'team1Name', 
            'label' => 'team1 Name',
            'rules' => 'required'
        ),
        'team2Name' => array( 
            'field' => 'team2Name', 
            'label' => 'team2 Name',
            'rules' => 'required'
        ),
    );

    protected $public_attributes = array(
            'id',
            'parlayCardId',
            'sportScheduleId',
            'sportCategoryId',
            'dateTime',
            'team1',
            'team2',
            'team1Name',
            'team2Name',
        );

    function __construct() 
    {
        parent::__construct();

        // loading parlay config model
        $this->load->model( 'parlayconfig' );
        $this->load->model( 'parlayschedule' );

    }
    
    protected function convertData( $parlayCard ) 
    {
        if ( is_object( $parlayCard ) ) 
        {
            if ( isset( $parlayCard->category->name ) ) 
                $parlayCard->sportCategoryName = $parlayCard->category->name;            
            else                 
                $parlayCard->sportCategoryName = null;            

            unset( $parlayCard->category );
        }

        return $parlayCard;
    }

    /**
     * get Sport Schedule from database
     * @param  int $id
     * @return array or object
     */
    protected function getByIdFromDb( $id ) 
    {
        // get object Sport Schedule by id from database
        $result = $this->with( 'category' )->get( $id );

        if ( empty($result) ) 
        {
            // return log errors when return empty result
            $errors = array( 'code' => 1, 'message' => 'Sport Parlay Card Not Found', 'statusCode' => 404 ); 
            return $errors; 
        } 
        else 
        {
            $result->statusCode = 200;
            $result->theme = $result->type;
            // return object of Sport Schedule
            return $result;
        }
    }

    public function getById( $id ) 
    {
        if ( ! is_numeric( $id ) || $id <= 0 ) 
            return array(  'code' => 1, 'error' => 'Id must be a numeric and greater than zero', 'statusCode' => 400 );               
        
        return $this->getByIdFromDb( $id );
    }

    public function getAll( $date, $range ) 
    {
        if ( ! $this->form_validation->valid_date( $date ) ) 
            return array( 'code' => 1, 'message' => 'The date field must contain a valid date (m-d-Y)', 'statusCode' => 400 );            
        if ( ! is_numeric( $range ) || $range <= -1 ) 
            return array( 'code' => 2, 'message' => 'Day Limit must be a numeric and greater than -1', 'statusCode' => 400 );
            
        $parlayCards = array();

        $parlayConfigs = $this->parlayconfig->getAllByDate( $date, $range );            

        if ( (int)$parlayConfigs['code'] !== 0 )
            return $parlayConfigs;            

        foreach ( $parlayConfigs['items'] as $parlayConfig ) 
        {    
            $cardData = $this->getByParlayId( $parlayConfig->parlayCardId );
            if ( $cardData ) 
            {
                $parlayConfig->cardData = $cardData;
                $parlayConfig->count = count($cardData);
                $parlayCards[] = $parlayConfig;
            }

        }
        
        if ( empty( $parlayCards ) ) 
            $result = array( 'code' => 2, 'message' => 'Games Not Found on date ' . $date, 'statusCode' => 404 );
        else 
            $result = array( 'code' => 0, 'games' => $parlayCards, 'count' => count( $parlayCards ), 'statusCode' => 200 );
            
        return $result;
    }
    
    public function getAllById( $id ) 
    {        
        $parlayCards = array();

        $parlayConfigs = $this->parlayconfig->getAllById( $id );

        if ( (int)$parlayConfigs['code'] !== 0 )
            return $parlayConfigs;            

        foreach ( $parlayConfigs['items'] as $parlayConfig ) 
        {    
            $cardData = $this->getByParlayId( $parlayConfig->parlayCardId );
            if ( $cardData ) 
            {
                $parlayConfig->cardData = $cardData;
                $parlayConfig->count = count($cardData);
                if($parlayConfig->type == "profootball2016" || $parlayConfig->type == "collegefootball2016")
                {
                    $parlayConfig->displayDate = date("l, F j", strtotime($parlayConfig->endDate));
                    $parlayConfig->displayWeek = "Week " . $parlayConfig->week;
                    $parlayConfig->winAmount = '$' . number_format($parlayConfig->cardWin, 0);
                    $parlayConfig->cardCount = count($parlayConfig->cardData);
                }
                $parlayCards[] = $parlayConfig;
            }
        }

        if ( empty( $parlayCards ) )             
            $result = array( 'code' => 2, 'message' => 'Games Not Found on date ' . $date, 'statusCode' => 404 );            
        else 
            $result = array( 'code' => 0, 'games' => $parlayCards, 'count' => count( $parlayCards ), 'statusCode' => 200 );                

        return $result;
    }
    
    public function getAllTypes( $date, $userType ) 
    {
        if ( ! $this->form_validation->valid_date( $date ) ) 
            return array( 'code' => 1, 'message' => 'The date field must contain a valid date (m-d-Y)', 'statusCode' => 200 );
                         
        $parlayCards = array();

        $parlayConfigs = $this->parlayconfig->getAllByDate( $date, 0, $userType );

        if ( (int)$parlayConfigs['code'] !== 0 )
            return $parlayConfigs;            

        foreach ( $parlayConfigs['items'] as $parlayConfig ) 
        {
            $cardData = $this->getByParlayId( $parlayConfig->parlayCardId );
            if ( $cardData ) 
            {
                $parlayConfig->cardData = $cardData;
                $parlayConfig->count = count($cardData);
                $parlayConfig->theme = $parlayConfig->type;
                $parlayCards[$parlayConfig->parlayCardId] = $parlayConfig;
            }

        }

        if ( empty( $parlayCards ) ) 
            $result = array( 'code' => 2, 'message' => 'Games Not Found on date ' . $date, 'statusCode' => 404 );
        else 
            $result = array( 'code' => 0, 'games' => $parlayCards, 'count' => count( $parlayCards ), 'statusCode' => 200 );

        return $result;
    }

    public function getNextCardId() 
    {    
        // ojb       
        $result = new stdClass();

        // get parlayCardId last
        $parlay = $this->limit(1)->order_by('parlayCardId', 'DESC')->get_by(array('parlayCardId !=' => 0));
        
        // set parlayId return
        $parlayCardId = !empty($parlay) ? ((int)$parlay->parlayCardId + 1) : 1; 

        $result->parlayCardId = $parlayCardId;
        $result->code = 0;
        $result->statusCode = 200;

        // return ojb
        return $result;
    }

    public function getByParlayId( $parlayCardId ) 
    {
        if ( $this->memcacheEnable ) 
        {
            $key = "KEY-SportParlayCard-$parlayCardId";
            $result = $this->memcacheInstance->get( $key );
            if ( $result ) return $result;
        }

        $where = array(
            'parlayCardId' => $parlayCardId
        );
        
        $rs = $this->db->query("SELECT c.id, c.parlayCardId, question, spread, 
            IF(overUnderScore IS NOT NULL, 'ou', IF(question IS NOT NULL, 'question', IF(spread IS NOT NULL, 'spread', 'normal'))) as type, IF(c.overUnderScore IS NULL, c.sportScheduleId, concat(c.id)) as sportScheduleId, c.sportCategoryId, c.dateTime, c.team1, c.team2, c.team1Name, c.team2Name, c.overUnderScore, SportCategories.name AS sportCategoryName, a.abbr as team1abbr, b.abbr as team2abbr 
            FROM (SportParlayCards c) 
            LEFT JOIN SportCategories ON SportCategories.id = c.sportCategoryId 
            LEFT JOIN SportTeams a ON c.team1 = a.id AND c.sportCategoryId = a.sportCategoryID 
            LEFT JOIN SportTeams b ON c.team2 = b.id AND c.sportCategoryId = b.sportCategoryID 
            WHERE parlayCardId = ? GROUP BY c.id ORDER BY c.sequence, c.id", array($parlayCardId));
        $result = $rs->result();
		
        //print $this->db->last_query(); die();
        // Check if any of the games have already started. If any game on the card has already started, then
        // do not return a card - this card can no longer be returned to the player.
        foreach ($result as $row) 
        {
            $dateCurrent = strtotime( 'now' );
            $gameTime = strtotime( $row->dateTime ) - 900;

            if ( $gameTime < $dateCurrent ) 
            {
                $result = NULL;
                break;
            }
        }

        if ( empty( $result ) )         
            return NULL;
                
        return $result;        
    }

    public function getByParlayCardId( $parlayCardId ) 
    {       
        $sportParlayCard = $this->get_many_by( 'parlayCardId', $parlayCardId );

        if ( empty( $sportParlayCard ) ) 
            return array( 'code' => 1, 'message' => 'Sport Parlay Card Not Found', 'statusCode' => 404 );            
       
        $result['code'] = 0;
        $result['statusCode'] = 200;

        return $result;
    }

    public function getTimeMatchFirst( $parlayCardId ) 
    {
        $parlayCard = $this->order_by( 'dateTime' )->get_by( 'parlayCardId', $parlayCardId );
        if ( empty( $parlayCard ) ) 
            return NULL;
        
        $dateTime = strtotime( $parlayCard->dateTime );        
        return $dateTime;
    }

    public function getScheduleAndTeamByParlayId( $parlayCardId ) 
    {
        $parlayCards = $this->db->select( "SportCategories.name AS catName, SportParlayCards.*")
            ->join( 'SportParlayCards', 'SportParlayCards.sportCategoryId = SportCategories.id', 'left' )
            ->where( 'SportParlayCards.parlayCardId', $parlayCardId )  
            ->order_by('SportParlayCards.sequence')
            ->order_by( 'SportParlayCards.id' )
            ->get( 'SportCategories' )
            ->result();

        if ( empty( $parlayCards ) )         
            return array( 'code' => 1, 'message' => 'Please create list match before saving the card', 'statusCode' => 400 );
            
        $scheduleIds    = array();
        $teamIds        = array();


        $teamCategories = array();

        foreach ( $parlayCards as $parlayCard ) 
        {    
            $sportScheduleId = $parlayCard->sportScheduleId;

            if(!$parlayCard->overUnderScore)
            {
                array_push( $scheduleIds, $sportScheduleId );
                array_push( $teamIds, $parlayCard->team1 );
                array_push( $teamIds, $parlayCard->team2 );
                $teamCategories[ $sportScheduleId . '-' . $parlayCard->team1 ] = array( $parlayCard->team1Name, $parlayCard->catName, $parlayCard->dateTime );
                $teamCategories[ $sportScheduleId . '-' . $parlayCard->team2 ] = array( $parlayCard->team2Name, $parlayCard->catName, $parlayCard->dateTime );
            }
            else
            {
                array_push( $scheduleIds, $parlayCard->id );
                array_push( $teamIds, $parlayCard->team1 );
                array_push( $teamIds, $parlayCard->team2 );
                $teamCategories[ $parlayCard->id . '-' . $parlayCard->team1 ] = array( "Under " . $parlayCard->overUnderScore . " in " . $parlayCard->team1Name . " VS. " . $parlayCard->team2Name, $parlayCard->catName, $parlayCard->dateTime );
                $teamCategories[ $parlayCard->id . '-' . $parlayCard->team2 ] = array( "Over " . $parlayCard->overUnderScore . " in " . $parlayCard->team1Name . " VS. " . $parlayCard->team2Name, $parlayCard->catName, $parlayCard->dateTime );
            }
        }

        $result = array( 'code' => 0, 'scheduleIds' => $scheduleIds, 'teamIds' => $teamIds, 'teamCategories' => $teamCategories, 'statusCode' => 200 );

        if ( $this->memcacheEnable )
            $this->user->updateMemcache( 'Key-Categories-' . $parlayCardId, $result );            

        return $result;        
    }
}
