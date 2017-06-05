<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require dirname(__FILE__) . '/../../vendor/autoload.php';

use Aws\Common\Aws;


class TrackEmail extends MY_Model
{
    public function emailOpened($campaignID, $emailID)
    {        
        $this->db->query("Update MarketingCampaignEmails set opened = 1 where marketing_campaign_id = ? and marketing_email_id = ?", array($campaignID, $emailID));        
        return array('message' => 'Update Successful', 'code' => 0);
    }
}
