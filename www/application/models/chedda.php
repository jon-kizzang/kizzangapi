<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Chedda extends MY_Model 
{

    // set table is Sport Schedule
    protected $_table = 'Chedda';

    protected $public_attributes = array(
            'playerId',
            'gameKey',
            'isUsed',
            'count',
            'created',
            'updated'
        );

    public function addEventNotification($playerId, $gameInfo, $gameType = "Slot")
    {
        $gameInfo['chedda'] = $this->getChedda();
        $rec = array('playerId' => $playerId, 'playerActionTaken' => 0, 'type' => 'chedda', 'data' => json_encode($gameInfo), 'pending' => 1, 'expireDate' => date("Y-m-d H:i:s", strtotime("+24 hours")));
        if(!$this->db->insert("EventNotifications", $rec))
            return array('code' => 1, 'message' => 'Error adding Event', 'statusCode' => 200);
        
        if(!isset($gameInfo['chedda']))
            $gameInfo['chedda'] = $this->getChedda ();
        
        $chedda = array('playerId' => $playerId, 'gameKey' => $gameType . "-" . $gameInfo['serialNumber'] . "-" . $gameInfo['entry'], 'isUsed' => 0, 'count' => $gameInfo['chedda']);
        return $this->add($chedda);
    }
    
    public function getEventNotifications($playerId)
    {
        $rs = $this->db->query("Select * from EventNotifications where playerId = ? and type = 'chedda' and pending = 1 order by id DESC", array($playerId));
        return array('code' => 0, 'notifications' => $rs->result(), 'count' => $rs->num_rows(), 'statusCode' => 200);
    }
    
    public function getChedda()
    {
        $this->load->model('configs');
        $config = $this->configs->getConfigElement('File', 'Chedda');
        $config->items = json_decode($config->info, true);
        $expArray = array();
        foreach($config->items as $index => $value)        
            for($i=0; $i < $value; $i++)
                $expArray[] = $index;
        
        return $expArray[rand(0, count($expArray) - 1)];
    }
    
    public function getStatus($playerId)
    {
       $ret = array('code' => 0);
       $ret['chedda'] = array('available' => 0, 'used' => 0);
       $rs = $this->db->query("Select playerId, isUsed, sum(count) as cnt from Chedda where playerId = ? group by playerId, isUsed", array($playerId));

       foreach($rs->result() as $rec)
       {
           if($rec->isUsed == 0)
               $ret['chedda']['available'] = $rec->cnt;
           if($rec->isUsed == 1)
               $ret['chedda']['used'] = $rec->cnt;
       }
       $ret['statusCode'] = 200;
       return $ret;
   }
   
   public function add($data)
   {
       $validate = array(        
            'playerId' => array(
                'field' => 'playerId',
                'rules' => 'required|numeric'
            ),
            'gameKey' => array(
                'field' => 'gameKey',
                'rules' => 'required|xss_clean|max_length[50]|trim'
            ),                               
            'count' => array(
                'field' => 'count',
                'rules' => 'required|numeric'
            )
        );
       
        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );
        
        if ( $this->form_validation->run() === FALSE )
            return array( 'code' => 1, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );
        
         $this->db->insert("Chedda", $data); 
         $insertId = $this->db->insert_id();
         return array('code' => 0, 'message' => 'Chedda Added to account', 'id' => $insertId, 'statusCode' => 200);
   }
}