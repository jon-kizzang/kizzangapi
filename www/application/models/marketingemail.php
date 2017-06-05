<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MarketingEmail extends MY_Model
{
       protected $_table = "marketing.impressions";
       protected $validate = array(
            'email_campaign_id' => array( 
                'field' => 'email_campaign_id', 
                'label' => 'Email Campaign ID',
                'rules' => 'required'
            ),
            'destination' => array( 
                'field' => 'destinationy', 
                'label' => 'destination',
                'rules' => 'required'
            ),
            'user_agent' => array( 
                'field' => 'user_agent', 
                'label' => 'user_agent',
                'rules' => 'required'
            ),
            'ip_address' => array( 
                'field' => 'ip_address', 
                'label' => 'ip_address',
                'rules' => 'required'
            ),
           'url' => array( 
                'field' => 'url', 
                'label' => 'url',
                'rules' => 'required'
            )
        );
       
       protected $public_attributes = array(
           'email_campaign_id',
           'destination',
           'user_agent',
           'ip_address',
           'adwords',
           'fingerprint',
           'url');
        
        public function impression( $data )
        {		
            if(isset($data['email_campaign_id']) && isset($data['destination']) && isset($data['user_agent']) && isset($data['ip_address']))
            {
                $data['fingerprint'] = "NONE";
                if(!isset($data['url']))
                    $data['url'] = "";
                if($this->insert($data, true))
                    return array('code' => 0, "id" => $data['email_campaign_id'], "statusCode" => 200);

                return array('code' => 2, 'message' => "Error inserting information", "statusCode" => 400);

            }
            return array('code' => 1, 'message' => "Invalid Array", "statusCode" => 400);
        }
}
