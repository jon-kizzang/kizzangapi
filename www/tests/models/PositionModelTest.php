<?php

/**
 * @group Model
 */
class PositionModelTest extends CIUnit_TestCase
{
    private $player;

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model(array('position', 'player', 'playperiod'));
        $this->position = $this->CI->position;
        $this->player = $this->CI->player;
        $this->playperiod = $this->CI->playperiod;

        // disable send SQS when run unit test
        $this->player->executeTesting = TRUE;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * idExit Function get id player exit
     *
     */
    public function idNotExit() {

        $dataDB = $this->player->order_by('id', 'DESC')->get_by( array('id !=' => 0) );

        if ( is_object($dataDB) && isset($dataDB->id) ) {

            return ((int)$dataDB->id + 1);
        }
        else {

            return 1;
        }
    }

    public function dataExpected($playerId) {

        try {

            $dataExpected = $this->CI->db->select('PlayPeriod.id, playerId')
            ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
            ->where('playerId', $playerId)
            ->order_by('id' ,'ASC')
            ->get('Players')->row();

            return $dataExpected;

        } catch (NotFoundException $e) {

            return $e;
        }
    }

    public function playerNotExitPosition() {

        $query = "SELECT id FROM Players  WHERE id NOT IN (SELECT DISTINCT playerId FROM Positions)";
        $row = $this->CI->db->query($query)->row();

        return $row ? $row->id : $this->getPlayer(1, null);
    }

