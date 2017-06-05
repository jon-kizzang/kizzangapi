<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lotteryconfig extends MY_Model 
{

    // set table is Sport Schedule
    protected $_table = 'LotteryConfigs';

    protected $public_attributes = array(
            'id',
            'numTotalBalls',
            'numAnswerBalls',
            'numCards',
            'cardLimit',
            'startDate',
            'endDate',
            'answerHash'
        );

    public function getRandomNumbers($id)
    {
        $rs = $this->db->query("Select * from LotteryConfigs where id = ?", array($id));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Lottery game not found.', 'statusCode' => 200);
        
        $config = $rs->row();
        $nums = array();
        $i = 0;
        
        
        while($i < $config->numAnswerBalls)
        {
            $rand = rand(1, $config->numTotalBalls);
            if(!in_array($rand, $nums))
                $nums[$i++] = $rand;
        }
        sort($nums);
        return array('code' => 0, 'nums' => implode(",", $nums), 'statusCode' => 200);
    }
    
    public function getCurrent($playerId)
    {
       $rs = $this->db->query("Select id, numTotalBalls, numAnswerBalls, numCards, cardLimit, startDate, endDate from LotteryConfigs where convert_tz(now(), 'GMT', 'US/Pacific') between startDate and endDate limit 1");
       if(!$rs->num_rows())
           return array('code' => 1, 'message' => 'No Valid Config Found', 'statusCode' => 200);
       
       $config = $rs->row();
       //Add memcached to this later
       if($config->cardLimit == "Per Day")
       {
           $rs = $this->db->query("Select count(*) as cnt from LotteryCards where playerId = ? 
               and date(convert_tz(created, 'GMT', 'US/Pacific')) = date(convert_tz(now(), 'GMT', 'US/Pacific')) 
               and lotteryConfigId = ?", array($playerId, $config->id));
           $limit = $rs->row();
           if($limit->cnt >= $config->numCards)
               return array('code' => 2, 'message' => 'Card limit has been reached for the Day', 'statusCode' => 200);
       }
       else
       {
           $rs = $this->db->query("Select count(*) as cnt from LotteryCards where playerId = ? and lotteryConfigId = ?", array($playerId, $config->id));
           $limit = $rs->row();
           if($limit->cnt >= $config->numCards)
               return array('code' => 2, 'message' => 'Card limit has been reached for this Game', 'statusCode' => 200);
       }
       
       return array('code' => 0, 'config' => $config, 'statusCode' => 200);
    }
        
}