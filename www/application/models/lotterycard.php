<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lotterycard extends MY_Model 
{

    // set table is Sport Schedule
    protected $_table = 'LotteryCards';

    protected $public_attributes = array(
            'id',
            'playerId',
            'lotteryConfigId',
            'answerHash',
            'created',
            'updated'
        );

   public function add($data)
   {
       $this->load->model('lotteryconfig');
       $this->load->model('chedda');
       $validate = array(        
            'playerId' => array(
                'field' => 'playerId',
                'rules' => 'required|numeric'
            ),
            'lotteryConfigId' => array(
                'field' => 'lotteryConfigId',
                'rules' => 'required|numeric'
            ),                               
            'answerHash' => array(
                'field' => 'answerHash',
                'rules' => 'required'
            )
        );
       
        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );
        
        if ( $this->form_validation->run() === FALSE )
            return array( 'code' => 1, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );
        
        $config = $this->lotteryconfig->getCurrent($data['playerId']);
        if($config['code'])
            return $config;
        
        $config = $config['config'];
        $answers = explode(",", $data['answerHash']);
        if(count($answers) != $config->numAnswerBalls)
            return array('code' => 4, 'message' => 'Number of answers is incorrect', 'statusCode' => 200);
        
        sort($answers);
        $data['answerHash'] = implode(",", $answers);
        //Check for duplicate
        $rs = $this->db->query("Select id from LotteryCards where playerId = ? and lotteryConfigId = ? and answerHash = ?", array($data['playerId'], $data['lotteryConfigId'], $data['answerHash']));
        if($rs->num_rows())
            return array('code' => 3, 'message' => 'You have already made these picks.||Please submit a new entry.', 'statusCode' => 200);
        
        $this->db->insert("LotteryCards", $data);
        $insertId = $this->db->insert_id();
        
        $this->chedda->addEventNotification($data['playerId'], array('type' => 'lottery', 'serialNumber' => sprintf("KL%05d", $config->id), 'entry' => $insertId));

        $rs = $this->db->query("Select l.id as configId, 'Lottery' as type, l.numCards as maxGames, 
            if(l.cardLimit = 'Per Game', count(c.id), sum(if(date(convert_tz(c.created, 'GMT', 'US/Pacific')) = date(convert_tz(now(), 'GMT', 'US/Pacific')), 1, 0))) as count
            From LotteryConfigs l
            Left join LotteryCards c on l.id = c.lotteryConfigId and playerId = ?
            Where l.id = ?
            Group by l.id", array($data['playerId'], $data['lotteryConfigId']));
        
        $counts = $rs->row();
        
        if($counts->count > $counts->maxGames)
            return array('code' => 2, 'message' => 'Max Games Reached for this Lottery Card', 'statusCode' => 200);

        $this->load->model('gamecount');
        $this->gamecount->add($data['playerId'], array('gameType' => 'Lottery', 'foreignId' => $config->id));
        
        return array('code' => 0, 'message' => 'Card Submitted', 'statusCode' => 200);
   }
}