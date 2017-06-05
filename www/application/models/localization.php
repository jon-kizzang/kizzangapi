<?php

class Localization extends MY_Model {
    
    // set table is Tickets
    protected $_table = 'Localization';

    protected $public_attributes = array(
        'id',
        'identifier',
        'languageId',
        'description',
        'translation'
        );

    protected function getFromDatabase( $languageId, $translationId ) 
    {    
        // get all lanagues
        $languageStrings = $this->get_many_by( array('languageId' => $languageId, 'id' => $translationId ) );
        
        if ( empty( $languageStrings ) ) 
            $results = array( 'code' => 1, 'message' => 'Language strings not found', 'statusCode' => 404 );        
        else             
            $results = array( 'code' => 0, 'languageStrings' => $languageStrings, 'statusCode' => 200 );        

        return $results;
    }

    protected function getAllFromDatabase( $languageId ) 
    {    
        // get all lanagues
        $languageStrings = $this->get_many_by( array( 'languageId' => $languageId ) );

        if ( empty( $languageStrings ) )            
            $results = array( 'code' => 1, 'message' => 'Language strings not found', 'statusCode' => 404 );        
        else            
            $results = array( 'code' => 0, 'languageStrings' => $languageStrings, 'statusCode' => 200 );        

        return $results;
    }

    public function getById( $languageId, $translationId ) 
    {
        return $this->getFromDatabase( $languageId, $translationId );        
    }

    public function getAll( $languageId ) 
    {
        return $this->getAllFromDatabase( $languageId );
    }
}   