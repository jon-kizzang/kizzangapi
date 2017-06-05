<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BGQuestionConfig extends MY_Model {

    // set table is BGQuestionsConfig
    protected $_table = 'BGQuestionsConfig';

     // one final match config will has more big game places
    protected $has_many = array( 
            'place' => array( 'model' => 'BGQuestionsPlace', 'primary_key' => 'parlayCardId' ),
        );

    // set validations rules
    protected $validate = array(

        'parlayCardId' => array( 
            'field' => 'parlayCardId', 
            'label' => 'parlayCardId',
            'rules' => 'required|greater_than[0]'
        ),
        'startDate' => array( 
            'field' => 'startDate', 
            'label' => 'Start Date',
            'rules' => 'required|valid_date'
        ),
        'endDate' => array( 
            'field' => 'endDate', 
            'label' => 'End Date',
            'rules' => 'required|valid_date'
        ),
        'serialNumber' => array( 
            'field' => 'serialNumber', 
            'label' => 'Serial Number',
            'rules' => 'required'
        ),
    );

    protected $public_attributes = array(
            'id',
            'parlayCardId',
            'startDate',
            'endDate',
            'serialNumber',
        );

    /**
     * get BGQuestionConfig from database
     * @param  int $id
     * @return array or object
     */
    protected function getByIdFromDb( $id ) {

        // get object BGQuestionConfig by id from database
        $result = $this->get( $id );

        if ( empty( $result ) ) {

            // return log errors when return empty result
            $error = array( 'code' => 1, 'message' => 'Big Game Question Config Not Found', 'statusCode' => 404 ); 

            return $error;
        }
        else {

            $result->statusCode = 200;

            // return object of bgQuestionConfig
            return $result;
        }
    }

    public function getById( $id ) 
    {
        // validate the id.
        if ( ! is_numeric( $id ) || $id <= 0 ) 
            return array( 'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );
                    
        $result = $this->getByIdFromDb( $id );
        return $result;
    }

    public function getByDate( $currentDate, $playerId = 1 ) 
    {
        $rs = $this->db->query("Select * from BGQuestionsConfig where now() between startDate and endDate");        

        if ( !$rs->num_rows() ) 
            $result = array( 'code' => 1, 'message' => 'Big Game Question Config Not Found on date ' . $currentDate, 'statusCode' => 404 );
        else         
            $result = array( 'code' => 0, 'items' => $rs->row(), 'statusCode' => 200 );            

        return $result;
    }

    protected function getAllFromDatabase( $limit, $offset ) 
    {
        $bgQuestionConfigs = $this->limit( $limit, $offset )->get_all();
        $count = $this->count_all();

        if ( empty( $bgQuestionConfigs ) ) 
            return array( 'code' => 1, 'message' => 'Big Game Question Config Not Found', 'statusCode' => 404 );
        else
            return array( 'code' => 0, 'finalConfigs' => $bgQuestionConfigs, 'limit' => (int)$limit, 'offset' => (int)$offset, 'count' => $count, 'statusCode' => 200 );
    }

    public function getAll( $limit, $offset ) 
    {
        return $this->getAllFromDatabase( $limit, $offset );
    }

}