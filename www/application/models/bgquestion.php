<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BGQuestion extends MY_Model {
    
    // set reference table BGQuestions to table BGAnswers by primary key id
    protected $has_many = array( 'answers' => array( 'model' => 'BGAnswer', 'primary_key' => 'questionId') );

    // set table is BGQuestions
    protected $_table = 'BGQuestions';

    protected $testing = FALSE;

    // set validations rules
    protected $validate = array(
        'categoryId' => array( 
            'field' => 'categoryId', 
            'label' => 'categoryId',
            'rules' => 'required|greater_than[0]'
        ),
        'question' => array( 
            'field' => 'question', 
            'label' => 'question',
            'rules' => 'required'
        ),
        'startDate' => array( 
            'field' => 'startDate', 
            'label' => 'startDate',
            'rules' => 'required|valid_datetime'
        ),
        'endDate' => array( 
            'field' => 'endDate', 
            'label' => 'endDate',
            'rules' => 'required|valid_datetime'
        ),
        'rule' => array( 
            'field' => 'rule', 
            'label' => 'rule',
            'rules' => 'required'
        ),
    );

    protected $public_attributes = array(
            'id',
            'categoryId',
            'question',
            'startDate',
            'endDate',
            'rule'
        );

    function __construct() {

        parent::__construct();

        // load bganswer model
        $this->load->model( 'bganswer' );

    }

    public function getByIdFromDB( $id ) 
    {
        $result = $this->with( 'answers' )->get( $id );

        if ( empty( $result ) ) 
        {
            $result = array( 'code' => 1, 'message' => 'Question Not Found', 'statusCode' => 404 );
        }
        else  
        {
            $result->code = 0;
            $result->statusCode = 200;
        }

        return $result;
    }

    public function getById( $id ) 
    {        
        if ( ! is_numeric( $id ) || $id <= 0 )        
            return array( 'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );            

        if ( $this->memcacheEnable ) 
        {
            $key = "KEY-Question-Id-$id";
            $result = $this->memcacheInstance->get( $key );
            if ( $result ) return $result;
        }

        // get question by id
        $result = $this->getByIdFromDB( $id );
        
        if ( $this->memcacheEnable && is_object( $result ) )         
            $this->user->updateMemcache( $key, $result );        

        return $result;
    }

    public function getAnswerIdByCategoryId( $categoryId ) 
    {
        if ( $this->memcacheEnable ) 
        {
            $key = "KEY-Question-Category-$categoryId";
            $questions = $this->memcacheInstance->get( $key );
            if ( $questions ) return $questions;
        }

        // get list question by category id
        $questions = $this->with( 'answers' )->get_many_by( 'categoryId', $categoryId );

        if ( ! empty( $questions ) ) 
        {
            $endDate            = NULL;
            $answerIds          = array();
            $questionAnswers    = array();
            $questionAnswerIds  = array();

            foreach ( $questions as $key => $question ) 
            {
                if ( ! $endDate )                
                    $endDate = $question->endDate;                

                foreach ( $question->answers as $answer ) 
                {                    
                    array_push( $answerIds, $answer->id );
                    $questionAnswerIds[$answer->id] = $question->id;
                    $questionAnswers[$answer->id] = array( $question->question, $answer->answer );
                }
            }

            $result = array( 'code' => 0, 'endDate' => $endDate, 'answerIds' => $answerIds, 'questionAnswerIds' => $questionAnswerIds, 'questionAnswers' => $questionAnswers, 'statusCode' => 200 );

            if ( $this->memcacheEnable )
                $this->user->updateMemcache( $key, $result );
            
        }
        else 
        {
            $result = array( 'code' => 1, 'message' => 'Question Not Found with category id: ' . $categoryId, 'statusCode' => 404 );
        }

        return $result;
    }
   
    public function getAnswerIdByParlayCardId( $parlayCardId ) 
    {
        if ( $this->memcacheEnable ) 
        {
            $key = "KEY-Question-PCI-$parlayCardId";
            $questions = $this->memcacheInstance->get( $key );
            if ( $questions ) return $questions;
        }

        // get list question by category id
        $questions = $this->with( 'answers' )->get_many_by( 'parlayCardId', $parlayCardId );

        if ( ! empty( $questions ) ) 
        {
            $endDate            = NULL;
            $answerIds          = array();
            $questionAnswers    = array();
            $questionAnswerIds  = array();

            foreach ( $questions as $key => $question ) 
            {
                if ( ! $endDate )
                    $endDate = $question->endDate;                

                foreach ( $question->answers as $answer ) 
                {
                    // push answer id to answerIds array
                    array_push( $answerIds, $answer->id );

                    // push question id and answer id to questionAnswerIds array
                    //  ex. array( 'a1' => 'q1', 'a2' => 'q1' )
                    $questionAnswerIds[$answer->id] = $question->id;
                    $questionAnswers[$answer->id] = array( $question->question, $answer->answer );
                }
            }

            $result = array( 'code' => 0, 'endDate' => $endDate, 'answerIds' => $answerIds, 'questionAnswerIds' => $questionAnswerIds, 'questionAnswers' => $questionAnswers, 'statusCode' => 200 );

            if ( $this->memcacheEnable )
                $this->user->updateMemcache( $key, $result );
            
        }
        else 
        {
            $result = array( 'code' => 1, 'message' => 'Question Not Found with parlay card id: ' . $parlayCardId, 'statusCode' => 404 );
        }

        return $result;
    }

    protected function getAllFromDatabase( $currentDate ) 
    {
        $this->load->model( 'bgquestionconfig' );
        $questionConfig = $this->bgquestionconfig->getByDate( $currentDate );
        
        if ( (int)$questionConfig['code'] !== 0 ) 
                return $questionConfig;            

        $questionConfigs = $questionConfig['items'];
        $questionConfigs->theme = 'sibiggame';
        
        // get all questions from database
        $questions = $this->with( 'answers' )->get_many_by( array( 'parlayCardId' => $questionConfigs->parlayCardId) );

        if ( empty( $questions ) ) 
        {
            $error = array( 'code' => 1, 'message' => 'Questions Not Found', 'statusCode' => 404 );
            return $error; 
        }
        else 
        {
            $questionConfigs->questions = $questions;

            // return all list of wedges
            $results = array( 'code' => 0, 'games' => $questionConfigs, 'statusCode' => 200 );
            return $results;
        }
    }

    public function getAll() 
    {
        $currentDate = date( 'Y-m-d H:i:s' );

        if ( $this->memcacheEnable ) 
        {
            $key = "KEY-Questions-All-$currentDate";
            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );
            if ( ! $this->testing && $result ) return $result;
        }

        // if empty result, will get all questions from database
        $result = $this->getAllFromDatabase( $currentDate );

        if ( $this->memcacheEnable && $result['code'] === 0 )
            $this->user->updateMemcache( $key, $result );           

        // return array all list questions 
        return $result;
    }    
}