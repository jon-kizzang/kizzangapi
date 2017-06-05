<?php
class TrackEmails extends MY_Controller
{
    public function __construct() 
    {
        parent::__construct();
        $this->load->model('trackemail');
    }

    protected function _detect_api_key()
    {
        return true;
    }

    public function emailOpened_get($campaignID, $emailID)
    {
        $results = $this->trackemail->emailOpened($campaignID, $emailID);
        $this->formatResponse( $results );
    }

    public function emailOpened_post($campaignID, $emailID)
    {
        $this->emailOpened_get($campaignID, $emailID);
    }
}
