<?php

class Roal extends MY_Model {
    
    // set table is Tickets
    protected $_table = 'Rules';

    // set validations rules
    protected $validate = array(
    );

    protected $public_attributes = array(
        'id',
        'TermsOfService',
        'PrivacyPolicy',
        'ParticipationRules',
        'created'
        );

    public function getCurrent()
    {
        $today = date("Y-m-d");
        $rs = $this->db->query("Select c.*, max(p.amount) as payouts, max(endTime) as endDate from ROALConfigs c
            Inner join Payouts p on gameType = 'ROAL'
            Inner join ROALQuestions q on q.ROALConfigId = c.id
            where cardDate = ?
            Group by c.id", array($today));
        if($rs->num_rows())
            return $rs->row();
        return NULL;
    }
    
    public function getById($id, $playerId)
    {
        $date = date("Y-m-d");
        $rs = $this->db->query("Select c.*, group_concat(amount) as payouts from ROALConfigs c
            Inner join Payouts p on gameType = 'ROAL'
            where c.id = ? and cardDate = ?
            Group by c.id", array($id, $date));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Invalide Run of a Lifetime Config', 'statusCode' => 200);
       
        $config = $rs->row();
        if(strstr($config->payouts, ","))
            $config->prizeList = implode(",", $config->payouts);
        else
            $config->prizeList[] = $config->payouts;
        $config->serialNumber = sprintf("KR%05d", $config->id);
        
        foreach($config->prizeList as &$prize)
            $prize = '$' .  number_format ($prize, $prize < 1000 ? 2 : 0);
        
        unset($config->payouts);
        $rs = $this->db->query("Select q.id as question_id, startTime, endTime, s.id as event_id, s.dateTime as date, c.name as category, s.team1, a.name as teamName1, s.team2, b.name as teamName2 from ROALQuestions q
                        Inner join SportSchedule s on s.id = q.SportScheduleId
                        Inner join SportTeams a on s.team1 = a.id and s.sportCategoryID = a.sportCategoryID
                        Inner join SportTeams b on s.team2 = b.id and s.sportCategoryID = b.sportCategoryID
                        Inner join SportCategories c on c.id = s.sportCategoryID
                        Where q.ROALConfigId = ?", array($id));
        $questions = $rs->result();
        
        $rs = $this->db->query("Select player_name, score from GameLeaderBoards where game_type = 'Parlay' and game_sub_type = 'ROAL' order by score DESC limit 10");
        $leaderboard = $rs->result();
        
        $player = array();
        $rs = $this->db->query("Select a.*, u.screenName From ROALAnswers a 
            Inner join Users u on u.id = a.playerId 
            where playerId = ? order by a.created DESC limit 1", array($playerId));
        if($rs->num_rows())
            $player = $rs->row();
        
        $rs = $this->db->query("Select a.currentStreak from ROALAnswers a
            Inner join ROALQuestions q on a.ROALQuestionID = q.id and q.answer IS NOT NULL
            where playerId = ? order by a.created DESC limit 1", array($playerId));
        
        if($rs->num_rows())
            $player->currentStreak = $rs->row()->currentStreak;
        
        return array('code' => 0, 'result' => compact('config','questions','leaderboard','player'), 'statusCode' => 200);
    }
    
    public function save($data)
    {
        $data = array_merge(array('ROALConfigId' => 0, 'ROALQuestionId' => 0, 'winningTeam' => 0, 'playerId' => 0), $data);
        foreach($data as $key => $value)
            if(!is_numeric($value) || !$value)
                return array('code' => 1, 'message' => 'Invalid param values', 'statusCode' => 200);
           
        $date = date("Y-m-d");
        $rs = $this->db->query("Select * from ROALConfigs where id = ? and cardDate = ?", array($data['ROALConfigId'], $date));
        if(!$rs->num_rows())
            return array('code' => 4, 'message' => 'Invalid Config', 'statusCode' => 200);
          
        $config = $rs->row();
        $rs = $this->db->query("Select * from ROALAnswers where playerId = ? and ROALConfigId = ?", array($data['playerId'], $data['ROALConfigId']));
        if($rs->num_rows())
            return array('code' => 2, 'message' => 'Duplicate Entry for this Config', 'statusCode' => 200);
        
        $this->db->insert('ROALAnswers', $data);
        if(!$this->db->affected_rows())
            return array('code' => 3, 'message' => 'Error adding in Entry', 'statusCode' => 200);
        
        $insertId = $this->db->insert_id();
        $this->load->model('chedda');
        $this->chedda->addEventNotification($data['playerId'], array('type' => 'ROAL', 'serialNumber' => sprintf("KR%05d", $data['ROALConfigId']), 'entry' => $insertId));
        
        $this->load->model('gamecount');        
        $this->gamecount->add($data['playerId'], array('gameType' => 'ROAL', 'theme' => $config->theme, 'foreignId' => $config->id, 'maxGames' => 1));
        return array('code' => 0, 'message' => 'Record Added', 'statusCode' => 200);
    }
}   