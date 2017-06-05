<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require dirname(__FILE__) . '/../../vendor/autoload.php';

use Aws\Common\Aws;

class BGPlayerCard extends MY_Model {

	// Use for fetching values from the db and updating memcache instead of
	// using memcache directly if a key already exists. Helpful for testing.
	private $testing = FALSE;

    // set table is BGPlayerCards
    protected $_table = 'BGPlayerCards';

    protected $token = null;

    // set validations rules
    protected $validate = array(
        'playerId' => array( 
            'field' => 'playerId', 
            'label' => 'playerId',
            'rules' => 'required|greater_than[0]'
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
    );

    // Only allow add field in public_attributes
    protected $public_attributes = array(
            'id',
            'playerId',
            'dateTime',
            'picksHash',
            'wins',
            'losses',
            'parlayCardId'
        );
    
    // mark execute function from unit test
    public $executeTesting = FALSE;

    /**
     * set token to get email
     * @param string $token
     * @return none
     */
    public function setToken( $token ) 
    {
        $this->token = $token;
    }

    protected function getByIdFromDb( $id ) 
    {
        // get object Sport Schedule by id from database
        $result = $this->get( $id );
        if ( empty( $result ) ) 
            return array( 'code' => 1, 'message' => 'Big Game Player Card Not Found', 'statusCode' => 404 ); 
        $result->statusCode = 200;
       
        return $result;        
    }
    
    public function getById( $id ) 
    {
        // validate the id.
        if ( ! is_numeric( $id ) || $id <= 0 )        
            return array(  'code' => 1, 'message' => 'Id must be a numeric and greater than zero', 'statusCode' => 400 );            
        
        $result = $this->getByIdFromDb( $id );

        return $result;
    }

    public function getHistory( $playerId, $limit, $offset ) 
    {        
        $where = array(
            'rank' => 0,
            'playerId' => $playerId
        );

        $games = $this->limit( $limit, $offset )->get_many_by( $where );

        if ( empty( $games) ) 
            $result = array( 'code' => 2, 'message' => 'Games History Not Found', 'statusCode' => 404 );
        else                     
            $result = array( 'code' => 0, 'games' => $games, 'offset' => (int)$offset, 'limit' => (int)$limit, 'count' => count($games), 'statusCode' => 200 );        

        return $result;
    }

    public function getHistoryWinner( $playerId, $limit, $offset ) 
    {
        $where = array(
                'winner' => 1,
                'playerId' => $playerId
            );
        
        $winners = $this->limit( $limit, $offset )->get_many_by( $where );

        if ( empty( $winners ) ) 
            $result = array( 'code' => 2, 'message' => 'Winner List Not Found with playerId ' . $playerId, 'statusCode' => 404 );
        else             
            $result = array( 'code' => 0, 'winners' => $winners, 'offset' => (int)$offset, 'limit' => (int)$limit, 'count' => count($winners), 'statusCode' => 200 );

        return $result;
    }

    public function add( $data ) 
    {
        $this->load->model( 'bgquestionconfig' );

        if ( empty( $data ) ) 
            return array( 'code' => 1, 'message' => 'Please enter the required data', 'statusCode' => 400 );

        $answerNotExists    = array();

        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $this->validate );

        if ( $this->form_validation->run() === FALSE )         
            return array( 'code' => 2, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 400 );
                
        $playerId = $data['playerId'];

        // Get tonights time at 11:59:59
        $beforeMidnight = strtotime( 'tomorrow - 1 second' );

        // get seconds from now to beforeMidnight
        // Parameter expire is expiration time in seconds
        $seconds = $beforeMidnight - strtotime( 'now' );               

        // Server sets the time when the card is saved to the db
        $data['dateTime'] = date( 'Y-m-d H:i:s' );

        $where = array( 
            'playerId' => $playerId,
            'parlayCardId' => $data['parlayCardId'],
            'picksHash' => $data['picksHash']
        );

        $countPlayerCard = $this->count_by( $where );

        if ( $countPlayerCard ) 
        {
            $result = array( 'code' => 5, 'message' => 'This is a duplicate Entry. Please pick again.', 'statusCode' => 200 );                    
            return $result;
        }

        $this->load->model( 'bgquestion' );

        // get questions by category id
        $bgQuestions = $this->bgquestion->getAnswerIdByParlayCardId( $data['parlayCardId'] );

        if ( (int)$bgQuestions['code'] === 1 )
            return $bgQuestions;                

        $answerIds = $bgQuestions['answerIds'];
        $picksArray = explode( ':', $data['picksHash'] );

        $answerNotExists = array_diff( $picksArray, $answerIds );

        if ( ! empty( $answerNotExists ) )
            return array( 'code' => 6, 'message' => 'Answers in (' . implode( ',', $answerNotExists ) . ') do not exist', 'statusCode' => 400 );

        $this->load->model("gamecount");
        $this->gamecount->setToken( $this->token );
        $this->user->setToken( $this->token );

        $countData = array( 'gameType' => 'SportsEvent' );
        $countResponse = $this->gamecount->add( $playerId, $countData );

        if ( is_array($countResponse ) )          
            return array( 'code' => 2, 'message' => 'Unable to increment game count', 'statusCode' => 200 );

        // set skip_validation = TRUE in 2nd parameter
        $insertId = $this->insert( $data, TRUE );
        
        $this->load->model("chedda");
        $gameInfo = array('serialNumber' => sprintf("KB%05d", $data['parlayCardId']), 'entry' => $insertId, 'type' => 'bigGame');
        $this->chedda->addEventNotification($playerId, $gameInfo);
        
        if ( $insertId ) 
        {                    
            // Increment the sports count event - even if the email doesn't get sent
            $this->load->model( 'gamecount' );           

            // get object Big Game Player Card by id 
            $result = $this->getById( $insertId );
            $result->endDate = $bgQuestions['endDate'];
            $result->statusCode = 201;
         }
        else 
        {
            $errorMessage = $this->db->_error_message();
            log_message( 'message', 'Insert Big Game Player Card: ' . $errorMessage );
            $result = array( 'code' => 4, 'message' => $errorMessage, 'statusCode' => 400 );
        }   

        // return object Sport Schedule
        return $result;
    }
   
