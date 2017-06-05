<?php

class Rule extends MY_Model {
    
    // set table is Tickets
    protected $_table = 'Rules';

    // set validations rules
    protected $validate = array(
    );

    protected $public_attributes = array(
        'id',
        'TermsOfService',
        'PrivacyPolicy',
        'ParticipationRules',
        'created'
        );

    /**
    * get all rules from database
    * @return array
    */
    protected function getAllFromDatabase() {
        
        // get all ticket is not deleted from database by offset and limit
        $rules = $this->order_by("id", "DESC")->limit(1)->get_all();
        
        if ( empty( $rules ) ) {
            // return log errors when ticket return null
            $results = array( 'code' => 1, 'message' => 'Rules Not Found', 'statusCode' => 404 );
        } else {
            // return all list of tickets
            $results = array( 'rules' => $rules, 'statusCode' => 200 );
        }

        return $results;
    }
  
    public function getAll() 
    {   
        return $this->getAllFromDatabase();
    }
}   