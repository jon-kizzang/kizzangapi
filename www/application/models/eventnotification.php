<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require dirname(__FILE__) . '/../../vendor/autoload.php';

use Aws\Common\Aws;

class EventNotification extends MY_Model {

    // set table is Event Notification
    protected $_table = 'EventNotifications';

    protected $token = null;

    // set validations rules
    protected $validate = array(
        'playerId' => array( 
            'field' => 'playerId', 
            'label' => 'playerId',
            'rules' => 'required'
        ),
        'type' => array( 
            'field' => 'type', 
            'label' => 'type',
            'rules' => 'required'
        ),
        'data' => array( 
            'field' => 'data', 
            'label' => 'data',
            'rules' => 'required'
        ),
        'pending' => array( 
            'field' => 'pending', 
            'label' => 'pending',
            'rules' => 'is_numeric'
        ),
        'playerActionTaken' => array( 
            'field' => 'playerActionTaken', 
            'label' => 'player Action Taken',
            'rules' => 'is_numeric'
        ),
        'expireDate' => array( 
            'field' => 'expireDate', 
            'label' => 'Expire Date'
        ),
    );

    protected $public_attributes = array(
            'id',
            'playerId',
            'type',
            'data',
            'pending',
            'added',
            'updated',
            'playerActionTaken',
            'expireDate'
        );
    // mark execute function from unit test
    public $executeTesting = FALSE;
   
    public function setToken( $token ) 
    {
        $this->token = $token;
    }
	    
