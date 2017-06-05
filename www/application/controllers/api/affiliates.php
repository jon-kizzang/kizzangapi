<?php
 
class Affiliates extends MY_Controller 
{    
    public function __construct() 
    {
        parent::__construct(
            TRUE, // Controller secured
            array(
                'getStatus' => array('Administrator', 'User', 'Guest'),
                'getCampaigns' => array('Administrator', 'User', 'Guest')
            )//secured action
        );

        //loading model
        $this->load->model('affiliate');

        // set token to player model use this variable
        if ($this->token) {
            $this->user->setToken($this->token);
        }
    }        

    public function getStatus_get($referralCode) 
    {        
        $result = $this->affiliate->getStatus($referralCode, $this->token);
        $this->formatResponse( $result );
    }

    public function getStatus_post($referralCode) 
    {
        $this->getStatus_get($referralCode);        
    }
    
    public function getCampaigns_post($type, $theme)
    {
        $result = $this->affiliate->getCampaigns($type, $theme);
        $this->formatResponse( $result );
    }
}