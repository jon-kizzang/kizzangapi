<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Affiliate extends MY_Model {

    // set table is wheel
    protected $_table = 'AffiliateGames';
    
    protected $public_attributes = array(
            'Sponsor_Advertising_Campaign_Id',
            'GameType',
            'Theme',
            'created',
            'updated'
        );
    
    public function getStatus( $code, $token) 
    {
        if(!$code)
            return array( 'code' => 1, 'message' => 'No Campaign for User', 'statusCode' => 200 );
        
        $rs = $this->db->query("Select * from Sponsor_Advertising_Campaigns 
            where code = ? and convert_tz(now(), 'GMT', 'US/Pacific') between start_date and end_date and advertising_medium_id = 'affiliate'", array($code));
        
        if(!$rs->num_rows())
            return array( 'code' => 2, 'message' => 'Invalid Campaign', 'statusCode' => 200 );
        
        $session = $this->sessions->getSessionData($token);
        $session['referralCode'] = $code;
        $this->sessions->updateData($token, 'session_data', $session);
        return array( 'code' => 0, 'message' => 'Campaign Valid', 'statusCode' => 200 );
    }
    
    public function getCampaigns($type, $theme)
    {
        $rs = $this->db->query("Select group_concat(Sponsor_Advertising_Campaign_Id) as campaigns from AffiliateGames where GameType = ? and Theme = ?", array($type, $theme));
        $campaigns = $rs->row()->campaigns;
        if(!$campaigns)
            return array( 'code' => 1, 'message' => 'No Matching Campaign Found', 'statusCode' => 200 );
        
        $rs = $this->db->query("Select a.id, a.message, s.artRepo as image
            From Sponsor_Advertising_Campaigns a
            Inner join Sponsors s on a.utm_source = s.id
            Where a.id in ('" . str_replace(",", "','", $campaigns) . "') and convert_tz(now(), 'GMT', 'US/Pacific') between start_date and end_date");
        return array('code' => 0, 'campaigns' => $rs->result(), 'statusCode' => 200);
    }

}