    public function getPlayer($emailConfirmed, $FBID, $player) {

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

    public function newDataPlayPeriod ( $playerId, $day, $gamesPlayed, $status, $countMissedDay) {

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

    public function newData ($playerId, $day, $gamesPlayed, $status, $position, $endPosition, $countMissedDay) {

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

    function testAddPosition() {

        // truncate table
        $this->CI->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->CI->db->query('TRUNCATE Players;');
        $this->CI->db->query('TRUNCATE Positions;');
        $this->CI->db->query('TRUNCATE PlayPeriod;');
        $this->CI->db->query('SET FOREIGN_KEY_CHECKS=1;');
        $this->CI->db->query("INSERT INTO `Players` (`roleId`, `gender`, `accountCreated`, `lastLogin`, `lastApprovedTOS`, `lastApprovedPrivacyPolicy`, `emailHash`, `passwordHash`, `accountEmail`, `payPal`, `emailCode`, `screenname`, `profileComplete`, `accountData`, `emailVerified`) VALUES ('1', '1', '2014-09-26 03:58:28', '2014-09-26 03:58:28', '2014-09-26 03:58:28', '2014-09-26 03:58:28', 'd9eaf0b87e204b0449760df19caacb12', '25f9e794323b453885f5181f1b624d0b', 'Xz4s0kDUm/w+6R/hvSaQVQgV+bX6dR4/KSV8PJJqkbjIevjvucbEiXMIx06n/FBS8efmQyBvtftpY770caGr1DDawnALO/cwan4cmY5cZZJA==', 'Xz4s0kDUm/w+6R/hvSaQVQgV+bX6dR4/KSV8PJJqkbjIevjvucbEiXMIx06n/FBS8efmQyBvtftpY770caGr1DDawnALO/cwan4cmY5cZZJA==', '71555ffedae2a0869bc21187f47dd2', 'Kizzang A', 0, '5x12CCa8dw4IWYlKWKSFUgUJM1weYSDrovDD22bQulseo6Oboikt4gIwB0Xjqmqs0LZNGbl+tP8uI8g5f6sjAq5mYyrXpUzaBxZHgPHYdMHYHEfLnvfbfKTrxlTkHEk+iMMGGfQqKIVu99qllHVWXv', '1')");

        $this->player->memcacheInstance->flush();

        $ruleAppliedLookup = array(
            1 => 'increment position',
            2 => 'Lucked out and stays on their current day',
            3 => 'Move back 25% of previous for missed day',
            4 => 'Move back 50% of previous for missed day',
            5 => 'Go Back'
        );

        $playerId         = $this->getPlayer(1, null, 1);

        $positionFirst    = $this->newData($playerId, 15, 15, 3, 9, 9, 0);
        $positionFirst    = $this->newData($playerId, 10, 3, 2, 10, 11, 1);
        $positionFirst    = $this->newData($playerId, 5, 15, 3, 10, 11, 1);
        $positionExit     = $this->newData($playerId , 1, 15, 3 ,9, 10, 0);

        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerId, 'endDate <' => date( 'Y-m-d' ) ) );

        if ( $positionExit != 0 ) {

            // verify create postion return is invalid
            $exitPosition = $this->position->get_by( array('calendarDate'=> date('Y-m-d'), 'playerId' => $positionExit) );

            if ($exitPosition) {
                $testResult = $this->position->add($positionExit, $playperiodPreDay);
                if (is_array($testResult) && isset($testResult['message'])) {

                    // Exist position on current time
                    $this->assertContains($testResult['message'], "Position exists in ". date('Y-m-d'). " with $positionExit", "To verify exist position on current time");
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
                $this->assertEquals($testResultOne['statusCode'], 201, "To verify status code return must be 201");
                // verify msg return is  Position created the successfully
                $this->assertContains($testResultOne['message'], "Position created successfully", "To verify Position created the successfully");

                // verify position return must be is 1
                $this->assertEquals((int)$testPosition->startPosition, 1, "To verify position return must be is 1");

                // verify position return must be is 1
                $this->assertEquals((int)$testPosition->endPosition, 1, "To verify position return must be is 1");

                // verify ruleApplied must be equal init position
                $this->assertContains($testPosition->ruleApplied, "init position", "To verify ruleApplied must be equal init position");
            }

        }

        if( $positionFirst ) {

            $playerIdSecond      = $positionFirst;
            $lastPosition        = $this->position->getLastPosition( $playerIdSecond );
            $resultSecond        = $this->position->add($playerIdSecond, $playperiodPreDay) ;
            $insertIdSecond      = $this->CI->db->insert_id();
            $testPositionSecond  = $this->position->get($insertIdSecond);

            if(is_array($resultSecond) && isset($resultSecond['statusCode']) && $resultSecond['statusCode'] == 201 ) {

                // To verify position return must be equal position +=1
                $this->assertEquals((int)$testPositionSecond->startPosition, ($lastPosition), "To verify position return must be equal position +=1");

                // To verify position return must be equal position +=1
                $this->assertEquals((int)$testPositionSecond->endPosition, ($lastPosition), "To verify position return must be equal position +=1");

                // To verify ruleApplied must be is equal string 'increment position'
                $this->assertContains($testPositionSecond->ruleApplied, "increment position", "To verify ruleApplied must be equal increment position");
            }
        }

        $playerIdThird  = $this->getPlayer(1, null, 2);
        $positionThird  = $this->newData($playerIdThird, 40, 15, 3, 9, 9, 0);
        $positionSecond = $this->newData($playerIdThird, 30, 3, 2, 10, 9, 1);
        $positionSecond = $this->newData($playerIdThird, 10, 15, 3, 10, 10, 1);
        $positionExit   = $this->newData($playerIdThird , 1, 15, 2, 9, 10, 2);

        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerIdThird, 'endDate <' => date( 'Y-m-d' ) ) );

        if( $playerIdThird ) {

            $lastPosition       = $this->position->getLastPosition( $playerIdThird );
            $resultThird        = $this->position->add( $playerIdThird, $playperiodPreDay);
            $insertIdThird      =     $this->CI->db->insert_id();
            $testPositionThird  = $this->position->get($insertIdThird);

            if( is_array($resultThird) && isset($resultThird['statusCode']) && $resultThird['statusCode'] == 201 ) {

                // To verify position return must be equal position previous
                $this->assertEquals((int)$testPositionThird->startPosition, (int)($lastPosition), "To verify position return must be equal position input previous");

                // To verify ruleApplied must be is equal string 'Lucked out and stays on their current day'
                $this->assertContains($testPositionThird->ruleApplied, "increment position", "To verify ruleApplied must be is equal string 'Lucked out and stays on their current day'");
            }
        }

        $playerIdFourth   = $this->getPlayer(1, null, 3);
        $positionThird    = $this->newData($playerIdFourth, 50, 15, 3, 9, 9, 2);
        $positionSecond   = $this->newData($playerIdFourth, 49, 3, 2, 10, 10,5);
        $positionSecond   = $this->newData($playerIdFourth, 35, 5, 2, 10, 10, 9);

        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerIdFourth, 'endDate <' => date( 'Y-m-d' ) ) );

        if( $playerIdFourth ) {

            $lastPosition       = $this->position->getLastPosition( $playerIdFourth );
            $testFourth         = $this->position->add($playerIdFourth, $playperiodPreDay);
            $insertIdFourth     = $this->CI->db->insert_id();
            $testPositionFourth = $this->position->get($insertIdFourth);

            // To verify position return is position -= Math.floor( position * 0.25 )
            $this->assertEquals((int)$testPositionFourth->endPosition, 1 , "To verify returned to day 1");

            // To verify ruleApplied must be is equal string 'Move back 25% of previous for missed day'
            $this->assertContains($testPositionFourth->ruleApplied, "returned to day 1", "To verify ruleApplied must be is equal string 'returned to day 1'");

        }

        // ($playerId, $day, $gamesPlayed, $status, $position, $endPosition, $countMissedDay)
        // $positionFifth = $this->newData($playerId, 1, 2 , 2, 9);
        $playerFifth      = $this->getPlayer(1, null, 4);
        $positionFifth    = $this->newData($playerFifth, 1 , 5, 2, 50, 50, 50);

        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerFifth, 'endDate <' => date( 'Y-m-d' ) ) );

        if ($positionFifth) {

            $playerIdFifth     = $playerFifth;

            $lastPosition      = $this->position->getLastPosition( $playerIdFifth );
            $testFifth         = $this->position->add($playerIdFifth, $playperiodPreDay);
            $insertIdFifth     =    $this->CI->db->insert_id();
            $testPositionFifth = $this->position->get($insertIdFifth);

            // if position 1-31
            if ( $lastPosition < 32 ) {
                $point = 1;
            }
            // if position 32-91
            elseif ( $lastPosition < 92 ) {
                $point = 31;
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
                $this->assertEquals((int)$testPositionFifth->endPosition, (int)($lastPosition), "To verify position return is position." );

                // To verify ruleApplied must be is equal string 'Move back 50% of previous for missed day'
                $this->assertContains($testPositionFifth->ruleApplied, "Lucked out and stays on their current day", "To verify ruleApplied must be is equal string 'Lucked out and stays on their current day'");

            } elseif ( $testPositionFifth->ruleCode == 1 ) {

                // To verify position return is position -= Math.floor( position * 0.5 )
                $this->assertEquals((int)$testPositionFifth->endPosition, (int)($lastPosition), "To verify create position is valid");

                // To verify ruleApplied must be is equal string 'Move back 50% of previous for missed day'
                $this->assertContains($testPositionFifth->ruleApplied, "Lucked out and stays on their current day", "To verify ruleApplied must be is equal string 'Lucked out and stays on their current day'");

            } elseif ( $testPositionFifth->ruleCode == 3 ) {

                // To verify position return is position -= Math.floor( position * 0.25 )
                $this->assertEquals((int)$testPositionFifth->endPosition, (int)($lastPosition - round( ( $lastPosition - $point ) * 0.25 )), "To verify position return is position -= Math.floor( position * 0.25 )");

                // To verify ruleApplied must be is equal string 'Move back 50% of previous for missed day'
                $this->assertContains($testPositionFifth->ruleApplied, "Move back 25% of previous for missed day", "To verify ruleApplied must be is equal string 'Move back 50% of previous for missed day'");


            } elseif ( $testPositionFifth->ruleCode == 4 ) {

                // To verify position return is position -= Math.floor( position * 0.5 )
                $this->assertEquals((int)$testPositionFifth->endPosition, (int)($lastPosition - round( ( $lastPosition - $point ) * 0.5 )), "To verify position return is position -= Math.floor( position * 0.5 )");

                // To verify ruleApplied must be is equal string 'Move back 50% of previous for missed day'
                $this->assertContains($testPositionFifth->ruleApplied, "Move back 50% of previous for missed day", "To verify ruleApplied must be is equal string 'Move back 50% of previous for missed day'");

            } else {

                $this->assertEquals((int)$testPositionFifth->endPosition, (int)$point, "To verify create position return GO BACK");

            }
        }

        $playerIdSix      = $this->getPlayer(1, null, 5);
        $positionSix      = $this->newData($playerIdSix, 92, 15, 3, 50, 51, 5);
        $positionSix      = $this->newData($playerIdSix, 91, 2, 2, 52, 53, 100);
        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerIdSix, 'endDate <' => date( 'Y-m-d' ) ) );

        if ($positionSix) {

            $playerIdSix     = $positionSix;

            $lastPosition    = $this->position->getLastPosition( $playerIdSix );
            $testSix         = $this->position->add($playerIdSix, $playperiodPreDay);
            $insertIdSix     = $this->CI->db->insert_id();
            $testPositionSix = $this->position->get($insertIdSix);

            // To verify position return is position = 31
            $this->assertEquals((int)$testPositionSix->startPosition, 31 , "To verify position return is position = 31");

            // To verify ruleApplied must be is equal string 'Go Back'
            $this->assertContains($testPositionSix->ruleApplied, "returned to day 31", "To verify ruleApplied must be is equal string 'Go Back'");

        }

        $playerSevent     = $this->getPlayer(1,null, 6);
        $positionSevent   = $this->newData($playerSevent, 100, 2, 2, 93, 93, 100);
        $positionSevent   = $this->newData($playerSevent, 93, 2, 2, 93, 93, 100);
        $positionSevent   = $this->newData($playerSevent, 92, 2, 2, 135, 135, 100);

        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerSevent, 'endDate <' => date( 'Y-m-d' ) ) );
         if ( $positionSevent ) {

            $playerIdSevent     = $positionSevent;

            $lastPosition       = $this->position->getLastPosition( $playerIdSevent );
            $testSevent         = $this->position->add($playerIdSevent, $playperiodPreDay);
            $insertIdSevent     = $this->CI->db->insert_id();
            $testPositionSevent = $this->position->get($insertIdSevent);

            // To verify position return is position = 60
            $this->assertEquals((int)$testPositionSevent->startPosition, 90 , "To verify position return is position = 90");

            // To verify ruleApplied must be is equal string 'Go Back'
            $this->assertContains($testPositionSevent->ruleApplied, "returned to day 90", "To verify ruleApplied must be is equal string 'Go Back'");

        }

        $playerEighth     = $this->getPlayer(1,null, 7);
        $positionEighth   = $this->newData($playerEighth, 92, 2, 2, 245, 245, 100);
        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerEighth, 'endDate <' => date( 'Y-m-d' ) ) );
        if ($positionEighth) {

            $playerIdEighth     = $positionEighth;

            $lastPosition       = $this->position->getLastPosition( $playerIdEighth );
            $testEighth         = $this->position->add($playerIdEighth, $playperiodPreDay);
            $insertIdEighth     = $this->CI->db->insert_id();
            $testPositionEighth = $this->position->get($insertIdEighth);

            // To verify position return is position = 90
            $this->assertEquals((int)$testPositionEighth->startPosition, 120 , "To verify position return is position = 90");

            // To verify ruleApplied must be is equal string 'Go Back'
            $this->assertContains($testPositionEighth->ruleApplied, "returned to day 120", "To verify ruleApplied must be is equal string 'Go Back'");

        }

        $playerNinth      = $this->getPlayer(1,null, 8);
        $positionNinth    = $this->newData($playerNinth, 40, 2, 2, 34, 34, 100);
        $playperiodPreDay = $this->playperiod->limit( 1 )
                    ->order_by( 'endDate', 'DESC' )
                    ->get_by( array( 'playerId' => $playerNinth, 'endDate <' => date( 'Y-m-d' ) ) );

        if ($positionNinth) {

            $playerIdNinth     = $positionNinth;

            $lastPosition      = $this->position->getLastPosition( $playerIdNinth );
            $testNinth         = $this->position->add($playerIdNinth, $playperiodPreDay);
            $insertIdNinth     = $this->CI->db->insert_id();
            $testPositionNinth = $this->position->get($insertIdNinth);

            // To verify position return is position = 90
            $this->assertEquals((int)$testPositionNinth->startPosition, 31 , "To verify position return is position = 120");

            // To verify ruleApplied must be is equal string 'Go Back'
            $this->assertContains($testPositionNinth->ruleApplied, "returned to day 31", "To verify ruleApplied must be is equal string 'Go Back'");

        }

    }

    function testGetAll() {

        $count         = $this->position->count_all();
        $IdInvalid     = array( NULL, '', 'abc', 0, -123, $this->idNotExit());
        $offsetInvalid = array(NULL, '', 'abc', 0);
        $limitInvalid  = array(NULL, '', 'abc', 0);
        if ( $count > 0 ) {

            $playerId = $this->newData(1,3,2,1,1,1,1);

            // To verify list postion return is invalid
            // ========================================
            // To verify id player id input is invalid
            foreach ($IdInvalid as $key => $value) {

                $testResultOne = $this->position->getAll($value, 0, 10);

                if ( is_array($testResultOne) && isset($testResultOne['message']))

                    if ( $key == 5) {

                        // To verify player does'nt exit
                        $this->assertContains($testResultOne['message'], "Player Id doesn't exist", "To verify player does'nt exit");

                    } else {

                        // To verify player id must be numberic and greater than 0
                        $this->assertContains($testResultOne['message'], "Player Id must is a numeric and greater than zero", "To verify player id must be numberic and greater than 0");
                    }
            }
            // To verify offset and limit input is invalid
            $offsetInvalid = array(null,'','abc', 0);
            $limitInvalid  = array(null,'','abc', 0);

            foreach ($limitInvalid as $key => $value) {

                if ( array_key_exists($key, $offsetInvalid) ){

                    $resultTest = $this->position->getAll($playerId,$offsetInvalid[$key], $limitInvalid[$key]);

                    if ( is_array($resultTest) && isset($resultTest['message']) ) {

                        // To verify get all list return is invalid
                        $this->assertContains($resultTest['message'], "Positions Not Found", "Verify offset and limit is invalid");
                    }
                }
            }
            // To verify list postion return is valid
            // ========================================
            $offset           = 0;
            $limit            = 10;
            $testResultSecond = $this->position->getAll($playerId, $limit, $offset);
            $countExpected    = $this->position->count_by('playerId', $playerId);

             if ( is_array($testResultSecond) && isset($testResultSecond['positions']) ) {

                // verify limit return must be equal limit input before
                if ( isset($testResultSecond['limit']) ) {
                    $this->assertEquals($testResultSecond['limit'], $limit, " To verify limit return must be equal limit input before");
                }

                // verify offset return must be equal offset input before
                if ( isset($testResultSecond['offset']) ) {

                    $this->assertEquals($testResultSecond['offset'], $offset, "To verify offset return must be equal offset input before");
                }
                // verify count return must be equal count when get form data

                if( isset($testResultSecond['count']) ) {

                    $this->assertEquals($testResultSecond['count'], $countExpected, "verify count return must be equal count get from data");
                }

                if( $limit < $countExpected)  {

                    // verify count list return must be equal value limit input
                    $this->assertEquals(sizeof($testResultSecond['positions']), $limit, "verify count return must be equal count when get form data");
                } else {

                    // verify count list return must be equal count of get on database
                    $this->assertEquals(sizeof($testResultSecond['positions']), $countExpected, "verify count list return must be equal count of get on database");
                }
            }

        } else {

            $this->assertEquals( FALSE,"Can't testing get all list positions.Pls make sure Postion isn't empty!" );

        }

    }
}