    public function getByPlayerId( $playerId ) 
    {
        if ( is_numeric($playerId))
        {            
            // get object Event Notification by id from database
            $rs = $this->db->query("Select * from EventNotifications 
                where playerId = ? and pending = 1 and (expireDate > ? or expireDate IS NULL) and type not in ('wheel','chedda')", array($playerId, date("Y-m-d H:i:s")));            

            if ( !$rs->num_rows() )             
                return array( 'code' => 0, 'eventNotifications' => null, 'count' => 0, 'statusCode' => 200 );
            
            $notifications = array();
            foreach($rs->result() as $row)            
                $notifications[] = $row;            
            
            return array( 'code' => 0, 'eventNotifications' => $notifications, 'count' => $rs->num_rows(), 'statusCode' => 200 );                            
        }
        else 
        {
            return array( 'code' => 2, 'message' => 'Invalid player Id', 'statusCode' => 404 );            
        }
    }
    
    public function ack($playerId, $id)
    {        
        $rs = $this->db->query("Select * from EventNotifications where id = ? and playerId = ? and pending = 1", array($id, $playerId));
        //print $this->db->last_query(); die();
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Invalid Notification', 'statusCode' => 200);
        
        $notification = $rs->row();
        $notification->info = json_decode($notification->data, true);
        
        $rs = $this->db->query("Select firstName as first_name, lastName as last_name, address, '' as unknown, city, state, zip, accountName, phone, phone as cellphone, email, dob, gender, '' as address2, emailStatus 
           From Users where id = ?", array($playerId));
       if(!$rs->num_rows())       
           return array('code' => 2, 'message' => 'Invalid Player ID', 'statusCode' => 200);
       $player = $rs->row();
       
       $this->processPreviousWins($player->email, $playerId);
              
       $this->db->trans_start();
       $this->db->query("Update EventNotifications set pending = 0 where id = ?", array($id));
       
       if($notification->type == "notice" || $notification->type == "chedda")
       {
           $this->db->trans_complete();
           return array('code' => 0, 'message' => 'Notification Updated', 'statusCode' => 200);
       }
       
       if($notification->type == "cheddaEvent")
       {
           $this->db->trans_complete();
           $this->load->model('chedda');
           $chedda = array('playerId' => $playerId, 'gameKey' => "Chedda-" . $notification->info['serialNumber'] . "-" . $notification->info['entry'], 'isUsed' => 0, 'count' => $notification->info['chedda']);
           return $this->chedda->add($chedda);
       }
                      
        $rs = $this->db->query("Select * from Winners where id = ?", array($notification->info['entry']));
        $winner = $rs->row();
        
        $types = array('Slots' => 'slotTournament', 'Scratchers' => 'scratchCard', 'Sweepstakes' => 'sweepstakes', 
            'Parlay' => 'dailyShowdown', 'BG' => 'bigGame', 'FT' => 'finalThree', 'Store' => 'store', 'Lottery' => 'lottery');
                
        $previous_win = 0;
        $rs = $this->db->query("Select sum(amount) as sm from Winners where processed = 1 and player_id = ? and YEAR(created) = ? and id <> ?", array($playerId, date('Y'), $notification->info['entry']));
        if($rs->num_rows())            
            $previous_win = $rs->row()->sm;            

        $win = $winner->amount;
        $rec = array('processed' => 1, 'status' => 'Claimed');
        if($win + $previous_win >= 600)
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
        //Get game expiration dates
        $rs = $this->db->query("Select * from GameExpireTimes where game = ? and ? between lowAmount and highAmount LIMIT 1", array($types[$winner->game_type], $previous_win < 600 && $previous_win + $win >= 600 ? $win + $previous_win : $win));
        if($rs->num_rows())
        {
            $time = $rs->row();
            $expiration_date = date('Y-m-d H:i:s', strtotime("+" . $time->numMinutes . " minutes"));
        }        
        else
        {
            $expiration_date = date('Y-m-d H:i:s', strtotime("+2 days"));
        }
        $rec['expirationDate'] = $expiration_date;
        //Update Winnner record
        $this->db->where(array('id' => $notification->info['entry'], 'serial_number' => $notification->info['serialNumber']));
        $this->db->update("Winners", $rec);
        
        $rs = $this->db->query("Select * from rightSignature.signins where templateId = 1 and status = 'Complete' and playerId = ? and YEAR(expirationDate) = YEAR(now())", array($playerId));
        if(!$rs->num_rows())
        {
            $guid = $this->gen_uuid();
            $rec = array('id' => $guid, 'playerId' => $playerId, 'status' => 'Pending', 'expirationDate' => $expiration_date);
            if($previous_win + $win >= 600)
                $rec['templateId'] = 1;
            else
                $rec['templateId'] = 2;
            
            $this->db->insert("rightSignature.signins", $rec);
            if($previous_win + $win >= 600)
            {
                $this->db->where(array('id' => $playerId));
                $this->db->update('Users', array('accountStatus' => 'W2 Blocked'));
                $body = $this->load->view('emails/wrapper', array('content' => $this->load->view('emails/600Tax', array('uuid' => $guid, 'expirationDate' => $expiration_date, 'total' => $previous_win + $win, 'amount' => $win), true), 'emailCode' => md5($player->accountName)), true);
            }
            else
            {
                $body = $this->load->view('emails/wrapper', array('content' => $this->load->view('emails/win', array('uuid' => $guid, 'expirationDate' => $expiration_date, 
                    'prize' => $winner->prize_name, 'game' => $winner->game_name, 'serialNumber' => $winner->serial_number, 'winnerId' => $winner->id), true), 'emailCode' => md5($player->accountName)), true);
            }
        }
        else
        {
            $this->db->where(array('id' => $notification->info['entry'], 'serial_number' => $notification->info['serialNumber']));
            $this->db->update("Winners", array('status' => 'Claimed'));
            $body = $this->load->view('emails/wrapper', array('content' => $this->load->view('emails/win', array('uuid' => $guid, 'expirationDate' => $expiration_date, 
                'prize' => $winner->prize_name, 'game' => $winner->game_name, 'serialNumber' => $winner->serial_number, 'winnerId' => $winner->id), true), 'emailCode' => md5($player->accountName)), true);
        }    
        if($player->emailStatus != 'Transaction Opt Out' && $player->emailStatus != 'Both Opt Out')                
            $this->user->sendGenericEmail($player->email, "You are a Potential Winner", $body, 'winners@kizzang.com');
                
        $this->db->trans_complete();
        if($this->db->trans_status() === FALSE)
            return array('code' => 4, 'message' => 'Transaction Failed', 'statusCode' => 200);
          
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
                $this->user->sendGenericEmail($player->email, "You are a Potential Winner", $body, 'winners@kizzang.com');
        }
        return array('code' => 0, 'message' => 'Transaction Successful', 'statusCode' => 200);
    }
    
    public function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
    
    public function processPreviousWins($email, $playerId)
    {
        $rs = $this->db->query("Select * from archive.paidList where email = ? and processed = 0", array($email));
        if($rs->num_rows())
        {
            foreach($rs->result() as $row)
            {
                $gameType = "";
                switch(substr($row->serialNumber, 0, 2))
                {
                    case 'KS': $gameType = 'Slots'; break;
                    case 'KZ': $gameType = 'Scratchers'; break;
                    case 'KW': $gameType = 'Sweepstakes'; break;
                    case 'KP': $gameType = 'Parlay'; break;
                    case 'KB': $gameType = 'BG'; break;
                    case 'KF': $gameType = 'FT'; break;
                    case 'KT': $gameType = 'Store'; break;
                    case 'KL': $gameType = 'Lottery'; break;
                }
                $rec = array('player_id' => $playerId, 'foreign_id' => 0, 'game_type' => $gameType, 'serial_number' => $row->serialNumber, 
                    'prize_name' => '$' . number_format($row->amount, 2), 'amount' => $row->amount, 'prize_email' => 'Imported from Old System',
                    'processed' => 0, 'status' => 'Approved');
                $this->db->insert('Winners', $rec);
                
                $this->db->query("Update archive.paidList set processed = 1 where id = ?", array($row->id));
            }
        }
    }
       
    protected function getByIdFromDb( $id ) 
    {
        // get object Event Notification by id from database
        $result = $this->get( $id );

        if ( empty( $result ) ) 
        {
            // return log errors when return empty result
            $errors = array( 'code' => 1, 'message' => 'Event Notification Not Found', 'statusCode' => 404 ); 
            return $errors; 
        } 
        else 
        {
            $result->statusCode = 200;
            $result->code = 0;
            // return object of Event Notification
            return $result;
        }
    }

    /**
    * get Event Notification by id
    * @param  int $id Event Notification id
    * @return array
    */
    public function getById( $id ) 
    {        
        if ( ! is_numeric( $id ) || $id <= 0 )           
            return array(  'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );            
        
        return $this->getByIdFromDb( $id );
    }
    
    public function getTicketEventDB($event_id, $player_id)
    {
        $notfication = $this->get_by(array('id' => $event_id, 'playerId' =>$player_id, 'pending' => 1));
        if($notfication)
            return true;
        
        return false;
    }

    /**
     * get all Event Notification
     * @return array
     */
    public function getAll() 
    {       
        $eventNotifications = $this->get_many_by( 'pending', 0 );
     
        if ( empty( $eventNotifications) ) 
            $result = array( 'code' => 1, 'message' => 'Event Notification Not Found', 'statusCode' => 404 );        
        else         
            $result = array( 'code' => 0, 'eventNotifications' => $eventNotifications, 'count' => count( $eventNotifications ), 'statusCode' => 200 );
            
        return $result;
    }

    public function add( $data, $playerId ) 
    {
        // validate data insert 
        if ( empty( $data ) || ( $playerId === FALSE ) ) 
            return array( 'code' => 1, 'message' => 'Please enter the required data', 'statusCode' => 400 );
            
        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $this->validate );

        if ( $this->form_validation->run() === FALSE )         
            $result = array( 'code' => 2, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 400 );                
        
        if ( array_key_exists( 'expireDate', $data ) )
        {
            $expireDateInt = strtotime( str_replace( '-', '/', $data['expireDate'] ) );
            if ( $expireDateInt <= strtotime( 'now' ) ) 
                return array( 'code' => 4, 'message' => 'The Expire Date must be greater than current date', 'statusCode' => 400 );

            $data['expireDate'] = date( 'Y-m-d H:i:s', $expireDateInt );
        }
        else
        {
            $data['expireDate'] = null;
        }

        $data['playerId']   = $playerId;
        $data['added']      = date( 'Y-m-d H:i:s' );
        $data['pending']    = 1;

        // set skip_validation = TRUE in 2nd parameter
        $insertId = $this->insert( $data, TRUE );

        if ( $insertId ) 
        {            
            $result = $this->getById( $insertId );
            $result->statusCode = 201;
        } 
        else 
        {          
            $errorMessage = $this->db->_error_message();
            log_message( 'error', 'Insert Event Notification: ' . $errorMessage );
            $result = array( 'code' => 3, 'message' => $errorMessage, 'statusCode' => 400 );
        }   
       
        return $result;
    }        
}
