<?php

/**
 * @group Model
 */
class PlayperiodModelTest extends CIUnit_TestCase
{
    private $player;

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model(array('playperiod', 'player'));
        $this->playperiod = $this->CI->playperiod;
        $this->player = $this->CI->player;

    }

    public function tearDown()
    {
        parent::tearDown();
    }

    protected function getPlayer($emailConfirmed, $FBID) {

        if ($emailConfirmed === 0) {
            $data = array(
                'email'     => substr( md5( uniqid( date( 'Y-m-d H:i:s' ), true ) ), 0, 30 )."login0@gmail.com",
                'password'  => "123456",
                'gender'    => '1',
                'firstName' => "User",
                'lastName'  => "Test",
                'dob'       => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
                'phoneHome' => '0123456789',
                'option3'   => TRUE,
                );
            $player = $this->player->add($data);
            if (is_object($player) && isset($player->id)) {

                $playerFormat = $this->player->getById($player->id, TRUE);
                return $playerFormat;
            } else {

                return FALSE;
            }
        } else {

            $data = array(
                'email' => substr( md5( uniqid( date( 'Y-m-d H:i:s' ), true ) ), 0, 30 )."login1@gmail.com",
                'password' => "123456",
                'gender' => 1,
                'firstName' => "User",
                'lastName' => "Test",
                'dob' => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
                'phoneHome' => '0123456789',
                'option3'   => TRUE,
                'fbId' => is_null($FBID) ? $FBID : md5( $FBID )
                );
            $player = $this->player->add($data);

            if ( is_object($player) && isset($player->id) ) {
                $confirmed = $this->player->emailVerified($player->emailCode);
                if (is_array($confirmed) && isset($confirmed['statusCode']) && $confirmed['statusCode'] === 200) {
                    $playerConfirmed = $this->player->getById($player->id, TRUE);
                    return $playerConfirmed;
                }

            } else {

                return FALSE;
            }

        }

    }

    protected function createCurrent($startTime, $endTime, $update , $playerID, $id) {
        if ( !$update && !$playerID && !$id) {

            $player = $this->player->order_by('id', 'RANDOM')->get_by('isDeleted', 0);
            if (is_object($player) && isset($player->id)) {

                $data = array(
                    'playerId' => $player->id,
                    'startDate' => $startTime,
                    'endDate' => $endTime,
                    'gamesCredit' => 15,
                    'gamesPlayed' => 0,
                    'status' => 2
                );

                // set skip_validation = TRUE in 2nd parameter
                $insertId = $this->playperiod->insert( $data, TRUE );
                if ($insertId) {

                    $result = $this->playperiod->getById( $player->id, $insertId );

                }

                return isset($result) ? $result : false;
            }

        } else {
            $data = array(
                    'playerId' => $playerID,
                    'startDate' => $startTime,
                    'endDate' => $endTime,
                    'gamesCredit' => 15,
                    'gamesPlayed' => 0,
                    'status' => 2
                );

            // set skip_validation = TRUE in 2nd parameter
            $updateId = $this->playperiod->update( $id, $data, TRUE );

            if ($updateId) {

                $result = $this->playperiod->getById( $playerID, $id );

            }

            return isset($result) ? $result : false;
        }

    }

    function testAddNewPeriod() {

        $query = $this->CI->db->query('TRUNCATE PlayPeriod;');

        $getIdNotExit = $this->player->limit(1)->order_by('id', 'DESC')->get_all();

        // verify player Id return invalid
        $dataExpected    = $this->getPlayer(0, null);
        $idNotExits      = isset($getIdNotExit[0]->id) ? ($getIdNotExit[0]->id + 1) : 1;
        $playerIdInvalid = array('', null, 'abc', 0, -123, $idNotExits );

        foreach ($playerIdInvalid as $key => $value) {

            $resultTest = $this->playperiod->add($playerIdInvalid[$key] );

            if ($key != 5) {

                if (isset($resultTest['message'])) {

                        //To verify id player return invalid
                        $this->assertContains($resultTest['message'], $this->CI->db->_error_message());
                }
            }
        }

        // verify playperiod return is valid with status is NotReady
        if (is_object($dataExpected) > 0 && isset($dataExpected->id)) {

            $testResult = $this->playperiod->add($dataExpected->id);

            if (is_object($testResult) && isset($testResult->id)) {

                // verify endtime return must be equal 23:59:59 of end day
                $this->assertEquals($testResult->endDate, date('m-d-Y 23:59:59'));

                // verify status return must be equal is NotReady
                $this->assertContains($testResult->status, "NotReady");

            } elseif (is_array($testResult) && isset($testResult['message'])) {

                // verify add new playperiod is exit on date
                $this->assertContains($testResult['message'], "PlayPeriod exists in ". date('Y-m-d'));
            }
        } else {

            $this->assertTrue(FALSE);
        }

        $dataExpectedEmailConfirm = $this->getPlayer(1, null);

        // verify playperiod return is valid with status is NotReady
        if ( is_object($dataExpectedEmailConfirm) && isset($dataExpectedEmailConfirm->id) ) {

            $testResultSecond = $this->playperiod->add($dataExpectedEmailConfirm->id);

            if (is_object($testResultSecond) && isset($testResultSecond->id)) {

                // verify endtime return must be equal 23:59:59 of end day
                $this->assertEquals($testResultSecond->endDate, date('m-d-Y 23:59:59'));

                // verify status return must be equal is NotReady
                $this->assertContains($testResultSecond->status, "Ready");

            } elseif (is_array($testResultSecond) && isset($testResultSecond['message'])) {

                // verify add new playperiod is exit on date
                $this->assertContains($testResultSecond['message'], "PlayPeriod exists in ". date('Y-m-d'));
            }
        } else {

            $this->assertTrue(FALSE);
        }
    }

    function testUpdatePeriod() {

        $dataExpectedEmailConfirm = $this->CI->db->select('PlayPeriod.id, playerId')
        ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
        ->where(array('isDeleted' => 0, 'emailVerified' => 0 ))
        ->order_by('id' ,'DESC')
        ->get('Players')->row();
        $gamesPlayed = 2;

        if (is_object($dataExpectedEmailConfirm)) {

            // verify status return must be is status NotReady
            $testNotReady = $this->playperiod->edit($dataExpectedEmailConfirm->playerId, $dataExpectedEmailConfirm->id, $gamesPlayed);

            if (is_object($testNotReady) && isset($testNotReady->id)) {

                $this->assertContains($testNotReady->status, "NotReady");
            }
        }

        $player = $this->getPlayer(1,null);

        $dataLogin = array(
            'email'    => $player->accountEmail,
            'password' => 123456,
            'deviceId' =>1);

        $login = $this->player->login($dataLogin);

        $this->player->setToken($login['token']);

        $dataExpected = $this->CI->db->select('PlayPeriod.id, playerId')
        ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
        ->where(array('isDeleted' => 0, 'emailVerified' => 1 ))
        ->order_by('id' ,'DESC')
        ->get('Players')->row();

        // To verify Id player of period return is invalid
        $idPlayerInvalid    = array(NULL, 'abc', '', -123, 0, 100000);
        $id_invalid         = array(NULL, 'abc', '', -123, 0, 100000);
        $gamesPlayedInvalid = array(NULL, 'abc', '', -123);
        $gamesPlayed = 2;
        foreach ($id_invalid as $key => $value) {

           $resultTest = $this->playperiod->edit($idPlayerInvalid[$key], $id_invalid[$key], $gamesPlayed );

            if(isset($resultTest['statusCode']) && $resultTest['statusCode'] == 400 ) {

                //verify playerId id is invalid
                $this->assertContains($resultTest['message'], "Id must be a numeric and greater than zero");
            }
        }

        // To verify period id is invalid
        if (isset($dataExpected->playerId)) {

            foreach ($id_invalid as $key => $value) {

                $resultTest = $this->playperiod->edit($dataExpected->playerId, $id_invalid[$key], $gamesPlayed );

                if(isset($resultTest['message'])) {
               
                    if($key == 5) {

                        // verify playerId id isn't exist result must be null
                        $this->assertContains($resultTest['message'], "Play Period Not Found");

                    } elseif(empty($id_invalid[$key])) {

                        //verify playerId id is invalid
                        $this->assertContains($resultTest['message'][0], "The Id field is required.");
                    } else {

                       //verify playerId id is invalid
                        $this->assertContains($resultTest['message'][0], "The Id field must contain a number greater than 0.");
                    }
                }
            }
        }

        // To verify period gameplayed is invalid
        if ( isset($dataExpected->playerId) && isset($dataExpected->id) ) {

            foreach ($gamesPlayedInvalid as $key => $value) {
            
                $resultTest = $this->playperiod->edit($dataExpected->playerId, $dataExpected->id, $gamesPlayedInvalid );
            
                if( isset($resultTest['message'][0]) ) {
            
                    // verify playerId id isn't exist result must be null
                    $this->assertContains($resultTest['message'][0], "The Games Playerd field is required.");
                }
            }
        }

        if ( isset($dataExpected->playerId) && isset($dataExpected->id) ) {

            $dataExpectedUpdate = $this->playperiod->getById($dataExpected->playerId,$dataExpected->id);
            $creditExpected     = $dataExpectedUpdate->gamesCredit;
            $gamesPlayed        = rand(0, $creditExpected);
            $endTimeExpected    = $dataExpectedUpdate->endDate;
            $endTimeInt         = strtotime( str_replace( '-', '/', $endTimeExpected));
            $statusExpected     = $dataExpectedUpdate->status;
            $currentExpected    =   strtotime( date( 'm/d/Y H:s:i' ));
            $testResult         = $this->playperiod->edit($dataExpected->playerId,$dataExpected->id, $gamesPlayed );

            if ( isset($testResult->id) ) {

                //To verify id return must be equal id input
                $this->assertEquals( $testResult->id, (int)$dataExpected->id );

                // To verify id return must be equal id input
                $this->assertEquals( $testResult->playerId, (int)$dataExpected->playerId );

                if( $currentExpected >= $endTimeInt ) {
            
                    if ($gameplayed < $creditExpected) {

                        // To verify update status period return is valid
                        $this->assertContains($testResult->status, "Expired");
                    }
            
                    if ($gameplayed = $creditExpected) {
                        
                        // To verify update status period return is Completed
                        $this->assertContains($testResult->status, "Completed");
                    }
                }

                if( $currentExpected < $endTimeInt ) {

                    if ($gamesPlayed == $creditExpected) {

                         // To verify update status period return is valid
                        $this->assertContains($testResult->status, "Completed");

                    } elseif ($gamesPlayed < $creditExpected) {

                        // To verify update status period return is valid
                        $this->assertContains($testResult->status, "Ready");
                    }
                }
            }
        }
    }

    function testUpdateStatusPeriod() {

        $player    = $this->getPlayer(1,null);
        $dataLogin = array('email' => $player->accountEmail, 'password' => 123456, 'deviceId'=>1);
        $login     = $this->player->login($dataLogin);
        $this->player->setToken($login['token']);

        $dataExpected = $this->CI->db->select('PlayPeriod.id, playerId')
        ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
        ->where('playerId',$player->id)
        ->order_by('id' ,'RANDOM')
        ->get('Players')->row();

        // To verify Id player of period return is invalid
        $idPlayerInvalid = array(NULL, 'abc', '', -123, 0, 100000);
        $id_invalid      = array(NULL, 'abc', '', -123, 0, 100000);
        $statusInvalid   = array('abc', -123, 6);
        $status          = 2;
        
        foreach ($id_invalid as $key => $value) {

          $resultTest = $this->playperiod->editStatus($idPlayerInvalid[$key], $id_invalid[$key], $status );
            
            if(isset($resultTest['statusCode']) && $resultTest['statusCode'] == 400 ) {
            
                //verify playerId id is invalid
                $this->assertContains($resultTest['message'], "Id must be a numeric and greater than zero");
            }
        }

        // To verify period id is invalid
        if (isset($dataExpected->playerId)) {

            foreach ($id_invalid as $key => $value) {
            
                $resultTestSecond = $this->playperiod->editStatus($dataExpected->playerId, $id_invalid[$key], $status );

                if(isset($resultTestSecond['message'])) {
            
                    if($key == 5) {

                        // verify playerId id isn't exist result must be null
                        $this->assertContains($resultTestSecond['message'], "Play Period Not Found");

                    } elseif( empty($id_invalid[$key]) ) {

                        //verify playerId id is invalid
                        $this->assertContains($resultTestSecond['message'][0], "The Id field is required.");

                    } else {

                       //verify playerId id is invalid
                        $this->assertContains($resultTestSecond['message'][0], "The Id field must contain a number greater than 0."); 
                    }
                }
            }   
        } 

        // To verify period status is invalid
        if (isset($dataExpected->playerId) && isset($dataExpected->id)) {

            foreach ($statusInvalid as $key => $value) {
            
                $resultTestThird = $this->playperiod->editStatus($dataExpected->playerId, $dataExpected->id, $value );
            
                if(isset($resultTestThird['message'])) {

                    if(is_string($value) || $value < 0) {

                        //To verify The Status field must contain a number greater than -1.
                        $this->assertContains($resultTestThird['message'][0], "The Status field must contain a number greater than -1.");
                    } else {

                       //verify Status must is numeric and in set (0,1,2,3,4,5)
                        $this->assertContains($resultTestThird['message'], "Status must is numeric and in set (0,1,2,3,4)"); 
                    }
                }
            }  
        }

        if ( isset($dataExpected->playerId) && isset($dataExpected->id) ) {

            $dataExpectedUpdate = $this->playperiod->getById($dataExpected->playerId,$dataExpected->id);
            $timeNow            = strtotime( date( 'm/d/Y H:s:i' ));
            $endDate            = strtotime( str_replace('-', '/', $dataExpectedUpdate->endDate ));
            $player             = $this->player->getById($dataExpected->playerId);
            $status             = 2;
            $testResultFourd = $this->playperiod->editStatus($dataExpected->playerId,$dataExpected->id, $status );

            if ( isset($testResultFourd->id) ) {

                if ($player->emailVerified == 0) {

                    // Verify status return must be is NotReady
                    $this->assertContains($testResultFourd->status, "NotReady");
                    
                } elseif ( $timeNow > $endDate) {
                    
                    // Verify status return must be is Expired
                    $this->assertContains($testResultFourd->status, "Expired");

                } elseif ($dataExpectedUpdate->gamesCredit == $dataExpectedUpdate->gamesPlayed) {
                    
                    // Verify status return must be is Completed
                    $this->assertContains($testResultFourd->status, "Completed");
                
                } elseif ( $status === 2) {

                    // Verify status return must be is Completed
                    $this->assertContains($testResultFourd->status, "Ready");
                }

            }

        }
    }

    function testGetAllPeriod() {

        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);

        $this->player->setToken( $login['token'] );

        $dataExpected = $this->CI->db->select('PlayPeriod.id, playerId')
        ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
        ->where('isDeleted', 0)
        ->order_by('id' ,'DESC')
        ->get('Players')->row();

        if ( is_object($dataExpected) && isset($dataExpected->id)) {
            
            // In case is invalid
            // ===========================
            // verify that player id, start time, end time is invalid
            // verify player Id return invalid 
            $idExist    = $this->player->limit(1)->order_by('id', 'DESC')->get_all();
            $idNotExits = isset($idExist[0]->id) ? ($idExist[0]->id + 1) : 1;
            $id_invalid = array(null, 'abc', '', -123, 0);
            $startTime  = array(null, 'abc', '', -123, 0);
            $endTime    = array(null, 'abc', '', -123, 0);
            $offset     = 0;
            $limit      = 10;

            foreach ($id_invalid as $key => $value) {

                if (array_key_exists($key, $startTime) && array_key_exists($key, $endTime)) {

                    $periodTest = $this->playperiod->getAll($id_invalid[$key], $limit, $offset, $startTime[$key], $endTime[$key]);

                    if ( isset($periodTest['message']) ) 

                        //in case player id is empty
                        $this->assertContains($periodTest['message'], 'Id must be a numeric and greater than zero');
                       
                }
            }

            foreach ($startTime as $key => $value) {

                if (array_key_exists($key, $endTime)) {

                    $periodTestTime = $this->playperiod->getAll($dataExpected->playerId, $limit, $offset, $startTime[$key], $endTime[$key]);
                    if ( isset($periodTestTime['message']) ) 

                        if (isset($periodTestTime['message'][0])) {

                            // in case period start Date is empty
                            $this->assertContains($periodTestTime['message'][0], 'Start Date must contain a valid date (m-d-Y H:i:s)');
                        }


                        if (isset($periodTestTime['message'][1])) {

                            // in case end time is empty
                            $this->assertContains($periodTestTime['message'][1], 'End Date must contain a valid date (m-d-Y H:i:s)');
                        }

                }
            }     

            // verify start time must be leeser than endTime
            $startTime        = "09-10-2014 00:00:00";
            $endTime          = "09-10-2014 00:00:00";
            $periodTestFourth = $this->playperiod->getAll($dataExpected->playerId, $limit, $offset, $startTime, $endTime);

            if(isset($periodTestFourth['message'])) {

                // in case period start Date is empty
                $this->assertContains($periodTestFourth['message'], 'End Date must greater than Start Date');
            }

            $idPeriod           = $dataExpected->id;
            $idPlayer           = $dataExpected->playerId;
            $dataExpectedPeriod = $this->playperiod->getById($idPlayer, $idPeriod);
            $startTime          = $dataExpectedPeriod->startDate;
            $endTime            = $dataExpectedPeriod->endDate;
            $startTimeStamp     = date('Y-m-d H:i:s', strtotime(str_replace( '-', '/', $startTime)));
            $endTimeStamp       = date('Y-m-d H:i:s', strtotime(str_replace( '-', '/', $endTime)));
            $resultExpected     = $this->playperiod->get_many_by(array('playerId' => $idPlayer, 'startDate >= ' => $startTimeStamp, 'endDate <= ' => $endTimeStamp ));

            $countExpected      = sizeof($resultExpected);
            $resultTest         = $this->playperiod->getAll($idPlayer, $limit, $offset, $startTime, $endTime);

            if ( is_array($resultTest) && isset($resultTest['playPeriod']) ) {

                // verify offset return must be equal previous offset input
                $this->assertEquals($resultTest['offset'], $offset) ;

                // verify limit return must be equal previous limit input 
                 $this->assertEquals($resultTest['limit'], $limit) ;
                
                // verify count return must be equal previous count input 
                 $this->assertEquals($resultTest['count'], $countExpected) ;
                
            } else {
                
                $this->assertTrue(FALSE); 
            }

        } else {
            
            $this->assertTrue(FALSE);
        }

    }

    function testGetPeriodById() {

        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);

        $this->player->setToken( $login['token'] );
        $dataExpected = $this->CI->db->select('PlayPeriod.id, playerId')
        ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
        ->where('isDeleted', 0)
        ->order_by('id' ,'DESC')
        ->get('Players')->row();

        if ( is_object($dataExpected) && isset($dataExpected->id) ) {

            // verify period id is invalid
            $idPlayerInvalid = array(null, 'abc', '', -123, 0, 100000);
            $id_invalid      = array(null, 'abc', '', -123, 0, 100000);

            foreach ($id_invalid as $key => $value) {

                $resultTestOne = $this->playperiod->getById($idPlayerInvalid[$key], $id_invalid[$key]);

                if(isset($resultTestOne['message'])) {

                    if($key == 5) {

                        // verify playerId id isn't exist result must be null
                        $this->assertContains($resultTestOne['message'], "Play Period Not Found");
                    } else {

                        //verify playerId id is invalid
                        $this->assertContains($resultTestOne['message'], "Id must be a numeric and greater than zero");
                    }
                }
            }

            if (isset($dataExpected->playerId)) {

                foreach ($id_invalid as $key => $value) {

                    $resultTestSecond = $this->playperiod->getById($dataExpected->playerId, $id_invalid[$key]);

                    if(isset($resultTestSecond['message'])) {

                        if ($key == 5) {

                            // Play Period Not Found
                            $this->assertContains($resultTestSecond['message'], "Play Period Not Found");

                        } else {
                           
                           // verify period id is invalid
                            $this->assertContains($resultTestSecond['message'], "Id must is a numeric and greater than zero"); 
                        }
                    }
                }
            }

            // verify period return is valid
            $resultTestValid = $this->playperiod->getById($dataExpected->playerId, $dataExpected->id);

            if(is_object($resultTestValid) && isset($resultTestValid->id) ) {
                
                $dataExpectedValid = $this->playperiod->with('status')->get($resultTestValid->id);
                
                foreach ( (array)$resultTestValid as $key => $value ) {

                    if ( array_key_exists($key, $dataExpectedValid) ) {

                         //verify period return is valid
                        $this->assertEquals($resultTestValid->$key, $dataExpectedValid->$key);
                    }
                }

                $currentTime  = strtotime( 'now' );
                $endTime      =  strtotime( str_replace( '-', '/', $resultTestValid->endDate ) );
                $startTime    = strtotime( str_replace( '-', '/', $resultTestValid->startDate ) );
                $timeInPeriod = 4*60*60;
                $dayInTime    = date('Y-m-d 20:00:00', $startTime);
                $dayInTimeInt = strtotime(str_replace( '-', '/', $dayInTime));

                if ( $startTime >= $dayInTimeInt ) {

                    // verify end time return must be equal Y-m-d 23:59:59
                    $this->assertEquals($resultTestValid->endDate, date('m-d-Y 23:59:59'));

                } else {

                    $timeExcepted = $startTime + $timeInPeriod;

                    // verify end time return must be equal startTime + timeInperiod
                    $this->assertEquals($resultTestValid->endDate, date('m-d-Y 23:59:59') );
                }

                if ( (int)$endTime > (int)$currentTime ) {

                    // verify status return must be not equal Expired
                    $this->assertTrue($resultTestValid->status != "Expired");

                    if( (int)$resultTestValid->gamesCredit > 0 ) {

                        // verify status return is valid
                        $this->assertTrue( $resultTestValid->status != "NotReady" );
                      
                        // verify status return must be not equal Invalid
                        $this->assertTrue( $resultTestValid->status != "Invalid" );
                    } 

                    if( (int)$resultTestValid->gamesCredit == 0 ) {

                        // verify status return must be not equal Invalid
                        $this->assertTrue($resultTestValid->status === "NotReady");
                    }

                } else {

                    if((int)$resultTestValid->gamesCredit == (int)$resultTestValid->gamesPlayed)

                        //verify status return must be equal Completed
                        $this->assertTrue($resultTestValid->status === "Completed");

                    if((int)$resultTestValid->gamesCredit > (int)$resultTestValid->gamesPlayed)

                        //verify status return must be equal Expired
                        $this->assertTrue( $resultTestValid->status === "Expired" );

                    if((int)$resultTestValid->gamesCredit < (int)$resultTestValid->gamesPlayed)
                    
                        //verify status return must be equal PlayedGreaterThanCredit
                        $this->assertTrue($resultTestValid->status === "PlayedGreaterThanCredit"); 
                }
            }

        } else {
            
            $this->assertTrue(FALSE);
        }
    }

    function testGetByPlayerId() {

        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);

        $this->player->setToken( $login['token'] );

        $dataExpected = $this->CI->db->select('PlayPeriod.id, playerId')
            ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
            ->where('isDeleted', 0)
            ->order_by('id' ,'DESC')
            ->get('Players')->row();

        if ( is_object($dataExpected) && isset($dataExpected->playerId) ) {

            // verify period id is invalid
            $idPlayerInvalid = array(null, 'abc', '', -123, 0, 100000);
            $offset          = 0;
            $limit           = 10;
            foreach ( $idPlayerInvalid as $key => $value ) {

                $resultTest = $this->playperiod->getByPlayerId($idPlayerInvalid[$key],$limit, $offset, false );
                
                if( isset($resultTest['message']) ) {

                    if($key == 5) {

                        // verify playerId id isn't exist result must be null
                        $this->assertContains($resultTest['message'], "Player Not Found");

                    } else {

                        //verify playerId id is invalid
                        $this->assertContains($resultTest['message'], "Id must be a numeric and greater than zero");
                    }
                }
            }

            // verify Play Period return is valid 
            $dataTest = $this->playperiod->getByPlayerId($dataExpected->playerId, $limit, $offset, false);

            if ( sizeof($dataTest) > 0 ) {

                if( isset($dataTest['message']) ) {

                    // To verify players doesn't exist
                    $this->assertContains($dataTest['message'], "Player Id doesn't exist");
                }

                // verify offset return must be equal offset input before
                if( isset($dataTest['offset']) ) {

                    $this->assertEquals((int)$dataTest['offset'], $offset );
                } 

                // verify limit return must be equal limit input before
                if( isset($dataTest['limit']) ){

                    $this->assertEquals((int)$dataTest['limit'], $limit );
                } 

                // count period return must be count PlayPeriod return
                if( isset($dataTest['count']) && sizeof($dataTest['playPeriods']) > 0 ) {

                    $this->assertEquals( (int)$dataTest['count'], sizeof($dataTest['playPeriods']) );
                }

                if( isset($dataTest['playPeriods']) && sizeof($dataTest['playPeriods']) > 0 ) {

                    // verify playerId return on period must be equal playerId before
                    foreach ($dataTest['playPeriods'] as $key => $value) {

                        $playerIdTest = (array)$dataTest['playPeriods'][$key];

                        foreach ( $playerIdTest as $key => $value) {
                        
                            if ($key == 'playerId') {

                                // To verify playerId return on period must be equal playerId before
                                $this->assertEquals( $playerIdTest[$key], (int)$dataExpected->playerId );
                            }
                        }
                    }    
                }
            }

        } else {

            $this->assertTrue(FALSE); 
        }
    }   
}