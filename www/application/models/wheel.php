<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Wheel extends MY_Model {

    // set table is wheel
    protected $_table = 'Wheels';

    // will call convertData function after get
    protected $after_get = array( 'convertData' );

    // set TRUE will not delete record, only set $soft_delete_key field to 1
    protected $soft_delete = TRUE;
    protected $soft_delete_key = 'isDeleted';

    // set validations rules
    protected $validate = array(

        'name' => array( 
            'field' => 'name', 
            'label' => 'name',
            'rules' => 'required'
        ),
        // verify name must be is required
        'numberOfWedges' => array( 
            'field' => 'numberOfWedges', 
            'label' => 'number of wedges',
            'rules' => 'required|greater_than[0]'
        ),
    );

    protected $public_attributes = array(
            'id',
            'name',
            'numberOfWedges',
        );

    /**
     * convert data to int
     * @param  object $wheel
     * @return object
     */
    protected function convertData( $wheel ) 
    {
        if ( is_object( $wheel ) ) 
        {
            $wheel->id = (int)$wheel->id;
            $wheel->numberOfWedges = (int)$wheel->numberOfWedges;
            $wheel->isDeleted = (int)$wheel->isDeleted;
        }

        return $wheel;
    }

    /**
     * get wheel from database
     * @param  int $id
     * @return array or object
     */
    protected function getByIdFromDb( $id ) 
    {
        // get object wheel by if from database
        $result = $this->get( $id );

        if ( empty($result) ) 
        {

            // return log errors when return empty result
            $errors = array( 'code' => 1, 'message' => 'Wheel Not Found' . $id, 'statusCode' => 404 ); 

            return $errors; 
        } 
        else {

            $result->code = 0;
            $result->statusCode = 200;

            // return object of wheel
            return $result;
        }
    }

    /**
    * get wheel by id
    * @param  int $id wheel id
    * @return array
    */
    public function getById( $id ) 
    {
        // validate the id.
        if ( ! is_numeric( $id ) || $id <= 0 ) 
        {    
            // return log errors when id input is invalid
            $error = array( 'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );                     
            return $error; 
        }

        if ( $this->memcacheEnable ) 
        {
            $key = "KEY-Wheel-$id";
            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );
            if ( ! $result ) 
            {
                // get wheel from database if empty on memcache
                $result = $this->getByIdFromDb( $id );
                $this->user->updateMemcache( $key, $result );
            }
            
            return $result;
        }

        // if not enabled caching, just return the data form database.
        return $this->getByIdFromDb( $id );

    }

    /**
    * get all wheels from database
    * @param  int $limit
    * @param  int $offset
    * @return array
    */
    protected function getAllFromDatabase( $limit, $offset ) 
    {
        // get all wheels from database by offset and limit
        $wheels = $this->where("isDeleted", 0)->limit( $limit, $offset )->get_all();
        $count = $this->count_by( 'isDeleted', 0 );

        if ( empty( $wheels ) ) 
        {
            // return log errors when wedge return null
            $errors = array( 'code' => 1, 'message' => 'wheel Not Found', 'statusCode' => 404 );
            return $errors; 
        } 
        else 
        {
            // return all list of wedges
            $results = array( 'code' => 0, 'wheels' => $wheels, 'limit' => (int)$limit, 'offset' => (int)$offset, 'count' => $count, 'statusCode' => 200 );
            return $results;
        }
    }

    /**
    * get all wheels
    * @param  int $limit
    * @param  int $offset
    * @return array
    */
    public function getAll( $limit, $offset ) 
    {
        if ( $this->memcacheEnable ) 
        {    
            $key = "KEY-Wheel-$limit-$offset";

            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );

            if ( ! $result ) 
            {
                // if empty result, will get all wedges from database
                $result = $this->getAllFromDatabase( $limit, $offset );
             
                $this->user->updateMemcache( $key, $result );   
            }
            return $result;
        }
        
        return $this->getAllFromDatabase( $limit, $offset );

    }    
}