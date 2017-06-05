<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ParlayTeam extends MY_Model {

    // set table is wheel
    protected $_table = 'SportTeams';
    protected $testing = FALSE;

    // set validations rules
    protected $validate = array(
        'sportCategoryId' => array( 
            'field' => 'sportCategoryId', 
            'label' => 'sportCategoryId',
            'rules' => 'required|greater_than[0]'
        ),
        'name' => array( 
            'field' => 'name', 
            'label' => 'name',
            'rules' => 'required'
        ),
    );

    protected $public_attributes = array(
            'id',
            'sportCategoryId',
            'name',
            'abbr'
        );
    
    public function getByIdFromDB( $id , $categoryId ) 
    {

        $result = $this->get_by( array( 'id' => $id, 'sportCategoryId' => $categoryId ) );

        if ( empty( $result ) ) 
            return array( 'code' => 1, 'message' => 'Sport Team Not Found', 'statusCode' => 404 );
        
        $result->code = 0;
        $result->statusCode = 200;

        return $result;
    }
    
    public function getById( $id , $categoryId ) 
    {
        // validate the id.
        if ( ! is_numeric( $categoryId ) || $categoryId <= 0 ) 
            return array( 'code' => 1, 'message' => ' Category Id must be a numeric and greater than zero', 'statusCode' => 400 );
                    
        return $this->getByIdFromDB( $id, $categoryId );
    }
    
    protected function getAllFromDatabase( $limit, $offset ) 
    {
        $sportTeams = $this->limit( $limit, $offset )->get_all();
        if ( empty( $sportTeams ) ) 
            $result = array( 'code' => 1, 'message' => 'Sport Teams Not Found', 'statusCode' => 404 );
        else 
            $result = array( 'code' => 0, 'count' => count($sportTeams), 'sportTeams' => $sportTeams, 'limit' => (int)$limit, 'offset' => (int)$offset ,'statusCode' => 200 );        

        return $result;
    }

    public function getAll( $limit, $offset ) 
    {        
        return $this->getAllFromDatabase( $limit, $offset );
    }

    public function getAllByCategory( $sportCategoryId ) 
    {
        if ( empty( $sportCategoryId ) ) 
            return array( 'code' => 1, 'message' => 'Please enter a valid sport category id', 'statusCode' => 400 );
           
        $teams = $this->order_by( 'id' )->get_many_by( 'sportCategoryId', $sportCategoryId );
      
        if ( empty( $teams ) ) 
            $result = array( 'code' => 2, 'message' => 'Sport Teams Not Found for categoryId: ' . $sportCategoryId, 'statusCode' => 404 );
        else 
            $result = array( 'code' => 0, 'teams' => $teams, 'count' => count( $teams ), 'statusCode' => 200 );

        return $result;
    }     
}