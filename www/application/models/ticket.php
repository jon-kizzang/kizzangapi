<?php

class Ticket extends MY_Model {
    
    // set table is Tickets
    protected $_table = 'Tickets';    

    // set validations rules
    protected $validate = array(
        'playerId' => array(
            'field' => 'playerId',
            'label' => 'playerId',
            'rules' => 'required|numeric'
        ),
        'sweepstakesId' => array(
            'field' => 'sweepstakesId',
            'label' => 'sweepstakesId',
            'rules' => 'required|numeric'
        ),
         'ticketDate' => array(
            'field' => 'ticketDate',
            'label' => 'ticketDate',
            'rules' => 'required'
        )
    );

    protected $public_attributes = array(
        'playerId',
        'swepstakesId',
        'ticketDate'
        );              

    public function add($sweepstakesId, $playerId)
    {
        $this->load->model('chedda');
        $this->load->model('configs');
        $ticketDate = date("Y-m-d");
        
        $rs = $this->db->query("Select * from Sweepstakes where id = ? and convert_tz(now(), 'GMT', 'US/Pacific') between startDate and endDate", array($sweepstakesId));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'This sweepstakes is not active or does not exist.', 'statusCode' => 200);
        
        $rs = $this->db->query("Select * from Tickets where playerId = ? and sweepstakesId = ? and ticketDate = ?", array($playerId, $sweepstakesId, $ticketDate));
        if($rs->num_rows())
            return array('code' => 2, 'message' => 'You have already entered this sweepstakes today.', 'statusCode' => 200);
                
        if(!$this->db->insert("Tickets", compact('playerId','sweepstakesId','ticketDate')))
        {
            print $this->db->last_query(); die();
            return array('code' => 3, 'message' => 'Entry did not save.', 'statusCode' => 200);
        }
        
        $config = $this->configs->getConfigElement('File', 'Chedda');
        $config->items = json_decode($config->info, true);
        $expArray = array();
        foreach($config->items as $index => $value)        
            for($i=0; $i < $value; $i++)
                $expArray[] = $index;
                
        $totalChedda = $expArray[rand(0, count($expArray) - 1)];
        $rec = array('playerId' => $playerId, 'gameKey' => 'Sweepstakes-' . $sweepstakesId . date('Y-m-d'), 'isUsed' => 0, 'count' => $totalChedda);
        $this->chedda->add($rec);        
        
        return array('code' => 0, 'message' => 'Ticket Added', 'chedda' => $totalChedda, 'statusCode' => 200);
    }
}   