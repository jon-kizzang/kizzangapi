<?php
/**
* Building testing unit Period
*
* - Testing get all period
* - Testing get period by ID
* - Testing create new period
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);

class test_playperiods extends CI_Controller
{
  
    function __construct()
        {
        parent::__construct();

        //loading library unit test
        $this->load->library('unit_test');

        // loading database test
        $this->load->database('test', TRUE);

        //To enable strict mode 
        $this->unit->use_strict(TRUE);

        // Disable database debugging so we can test all units without stopping
        // at the first SQL error
        $this->db->db_debug = FALSE;
    }

    protected function getPlayer($emailConfirmed, $FBID) {

        if ($emailConfirmed === 0) {
            $data = array(
                'email' => substr( md5( uniqid( date( 'Y-m-d H:i:s' ), true ) ), 0, 30 )."login0@gmail.com",
                'password' => "123456",
                'gender' => '1',
                'firstName' => "User",
                'lastName' => "Test",
                'dob' => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
                'phoneHome' => '0123456789',
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
   /**
   * testGetByID 
   *
   * Function testing get playPeriod by ID
   */
    public function testGetByID () {
        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);

        $this->player->setToken( $login['token'] );
        $dataExpected = $this->db->select('PlayPeriod.id, playerId')
        ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
        ->where('isDeleted', 0)
        ->order_by('id' ,'DESC')
        ->get('Players')->row();
        if ( is_object($dataExpected) && isset($dataExpected->id) ) {

            // verify period id is invalid
            $idPlayerInvalid = array(null, 'abc', '', -123, 0, 100000);
            $id_invalid = array(null, 'abc', '', -123, 0, 100000);

            foreach ($id_invalid as $key => $value) {
              $resultTestOne = $this->playperiod->getById($idPlayerInvalid[$key], $id_invalid[$key]);

                if(isset($resultTestOne['errors'])) {

                    if($key == 5) {

                        // verify playerId id isn't exist result must be null
                        $this->unit->run($resultTestOne['errors'], "Play Period Not Found", "verify playerId id isn't exist result must be null", "In case playerId is not exist");
                    } else {

                        //verify playerId id is invalid
                        $this->unit->run($resultTestOne['errors'], "Id must be a numeric and greater than zero", "verify playerId id is invalid", "In case period id invalid");
                    }
                }
            }

            if (isset($dataExpected->playerId)) {

                foreach ($id_invalid as $key => $value) {

                    $resultTestSecond = $this->playperiod->getById($dataExpected->playerId, $id_invalid[$key]);

                    if(isset($resultTestSecond['errors'])) {
                        if ($key == 5) {

                            // Play Period Not Found
                            $this->unit->run($resultTestSecond['errors'], "Play Period Not Found", "verify period id is invalid", "Verify Play Period Not Found");

                        } else {
                           
                           // verify period id is invalid
                            $this->unit->run($resultTestSecond['errors'], "Id must is a numeric and greater than zero", "verify period id is invalid", "In case period id invalid"); 
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
                        $this->unit->run($resultTestValid->$key, $dataExpectedValid->$key,"verify period return is valid", $key. " must be equal " . $key . " from database of Playperiod.");
                    }
                }

                $currentTime = strtotime( 'now' );
                $endTime =  strtotime( str_replace( '-', '/', $resultTestValid->endDate ) );
                $startTime = strtotime( str_replace( '-', '/', $resultTestValid->startDate ) );
                $timeInPeriod = 4*60*60;
                $dayInTime = date('Y-m-d 20:00:00', $startTime);
                $dayInTimeInt = strtotime(str_replace( '-', '/', $dayInTime));

                if ( $startTime >= $dayInTimeInt ) {

                    // verify end time return must be equal Y-m-d 23:59:59
                    $this->unit->run($resultTestValid->endDate, date('m-d-Y 23:59:59'), 'verify end time return must be equal Y-m-d 23:59:59', 'verify endTime return is valid');
                } else {

                    $timeExcepted = $startTime + $timeInPeriod;

                    // verify end time return must be equal startTime + timeInperiod
                    $this->unit->run($resultTestValid->endDate, date('m-d-Y 23:59:59') , "verify end time return must be equal startTime + timeInperiod", "verify end time return is valid");
                }
                if ( (int)$endTime > (int)$currentTime ) {

                    // verify status return must be not equal Expired
                    $this->unit->run($resultTestValid->status != "Expired", 'is_true', "verify status return must be not equal Expired", 'verify status return is valid');

                    if((int)$resultTestValid->gamesCredit > 0) {
                        // verify status return is valid
                        $this->unit->run($resultTestValid->status != "NotReady", 'is_true', 'verify status return must be not equal NotReady', "verify status return is valid");
                      
                        // verify status return must be not equal Invalid
                        $this->unit->run($resultTestValid->status != "Invalid", 'is_true', 'verify status return must be not equal Invalid', "verify status return is valid");
                    } 

                    if((int)$resultTestValid->gamesCredit == 0) {

                        // verify status return must be not equal Invalid
                        $this->unit->run($resultTestValid->status === "NotReady", 'is_true', 'verify status return must be not equal NotReady', "verify status return is valid");
                    }

                } else {

                    if((int)$resultTestValid->gamesCredit == (int)$resultTestValid->gamesPlayed)

                        //verify status return must be equal Completed
                        $this->unit->run($resultTestValid->status === "Completed", 'is_true', "verify status return must be equal Completed", "verify status return is valid");

                    if((int)$resultTestValid->gamesCredit > (int)$resultTestValid->gamesPlayed)

                        //verify status return must be equal Expired
                        $this->unit->run($resultTestValid->status === "Expired", 'is_true', "verify status return must be equal Expired", "verify status return is valid");

                    if((int)$resultTestValid->gamesCredit < (int)$resultTestValid->gamesPlayed)
                    
                        //verify status return must be equal PlayedGreaterThanCredit
                        $this->unit->run($resultTestValid->status === "PlayedGreaterThanCredit", 'is_true', "verify status return must be equal PlayedGreaterThanCredit", "verify status return is valid"); 
                }
            }

        } else {
            echo "<h4 style='color: red;'>Can't verify Playperiod is valid. Please make sure Poried doesn't empty or Player had been Deleted. Try run testing add new PlayPeriods.<h4>";
        }
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
        

  }

    /**
    * testGetByPlayerId 
    *
    * 
    */
    public function testGetByPlayerId () {
        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);

        $this->player->setToken( $login['token'] );
         $dataExpected = $this->db->select('PlayPeriod.id, playerId')
        ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
        ->where('isDeleted', 0)
        ->order_by('id' ,'DESC')
        ->get('Players')->row();
        if (is_object($dataExpected) && isset($dataExpected->playerId)) {

            // verify period id is invalid
            $idPlayerInvalid = array(null, 'abc', '', -123, 0, 100000);
            $offset = 0;
            $limit = 10;
            foreach ($idPlayerInvalid as $key => $value) {
                $resultTest = $this->playperiod->getByPlayerId($idPlayerInvalid[$key],$limit, $offset, false );
                if(isset($resultTest['errors'])) {

                    if($key == 5) {

                        // verify playerId id isn't exist result must be null
                        $this->unit->run($resultTest['errors'], "Player Not Found", "verify playerId id isn't exist result must be null", "In case playerId is not exist");
                    } else {

                        //verify playerId id is invalid
                        $this->unit->run($resultTest['errors'], "Id must be a numeric and greater than zero", "verify playerId id is invalid", "In case period id invalid");
                    }
                }
            }

            // verify Play Period return is valid 
            $dataTest = $this->playperiod->getByPlayerId($dataExpected->playerId, $limit, $offset, false);

            if ( sizeof($dataTest) > 0) {
                if(isset($dataTest['errors']) ) {
                  $this->unit->run($dataTest['errors'], "Player Id doesn't exist", "To verify player is deleted or doesn't exit", "verify players doesn't exist");
                }
                // verify offset return must be equal offset input before
                if(isset($dataTest['offset'])) 
                    $this->unit->run((int)$dataTest['offset'], $offset, "verify offset return must be equal offset input before", "verify Playperiod return is valid" );
                // verify limit return must be equal limit input before
                if(isset($dataTest['limit'])) 
                    $this->unit->run((int)$dataTest['limit'], $limit, "verify limit return must be equal limit input before", "verify Playperiod return is valid" );

                // count period return must be count PlayPeriod return
                if(isset($dataTest['count']) && sizeof($dataTest['playPeriods']) > 0) 
                    $this->unit->run((int)$dataTest['count'], sizeof($dataTest['playPeriods']), "verify limit return must be equal limit input before", "verify Playperiod return is valid" );

                if(isset($dataTest['playPeriods']) && sizeof($dataTest['playPeriods']) > 0) {

                    // verify playerId return on period must be equal playerId before
                    foreach ($dataTest['playPeriods'] as $key => $value) {

                        $playerIdTest = (array)$dataTest['playPeriods'][$key];
                        foreach ( $playerIdTest as $key => $value) {
                            if ($key == 'playerId') {

                                // To verify playerId return on period must be equal playerId before
                                $this->unit->run($playerIdTest[$key], (int)$dataExpected->playerId, 'verify playerId return on period must be equal playerId before', 'verify Playperiod return is valid');
                            }
                        }
                    }    
                }
            }

            // To verify current is true 
            // $startTimeCurrent = "2014-04-22 00:00:00";
            // $endTimeCurrent = "2014-04-24 00:00:00";
            // $dataExpectedCurrent = $this->createCurrent($startTimeCurrent, $endTimeCurrent, false, false, false);
            // if(is_object($dataExpectedCurrent) && isset($dataExpectedCurrent->id)) {

            //     $dataTestCurrentime = $this->playperiod->getByPlayerId($dataExpectedCurrent->playerId, $limit, $offset, true);
            //     var_dump($dataTestCurrentime); die;
            //     $startTimeStamp = date('Y-m-d H:i:s', strtotime(str_replace( '-', '/', $dataTestCurrentime->startDate)));
            //     $endTimeStamp = date('Y-m-d H:i:s', strtotime(str_replace( '-', '/', $dataTestCurrentime->endDate)));
            //     // verify startTime and endTime return must be equal time input previous
            //     $this->unit->run($startTimeCurrent, $startTimeStamp, "verify startTime and endTime return must be equal time input previous");
            //     $this->unit->run($endTimeCurrent, $endTimeStamp, "verify startTime and endTime return must be equal time input previous");

            //     $id = $dataTestCurrentime->id;
            //     $playerID = $dataTestCurrentime->playerId;
            //     $endTimeCurrentSecond = "2014-04-22 11:00:00";
            //     $dataExpectedCurrentSecond = $this->createCurrent($startTimeCurrent, $endTimeCurrentSecond, true, $playerID, $id);
            //     if( is_object($dataExpectedCurrentSecond) && isset($dataExpectedCurrentSecond->id)){

            //         $dataTestCurrentimeSecond = $this->playperiod->getByPlayerId($dataExpectedCurrentSecond->playerId, $limit, $offset, true);

            //         if(is_object($dataTestCurrentimeSecond) && isset($dataTestCurrentimeSecond->id)) {
            //             $startTimeStamp = date('Y-m-d H:i:s', strtotime(str_replace( '-', '/', $dataTestCurrentimeSecond->startDate)));
            //             $endTimeStamp = date('Y-m-d H:i:s', strtotime(str_replace( '-', '/', $dataTestCurrentimeSecond->endDate)));
            //             $this->unit->run($startTimeStamp, $startTimeCurrent, "verify startTime and endTime return must be equal time input previous");
            //             $this->unit->run($endTimeCurrentSecond, $endTimeStamp, "verify startTime and endTime return must be equal time input previous");
            //         }
            //     }

            // }
            
        } else {

           echo "<h4 style='color: red;'>Can't verify Playperiod is valid. Please make sure Poried doesn't empty. Try run testing add new PlayPeriods.<h4>"; 
        }
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    }

  /**
   * testGetAll 
   *
   * function testing retrieve all PlayPeriods for a given player
   */
    public function testGetAll () {
        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);

        $this->player->setToken( $login['token'] );
        $dataExpected = $this->db->select('PlayPeriod.id, playerId')
        ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
        ->where('isDeleted', 0)
        ->order_by('id' ,'DESC')
        ->get('Players')->row();
        if ( is_object($dataExpected) && isset($dataExpected->id)) {
            
            // In case is invalid
            // ===========================
            // verify that player id, start time, end time is invalid
            // verify player Id return invalid 
            $idExist = $this->player->limit(1)->order_by('id', 'DESC')->get_all();
            $idNotExits = isset($idExist[0]->id) ? ($idExist[0]->id + 1) : 1;
            $id_invalid = array(null, 'abc', '', -123, 0);
            $startTime = array(null, 'abc', '', -123, 0);
            $endTime = array(null, 'abc', '', -123, 0);
            $offset = 0;
            $limit = 10;
            foreach ($id_invalid as $key => $value) {
                if (array_key_exists($key, $startTime) && array_key_exists($key, $endTime)) {

                    $periodTest = $this->playperiod->getAll($id_invalid[$key], $limit, $offset, $startTime[$key], $endTime[$key]);

                    if ( isset($periodTest['errors']) ) 

                        //in case player id is empty
                        $this->unit->run($periodTest['errors'], 'Id must be a numeric and greater than zero', 'To verify that player id is invalid', 'To verify in case player id is empty');
                       
                }
            }

            foreach ($startTime as $key => $value) {
                if (array_key_exists($key, $endTime)) {

                    $periodTestTime = $this->playperiod->getAll($dataExpected->playerId, $limit, $offset, $startTime[$key], $endTime[$key]);
                    if ( isset($periodTestTime['errors']) ) 

                        if (isset($periodTestTime['errors'][0]))

                            // in case period start Date is empty
                            $this->unit->run($periodTestTime['errors'][0], 'Start Date must contain a valid date (m-d-Y H:i:s)','verify that start time is invalid' , 'in case period start Date is empty');
                        if (isset($periodTestTime['errors'][1]))

                            // in case end time is empty
                            $this->unit->run($periodTestTime['errors'][1], 'End Date must contain a valid date (m-d-Y H:i:s)', 'verify that end time is invalid', 'in case end time is empty');
                }
            }     

            // verify start time must be leeser than endTime
            $startTime = "09-10-2014 00:00:00";
            $endTime = "09-10-2014 00:00:00";
            $periodTestFourth = $this->playperiod->getAll($dataExpected->playerId, $limit, $offset, $startTime, $endTime);
            if(isset($periodTestFourth['errors'])) {

                // in case period start Date is empty
                $this->unit->run($periodTestFourth['errors'], 'End Date must greater than Start Date',"verify start time must be leeser than endTime" , 'in case start Date greater than end date');
            }

            $idPeriod = $dataExpected->id;
            $idPlayer = $dataExpected->playerId;
            $dataExpectedPeriod = $this->playperiod->getById($idPlayer, $idPeriod);
            $startTime = $dataExpectedPeriod->startDate;
            $endTime = $dataExpectedPeriod->endDate;
            $startTimeStamp = date('Y-m-d H:i:s', strtotime(str_replace( '-', '/', $startTime)));
            $endTimeStamp = date('Y-m-d H:i:s', strtotime(str_replace( '-', '/', $endTime)));
            $resultExpected = $this->playperiod->get_many_by(array('playerId' => $idPlayer, 'startDate >= ' => $startTimeStamp, 'endDate <= ' => $endTimeStamp ));

            $countExpected = sizeof($resultExpected);
            $resultTest = $this->playperiod->getAll($idPlayer, $limit, $offset, $startTime, $endTime);
            if ( is_array($resultTest) && isset($resultTest['playPeriod']) ) {

                // verify offset return must be equal previous offset input
                $this->unit->run($resultTest['offset'], $offset, "To verify that list period return is valid", "verify offset return must be equal previous offset input") ;
                // verify limit return must be equal previous limit input 
                 $this->unit->run($resultTest['limit'], $limit, "To verify that list period return is valid", "verify limit return must be equal previous limit input") ;
                // verify count return must be equal previous count input 
                 $this->unit->run($resultTest['count'], $countExpected, "To verify that list period return is valid", "verify count return must be equal previous count input") ;
                
            } else {
                echo "<h4 style='color: red;'>Can't verify get all list period is case valid. Try run testing add new PlayPeriods before test Get All list period.<h4>"; 
            }

        } else {
            echo "<h4 style='color: red;'>Can't verify get all list period is valid. Please make sure Poried doesn't empty. Try run testing add new PlayPeriods before test Get All list period.<h4>"; 
        }
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
        
    }

    /**
    * testAddNewperiod 
    *
    * Function testing create new period
    */
    public function testAddNewPeriod () {

        $query = $this->db->query('TRUNCATE PlayPeriod;');
        $getIdNotExit = $this->player->limit(1)->order_by('id', 'DESC')->get_all();
        
        // verify player Id return invalid 
        $dataExpected = $this->getPlayer(0, null);
        $idNotExits = isset($getIdNotExit[0]->id) ? ($getIdNotExit[0]->id + 1) : 1;

        $playerIdInvalid = array('', null, 'abc', 0, -123, $idNotExits );

        foreach ($playerIdInvalid as $key => $value) {

            $resultTest = $this->playperiod->add($playerIdInvalid[$key] );
            if ($key != 5) {
                if (isset($resultTest['errors'])) {

                        //To verify id player return invalid
                        $this->unit->run($resultTest['errors'], $this->db->_error_message(), "verify id player return invalid", "To verify id player return invalid");
                }
            }
        }
        // verify playperiod return is valid with status is NotReady
        if (is_object($dataExpected) > 0 && isset($dataExpected->id)) {
            $testResult = $this->playperiod->add($dataExpected->id);

            if (is_object($testResult) && isset($testResult->id)) {
                // verify endtime return must be equal 23:59:59 of end day
                $this->unit->run($testResult->endDate, date('m-d-Y 23:59:59'), "verify playperiod return is valid", "verify endtime return must be equal 23:59:59 of end day");

                // verify status return must be equal is NotReady       
                $this->unit->run($testResult->status, "NotReady", "verify playperiod return is valid", "verify status return must be equal is NotReady");

            } elseif (is_array($testResult) && isset($testResult['errors'])) {
                // verify add new playperiod is exit on date
                $this->unit->run($testResult['errors'], "PlayPeriod exists in ". date('Y-m-d'));
            }    
        } else {
            echo "<h4 style='color: red;'>Can't verify add a new Playperiod. Because doen't exit players.<h4>";
        }

        $dataExpectedEmailConfirm = $this->getPlayer(1, null);
        // verify playperiod return is valid with status is NotReady
        if ( is_object($dataExpectedEmailConfirm) && isset($dataExpectedEmailConfirm->id) ) {

            $testResultSecond = $this->playperiod->add($dataExpectedEmailConfirm->id);
           
            if (is_object($testResultSecond) && isset($testResultSecond->id)) {

                // verify endtime return must be equal 23:59:59 of end day
                $this->unit->run($testResultSecond->endDate, date('m-d-Y 23:59:59'), "verify playperiod return is valid", "verify endtime return must be equal 23:59:59 of end day");

                // verify status return must be equal is NotReady       
                $this->unit->run($testResultSecond->status, "Ready", "verify playperiod return is valid", "verify status return must be equal is NotReady");

            } elseif (is_array($testResultSecond) && isset($testResultSecond['errors'])) {

                // verify add new playperiod is exit on date
                $this->unit->run($testResultSecond['errors'], "PlayPeriod exists in ". date('Y-m-d'));
            }    
        } else {
            echo "<h4 style='color: red;'>Can't verify add a new Playperiod. Because doen't exit players.<h4>";
        }
        // verify playperiod return is valid with status is Ready
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    }

    /**
    * testUpdateperiod
    *
    * Function testing update period
    */
    public function testUpdatePeriod () {
        
        $dataExpectedEmailConfirm = $this->db->select('PlayPeriod.id, playerId')
        ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
        ->where(array('isDeleted' => 0, 'emailVerified' => 0 ))
        ->order_by('id' ,'DESC')
        ->get('Players')->row();
        $gamesPlayed = 2;

        if (is_object($dataExpectedEmailConfirm)) {

            // verify status return must be is status NotReady
            $testNotReady = $this->playperiod->edit($dataExpectedEmailConfirm->playerId, $dataExpectedEmailConfirm->id, $gamesPlayed);

            if (is_object($testNotReady) && isset($testNotReady->id)) {

                $this->unit->run($testNotReady->status, "NotReady", "Verify update is valid", "verify status return must be is status NotReady");
            } 
        }
        $player = $this->getPlayer(1,null);
        $dataLogin = array('email' => $player->accountEmail, 'password' => 123456, 'deviceId'=>1);
        $login = $this->player->login($dataLogin);
        $this->player->setToken($login['token']);
        $dataExpected = $this->db->select('PlayPeriod.id, playerId')
        ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
        ->where(array('isDeleted' => 0, 'emailVerified' => 1 ))
        ->order_by('id' ,'DESC')
        ->get('Players')->row();
        // To verify Id player of period return is invalid
        $idPlayerInvalid = array(NULL, 'abc', '', -123, 0, 100000);
        $id_invalid = array(NULL, 'abc', '', -123, 0, 100000);
        $gamesPlayedInvalid = array(NULL, 'abc', '', -123);
        $gamesPlayed = 2;
        foreach ($id_invalid as $key => $value) {
           $resultTest = $this->playperiod->edit($idPlayerInvalid[$key], $id_invalid[$key], $gamesPlayed );
            if(isset($resultTest['errors'])) {
                if($key == 5) {

                    // verify playerId id isn't exist result must be null
                    $this->unit->run($resultTest['errors'], "Player Not Found", "verify playerId id isn't exist result must be null", "In case playerId is not exist");
                } else {

                    //verify playerId id is invalid
                    $this->unit->run($resultTest['errors'], "Id must be a numeric and greater than zero", "verify playerId id is invalid", "In case period id invalid");
                }
            }
        }
        // To verify period id is invalid
        if (isset($dataExpected->playerId)) {
            foreach ($id_invalid as $key => $value) {
                $resultTest = $this->playperiod->edit($dataExpected->playerId, $id_invalid[$key], $gamesPlayed );

                if(isset($resultTest['errors'])) {
                    if($key == 5) {

                        // verify playerId id isn't exist result must be null
                        $this->unit->run($resultTest['errors'], "Play Period Not Found", "verify playerId id isn't exist result must be null", "In case playerId is not exist");
                    } elseif(empty($id_invalid[$key])) {

                        //verify playerId id is invalid
                        $this->unit->run($resultTest['errors'][0], "The Id field is required.", "verify playerId id is invalid", "In case period id invalid");
                    } else {

                       //verify playerId id is invalid
                        $this->unit->run($resultTest['errors'][0], "The Id field must contain a number greater than 0.", "verify playerId id is invalid", "In case period id invalid"); 
                    }
                }
            }   
        } 

        // To verify period gameplayed is invalid
        if (isset($dataExpected->playerId) && isset($dataExpected->id)) {
            foreach ($gamesPlayedInvalid as $key => $value) {
                $resultTest = $this->playperiod->edit($dataExpected->playerId, $dataExpected->id, $gamesPlayedInvalid );
                if(isset($resultTest['errors'][0])) {
                    // verify playerId id isn't exist result must be null
                    $this->unit->run($resultTest['errors'][0], "The Games Playerd field is required.", "verify playerId id isn't exist result must be null", "In case playerId is not exist");
                }
            }  
        }

        if ( isset($dataExpected->playerId) && isset($dataExpected->id) ) {

            $dataExpectedUpdate = $this->playperiod->getById($dataExpected->playerId,$dataExpected->id);
            $creditExpected = $dataExpectedUpdate->gamesCredit;
            $gamesPlayed = rand(0, $creditExpected);
            $endTimeExpected = $dataExpectedUpdate->endDate;
            $endTimeInt = strtotime( str_replace( '-', '/', $endTimeExpected));
            $statusExpected = $dataExpectedUpdate->status;
            $currentExpected = strtotime( date( 'm/d/Y H:s:i' ));
            $testResult = $this->playperiod->edit($dataExpected->playerId,$dataExpected->id, $gamesPlayed );
            if ( isset($testResult->id) ) {

                $this->unit->run($testResult->id, (int)$dataExpected->id, "To verify id return must be equal id input", "To verify update Play Period is valid");
                $this->unit->run($testResult->playerId, (int)$dataExpected->playerId, "To verify id return must be equal id input", "To verify update Play Period is valid");

                if($currentExpected >= $endTimeInt ) {
                    if ($gameplayed < $creditExpected)
                        // To verify update status period return is valid
                        $this->unit->run($testResult->status, "Expired", "To verify update status period return is Expired", "To verify update status period return is valid");
                    if ($gameplayed = $creditExpected) {
                        $this->unit->run($testResult->status, "Completed", "To verify update status period return is Completed", "To verify update status period return is valid");
                    }
                } 

                if($currentExpected < $endTimeInt) {

                    if ($gamesPlayed == $creditExpected) {

                         // To verify update status period return is valid
                        $this->unit->run($testResult->status, "Completed", "To verify update status period return is Completed", "To verify update status period return is valid"); 
                    } elseif ($gamesPlayed < $creditExpected) {
                        
                        // To verify update status period return is valid
                        $this->unit->run($testResult->status, "Ready", "To verify update status period return is Ready", "To verify update status period return is valid"); 
                    }
                }
            }
        }
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    }

    /**
     * testUpdateStatusPeriod 
     * 
     * Testing update status of playperiod
     */
    public function testUpdateStatusPeriod () {
        $player = $this->getPlayer(1,null);
        $dataLogin = array('email' => $player->accountEmail, 'password' => 123456, 'deviceId'=>1);
        $login = $this->player->login($dataLogin);
        $this->player->setToken($login['token']);

        $dataExpected = $this->db->select('PlayPeriod.id, playerId')
        ->join('PlayPeriod', 'PlayPeriod.playerId=Players.id')
        ->where('playerId',$player->id)
        ->order_by('id' ,'RANDOM')
        ->get('Players')->row();

        // To verify Id player of period return is invalid
        $idPlayerInvalid = array(NULL, 'abc', '', -123, 0, 100000);
        $id_invalid = array(NULL, 'abc', '', -123, 0, 100000);
        $statusInvalid = array('abc', -123, 6);
        $status = 2;
        
        foreach ($id_invalid as $key => $value) {
          $resultTest = $this->playperiod->editStatus($idPlayerInvalid[$key], $id_invalid[$key], $status );
            if(isset($resultTest['errors'])) {
                if($key == 5) {

                    // verify playerId id isn't exist result must be null
                    $this->unit->run($resultTest['errors'], "Player Not Found", "verify playerId id isn't exist result must be null", "In case playerId is not exist");
                } else {

                    //verify playerId id is invalid
                    $this->unit->run($resultTest['errors'], "Id must is a numeric and greater than zero", "verify playerId id is invalid", "In case period id invalid");
                }
            }
        }

        // To verify period id is invalid
        if (isset($dataExpected->playerId)) {
            foreach ($id_invalid as $key => $value) {
                $resultTestSecond = $this->playperiod->editStatus($dataExpected->playerId, $id_invalid[$key], $status );

                if(isset($resultTestSecond['errors'])) {
                    if($key == 5) {

                        // verify playerId id isn't exist result must be null
                        $this->unit->run($resultTestSecond['errors'], "Play Period Not Found", "verify playerId id isn't exist result must be null", "In case playerId is not exist");
                    } elseif(empty($id_invalid[$key])) {

                        //verify playerId id is invalid
                        $this->unit->run($resultTestSecond['errors'][0], "The Id field is required.", "verify playerId id is invalid", "In case period id invalid");
                    } else {

                       //verify playerId id is invalid
                        $this->unit->run($resultTestSecond['errors'][0], "The Id field must contain a number greater than 0.", "verify playerId id is invalid", "In case period id invalid"); 
                    }
                }
            }   
        } 

        // To verify period status is invalid
        if (isset($dataExpected->playerId) && isset($dataExpected->id)) {
            foreach ($statusInvalid as $key => $value) {
                $resultTestThird = $this->playperiod->editStatus($dataExpected->playerId, $dataExpected->id, $value );
                if(isset($resultTestThird['errors'])) {

                    if(is_string($value) || $value < 0) {

                        //To verify The Status field must contain a number greater than -1.
                        $this->unit->run($resultTestThird['errors'][0], "The Status field must contain a number greater than -1.", "To verify The Status field must contain a number greater than -1." ,"In case period id invalid");
                    } else {

                       //verify Status must is numeric and in set (0,1,2,3,4,5)
                        $this->unit->run($resultTestThird['errors'], "Status must is numeric and in set (0,1,2,3,4)", "To verify Status must is numeric and in set (0,1,2,3,4,5)", "In case period id invalid"); 
                    }
                }
            }  
        }
        if ( isset($dataExpected->playerId) && isset($dataExpected->id) ) {
            $dataExpectedUpdate = $this->playperiod->getById($dataExpected->playerId,$dataExpected->id);
            $timeNow = strtotime( date( 'm/d/Y H:s:i' ));
            $endDate = strtotime( str_replace('-', '/', $dataExpectedUpdate->endDate ));
            $player = $this->player->getById($dataExpected->playerId);
            $status = 2;
            $testResultFourd = $this->playperiod->editStatus($dataExpected->playerId,$dataExpected->id, $status );

            if ( isset($testResultFourd->id) ) {

                if ($player->emailVerified == 0) {

                    // Verify status return must be is NotReady
                    $this->unit->run($testResultFourd->status, "NotReady", "Verify update  status is valid", "Verify status return must be is NotReady");
                    
                } elseif ( $timeNow > $endDate) {
                    
                    // Verify status return must be is Expired
                    $this->unit->run($testResultFourd->status, "Expired", "Verify update  status is valid", "Verify status return must be is Expired");

                } elseif ($dataExpectedUpdate->gamesCredit == $dataExpectedUpdate->gamesPlayed) {
                    
                    // Verify status return must be is Completed
                    $this->unit->run($testResultFourd->status, "Completed", "Verify update  status is valid", "Verify status return must be is Completed");
                } elseif ( $status === 2) {
                    // Verify status return must be is Completed
                    $this->unit->run($testResultFourd->status, "Ready", "Verify update  status is valid", "Verify status return must be is Ready");
                }

            }

        }
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());    
    }

    /**
     * returnResult
     *  
     * return description in case pass or failed on test case
     */
    function returnResult($results) {
        $passed = [];
        $failed = [];
        foreach($results as $value) {
            if($value['Result'] === "Passed") {
                array_push($passed, $value['Result']);
            }

            if($value['Result'] === "Failed") {
                array_push($failed, $value['Result']);
            }
        }
        return "<h1> Tests: ". sizeof($results). ", Passed: " .sizeof($passed). ", Failed:".sizeof($failed)."</h1>";
    }
}