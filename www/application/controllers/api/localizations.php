<?php

class Localizations extends MY_Controller 
{	
    public function __construct() 
    {              
        parent::__construct(
            TRUE, // Controller secured
            array(
               'getById' => array('Administrator', 'User', 'Guest'),
               'getAll'	=> array('Administrator', 'User', 'Guest'),
            )//secured action
        );
        
        $this->load->model('localization');
        
        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );
    }

    /**
     * get all language localization strings
     * GET /api/localization
     * @return json
     */
    public function getAll_get( $languageId ) 
    {        
        $results = $this->localization->getAll( $languageId );
        $this->formatResponse( $results );		
    }

    public function getAll_post( $languageId ) 
    {
        $this->getAll_get( $languageId );
    }

    /**
     * get language localization strings by language
     * GET /api/localization/{language}
     * @return json
     */
    public function getById_get( $languageId, $translationId ) 
    {
        $results = $this->localization->getById( $languageId, $translationId );
        $this->formatResponse( $results );
    }

    public function getById_post( $languageId, $translationId ) 
    {
        $this->getById_get( $languageId, $translationId );
    }
}