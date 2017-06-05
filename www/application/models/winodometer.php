<?php

class WinOdometer extends MY_Model {
    
    // set table is WinOdometer
    protected $_table = 'WinOdometer';

    // set validations rules
    protected $validate = array(

        // verify resetAmount must be is required
        'resetAmount' => array(
            'field' => 'resetAmount',
            'label' => 'resetAmount',
            'rules' => 'required|greater_than[0]'
        ),

        // verify currentAmount must be is required
        'currentAmount' => array(
            'field' => 'currentAmount',
            'label' => 'currentAmount',
            'rules' => 'required|greater_than[0]'
        ),
    );

    protected $public_attributes = array(
        'id',
        'resetAmount',
        'currentAmount'
        );

    /**
     * get Win Odometer from database
     * @param  int $wheelId
     * @param  int $id
     * @return array or object
     */
    protected function getByIdFromDb( $id ) {

        // get object Win Odometer by if from database
        $result = $this->get_by('id' , $id );

        if ( empty( $result ) ) {

            // return log errors when return empty result
            $result = array( 'code' => 1, 'message' => 'Win Odometer Not Found', 'statusCode' => 404 ); 
        } 
        else {

            $result->statusCode = 200;           
        }

        // return object of Win Odometers
        return $result;
    }

    /**
    * get win odometer by id
    * @param  int $wheelId
    * @param  int $id win odometer id
    * @return array
    */
    public function getById( $id ) {
        
        if ( $this->memcacheEnable ) {
            
            $key = "KEY-WinOdometer";

            // the first at all, get the result from memcache
            $result = NULL; //$this->memcacheInstance->get( $key );

            if ( ! $result ) {

                // get win odometer from database if empty on memcache
                $result = $this->getByIdFromDb( $id );

                // set the result to memcache for use later
                $this->user->updateMemcache( $key, $result );

            }
        }
        else {

            // if not enabled caching, just return the data form database.
            $result = $this->getByIdFromDb( $id );
        }

        // return object of win odometers
        return $result;
    }   
}