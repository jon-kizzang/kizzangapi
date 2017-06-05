<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use Aws\Common\Aws;

class Bracket extends MY_Model {
       
    protected $_table = 'BracketConfigs';

    protected $testing = FALSE;    

    function __construct() 
    {
        parent::__construct();
    }
    
    public function getByIdTest( $id, $playerId ) 
    {
        $rs = $this->db->query("Select * from BracketConfigs where id = ?", array($id));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Bracket does not exist', 'statusCode' => 200);
        
        $config = $rs->row();
        $serialNumber = sprintf("KB%05d", $id);
        
        $rs = $this->db->query("Select round, startDate, endDate from BracketTimes where bracketConfigId = ? order by round DESC", array($id));
        $times = $rs->result();
        
        $teams = array();
        $rs = $this->db->query("Select teamId1 as team_id, name, teamRank1 as seed, concat(wins, '-', losses) as record, division  
            from BracketMatchups b 
            Inner join SportTeams s on b.teamId1 = s.id and s.sportCategoryID = ? 
            order by s.id", array($config->sportCategoryId));
        foreach($rs->result() as $row)
            $teams[] = $row;
        
        $rs = $this->db->query("Select teamId2 as team_id, name, teamRank2 as seed, concat(wins, '-', losses) as record, division  
            from BracketMatchups b 
            Inner join SportTeams s on b.teamId2 = s.id and s.sportCategoryID = ? 
            order by s.id", array($config->sportCategoryId));
        foreach($rs->result() as $row)
            $teams[] = $row;
                
        $divisions = array('MidWest','West','South','East');
        $games = array();
        foreach($divisions as $division)
        {
            $rs = $this->db->query("Select * from BracketMatchups where bracketConfigId = ? and division = ? order by sequence", array($id, $division));
            if(!$rs->num_rows() || $rs->num_rows() != 8)
                return array('code' => 2, 'message' => "Division $division is missing records", 'statusCode' => 200);
            
            foreach($rs->result() as $matchup)            
                $games['round_64'][$division][] = array('team1' => array($matchup->teamId1), 'team2' => array($matchup->teamId2));
        }  
        
        $startDate = strtotime(date("Y-m-d H:00:00", strtotime("now")));
        $endDate = $startDate + 3600;
        $now = strtotime(date("Y-m-d H:i:s", strtotime("now")));
        $sqlStartDate = $sqlEndDate = NULL;
        for($i = $startDate; $i < $endDate; $i += 720)
        {
            if($now > $i && $now < $i +720)
            {
                $sqlStartDate = date("Y-m-d H:i:s", $i);
                $sqlEndDate =  date("Y-m-d H:i:s", $i + 720);
                break;
            }
        }
        
        $created = strtotime(date("Y-m-d"));
        $configEndDate = strtotime($config->endDate);
        if($created + 720 < $configEndDate)
            $endDate = str_replace("##", "GMT-0800", date("D M d Y H:i:s ## (T)", strtotime($sqlEndDate)));
        else
            $endDate = str_replace("##", "GMT-0800", date("D M d Y H:i:s ## (T)", $configEndDate));
        
        $rs = $this->db->query("Select *, convert_tz(created, 'GMT', 'US/Pacific') as cvdate from BracketPlayerMatchups where playerId = ? and date(convert_tz(created, 'GMT', 'US/Pacific')) between ? and ?", array($playerId, $sqlStartDate, $sqlEndDate));
        //print $this->db->last_query(); die();
        if($rs->num_rows())
        {
            $picks = json_decode ($rs->row()->data);
            $tiebreaker1 = $rs->row()->tieBreakerTeam1;
            $cardId = $rs->row()->id;
            $tiebreaker2 = $rs->row()->tieBreakerTeam2;
            $status = $rs->row()->status;                        
        }
        else
        {
            $picks = $cardId = NULL;
            $tiebreaker1 = $tiebreaker2 = 0;
            $status = "New";
        }
        
        return array('code' => 0, 'teams' => $teams, 'games' => $games, 'times' => $times, 'picks' => $picks, 'cardId' => $cardId, 'tiebreaker1' => $tiebreaker1, 
            'tiebreaker2' => $tiebreaker2, 'endDate' => $endDate, 'status' => $status, 'serialnumber' => $serialNumber, 'statusCode' => 200);
    }   
    
    public function getById( $id, $playerId ) 
    {
        $rs = $this->db->query("Select * from BracketConfigs where id = ?", array($id));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Bracket does not exist', 'statusCode' => 200);
        
        $config = $rs->row();
        $serialNumber = sprintf("KB%05d", $id);
        
        $rs = $this->db->query("Select round, startDate, endDate from BracketTimes where bracketConfigId = ? order by round DESC", array($id));
        $times = $rs->result();
        foreach($times as &$time)
            if($time->round == 2 || $time->round == 4)
                $time->startDate = "";
        
        $teams = array();
        $rs = $this->db->query("Select teamId1 as team_id, name, teamRank1 as seed, concat(wins, '-', losses) as record, division  
            from BracketMatchups b 
            Inner join SportTeams s on b.teamId1 = s.id and s.sportCategoryID = ? 
            order by s.id", array($config->sportCategoryId));
        foreach($rs->result() as $row)
            $teams[] = $row;
        
        $rs = $this->db->query("Select teamId2 as team_id, name, teamRank2 as seed, concat(wins, '-', losses) as record, division  
            from BracketMatchups b 
            Inner join SportTeams s on b.teamId2 = s.id and s.sportCategoryID = ? 
            order by s.id", array($config->sportCategoryId));
        foreach($rs->result() as $row)
            $teams[] = $row;
                
        $divisions = array('MidWest','West','South','East');
        $games = array();
        foreach($divisions as $division)
        {
            $rs = $this->db->query("Select * from BracketMatchups where bracketConfigId = ? and division = ? order by sequence", array($id, $division));
            if(!$rs->num_rows() || $rs->num_rows() != 8)
                return array('code' => 2, 'message' => "Division $division is missing records", 'statusCode' => 200);
            
            foreach($rs->result() as $matchup)            
                $games['round_64'][$division][] = array('team1' => array($matchup->teamId1), 'team2' => array($matchup->teamId2));
        }
        
        $created = strtotime(date("Y-m-d"));
        $configEndDate = strtotime($config->endDate);
        if($created + 86400 < $configEndDate)
           $endDate = str_replace("##", "GMT-0700", date("D M d Y 0:i:s ## (T)", $created + 86400));
        else
            $endDate = str_replace("##", "GMT-0700", date("D M d Y H:i:s ## (T)", $configEndDate));
        
        $rs = $this->db->query("Select *, convert_tz(created, 'GMT', 'US/Pacific') as cvdate from BracketPlayerMatchups where playerId = ? and date(convert_tz(created, 'GMT', 'US/Pacific')) = date(convert_tz(now(), 'GMT', 'US/Pacific'))", array($playerId));
        if($rs->num_rows())
        {            
            $picks = json_decode ($rs->row()->data);
            $tiebreaker1 = $rs->row()->tieBreakerTeam1;
            $cardId = $rs->row()->id;
            $tiebreaker2 = $rs->row()->tieBreakerTeam2;
            $status = $rs->row()->status;
        }
        else
        {
            $picks = $cardId = NULL;
            $tiebreaker1 = $tiebreaker2 = 0;
            $status = "New";
        }
           
        return array('code' => 0, 'teams' => $teams, 'games' => $games, 'times' => $times, 'picks' => $picks, 'cardId' => $cardId, 'tiebreaker1' => $tiebreaker1, 
            'tiebreaker2' => $tiebreaker2, 'endDate' => $endDate, 'status' => $status, 'serialnumber' => $serialNumber, 'statusCode' => 200);
    }   
    
    public function celebrities($id)
    {
        if(!is_numeric($id))
            return array('code' => 1, 'message' => 'Invalid id', 'statusCode' => 200);
        
        $rs = $this->db->query("Select * from BracketConfigs where id = ?", array($id));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Invalid Bracket Id', 'statusCode' => 200);
        
        $config = $rs->row();
        
        $teams = array();
        $rs = $this->db->query("Select * from SportTeams where sportCategoryID = ? order by id", array($config->sportCategoryId));
        foreach($rs->result() as $team)
            $teams[$team->id] = $team;
        
        $rs = $this->db->query("Select id as cardId, playerId, status, data, date(convert_tz(created, 'GMT', 'US/Pacific')) as date 
            from BracketPlayerMatchups where bracketConfigId = ? and playerId in (Select id from Players where isCelebrity = 1) order by created DESC", array($id));
        $count = $rs->num_rows();
        $cards = $rs->result();
        if($count)
        {
            $players = array();
            foreach($cards as &$card)
            {                
                if(!isset($players[$card->playerId]))                
                {
                    $rs = $this->db->query("Select * from PlayerDups where id = ?", array($card->playerId));
                    $person = $rs->row();
                    $players[$person->id] = $person;
                }
                $card->playerName = $players[$card->playerId]->first_name . " " . $players[$card->playerId]->last_name;
                $data = json_decode($card->data, true);
                $finalTeam = "";
                foreach($data as $block)
                {
                    if(!array_key_exists("round_2", $block))
                        continue;
                    
                    if(count($block['round_2']))
                    {
                        $final = $block['round_2'][0];
                        if(isset($final['winner']))
                            $finalTeam = $teams[$final['winner']]->name;
                    }
                }
                unset($card->data);
                $card->date = date("n/j/y", strtotime($card->date));
                $card->finalTeam = $finalTeam;
            }
            //print_r($players); die();
        }
        return array('code' => 0, 'cards' => $cards, 'count' => $count, 'statusCode' => 200);
    }
    
     public function cardsTest($playerId, $id)
    {
        if(!is_numeric($id))
            return array('code' => 1, 'message' => 'Invalid id', 'statusCode' => 200);
        
        $rs = $this->db->query("Select * from BracketConfigs where id = ?", array($id));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Invalid Bracket', 'statusCode' => 200);
        
        $rs = $this->db->query("Select b.id as cardId, convert_tz(b.created, 'GMT', 'US/Pacific') as created, date(convert_tz(b.created, 'GMT', 'US/Pacific')) as date, screenName as playerName, isCelebrity as celebrity,
            b.status, if(datediff(date(convert_tz(b.created, 'GMT', 'US/Pacific')), date(convert_tz(now(), 'GMT', 'US/Pacific'))) <> 0 AND b.status = 'Saved', 1, 0) as expired
            from BracketPlayerMatchups b
            Inner join Players p on p.id = b.playerId 
            where playerId = ? and bracketConfigId = ?", array($playerId, $id));
        
        $cards = $rs->result();
        //print_r($cards); die();
        
        $config = $rs->row();
        $dates = array();
        $startDate = strtotime(date("Y-m-d H:00:00", strtotime("now")));
        $endDate = $startDate + 3600;
        $simDate = $startDate;
        $k = 1;
        for($i = $startDate; $i < $endDate; $i += 720)
        {
            $dates[date("Y-m-d", $simDate)]['day'] = $k++;
            $dates[date("Y-m-d", $simDate)]['displayDate'] = date("F d", $simDate);
            $dates[date("Y-m-d", $simDate)]['endDate'] = str_replace("##", "GMT", date("D M d Y H:i:s ##O (T)", $i + 720));
            $dates[date("Y-m-d", $simDate)]['date'] = str_replace("##", "GMT", date("D M d Y H:i:s ##O (T)", $i));
            foreach($cards as $card)
            {
                if(strtotime($card->created) >= $i && strtotime($card->created) < $i + 720)
                {
                    $dates[date("Y-m-d", $simDate)]['cardId'] = $card->cardId;
                    $dates[date("Y-m-d", $simDate)]['status'] = $card->status;
                    $dates[date("Y-m-d", $simDate)]['expired'] = $card->expired;
                }
            }
            $simDate += 86400;
        }
        
        $rs = $this->db->query("Select count(*) as cnt from BracketPlayerMatchups where bracketConfigId = ?", array($id));
        $count = $rs->row()->cnt;                
        
        $hasEmail = false;
        $player = $this->user->getById($playerId);
        if(isset($player->accountData['email']) && $player->accountData['email'])
            $hasEmail = true;
        
        foreach($dates as &$date)
        {
            if(strtotime($date['endDate']) < strtotime(date("Y-m-d H:i:s")))
            {
                if(isset($date['cardId']) && $date['status'] == 'Completed')                
                    $date['lobbyMessage'] = "Bracket Completed";                
                else                
                    $date['lobbyMessage'] = "Bracket Expired";
            }
            elseif(strtotime($date['date']) < strtotime(date("Y-m-d H:i:s")) && strtotime(date("Y-m-d H:i:s")) < strtotime($date['endDate']))
            {
                if(!isset($date['cardId']))
                {
                    $date['lobbyMessage'] = "Bracket Ready";
                }
                else
                {
                    if($date['status'] == "Completed")
                        $date['lobbyMessage'] = "Bracket Completed";
                    else
                        $date['lobbyMessage'] = "Bracket Incomplete";
                }
            }
            else
            {
                $date['lobbyMessage'] = "Upcoming Bracket";
            }
        }
        $dates = array_values($dates);
        
        return array('code' => 0, 'dates' => $dates, 'hasEmail' => $hasEmail, 'count' => $count, 'statusCode' => 200);
    }
    
    public function cards($playerId, $id)
    {
        if(!is_numeric($id))
            return array('code' => 1, 'message' => 'Invalid id', 'statusCode' => 200);
        
        $rs = $this->db->query("Select * from BracketConfigs where id = ?", array($id));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Invalid Bracket', 'statusCode' => 200);
        
        $config = $rs->row();
        $dates = array();
        $startDate = strtotime(date("Y-m-d", strtotime($config->startDate)));
        $endDate = strtotime(date("Y-m-d", strtotime($config->endDate)));
        $k = 1;
        for($i = $startDate; $i <= $endDate; $i += 86400)
        {
            $dates[date("Y-m-d", $i)]['day'] = $k++;
            $dates[date("Y-m-d", $i)]['displayDate'] = date("F d", $i);
            $dates[date("Y-m-d", $i)]['endDate'] = str_replace("##", "GMT-0700", date("D M d Y 0:i:s ## (T)", $i + 86400));
            $dates[date("Y-m-d", $i)]['date'] = str_replace("##", "GMT-0700", date("D M d Y 0:i:s ## (T)", $i));
            if($i == $endDate)
                $dates[date("Y-m-d", $i)]['endDate'] = str_replace("##", "GMT-0700", date("D M d Y 0:i:s ## (T)", strtotime($config->endDate)));            
                
        }
        if($i - $endDate < 86400)
        {
            $dates[date("Y-m-d", $i)]['day'] = $k++;
            $dates[date("Y-m-d", $i)]['displayDate'] = date("F d", $endDate);
            $dates[date("Y-m-d", $i)]['endDate'] = str_replace("##", "GMT-0700", date("D M d Y H:i:s ## (T)", strtotime($config->endDate)));
            $dates[date("Y-m-d", $i)]['date'] = str_replace("##", "GMT-0700", date("D M d Y H:i:s ## (T)", $i));
        }
        
        $rs = $this->db->query("Select count(*) as cnt from BracketPlayerMatchups where bracketConfigId = ?", array($id));
        $count = $rs->row()->cnt;
        
        $rs = $this->db->query("Select b.id as cardId, date(convert_tz(b.created, 'GMT', 'US/Pacific')) as date, screenName as playerName, isCelebrity as celebrity,
            b.status, if(datediff(date(convert_tz(b.created, 'GMT', 'US/Pacific')), date(convert_tz(now(), 'GMT', 'US/Pacific'))) <> 0 AND b.status = 'Saved', 1, 0) as expired
            from BracketPlayerMatchups b
            Inner join Players p on p.id = b.playerId 
            where playerId = ? and bracketConfigId = ?", array($playerId, $id));
        
        $cards = $rs->result();
        
        foreach($cards as $card)
        {
            $dates[$card->date]['cardId'] = $card->cardId;
            $dates[$card->date]['status'] = $card->status;
            $dates[$card->date]['expired'] = $card->expired;
        }
        
        $hasEmail = false;
        $player = $this->user->getById($playerId);
        if(isset($player->accountData['email']) && $player->accountData['email'])
            $hasEmail = true;
        
        foreach($dates as $index => &$date)
        {
            if(strtotime($date['endDate']) < strtotime(date("Y-m-d H:i:s")))
            {
                if(isset($date['cardId']) && $date['status'] == 'Completed')                
                    $date['lobbyMessage'] = "Bracket Completed";                
                else                
                    $date['lobbyMessage'] = "Bracket Expired";
            }
            elseif(strtotime($index) == strtotime(date("Y-m-d")))
            {
                if(!isset($date['cardId']))
                {
                    $date['lobbyMessage'] = "Bracket Ready";
                }
                else
                {
                    if($date['status'] == "Completed")
                        $date['lobbyMessage'] = "Bracket Completed";
                    else
                        $date['lobbyMessage'] = "Bracket Incomplete";
                }
            }
            else
            {
                $date['lobbyMessage'] = "Upcoming Bracket";
            }
        }
        $dates = array_values($dates);
        
        return array('code' => 0, 'dates' => $dates, 'hasEmail' => $hasEmail, 'count' => 0, 'statusCode' => 200);
    }
    
    public function card($playerId, $id)
    {
        $rs = $this->db->query("Select id as cardId, playerId, bracketConfigId, data as picks, tieBreakerTeam1 as tiebreaker1, tieBreakerTeam2 as tiebreaker2, 
            date(convert_tz(created, 'GMT', 'US/Pacific')) as date from BracketPlayerMatchups 
            where id = ? AND (playerId = ? OR playerId in (Select id from Players where isCelebrity = 1))", array($id, $playerId));
        
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Card not found', 'statusCode' => 200);
        
        $card = $rs->row();
        $rs = $this->db->query("Select * from BracketConfigs where id = ?", array($card->bracketConfigId));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Config not found', 'statusCode' => 200);
        
        $config = $rs->row();
        
        $teams = array();
        $rs = $this->db->query("Select teamId1 as team_id, name, teamRank1 as seed, concat(wins, '-', losses) as record, division  
            from BracketMatchups b 
            Inner join SportTeams s on b.teamId1 = s.id and s.sportCategoryID = ? 
            order by s.id", array($config->sportCategoryId));
        foreach($rs->result() as $row)
            $teams[] = $row;
        
        $rs = $this->db->query("Select teamId2 as team_id, name, teamRank2 as seed, concat(wins, '-', losses) as record, division  
            from BracketMatchups b 
            Inner join SportTeams s on b.teamId2 = s.id and s.sportCategoryID = ? 
            order by s.id", array($config->sportCategoryId));
        foreach($rs->result() as $row)
            $teams[] = $row;
        
        $name = "Player";
        $rs = $this->db->query("Select * from PlayerDups where id = ?", array($card->playerId));
        if($rs->num_rows())
            $name = $rs->row()->first_name . " " . $rs->row()->last_name;
        
        return array('code' => 0, 'card' => $card, 'cardId' => $card->id, 'teams' => $teams, 'name' => $name, 'serialNumber' => sprintf("KB%05d", $config->id), 'statusCode' => 200);
    }
    
    public function email($playerId, $data, $type = 0)
    {
        $template = array('cardId','emails');
        //$emails = array('jonathan.taylor@kizzang.com', 'justin.mette@kizzang.com');
        //print json_encode($emails);

        foreach($template as $field)
        {
            if(!isset($data[$field]))
                return array('code' => 1, 'message' => "Invalid parameters: $field", 'statusCode' => 200);
            
            switch($field)
            {
                case 'cardId':
                    if(!is_numeric($data[$field]))
                        return array('code' => 1, 'message' => "Invalid value for $field", 'statusCode' => 200);
                    break;
                    
                case 'emails':
                    $emails = json_decode($data['emails'], true);
                    if(!is_array($emails))
                        return array('code' => 1, 'message' => "Invalid email array", 'statusCode' => 200);
                    break;
            }
        }
        
        $rs = $this->db->query("Select * from BracketPlayerMatchups where id = ?", array($data['cardId']));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Invalid Card', 'statusCode' => 200);
        
        $card = $rs->row();
        //Get Email Body
        if($card->playerId <> $playerId)
        {
            $rs = $this->db->query("Select * from PlayerDups where id = ?", array($card->playerId));
            $celeb = $rs->row();
            
            $rs = $this->db->query("Select * from PlayerDups where id = ?", array($playerId));
            $player = $rs->row();
            
            $title = "Celeb Email";
            
            $body = $this->load->view("/emails/celeb_bracket", array('name' => $celeb->first_name . " " . $celeb->last_name, 'playerName' => $player->first_name . " " . $player->last_name,
                'emailCode' => md5($player->email)), true);
        }
        elseif($type)
        {
            $rs = $this->db->query("Select * from PlayerDups where id = ?", array($playerId));
            $player = $rs->row();
            
            $title = "Confirm Bracket";
            
            $body = $this->load->view("/emails/confirm_bracket", array('first_name' => $player->first_name, 'emailCode' => md5($player->email)), true);
        }
        else
        {
            $rs = $this->db->query("Select * from PlayerDups where id = ?", array($playerId));
            $player = $rs->row();
            
            $title= "Share Bracket";
            
            $body = $this->load->view("/emails/share_bracket", array('name' => $player->first_name . " " . $player->last_name, 'emailCode' => md5($player->email)), true);
        }
                
        if(!$this->createImage($card))
            return array('code' => 2, 'message' => 'Bracket Failed to create image', 'statusCode' => 200);
        
        $src_url = "https://kizzang-campaigns.s3.amazonaws.com/emails/brackets/" . getenv("ENV") . "/" . $data['cardId'] . ".png";
        $dest_url = "/tmp/" . $data['cardId'] . ".png";
        
        if(copy($src_url, $dest_url))
        {
            foreach($emails as $email)
            {                
                if(!$this->emailImage($email, $dest_url, $body, $title))
                        return array('code' => 3, 'Error Sending Email', 'statusCode' => 200);                
            }
        }
        else
        {
            return array('code' => 4, 'message' => "Error copying image", 'statusCode' => 200);
        }
        
        return array('code' => 0, 'message' => 'Email send successfully', 'statusCode' => 200);
    }
    
    private function emailImage($to_address, $image_url, $body, $title)
    {
        $this->load->library('email');
        $config['mailtype']     = 'html';
        $config['protocol']     = 'smtp';
        $config['smtp_host']    = 'tls://email-smtp.us-east-1.amazonaws.com';
        $config['smtp_user']    = 'AKIAJNBPMBFTVPTBEWRQ';
        $config['smtp_pass']    = 'AgKt69yJlGzN186y23i+SYSfN6ihp0un7/TcShzKr5Wh';
        $config['smtp_port']    = '465';
        $config['wordwrap']     = TRUE;
        $config['newline']      = "\r\n"; 
        
        $this->email->initialize($config);

        $this->email->from("welcome@kizzang.com");
        $this->email->to($to_address);
        $this->email->subject($title);
        $this->email->attach($image_url);
        $this->email->message($body);
        
        if($this->email->send())
            return TRUE;
        else
            return FALSE; 
    }
    
    public function image($id)
    {
        $rs = $this->db->query("Select * from BracketPlayerMatchups where id = ?", array($id));
        if(!$rs->num_rows())
            die();

        $card = $rs->row();

        $rs = $this->db->query("Select * from BracketConfigs where id = ?", array($card->bracketConfigId));
        if(!$rs->num_rows())
            die();
        
        $config = $rs->row();
        
        $rs = $this->db->query("Select * from BracketMatchups where bracketConfigId = ?", array($config->id));
        if(!$rs->num_rows())
            die();
        
        $matchups = $rs->result();
        $divisions = array();
        foreach($matchups as $matchup)
        {
            $divisions[$matchup->division][] = $matchup->teamId1;
            $divisions[$matchup->division][] = $matchup->teamId2;
        }
        
        $rs = $this->db->query("Select t.*, if(ISNULL(a.division), b.division, a.division) as division, if(ISNULL(a.teamRank1), b.teamRank2, a.teamRank1) as teamRank from SportTeams t
            Left Join BracketMatchups a on a.teamId1 = t.id
            Left Join BracketMatchups b on b.teamId2 = t.id
            where t.sportCategoryID = ? 
            and (t.id in (Select teamId1 from BracketMatchups where bracketConfigId = ?) 
                or t.id in (Select teamId2 from BracketMatchups where bracketConfigId = ?))", array($config->sportCategoryId, $config->id, $config->id));
        
        if(!$rs->num_rows())
            die();
        
        $teams = array();
        foreach($rs->result() as $team)
            $teams[$team->id] = $team;
        
        $answers = json_decode($card->data, true);
        
        $ret = array();
        foreach($answers as $outer)
        {
            foreach($outer as $iround => $round)        
            {
                $index = 0;
                $old_round = "";
                foreach($round as $game)
                {
                    $current_round = str_replace("round_", "",  $iround);
                    if($teams[$game['team1']]->division != $old_round)
                    {
                        $index = 0;
                        $old_round = $teams[$game['team1']]->division;                        
                    }
                    if($current_round == 1)
                    {
                        $ret['left_champ'] = $teams[$game['team1']]->name;
                        $ret['right_champ'] = $teams[$game['team2']]->name;
                        $ret['champ'] = $teams[$game['winner']]->name;
                    }
                    if($current_round == 2)
                    {
                        $ret["_2_" . $index++] = array('name' => $teams[$game['team1']]->name, 'abbr' => $teams[$game['team1']]->abbr);
                        $ret["_2_" . $index++] = array('name' => $teams[$game['team2']]->name, 'abbr' => $teams[$game['team2']]->abbr);
                    }
                    elseif($current_round == 4)
                    {
                        $ret["_4_" . $teams[$game['team1']]->division] = array('name' => $teams[$game['team1']]->name, 'abbr' => $teams[$game['team1']]->abbr);
                        $ret["_4_" . $teams[$game['team2']]->division] = array('name' => $teams[$game['team2']]->name, 'abbr' => $teams[$game['team2']]->abbr);
                    }
                    else
                    {
                        $ret[str_replace("round", "",  $iround) . "_" . $index++ . "_" . $teams[$game['team1']]->division] = array('name' => $teams[$game['team1']]->name, 'abbr' => $teams[$game['team1']]->abbr, 'rank'  => $teams[$game['team1']]->teamRank);
                        $ret[str_replace("round", "",  $iround) . "_" . $index++ . "_" . $teams[$game['team2']]->division] = array('name' => $teams[$game['team2']]->name, 'abbr' => $teams[$game['team2']]->abbr, 'rank'  => $teams[$game['team2']]->teamRank);
                    }                    
                }
            }
        }
        
        //print_r($answers); die();
        $rs = $this->db->query("Select * from PlayerDups where id = ?", array($card->playerId));
        $player = $rs->row();
        //print_r($player);
        $ret['name'] = $player->first_name . " " . $player->last_name;
        $ret['serialNumber'] = sprintf("KB%05d", $config->id);
        $ret['cardId'] = $card->id;
        $ret['tieBreakerTeam'] = $card->tieBreakerTeam1;        
        //print_r($ret); die();
        $page = $this->load->view("bracket_challenge", $ret, true);
        print $page;
        die();
    }
    
    private function createImage($data)
    {
        $filename = "http://kizzang-campaigns.s3.amazonaws.com/emails/brackets/" . getenv("ENV") . "/" . $data->id . ".png";
        if(file_exists($filename) && filemtime($filename) > strtotime($data->updated))
            return true;
        
        //$url = "http://local.chefapi.com";
       
        $url = "https://api.kizzang.com";
        
        switch(getenv("ENV"))
        {
            case "dev": $url = "https://devapi.kizzang.com"; break;            
            case "prod": $url = "https://api.kizzang.com"; break;
        }
        
        $command = BASEPATH . "../images/" . "phantomjs " . BASEPATH . "../images/rasterize.js " . "$url/api/brackets/image/" . $data->id . " /tmp/" . $data->id . ".png";
        //print $command;
        $ret = passthru($command);

        if($ret)
            return false;
        
        //Push to S3
        $cur_file = $data->id . ".png";
        $this->load->library('s3');
        $this->s3->putObjectFile("/tmp/" . $data->id . ".png", 'kizzang-campaigns',  "emails/brackets/" . getenv("ENV") . '/' . $cur_file, 'public-read');
        return true;
    }
    
    public function button()
    {
        $rs = $this->db->query("Select * from BracketConfigs where date(convert_tz(now(), 'GMT', 'US/Pacific')) between startDate and viewDate");
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'No valid Bracket Challenge', 'statusCode' => 200);
        $config = $rs->row();
        return array('code' => 0, 'id' => $config->id, 'statusCode' => 200);
    }
    
    public function save($playerId, $data)
    {
        $template = array('cardId','status','bracketConfigId','tiebreaker1','picks');
        foreach($template as $field)
        {
            if(!isset($data[$field]))
                return array('code' => 1, 'message' => "Field: $field missing in post", 'statusCode' => 200);
            
            switch($field)
            {                
                case 'tiebreaker1':                
                case 'bracketConfigId':
                    if(!is_numeric($data[$field]))
                        return array('code' => 1, 'message' => "$field needs to be numeric", "statusCode" => 200);
                    break;
                    
                case 'status':
                case 'picks':
                    if(!$data[$field])
                        return array('code' => 1, 'message' => "$field needs to be set", "statusCode" => 200);
                    break;      
                    
                case 'cardId':
                    if(!isset($data['cardId']))
                        return array('code' => 1, 'message' => 'cardId not set', 'statusCode' => 200);
                    break;
            }
        }
        
        //if($data['status'] == "Completed")
        //    return array('code' => 2, 'message' => 'Card Completed for the day', 'statusCode' => 200);
        
        //Check to see if the bracket is still valid
        $rs = $this->db->query("Select * from BracketConfigs where id = ? and convert_tz(now(), 'GMT', 'US/Pacific') between startDate and endDate", array($data['bracketConfigId']));
        if(!$rs->num_rows())
            return array('code' => 2, 'message' => 'Invalid Bracket', 'statusCode' => 200);
        
        $rec =array('bracketConfigId' => $data['bracketConfigId'], 'data' => $data['picks'], 'playerId' => $playerId, 
            'tieBreakerTeam1' => $data['tiebreaker1'], 'status' => $data['status']);
        
        if($data['cardId'] && is_numeric($data['cardId']))
        {
            $this->db->where('id', $data['cardId']);             
            $this->db->update('BracketPlayerMatchups', $rec);
        }
        else
        {
            $this->db->insert('BracketPlayerMatchups', $rec);
        }
        
        if($this->db->affected_rows() || isset($data['cardId']))
        {
            if(isset($data['cardId']) && is_numeric($data['cardId']))
                $id = $data['cardId'];
            else
                $id = $this->db->insert_id();

            $this->load->model("chedda");
            $gameInfo = array('serialNumber' => sprintf("KB%05d", $data['bracketConfigId']), 'entry' => $id, 'type' => 'bracket');
            $this->chedda->addEventNotification($playerId, $gameInfo);
            
            $player = $this->user->getById($playerId);
            
            $ret = $this->email($playerId, array('cardId' => $id, 'emails' => json_encode(array($player->accountData['email']))), 1);
            if(!$ret['code'])
                return array('code' => 0, 'message' => 'Entry Saved', 'statusCode' => 201);
            else
                return $ret;
        }
        return array('code' => 3, 'message' => 'Error Updating DB', 'statusCode' => 200);
    }        
}