    public function getAllByDate( $date ) 
    {
        if ( ! $this->form_validation->valid_date( $date ) ) 
            return array( 'code' => 1, 'message' => 'The date field must contain a valid date (m-d-Y)', 'statusCode' => 400 );

        $playerCards = $this->get_many_by( array( 'date_format(dateTime,"%m-%d-%Y")' => array( 'isRaw' => "'$date'" ) ) );

        // if found any cards
        if ( ! empty( $playerCards) ) 
            $result = array( 'code' => 0, 'playerCards' => $playerCards, 'count' => count( $playerCards ), 'statusCode' => 200 );
        else 
            $result = array( 'code' => 2, 'message' => 'Player Card Not Found on date ' . $date, 'statusCode' => 404 );        

        return $result;
    }

    public function getCurrent( $playerId ) 
    {
        if ( ! is_numeric( $playerId ) || $playerId <= 0 ) 
            return array( 'code' => 1, 'message' => 'Id player must be a numeric and greater than zero', 'statusCode' => 400 );

        $this->load->model( 'bgpick' );
        $this->load->model( 'bgquestion' );

        $currentCards = array();
        $date = date( 'Y-m-d' );
        $playerCards = $this->get_many_by( array( 'playerId' => $playerId, 'date_format(dateTime,"%Y-%m-%d")' => array( 'isRaw' => "'$date'" ) ) );

        if ( ! empty( $playerCards ) ) 
        {
            // get all by date current
            $questions = $this->bgquestion->getAll();

            if ( (int)$questions['code'] === 0 ) 
                $questions = $questions['games']->questions;
            else
                $questions = array();

            foreach ( $playerCards as $key => $playerCard ) 
            {
                $row = array();
                $answerChoices = array();
                $row['id'] = $playerCard->id;
                $row['dateTime'] = $playerCard->dateTime;

                $picksArray = explode( ':', $playerCard->picksHash );
                foreach ( $questions as $question ) 
                {                
                    foreach ( $question->answers as $answer ) 
                    {                    
                        if ( in_array( $answer->id, $picksArray ) ) 
                        {
                            $rowAnswer = array();
                            $rowAnswer['Q'] = $question->question;
                            $rowAnswer['A'] = $answer->answer;
                           array_push( $answerChoices, $rowAnswer );
                            break;
                        }
                    }
                }

                $row['answerChoices'] = $answerChoices;
                array_push( $currentCards, $row );
            }
        }

        if ( ! empty( $currentCards ) ) 
            $result = array( 'code' => 0, 'cards' => $currentCards, 'count' => count( $currentCards ), 'statusCode' => 200 );
        else 
            $result = array( 'code' => 1, 'message' => 'The Player Cards not found in the current date', 'statusCode' => 200 );    

        return $result;
    }
}