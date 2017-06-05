<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bingo extends MY_Model {

    // set table is wheel
    protected $_table = 'BingoGames';
    
    protected $public_attributes = array(
            'startTime',
            'endTime',
            'cardNumbersPicked',
            'maxNumber',
            'created'
        );
    
    public function initPlayer($playerId)
    {
        //Check for future game
        $rs = $this->db->query("Select * from BingoGames where convert_tz(now(), 'GMT', 'US/Pacific') < startTime LIMIT 1");
        $futureGame = $rs->row();
        
        //Check for current game and they have to have a card at this point
        $rs = $this->db->query("Select * from BingoGames 
            where (convert_tz(now(), 'GMT', 'US/Pacific') between startTime and endTime) 
            and id in (Select bingoGameId from BingoCards where playerId = ?) LIMIT 1", array($playerId));
        $currentGame = $rs->row();
        
        if($currentGame)
        {
            $rs = $this->db->query("Select * from BingoCards where bingoGameId = ? and playerId = ?", array($currentGame->id, $playerId));
            $card = $rs->row();
            return array('code' => 0, 'delayTime' => 0, 'card' => $card, 'statusCode' => 200);
        }
        elseif($futureGame)
        {
            $rs = $this->db->query("Select * from BingoCards where bingoGameId = ? and playerId = ?", array($futureGame->id, $playerId));
            
            if($rs->num_rows())
                $card = $rs->row();
            else
                $card = $this->generateCard($futureGame->id, $playerId, json_decode($futureGame->cardNumbersPicked));
            return array('code' => 0, 'delayTime' => strtotime($futureGame->startTime) - strtotime("NOW") - 60, 'card' => $card, 'statusCode' => 200);
        }
        return array('code' => 1, 'error' => 'No Current Game', 'statusCode' => 200);
    }
    
    private function generateCard($gameId, $playerId, $gameNums)
    {
        $this->load->model('chedda');
        $ranges = array(
            array('min' => 1, 'max' => 15), 
            array('min' => 16, 'max' => 30),
            array('min' => 31, 'max' => 45),
            array('min' => 46, 'max' => 60),
            array('min' => 61, 'max' => 75));
        
        $numbers = array();
        foreach($ranges as $index => $range)
        {
            $nums = 0;
            while($nums < ($index == 2) ? 4 : 5)
            {
                $num = rand($range['min'], $range['max']);
                if(!in_array($num, $numbers))
                {
                    $numbers[] = $num;
                    $nums++;
                }
            }
        }
        
        //Assign Chedda
        $chedda = array();
        $cheddaWin = 0;
        $index = 0;
        
        while($index < 4)
        {
            $place = 0;
            $numChedda = $this->chedda->getChedda();
            do
            {
                $place = $numbers[rand(0, count($numbers) - 1)];
            } while(array_key_exists($place, $chedda));
            $chedda[$place] = array('num' => $place, 'chedda' => $numChedda);
            if(in_array($place, $gameNums))
                $cheddaWin += $numChedda;
            $index++;
        }
        
        $numbers[] = 76;
        $numbers[] = 77;
        
        if($cheddaWin)
        {
            $this->chedda->add(array('playerId' => $playerId, 'gameKey' => $playerId . $gameId, 'count' => $cheddaWin));
        }
        
        sort($numbers);
        $rec = array('bingoGameId' => $gameId, 'playerId' => $playerId, 'cardNumbers' => json_encode($numbers), 'chedda' => json_encode(array_values($chedda)));
        $this->db->insert('BingoCards', $rec);
        
        $rs = $this->db->query("Select * from BingoCards where bingoGameId = ? and playerId = ?", array($gameId, $playerId));
        return $rs->row();
                
    }

    public function checkBingo($playerId)
    {
        $rs = $this->db->query("Select * from BingoGames 
            where (convert_tz(now(), 'GMT', 'US/Pacific') between startTime and endTime)");
        
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'No active Games', 'statusCode' => 200);
        
        $game = $rs->row();
        $this->db->query("Update BingoGames set status = 'Paused' where id = ?", array($game->id));
        
        $rs = $this->db->query("Select * from BingoCards where bingoGameId = ? and playerId = ?", array($game->id, $playerId));
        if(!$rs->num_rows())
            return array('code' => 2, 'message' => 'No Card Found', 'statusCode' => 200);
        
        $card = $rs->row();
        
        //Temp until I find a better way
        if($game->currentNum < 27)
        {
            $this->db->query("Update BingoGames set status = 'Active' where id = ?", array($game->id));
            return array('code' => 3, 'message' => 'Not enough numbers called', 'statusCode' => 200);
        }        
        $gameNums = array_slice(json_decode($game->cardNumbersPicked), $game->currentNum + 1);
        $playerNums = json_decode($card->cardNumbers);
        if(count(array_intersect($playerNums, $gameNums)) == count($playerNums) && in_array($gameNums[count($gameNums) - 1], $playerNums))
        {
            //Find the prize they won
            $rs = $this->db->query("Select * from Payouts where gameType = 'bingo' and ? between startRank and endRank", array($index));
            if($rs->num_rows())
                $prize = number_format($rs->row()->amount, 2);
            else
                $prize = 0.00;
            $this->db->query("Update BingoGames set status = 'Complete' where id = ?", array($game->id));
            $card->prize = $prize;
            $this->addEvent($card, $playerId);
            return array('code' => 0, 'bingo' => true, 'numbersCalled' => $index, 'prize' => '$ ' . $prize, 'statusCode' => 200);
        }
        $this->db->query("Update BingoGames set status = 'Active' where id = ?", array($game->id));
        return array('code' => 0, 'bingo' => false, 'numbersCalled' => $index, 'statusCode' => 200);
    }
    
    private function addEvent( $data, $playerId )
	{
        $this->load->model( 'eventnotification' );
        $this->load->model('winner');
        $db = $this->load->database("default", true);

        $previous_win = 0;
        $win = $data->prize;
        $rs = $db->query("Select sum(amount) as amount from Winners where player_id = ? and year(created) = year(now())", array($playerId));
        if($rs->num_rows())
            $previous_win = $rs->row()->amount;

        $time = 2880;
        $rs = $db->query("Select numMinutes from GameExpireTimes where game = 'bingo' and ? between lowAmount and highAmount LIMIT 1", array($win));
        if($rs->num_rows())
            $time = $rs->row()->numMinutes;        

        $winner_data = array('player_id' => $playerId,
            'game_type' => 'Bingo',
            'foreign_id' => $data->bingoGameId . $data->playerId,
            'serial_number' => sprintf("KB%05d", $data->bingoGameId),
            'prize_name' => '$ ' . $data->prize,
            'amount' => $data->prize,
            'processed' => 0,
            'game_name' => "Bingo",
            'order_num' => $data->bingoGameId . $data->playerId,
            'expirationDate' => date("Y-m-d H:i:s", strtotime("+" . $time . " minutes")));

        $w2Blocked = false;
        if($previous_win + $win >= 600)
        {
             $rs = $db->query("Select s.id from rightSignature.signins s
                 Inner join rightSignature.templates t on t.id = s.templateId and t.type in ('W9','Notarize') 
                 Where s.playerId = ? and YEAR(now()) = YEAR(s.created) and status = 'Complete'", array($playerId));
             if(!$rs->num_rows())
                 $w2Blocked = true;                                      
        }

        if($w2Blocked)
            $db->query("Update Users set accountStatus = 'W2 Blocked' where id = ?", array($playerId));

        $winnerResult = $this->winner->add($winner_data);

         // Append the app version number to each data event
         $eventData = array(
             'data' => json_encode( array( 'serialNumber' => $winner_data->serial_number, 
                                                       'entry' => $winnerResult->id, 
                                                       'prizeAmount' => $winner_data->amount,
                                                       'prizeName' => $winner_data->prize_amount,
                                                       'gameName' => 'Bingo'
                                                 ) ),
             'type' => 'bingo',
             'buttonType' => 1,
             'pending' => 1,
             'playerId' => $playerId,
             'expireDate' => date("Y-m-d H:i:s", strtotime("+" . $time . " minutes"))
         );

        $eventResult = $this->eventnotification->add( $eventData, $playerId );                              

        return $eventResult;
	}
}