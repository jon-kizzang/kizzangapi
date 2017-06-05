<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Storeitem extends MY_Model 
{

    // set table is Sport Schedule
    protected $_table = 'Store';

    protected $public_attributes = array(
            'id',
            'stortTitle',
            'longTitle',
            'summary',
            'imageUrl',
            'chedda',
            'amount',
            'startDate',
            'endDate'
        );

   public function getAll($limit, $playerId)
   {
       $rs = $this->db->query("Select sum(amount) as amt from Winners where player_id = ? and game_type = 'Store' and YEAR(now()) = ?", array($playerId, date("Y")));
       if($rs->row()->amt >= 300)
           return array('code' => 1, 'message' => 'You cannot exceed $300 per year in Chedda purchases. ', 'year' => date("Y"), 'statusCode' => 200);
       
       $ret = array('code' => 0);
       $rs = $this->db->query("Select id, shortTitle, longTitle, imageUrl, chedda, amount, summary from Store where convert_tz(now(), 'GMT', 'US/Pacific') between startDate and endDate order by chedda limit " . $limit);
       $ret['storeItems'] = $rs->result();
       $ret['statusCode'] = 200;
       return $ret;
   }
   
   public function buy($id, $playerId)
   {
       //$this->db->trans_off();
       $rs = $this->db->query("Select * from Store where id = ? and convert_tz(now(), 'GMT', 'US/Pacific') between startDate and endDate", array($id));
       
       if(!$rs->num_rows())
           return array('code' => 1, 'message' => 'Invalid Store Item', 'statusCode' => 200);
       
       $item = $rs->row();
       
       $rs = $this->db->query("Select firstName as first_name, emailStatus, lastName as last_name, accountName, address, '' as unknown, city, state, zip, phone, phone as cellphone, email, dob, gender, '' as address2
           From Users where id = ?", array($playerId));
       if(!$rs->num_rows())       
           return array('code' => 3, 'message' => 'Invalid Player ID', 'statusCode' => 200);
       $player = $rs->row();
       
       $rs = $this->db->query("Select playerId, sum(count) as cnt from Chedda where playerId = ? and isUsed = 0 group by playerId", array($playerId));
       if(!$rs->num_rows)
           return array('code' => 2, 'message' => 'Insufficient Amount of Chedda', 'statusCode' => 200);
       
       $aggregate = $rs->row();
       if($item->chedda > $aggregate->cnt)
           return array('code' => 2, 'message' => 'Insufficient Amount of Chedda ' . $aggregate->cnt, 'statusCode' => 200);
       
       
       $rs = $this->db->query("Select sum(amount) as amt from Winners where player_id = ? and game_type = 'Store' and YEAR(now()) = ?", array($playerId, date("Y")));
       if($rs->row()->amt + $item->amount > 300)
           return array('code' => 4, 'message' => 'You cannot exceed $300 per year in Chedda purchases. ', 'statusCode' => 200);
       
       $totalCost = $item->chedda;
       $keys = array();
       $rs = $this->db->query("Select * from Chedda where playerId = ? and isUsed = 0", array($playerId));
       
       $cheddas = $rs->result();
       $this->db->trans_start();
       foreach($cheddas as $row)
       {
           if(!$totalCost)
               break;
           
           if($totalCost >= $row->count)
           {
               $keys[] = $row->gameKey;
               $totalCost -= $row->count;
           }
           else
           {
               $this->db->query("Update Chedda set isUsed = 0, count = ? where playerId = ? and isUsed = 0 and gameKey = ?", array($row->count - $totalCost, $playerId, $row->gameKey));
               $this->db->query(sprintf("Insert into Chedda (playerId, gameKey, isUsed, count) values (%d,'%s',%d,%d)", $playerId, $row->gameKey . "-" . rand(0, 1000000), $item->id, $totalCost));
               break;
           }
       }
       if(count($keys))
            $this->db->query(sprintf("Update Chedda set isUsed = %d where playerId = %d and gameKey in ('%s')", $item->id, $playerId, implode("','", $keys)));
       
       $rec = array('player_id' => $playerId, 'foreign_id' => $item->id, 'game_type' => 'Store', 
           'serial_number' => sprintf("KT%05d", $item->id), 'prize_name' => $item->summary, 'amount' => $item->amount, 'processed' => 1, 'status' => 'Claimed');
       
       $rs = $this->db->query("Select sum(amount) as amt from Winners where player_id = ? and YEAR(created) = ?", array($playerId, date("Y")));
       $currentAmount = $rs->row()->amt;
       if($currentAmount + $item->amount >= 600)
       {
           $rs = $this->db->query("Select s.id from rightSignature.signins s
               Inner join rightSignature.templates t on t.id = s.templateId and t.type = 'W9'
               Where s.playerId = ? and YEAR(now()) = YEAR(s.created) and status = 'Complete'", array($playerId));
           if(!$rs->num_rows())
               $rec['status'] = 'Document';
           $templateId = 1;
       }
       else
       {
           $rs = $this->db->query("Select s.id from rightSignature.signins s
               Inner join rightSignature.templates t on t.id = s.templateId and t.type = 'DL'
               Where s.playerId = ? and YEAR(now()) = YEAR(s.created) and status = 'Complete'", array($playerId));
           if(!$rs->num_rows())
               $rec['status'] = 'Document';
           $templateId = 2;
       }
       
       if(!$this->db->insert("Winners", $rec))
       {
           $this->db->trans_rollback();
           return array('code' => 3, 'message' => 'Error Processing Transaction', 'statusCode' => 200);
       }              
       
       $this->db->trans_commit();
       
       $rec['id'] = $this->db->insert_id();
       $body = $this->load->view("emails/wrapper", array('content' => $this->load->view('emails/cheddaReceipt', array('prize' => $rec['prize_name'], 'email' => $player->email), true), 'emailCode' => md5($player->accountName)), true);
       if($player->emailStatus != 'Transaction Opt Out' && $player->emailStatus != 'Both Opt Out')
           $this->user->sendGenericEmail($player->email, "Kizzang - Chedda Purchase", $body);
        
       if($rec['status'] == 'Document')
       {
           $this->load->model('eventnotification');
            $guid = $this->eventnotification->gen_uuid();
            $expiration_date = date("Y-m-d H:i:s", strtotime("+24 hours"));
            $rec = array('id' => $guid, 'playerId' => $playerId, 'status' => 'Pending', 'expirationDate' => $expiration_date, 'templateId' => $templateId);
            
            $this->db->insert("rightSignature.signins", $rec);
            if($templateId == 1)
            {
                $this->db->where(array('id' => $playerId));
                $this->db->update('Users', array('accountStatus' => 'W2 Blocked'));
                $body = $this->load->view('emails/wrapper', array('content' => $this->load->view('emails/600Tax', array('uuid' => $guid, 'expirationDate' => $expiration_date, 'total' => $currentAmount + $item->amount, 'amount' => $item->amount), true), 'emailCode' => md5($player->accountName)), true);
            }
            else
            {
                $body = $this->load->view('emails/wrapper', array('content' => $this->load->view('emails/win', array('uuid' => $guid, 'expirationDate' => $expiration_date, 
                    'prize' => $item->summary, 'game' => "Chedda Store", 'serialNumber' => sprintf("KT%05d", $item->id), 'winnerId' => 0), true), 'emailCode' => md5($player->accountName)), true);
            }
            if($player->emailStatus != 'Transaction Opt Out' && $player->emailStatus != 'Both Opt Out')                
                $this->user->sendGenericEmail($player->email, "Potential Winner", $body);
       }
       return array('code' => 0, 'message' => 'Purchase Successful', 'statusCode' => 200);
   }     
}