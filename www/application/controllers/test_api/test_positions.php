<?php

/**
* 
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);

class test_positions extends CI_Controller
{
    
    function __construct() {

        parent::__construct();

        // init the memcache
        $this->config->load( 'memcache' );

        $memcacheServer = $this->config->item( 'memcache_server' );
        $memcachePort = $this->config->item( 'memcache_port' );

        $memcacheInstance = new Memcache();
        $memcacheInstance->pconnect( $memcacheServer, $memcachePort );
        $memcacheInstance->flush();

        // loading model position
        $this->load->model('position');
        
        // loading library unit test
        $this->load->library('unit_test');

        // loading database test
        $this->load->database('test', TRUE);

        //To enable strict mode 
        $this->unit->use_strict(TRUE);

        // Disable database debugging so we can test all units without stopping
        // at the first SQL error
        $this->db->db_debug = FALSE;

        // Load syntax highlighting helper
        $this->load->helper('text');
    }

    /**
     * idExit Function get id player exit
     * 
     * @return id player
     */
    protected function idNotExit() {

        $dataDB = $this->player->order_by('id', 'DESC')->limit(1)->get_all();
        
        if (is_object($dataDB) && isset($dataDB->id)) {
            
            return ((int)$dataDB->id + 1);
        } 
        else {

            return 1;
        }
    }

    /**
     * count fucntion get count of player
     * @return count of player is not deleted
     */
    protected function count () {

        $count = $this->player->count_by('isDelete', 0);
        return $count;
    }

    protected function dataExpected($playerId) {

        try {

            $dataExpected = $this->db->select('PlayPeriod.id, playerId')
            ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
            ->where('playerId', $playerId)
            ->order_by('id' ,'ASC')
            ->get('Players')->row();

            return $dataExpected;
            
        } catch (NotFoundException $e) {

            return $e;
        }
    }

    protected function playerNotExitPosition() {

        $query = "SELECT id FROM Players  WHERE id NOT IN (SELECT DISTINCT playerId FROM Positions)";
        $row = $this->db->query($query)->row();

        return $row ? $row->id : $this->getPlayer(1, null);
    }

    protected function getPlayer($emailConfirmed, $FBID, $player) {

        if ($emailConfirmed === 0) {
            $data = array(
                'email' =>"login".$player."@gmail.com",
                'password' => "123456",
                'gender' => '1',
                'firstName' => "User",
                'lastName' => "Test",
                'dob' => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
                'phoneHome' => '0123456789',
                );
            $player = $this->player->add($data);
            if (is_object($player) && isset($player->id)) {
                return $player->id;
            } else {

                return FALSE;
            }
        } else {
            
            $data = array(
                'email' =>"login".$player."@gmail.com",
                'password' => "123456",
                'gender' => 1,
                'firstName' => "User",
                'lastName' => "Test",
                'dob' => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
                'phoneHome' => '0123456789',
                'fbId' => is_null($FBID) ? $FBID : md5( $FBID )
                );
            $player = $this->player->add($data);

            if ( is_object($player) && isset($player->id) ) {
                $confirmed = $this->player->emailVerified($player->emailCode);
                if (is_array($confirmed) && isset($confirmed['statusCode']) && $confirmed['statusCode'] === 200) {
                    $playerConfirmed = $this->player->getById($player->id, TRUE);
                    return $playerConfirmed->id;
                }
            
            } else {

                return FALSE;
            }

        }

    }

    protected function newDataPlayPeriod ( $playerId, $day, $gamesPlayed, $status, $countMissedDay) {

        $startTime = date('Y-m-d H:i:s', strtotime("-$day day"));
        $endTime = date('Y-m-d 23:59:59', strtotime("-$day day"));

        $dataPlayPeriod = $data = array(
                'playerId' => $playerId,
                'startDate' => $startTime,
                'endDate' => $endTime,
                'gamesCredit' => 15,
                'gamesPlayed' => $gamesPlayed,
                'status' => $status,
                'countMissedDay' => isset($countMissedDay) ? $countMissedDay : 0
            );

        $row = $this->playperiod->insert($dataPlayPeriod, TRUE);

        if ($row) {

            return  $playerId;
        } else {

            return 0;
        } 

    }

    protected function newData ($playerId, $day, $gamesPlayed, $status, $position, $endPosition, $countMissedDay) {

        $playerId = $this->newDataPlayPeriod($playerId, $day, $gamesPlayed, $status, $countMissedDay);

        $data = array(
                    'playerId' => $playerId,
                    'startPosition' => $position,
                    'endPosition' => $endPosition,
                    'calendarDate' => date('Y-m-d',  strtotime("-$day day")),
                    'ruleApplied' => 'init position',
                    'ruleCode' => 0,
                );
        $row = $this->position->insert($data, TRUE);

        if ($row) {
            $this->position->getAll($playerId, 10, 0);
            return  $playerId;

        } else {

            return 0;
        } 
    }
    /**
     * testCreatePostions
     *
     * Function testing create a position to verify valid or invalid
     * 
     */
    public  function testCreatePositions () {

        // truncate table
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->db->query('TRUNCATE Players;');
        $this->db->query('TRUNCATE Positions;');
        $this->db->query('TRUNCATE PlayPeriod');
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
        $this->db->query("INSERT INTO `Players` (`roleId`, `gender`, `accountCreated`, `lastLogin`, `lastApprovedTOS`, `lastApprovedPrivacyPolicy`, `emailHash`, `passwordHash`, `accountEmail`, `payPal`, `emailCode`, `screenname`, `profileComplete`, `accountData`, `emailVerified`) VALUES ('1', '1', '2014-09-26 03:58:28', '2014-09-26 03:58:28', '2014-09-26 03:58:28', '2014-09-26 03:58:28', 'd9eaf0b87e204b0449760df19caacb12', '25f9e794323b453885f5181f1b624d0b', 'Xz4s0kDUm/w+6R/hvSaQVQgV+bX6dR4/KSV8PJJqkbjIevjvucbEiXMIx06n/FBS8efmQyBvtftpY770caGr1DDawnALO/cwan4cmY5cZZJA==', 'Xz4s0kDUm/w+6R/hvSaQVQgV+bX6dR4/KSV8PJJqkbjIevjvucbEiXMIx06n/FBS8efmQyBvtftpY770caGr1DDawnALO/cwan4cmY5cZZJA==', '71555ffedae2a0869bc21187f47dd2', 'Kizzang A', 0, '5x12CCa8dw4IWYlKWKSFUgUJM1weYSDrovDD22bQulseo6Oboikt4gIwB0Xjqmqs0LZNGbl+tP8uI8g5f6sjAq5mYyrXpUzaBxZHgPHYdMHYHEfLnvfbfKTrxlTkHEk+iMMGGfQqKIVu99qllHVWXv', '1')");

        $ruleAppliedLookup = array(
            1 => 'increment position',
            2 => 'Lucked out and stays on their current day',
            3 => 'Move back 25% of previous for missed day',
            4 => 'Move back 50% of previous for missed day',
            5 => 'Go Back'
        );

        $playerId = $this->getPlayer(1, null, 1);
        $positionFirst = $this->newData($playerId, 15, 15, 3, 9, 9, 0);
        $positionFirst = $this->newData($playerId, 10, 3, 2, 10, 11, 1);
        $positionFirst = $this->newData($playerId, 5, 15, 3, 10, 11, 1);
        $positionExit = $this->newData($playerId , 1, 15, 3 ,9, 10, 0);

        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerId, 'endDate <' => date( 'Y-m-d' ) ) );

        if ( $positionExit != 0 ) {
            // verify create postion return is invalid
            $exitPosition = $this->position->get_by(  array('calendarDate'=> date('Y-m-d'), 'playerId' => $positionExit) );
            if ($exitPosition) {
                $testResult = $this->position->add($positionExit, $playperiodPreDay);
                if (is_array($testResult) && isset($testResult['errors'])) {
                    // Exist position on current time     
                    $this->unit->run($testResult['errors'], "Position exists in ". date('Y-m-d'). " with $positionExit", "To verify create position is invalid", "Exist position on current time");
                }

            } 
        }

        // verify create posistion is valid 
        // ====================================
        $positionOne = $this->playerNotExitPosition();

        if ( $positionOne ) {

            $testResultOne = $this->position->add($positionOne, $playperiodPreDay);

            // To verify position return 1
            if (is_array($testResultOne) && isset($testResultOne['statusCode'])) {
            
                $testPosition = $this->position->get_by('playerId', $positionOne);
                // status code return must be 201
                $this->unit->run($testResultOne['statusCode'], 201, "To verify create position is valid", "status code return must be 201");
                // verify msg return is  Position created the successfully
                $this->unit->run($testResultOne['message'], "Position created successfully", "To verify create position is valid", "To verify Position created the successfully");
                
                // verify position return must be is 1
                $this->unit->run((int)$testPosition->startPosition, 1, "To verify create position is valid", "To verify position return must be is 1");

                // verify position return must be is 1
                $this->unit->run((int)$testPosition->endPosition, 1, "To verify create position is valid", "To verify position return must be is 1");

                // verify ruleApplied must be equal init position
                $this->unit->run($testPosition->ruleApplied, "init position", "To verify create position is valid", "verify ruleApplied must be equal init position");
            }

        }

        if( $positionFirst ) {
            $playerIdSecond = $positionFirst;
            $lastPosition = $this->position->getLastPosition( $playerIdSecond );
            $resultSecond = $this->position->add($playerIdSecond, $playperiodPreDay) ;
            $insertIdSecond = $this->db->insert_id();
            $testPositionSecond  = $this->position->get($insertIdSecond);
            if(is_array($resultSecond) && isset($resultSecond['statusCode']) && $resultSecond['statusCode'] == 201 ) {

                // To verify position return must be equal position +=1
                $this->unit->run((int)$testPositionSecond->startPosition, ($lastPosition), "To verify create position is valid", "To verify position return must be equal position +=1");

                // To verify position return must be equal position +=1
                $this->unit->run((int)$testPositionSecond->endPosition, ($lastPosition), "To verify create position is valid", "To verify position return must be equal position +=1");

                // To verify ruleApplied must be is equal string 'increment position'
                $this->unit->run($testPositionSecond->ruleApplied, "increment position", "To verify create position is valid", "verify ruleApplied must be equal increment position");    
            }
        }

        $playerIdThird = $this->getPlayer(1, null, 2);
        $positionThird = $this->newData($playerIdThird, 40, 15, 3, 9, 9, 0);
        $positionSecond = $this->newData($playerIdThird, 30, 3, 2, 10, 9, 1);
        $positionSecond = $this->newData($playerIdThird, 10, 15, 3, 10, 10, 1);
        $positionExit = $this->newData($playerIdThird , 1, 15, 2, 9, 10, 2);

        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerIdThird, 'endDate <' => date( 'Y-m-d' ) ) );
        
        if( $playerIdThird ) {
            $lastPosition = $this->position->getLastPosition( $playerIdThird );
            $resultThird = $this->position->add( $playerIdThird, $playperiodPreDay);
            $insertIdThird = $this->db->insert_id();
            $testPositionThird  = $this->position->get($insertIdThird);

            if(is_array($resultThird) && isset($resultThird['statusCode']) && $resultThird['statusCode'] == 201 ) {
                // To verify position return must be equal position previous
                $this->unit->run((int)$testPositionThird->startPosition, (int)($lastPosition), "To verify create position is valid", "To verify position return must be equal position input previous");

                // To verify ruleApplied must be is equal string 'Lucked out and stays on their current day'
                $this->unit->run($testPositionThird->ruleApplied, "increment position", "To verify create position is valid", "To verify ruleApplied must be is equal string 'Lucked out and stays on their current day'");    
            }
        }

        $playerIdFourth = $this->getPlayer(1, null, 3);
        $positionThird = $this->newData($playerIdFourth, 50, 15, 3, 9, 9, 2);
        $positionSecond = $this->newData($playerIdFourth, 49, 3, 2, 10, 10,5);
        $positionSecond = $this->newData($playerIdFourth, 35, 5, 2, 10, 10, 9);

        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerIdFourth, 'endDate <' => date( 'Y-m-d' ) ) );

        if( $playerIdFourth ) {
            
            $lastPosition = $this->position->getLastPosition( $playerIdFourth );
            $testFourth = $this->position->add($playerIdFourth, $playperiodPreDay);
            $insertIdFourth = $this->db->insert_id();
            $testPositionFourth = $this->position->get($insertIdFourth);

            // To verify position return is position -= Math.floor( position * 0.25 )
            $this->unit->run((int)$testPositionFourth->endPosition, 1 , "To verify returned to day 1", "To verify create position is valid");

            // To verify ruleApplied must be is equal string 'Move back 25% of previous for missed day'
            $this->unit->run($testPositionFourth->ruleApplied, "returned to day 1", "To verify ruleApplied must be is equal string 'returned to day 1'");  

        }

        // ($playerId, $day, $gamesPlayed, $status, $position, $endPosition, $countMissedDay)
        // $positionFifth = $this->newData($playerId, 1, 2 , 2, 9); 
        $playerFifth = $this->getPlayer(1, null, 4);
        $positionFifth = $this->newData($playerFifth, 1 , 5, 2, 50, 50, 50);

        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerFifth, 'endDate <' => date( 'Y-m-d' ) ) );

        if ($positionFifth) {

            $playerIdFifth = $playerFifth;
                
            $lastPosition = $this->position->getLastPosition( $playerIdFifth );
            $testFifth = $this->position->add($playerIdFifth, $playperiodPreDay);
            $insertIdFifth = $this->db->insert_id();
            $testPositionFifth = $this->position->get($insertIdFifth);
            // if position 1-31
            if ( $lastPosition < 32 ) {
                $point = 1;
            }
            // if position 32-91
            elseif ( $lastPosition < 92 ) {
                $point = 30;
            }
            // if position 92-121
            elseif ( $lastPosition < 122 ) {
                $point = 60;
            }
            // if position 122-240
            elseif ( $lastPosition < 241) {
                $point = 90;
            }
            // else position 241-364
            else {
                $point = 120;
            }

            if ( $testPositionFifth->ruleCode == 2 ) {

                // To verify position return is position -= Math.floor( position * 0.5 )
                $this->unit->run((int)$testPositionFifth->endPosition, (int)($lastPosition - round( ( $lastPosition - $point ) * 0.25 )), "To verify position return is position -= Math.floor( position * 0.25 )", "To verify create position is valid");

                // To verify ruleApplied must be is equal string 'Move back 50% of previous for missed day'
                $this->unit->run($testPositionFifth->ruleApplied, "Move back 25% of previous for missed day", "To verify create position is valid", "To verify ruleApplied must be is equal string 'Move back 50% of previous for missed day'");  
            } elseif ( $testPositionFifth->ruleCode == 1 ) {

                // To verify position return is position -= Math.floor( position * 0.5 )
                $this->unit->run((int)$testPositionFifth->endPosition, (int)($lastPosition), "To verify position return is position ", "To verify create position is valid");

                // To verify ruleApplied must be is equal string 'Move back 50% of previous for missed day'
                $this->unit->run($testPositionFifth->ruleApplied, "Lucked out and stays on their current day", "To verify ruleApplied must be is equal string 'Lucked out and stays on their current day'"); 
            } elseif ( $testPositionFifth->ruleCode == 3 ) {

                // To verify position return is position -= Math.floor( position * 0.5 )
                $this->unit->run((int)$testPositionFifth->endPosition, (int)($lastPosition - round( ( $lastPosition - $point ) * 0.5 )), "To verify position return is position ", "To verify create position is valid");

                // To verify ruleApplied must be is equal string 'Move back 50% of previous for missed day'
                $this->unit->run($testPositionFifth->ruleApplied, "Move back 50% of previous for missed day", "To verify ruleApplied must be is equal string 'Move back 50% of previous for missed day'"); 
            } 

        } 

        $playerIdSix = $this->getPlayer(1, null, 5);
        $positionSix = $this->newData($playerIdSix, 92, 15, 3, 50, 51, 5);
        $positionSix = $this->newData($playerIdSix, 91, 2, 2, 52, 53, 100);
        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerIdSix, 'endDate <' => date( 'Y-m-d' ) ) );

        if ($positionSix) {

            $playerIdSix = $positionSix;
                
            $lastPosition = $this->position->getLastPosition( $playerIdSix );
            $testSix = $this->position->add($playerIdSix, $playperiodPreDay);
            $insertIdSix = $this->db->insert_id();
            $testPositionSix = $this->position->get($insertIdSix);
            // To verify position return is position = 30
            $this->unit->run((int)$testPositionSix->startPosition, 30 , "To verify position return is position = 30", "To verify create position is valid");

            // To verify ruleApplied must be is equal string 'Go Back'
            $this->unit->run($testPositionSix->ruleApplied, "returned to day 30", "To verify create position is valid", "To verify ruleApplied must be is equal string 'Go Back'");  

        } 

        $playerSevent = $this->getPlayer(1,null, 6);
        $positionSevent = $this->newData($playerSevent, 100, 2, 2, 93, 93, 100);
        $positionSevent = $this->newData($playerSevent, 93, 2, 2, 93, 93, 100);
        $positionSevent = $this->newData($playerSevent, 92, 2, 2, 135, 135, 100);
        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerSevent, 'endDate <' => date( 'Y-m-d' ) ) );
         if ($positionSevent) {

            $playerIdSevent = $positionSevent;
                
            $lastPosition = $this->position->getLastPosition( $playerIdSevent );
            $testSevent = $this->position->add($playerIdSevent, $playperiodPreDay);
            $insertIdSevent = $this->db->insert_id();
            $testPositionSevent = $this->position->get($insertIdSevent);
            // To verify position return is position = 60
            $this->unit->run((int)$testPositionSevent->startPosition, 90 , "To verify position return is position = 90", "To verify create position is valid");

            // To verify ruleApplied must be is equal string 'Go Back'
            $this->unit->run($testPositionSevent->ruleApplied, "returned to day 90", "To verify create position is valid", "To verify ruleApplied must be is equal string 'Go Back'");  

        } 

        $playerEighth = $this->getPlayer(1,null, 7);
        $positionEighth = $this->newData($playerEighth, 92, 2, 2, 245, 245, 100);
        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerEighth, 'endDate <' => date( 'Y-m-d' ) ) );
        if ($positionEighth) {

            $playerIdEighth = $positionEighth;
                
            $lastPosition = $this->position->getLastPosition( $playerIdEighth );
            $testEighth = $this->position->add($playerIdEighth, $playperiodPreDay);
            $insertIdEighth = $this->db->insert_id();
            $testPositionEighth = $this->position->get($insertIdEighth);
            // To verify position return is position = 90
            $this->unit->run((int)$testPositionEighth->startPosition, 120 , "To verify position return is position = 90", "To verify create position is valid");

            // To verify ruleApplied must be is equal string 'Go Back'
            $this->unit->run($testPositionEighth->ruleApplied, "returned to day 120", "To verify create position is valid", "To verify ruleApplied must be is equal string 'Go Back'");  

        }

        $playerNinth = $this->getPlayer(1,null, 8);
        $positionNinth = $this->newData($playerNinth, 40, 2, 2, 34, 34, 100);
        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerNinth, 'endDate <' => date( 'Y-m-d' ) ) );
        if ($positionNinth) {

            $playerIdNinth = $positionNinth;
                
            $lastPosition = $this->position->getLastPosition( $playerIdNinth );
            $testNinth = $this->position->add($playerIdNinth, $playperiodPreDay);
            $insertIdNinth = $this->db->insert_id();
            $testPositionNinth = $this->position->get($insertIdNinth);
            
            // To verify position return is position = 90
            $this->unit->run((int)$testPositionNinth->startPosition, 30 , "To verify position return is position = 120", "To verify create position is valid");

            // To verify ruleApplied must be is equal string 'Go Back'
            $this->unit->run($testPositionNinth->ruleApplied, "returned to day 30", "To verify create position is valid", "To verify ruleApplied must be is equal string 'Go Back'");  

        } 
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    /**
     * testGetAll 
     *
     * Function testing get all list positions to verify valid or invalid
     */
    public function testGetAll() {
        $count = $this->position->count_all();
        $IdInvalid = array( NULL, '', 'abc', 0, -123, $this->idNotExit());
        $offsetInvalid = array(NULL, '', 'abc', 0);
        $limitInvalid = array(NULL, '', 'abc', 0);

        if ( $count > 0 ) {
            $playerId = $this->newData(1,3,2,1,1,1,1);
            
            // To verify list postion return is invalid
            // ========================================
            // To verify id player id input is invalid
            foreach ($IdInvalid as $key => $value) {

                $testResultOne = $this->position->getAll($value, 0, 10);

                if ( is_array($testResultOne) && isset($testResultOne['errors']))

                    if ( $key == 5) {

                        // To verify player does'nt exit
                        $this->unit->run($testResultOne['errors'], "Positions Not Found", "To verify list postion return is invalid", "To verify player does'nt exit");
                    } else {

                        // To verify player id must be numberic and greater than 0
                        $this->unit->run($testResultOne['errors'], "Player Id must is a numeric and greater than zero","To verify list postion return is invalid", "To verify player id must be numberic and greater than 0");
                    }
            }
            // To verify offset and limit input is invalid 
            $offsetInvalid = array(null,'','abc', 0);
            $limitInvalid = array(null,'','abc', 0);
            foreach ($limitInvalid as $key => $value) {

                if ( array_key_exists($key, $offsetInvalid) ){

                    $resultTest = $this->position->getAll($playerId,$offsetInvalid[$key], $limitInvalid[$key]);
                    if ( is_array($resultTest) && isset($resultTest['errors']) ) {

                        // To verify get all list return is invalid
                        $this->unit->run($resultTest['errors'], "Positions Not Found", "To verify get all list return is invalid", "Verify offset and limit is invalid");
                    }
                }
            }
            // To verify list postion return is valid
            // ========================================
            $offset = 0;
            $limit = 10;
            $testResultSecond = $this->position->getAll($playerId, $limit, $offset);
            $countExpected = $this->position->count_by('playerId', $playerId);

             if (is_array($testResultSecond) && isset($testResultSecond['positions'])) {

                // verify limit return must be equal limit input before
                if ( isset($testResultSecond['limit']) ) {
                    $this->unit->run($testResultSecond['limit'], $limit, "To verify list return is valid", "verify limit return must be equal limit input before");
                }

                // verify offset return must be equal offset input before
                if ( isset($testResultSecond['offset']) ) {
                    $this->unit->run($testResultSecond['offset'], $offset, "To verify list return is valid", "verify offset return must be equal offset input before");
                }
                // verify count return must be equal count when get form data

                if( isset($testResultSecond['count']) ) {

                    $this->unit->run($testResultSecond['count'], $countExpected, "To verify list return is valid", "verify count return must be equal count get from data");
                }

                if( $limit < $countExpected)  {

                    // verify count list return must be equal value limit input
                    $this->unit->run(sizeof($testResultSecond['positions']), $limit, "To verify get list sweepstake is valid", "verify count return must be equal count when get form data"); 
                } else {
                    // verify count list return must be equal count of get on database
                    $this->unit->run(sizeof($testResultSecond['positions']), $countExpected, "To verify get list sweepstake is valid", "verify count list return must be equal count of get on database");
                }
            }

        } else {

           echo "<h4 style = 'color:red;'>Can't testing get all list positions.Pls make sure Postion isn't empty!"; 
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    /**
     * [returnResult description]
     * @param  [type] $results [description]
     * @return [type]          [description]
     */
    function returnResult($results) {
        $passed = [];
        $failed = [];
        foreach($this->unit->result() as $value) {
            if($value['Result'] === "Passed") {
                array_push($passed, $value['Result']);
            }

            if($value['Result'] === "Failed") {
                array_push($failed, $value['Result']);
            }
        }

        return  "<h1> Tests: ". sizeof($results). ", Passed: " .sizeof($passed). ", Failed:".sizeof($failed)."</h1>";
    }  
}