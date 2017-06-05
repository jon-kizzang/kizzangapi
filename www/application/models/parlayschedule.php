<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ParlaySchedule extends MY_Model {

    // set table is Sport Schedule
    protected $_table = 'SportSchedule';

    // set validations rules
    protected $validate = array(
        'sportCategoryID' => array( 
            'field' => 'sportCategoryID', 
            'label' => 'sport Category ID',
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
    );

    protected $public_attributes = array(
            'id',
            'sportCategoryID',
            'group',
            'dateTime',
            'team1',
            'team2'
        );

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
            $errors = array( 'code' => 1, 'message' => 'Sport Schedule Not Found', 'statusCode' => 404 ); 

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
        if ( ! is_numeric( $id ) || $id <= 0 ) 
            return array(  'code' => 1, 'error' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );            

        return $this->getByIdFromDb( $id );
    }

    public function getAllByDate( $date, $limit, $offset ) 
    {
        if ( ! $this->form_validation->valid_date( $date ) ) 
            return array( 'code' => 1, 'message' => 'The date field must contain a valid date (m-d-Y)', 'statusCode' => 400 );

        $games = $this->limit( $limit, $offset )->get_many_by( array( 'date_format(dateTime,"%m-%d-%Y")' => array('isRaw' =>  "'$date'") ) );

        if ( empty( $games) ) 
            $result = array( 'code' => 2, 'message' => 'Sport Schedule Not Found on date ' . $date, 'statusCode' => 404 );        
        else         
            $result = array( 'code' => 0, 'games' => $games, 'count' => count( $games ), 'statusCode' => 200 );
            
        return $result;
    }

    public function getAllByCategory( $categoryId, $limit, $offset ) 
    {
        if ( ! is_numeric( $categoryId) || $categoryId <= 0 ) 
            return  array( 'code' => 1, 'message' => 'Category Id must be a numeric and greater than zero', 'statusCode' => 400 );
                            
        $games = $this->limit( $limit, $offset )->get_many_by( 'sportCategoryID', $categoryId );

        // if not found any sport schedule
        if ( empty( $games) ) 
            return $result = array( 'code' => 2, 'message' => 'Sport Schedule Not Found with category id ' . $categoryId, 'statusCode' => 404 );
        else 
            $result = array( 'code' => 0, 'games' => $games, 'count' => count( $games ), 'statusCode' => 200 );
            
        return $result;
    }

    protected function checkCategoryTeam( $categoryId, $team1, $team2 ) 
    {
        $count = $this->db->select('count(SportTeams.id) AS count', FALSE )
                    ->join( 'SportCategories', 'SportCategories.id = SportTeams.sportCategoryID' )
                    ->where_in( 'SportTeams.id', array( $team1, $team2 ) )
                    ->where( 'sportCategoryID', $categoryId )
                    ->get( 'SportTeams' )->row()->count;

        $isValid = ( $count == 2 ) ? TRUE : FALSE;

        return $isValid;
    }
}