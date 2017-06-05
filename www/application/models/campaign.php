<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Campaign extends MY_Model {

    // set table to DonationOrganizations
    protected $_table = 'Sponsor_Advertising_Campaigns';

    // set validations rules
    protected $validate = array(
        'name' => array( 
            'field' => 'name', 
            'label' => 'name',
            'rules' => 'required'
        ),
    );

    protected $public_attributes = array(
            'id',
        );

    protected function getByIdFromDb( $campaignId ) 
    {
        $campaign = $this->get( $campaignId );

        if ( empty($campaign) )
            $result = array( 'code' => 1, 'error' => 'Campaign not found', 'statusCode' => 200 );                     
        else 
            $result = array( 'code' => 0, 'campaign' => $campaign, 'statusCode' => 200 );

        return $result;
    }

    public function getById( $campaignId ) 
    {        
        return $this->getByIdFromDb( $campaignId );
    }   
}