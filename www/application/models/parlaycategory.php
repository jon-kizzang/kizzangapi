<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ParlayCategory extends MY_Model {

    // set table is wheel
    protected $_table = 'SportCategories';
    protected $testing = FALSE;

    protected $has_many = array( 'parlaycard' => array( 'model' => 'parlaycard', 'primary_key' => 'id' ) );

    // set validations rules
    protected $validate = array(
        'name' => array( 
            'field' => 'name', 
            'label' => 'name',
            'rules' => 'required'
        ),
        'sort' => array( 
            'field' => 'sort', 
            'label' => 'sort',
            'rules' => 'required|greater_than[0]'
        ),
    );

    protected $public_attributes = array(
            'id',
            'name',
            'sort'
        );

     /**
     * get Parlay Category by id from database
     * @param  int $id
     * @return object
     */
    public function getByIdFromDB( $id ) 
    {
        $result = $this->get_by(  'id' , $id );

        if ( empty( $result ) ) 
            return array( 'code' => 1, 'message' => 'Parlay Category Not Found', 'statusCode' => 404 );
        
        $result->code = 0;
        $result->statusCode = 200;        

        return $result;
    }

    public function getById( $id ) 
    {
        if ( ! is_numeric( $id ) || $id <= 0 ) 
            return array( 'code' => 1, 'message' => 'Id must be a numeric and greater than zero', 'statusCode' => 200 );   
                    
        return $this->getByIdFromDB( $id );        
    }

    protected function getAllFromDatabase( $limit, $offset ) 
    {    
        $categories = $this->order_by( 'sort' )->limit( $limit, $offset )->get_all();
        if ( empty( $categories ) )         
            $result = array( 'code' => 1, 'message' => 'Parlay Categorys Not Found', 'statusCode' => 200 );        
        else         
            $result = array( 'code' => 0, 'counts' => count($categories), 'categories' => $categories, 'limit' => (int)$limit, 'offset' => (int)$offset , 'statusCode' => 200 );        

        return $result;
    }

    public function getAll( $limit, $offset ) 
    {
        return $this->getAllFromDatabase( $limit, $offset );
    }   
}