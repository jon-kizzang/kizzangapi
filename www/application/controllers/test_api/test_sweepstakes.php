<?php

/**
* 
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);

class test_sweepstakes extends CI_Controller
{
  
    function __construct()
    {
        parent::__construct();

        $this->load->model( 'sweepstake' );
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

    public function testAddNewSweepstake () {
        
        $nameInvalid = array(NULL, '');
        $desInvalid = array(NULL, '');
        $startTimeInvalid = array(null, 'abc', '', -123, 0, '00-00-0000 00:00:00', '2014-02-02 10:00:00', '01:02:25 2014/08/02');
        $endTimeInvalid = array(null, 'abc', '', -123, 0,'00-00-0000 00:00:00', '2014-02-02 10:00:00', '01:02:25 2014/08/02');
        $name = "Name of sweepstake";
        $des = "Description of sweepstake";
        $endTime = '10-09-2014 12:00:00';
        $startTime = '09-09-2014 10:00:00';

        // verify add new sweepstake is invalid
        foreach ($nameInvalid as $key => $value) {
           
           if(array_key_exists($key, $desInvalid)) {
                $data = array(
                    'name' => $nameInvalid[$key],
                    'description' => $desInvalid[$key],
                    'startDate' => $startTime,
                    'endDate'=> $endTime
                    );

                $resultTest = $this->sweepstake->add( $data );

                if ( is_array($resultTest) && isset($resultTest['errors']) ) {

                    // verify name is invalid
                    if ( isset($resultTest['errors'][0]) )
                        $this->unit->run( $resultTest['errors'][0], "The name field is required.", "To verify add new sweepstake is invalid", "Verify name is invalid");

                    // verify description is invalid
                    if ( isset($resultTest['errors'][1]) )
                        $this->unit->run( $resultTest['errors'][1], "The description field is required.", "To verify add new sweepstake is invalid", "Verify name is invalid");
                }
           }             
        }

        foreach ( $startTimeInvalid as $key => $value ) {
            if ( array_key_exists( $key, $endTimeInvalid ) ) {
                $dataSeconds = array(
                    'name' => $name,
                    'description' => $des,
                    'startDate' => $startTimeInvalid[$key],
                    'endDate'=> $endTimeInvalid[$key]
                    );
                $resultTestSecond = $this->sweepstake->add( $dataSeconds );
                if ( is_array($resultTestSecond) && isset($resultTestSecond['errors']) ) {

                    if ( empty( $startTimeInvalid[$key] ) && empty( $endTimeInvalid[$key] )) {

                       // verify startTime must be is required
                       if ( isset( $resultTestSecond['errors'][0] ) )
                            $this->unit->run($resultTestSecond['errors'][0], "The startDate field is required.", "To verify add new sweepstake is invalid", "verify startTime must be is required");
                       // verify end time must be is required
                       if ( isset( $resultTestSecond['errors'][1] ) )
                            $this->unit->run($resultTestSecond['errors'][1], "The endDate field is required.", "To verify add new sweepstake is invalid", "verify end time must be is required");     

                    } else {

                        // verify startTime must be correct format TimeStamp
                       if ( isset( $resultTestSecond['errors'][0] ) )
                            $this->unit->run($resultTestSecond['errors'][0], "The startDate field must contain a valid date (m-d-Y H:i:s)", "To verify add new sweepstake is invalid", "verify startTime must be correct format TimeStamp");

                        // verify startTime must be correct format TimeStamp
                       if ( isset( $resultTestSecond['errors'][1] ) )
                            $this->unit->run($resultTestSecond['errors'][1], "The endDate field must contain a valid date (m-d-Y H:i:s)", "To verify add new sweepstake is invalid", "verify startTime must be correct format TimeStamp");

                    }
                }
            }
        }

        $dataSeconds['startDate'] = $endTime;
        $dataSeconds['endDate'] = $startTime;
        $testResultThird = $this->sweepstake->add($dataSeconds);

        // verify endTime must be greater than startTime
        if (is_array($testResultThird) && isset($testResultThird['errors'])) {

            $this->unit->run($testResultThird['errors'], "End Date must greater than Start Date", "To verify add new sweepstake is invalid", "verify endTime must be greater than startTime");
        } 

        // verify add new sweepstake is valid
        $dataValid = array(
            'name' => $name,
            'description' => $des,
            'startDate' => $startTime,
            'endDate' => $endTime
            );
        $testResultThird = $this->sweepstake->add($dataValid);

        if ( is_object($testResultThird) && isset($testResultThird->id) ) {

            $dataExpected = $this->sweepstake->get_by(array('id' => $testResultThird->id, 'isDeleted' => 0));

            foreach ((array)$testResultThird as $key => $value) {
                if ( array_key_exists($key, (array)$dataExpected) ) {
                    
                    if ($key != "statusCode") {

                        // verify add new sweepstakes is valid
                        $this->unit->run($testResultThird->$key, $dataExpected->$key, "To verify add new sweepstake is valid", "verify ". $key . "return must be equal " . $key. " had added on database" );
                    }
                }
            }
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    /**
     * testGetByID 
     *
     * Testing all case return with fucntion get sweepstake by Id
     * 
     */
    public function testGetByID () {

        $dataExit = $this->sweepstake->order_by('id', 'DESC')->get_by('isDeleted', 0);
        $idNotExit = isset($dataExit->id) ? ((int)$dataExit->id + 1) : 1 ;

        $count = $this->sweepstake->count_by('isDeleted', 0);
        
        // verify get by id return invalid
        $idIvalid = array(NULL, '', 0, 'abc', -123, $idNotExit);

        foreach ($idIvalid as $key => $value) {

            $testResullt = $this->sweepstake->getById($idIvalid[$key]);
            if ( is_array($testResullt) && isset($testResullt['errors']) ) {

                // verify id is not exit on database
                if ($key == 5) {
                    
                    $this->unit->run($testResullt['errors'], "Sweepstake Not Found", "To verify get sweepstakes by Id is invalid", "verify id is not exit on database");
                } else {

                    // verify id is invalid
                    $this->unit->run($testResullt['errors'], "Id must is a numeric and greater than zero", "To verify get sweepstakes by Id is invalid", "verify id is invalid");
                }
            }
        }

        if ( $count > 0 ) {

            $dataExpected = $this->sweepstake->order_by('id', 'DESC')->get_by(array('isDeleted'=> 0));

            if ( is_object($dataExpected) && isset($dataExpected->id) ) {

                $testResultSecond = $this->sweepstake->getById($dataExpected->id);

                if (is_object($testResultSecond) && isset($testResultSecond->id)) {

                    foreach ((array)$testResultSecond as $key => $value) {
                        if ( array_key_exists( $key, (array)$dataExpected ) ) {

                            // To verify get sweepstake by Id return is valid
                            if ( $key != "statusCode" ) {
                                $this->unit->run($testResultSecond->$key, $dataExpected->$key, "To verify get sweepstake by Id return is valid", "Verify " . $key . "return must be equal " . $key . "get on database");
                            }

                        }
                    }
                }
            }
        } else {

            echo "<h4 style='color: red;'>Can't verify get sweepstake by Id is valid. Please make sure Sweepstake doesn't empty or all have deleted. Try run testing add new sweepstake before test Get sweepstake by Id.<h4>"; 
        }
        
        echo $this->unit->report();  
        echo $this->returnResult($this->unit->result());
    }

    /**
     * [testGetAll description]
     * @return [type] [description]
     */
    public function testGetAll() {

        $countExpected = $this->sweepstake->count_by( 'isDeleted', 0 );
        if ((int)$countExpected > 0 ) {

            // Todo with offset and limit with -123
            $offsetInvalid = array(null,'','abc', 0);
            $limitInvalid = array(null,'','abc', 0);
            foreach ($limitInvalid as $key => $value) {

                if ( array_key_exists($key, $offsetInvalid) ){

                    $resultTest = $this->sweepstake->getAll($offsetInvalid[$key], $limitInvalid[$key]);
                    if ( is_array($resultTest) && isset($resultTest['errors']) ) {

                        // To verify get all list return is invalid
                        $this->unit->run($resultTest['errors'], "Sweepstakes Not Found", "To verify get all list return is invalid", "Verify offset and limit is invalid");
                    }
                }
            }

            // To verify Gel all list sweepstake return is valid follow offset and limit
            $offset = 0;
            $limit = 4;

            $resultTestSecond = $this->sweepstake->getAll($offset, $limit);
            if (is_array($resultTestSecond) && isset($resultTestSecond['sweepstakes'])) {

                // verify limit return must be equal limit input before
                if ( isset($resultTestSecond['limit']) ) {
                    $this->unit->run($resultTestSecond['limit'], $limit, "To verify list return is valid", "verify limit return must be equal limit input before");
                }

                // verify offset return must be equal offset input before
                if ( isset($resultTestSecond['offset']) ) {
                    $this->unit->run($resultTestSecond['offset'], $offset, "To verify list return is valid", "verify offset return must be equal offset input before");
                }
                // verify count return must be equal count when get form data

                if( isset($resultTestSecond['count']) ) {

                    $this->unit->run($resultTestSecond['count'], $countExpected, "To verify list return is valid", "verify count return must be equal count get from data");
                }

                if( $limit < $countExpected)  {
                    // verify count list return must be equal value limit input
                    $this->unit->run(sizeof($resultTestSecond['sweepstakes']), $limit, "To verify get list sweepstake is valid", "verify count return must be equal count when get form data"); 
                } else {
                    // verify count list return must be equal count of get on database
                    $this->unit->run(sizeof($resultTestSecond['sweepstakes']), $countExpected, "To verify get list sweepstake is valid", "verify count list return must be equal count of get on database");
                }
            }

            // To verify Gel all list sweepstake return is valid follow offset and limit
            $offset = 2;
            $limit = 4;
            $resultTestThird = $this->sweepstake->getAll($offset, $limit);
            $dataExpectedOffset = $this->sweepstake->limit(3)->get_many_by('isDeleted', 0);

            if (is_array($resultTestThird) && isset($resultTestThird['sweepstakes'])) {

                // verify list return must be follow offset is 2
                if(  sizeof($dataExpectedOffset) >= 3 && sizeof($resultTestThird['sweepstakes']) >= 3) {
                
                    $this->unit->run($dataExpectedOffset[$offset]->id, $resultTestThird['sweepstakes'][0]->id, "verify all list return is valid follow offset", "verify id return must be equal id when get on database");
                } 

                // verify limit return must be equal limit input before
                if ( isset($resultTestThird['limit']) ) {
                    $this->unit->run($resultTestThird['limit'], $limit, "To verify list return is valid", "verify limit return must be equal limit input before");
                }

                // verify offset return must be equal offset input before
                if ( isset($resultTestThird['offset']) ) {
                    $this->unit->run($resultTestThird['offset'], $offset, "To verify list return is valid", "verify offset return must be equal offset input before");
                }

                // verify count return must be equal count when get form data
                if( isset($resultTestThird['count']) ) {
                    $this->unit->run($resultTestThird['count'], $countExpected, "To verify list return is valid", "verify offset return must be equal offset input before");
                }

                if( $limit < $countExpected)  {

                    // verify count list return must be equal value limit input
                    $this->unit->run(sizeof($resultTestThird['sweepstakes']), $limit, "To verify get list sweepstake is valid", "verify count return must be equal count when get form data"); 
                } else {

                    // verify count list return must be equal count of get on database
                    $this->unit->run(sizeof($resultTestThird['sweepstakes']), $countExpected, "To verify get list sweepstake is valid", "verify count list return must be equal count of get on database");
                }
            }

        } else {
            echo "<h4 style='color: red;'>Can't verify Sweepstales is valid. Please make sure Sweepstales doesn't empty. Try run testing add new Sweepstales.<h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());

    }

    /**
     * testDeleteSweepstake 
     * 
     * - Testing function detroy sweepstake
     */
    public function testDeleteSweepstake() {
        // verify destroy sweepstake is invalid
        // verify id is invalid 
        $countExpected = $this->sweepstake->count_by('isDeleted', 0);

        // verify destroy sweepstake is valid
        if ($countExpected > 0) {
            $dataExit = $this->sweepstake->order_by('id', 'DESC')->get_by('isDeleted', 0);
            $count = $this->sweepstake->count_by('isDeleted', 0);
            
            // verify get by id return invalid
            $idIvalid = array(NULL, '', 0, 'abc', -123);

            foreach ($idIvalid as $key => $value) {

                $testResullt = $this->sweepstake->destroy($idIvalid[$key]);

                if ( is_array($testResullt) && isset($testResullt['errors']) ) 

                    // verify id is invalid
                    $this->unit->run($testResullt['errors'], "Id must is a numeric and greater than zero", "To verify get sweepstakes by Id is invalid", "verify id is invalid");
                
            }
            
            if ( is_object($dataExit) && isset($dataExit->id) ) {

                $testResultSecond = $this->sweepstake->destroy($dataExit->id);
                $dataExpected = $this->sweepstake->with_deleted()->get($dataExit->id);
                // verify isDelelte return must be is 1
                $this->unit->run($dataExpected->isDeleted, '1', "To verify delete is valid", "verify isDelelte return must be is 1");
                if(sizeof($testResultSecond) > 0) {
                        // content
                        if (isset($testResultSecond[0])) {

                            //verify content had deleted!
                            $this->unit->run($testResultSecond[0], 'is_null', "To verify delete is valid", "verify content had deleted!");
                        } 

                        if ( isset($testResultSecond[1]) )

                            //verify status return must be is 204
                            $this->unit->run($testResultSecond[1], 204, "To verify delete is valid", "verify status return must be is 204");
                }
            }   
        } else {

            echo "<h4 style='color: red;'>Can't verify get all list sweepstakes is valid. Please make sure Sweepstakes doesn't empty or exist but all status isDeleted = 1. Try run testing add new Sweepstakes before test Get All list sweepstakes.<h4>"; 
        }
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());

    }

    /**
     * testUpdateSweepstake 
     *
     * Testing function update Sweepstake 
     */
    public function testUpdateSweepstake () {

        $nameInvalid = array(NULL, '');
        $desInvalid = array(NULL, '');
        $startTimeInvalid = array(null, 'abc', '', -123, 0, '00-00-0000 00:00:00', '2014-02-02 10:00:00', '01:02:25 2014/08/02');
        $endTimeInvalid = array(null, 'abc', '', -123, 0,'00-00-0000 00:00:00', '2014-02-02 10:00:00', '01:02:25 2014/08/02');
        $name = "Name of sweepstake";
        $des = "Description of sweepstake";
        $endTime = '10-09-2014 12:00:00';
        $startTime = '09-09-2014 10:00:00';

        $countExpected = $this->sweepstake->count_by('isDeleted', 0);

        if ($countExpected > 0) {

            $dataExit = $this->sweepstake->order_by('id', 'DESC')->get_by('isDeleted', 0);
            $idExit = $dataExit->id;
            $idNotExit = isset($dataExit->id) ? ((int)$dataExit->id + 1) : 1 ;
            $idInvalid = array(NULL, '', 0, 'abc', -123, $idNotExit);
            $dataUpdate = array('name'=>$name, 'description' => $des, 'startDate' => $startTime, 'endDate' => $endTime);    
            // verify update sweepstake is invalid
            foreach ($idInvalid as $key => $value) {

                $testResult = $this->sweepstake->edit($value, $dataUpdate);
                
                if( is_array($testResult) && isset($testResult['errors'])) {

                    // verify id sweepstake is invalid
                    if( $key == 5 ) {
                        $this->unit->run($testResult['errors'], "Sweepstake Not Found" , "To verify update sweepstake is invalid", "verify id sweepstake isn't exist");
                    } else {

                       $this->unit->run($testResult['errors'], "Id must is a numeric and greater than zero", "To verify update sweepstake is invalid", "verify id sweepstake is invalid"); 
                    }
                }   
            }

            // verify add new sweepstake is invalid
            foreach ($nameInvalid as $key => $value) {
               
               if(array_key_exists($key, $desInvalid)) {
                    $data = array(
                        'name' => $nameInvalid[$key],
                        'description' => $desInvalid[$key],
                        'startDate' => $startTime,
                        'endDate'=> $endTime
                        );

                    $resultTestSecond = $this->sweepstake->edit( $idExit, $data );

                    if ( is_array($resultTestSecond) && isset($resultTestSecond['errors']) ) {

                        // verify name is invalid
                        if ( isset($resultTestSecond['errors'][0]) )
                            $this->unit->run( $resultTestSecond['errors'][0], "The name field is required.", "To verify update sweepstake is invalid", "Verify name is invalid");

                        // verify description is invalid
                        if ( isset($resultTestSecond['errors'][1]) )
                            $this->unit->run( $resultTestSecond['errors'][1], "The description field is required.", "To verify update sweepstake is invalid", "Verify name is invalid");
                    }
               }             
            }

            foreach ( $startTimeInvalid as $key => $value ) {
                if ( array_key_exists( $key, $endTimeInvalid ) ) {
                    $dataSeconds = array(
                        'name' => $name,
                        'description' => $des,
                        'startDate' => $startTimeInvalid[$key],
                        'endDate'=> $endTimeInvalid[$key]
                        );
                    $resultTestThird = $this->sweepstake->edit( $idExit, $dataSeconds );
                    if ( is_array($resultTestThird) && isset($resultTestThird['errors']) ) {

                        if ( empty( $startTimeInvalid[$key] ) && empty( $endTimeInvalid[$key] )) {

                           // verify startTime must be is required
                           if ( isset( $resultTestThird['errors'][0] ) )
                                $this->unit->run($resultTestThird['errors'][0], "The startDate field is required.", "To verify update sweepstake is invalid", "verify startTime must be is required");

                           // verify end time must be is required
                           if ( isset( $resultTestThird['errors'][1] ) )
                                $this->unit->run($resultTestThird['errors'][1], "The endDate field is required.", "To verify update sweepstake is invalid", "verify end time must be is required");     

                        } else {

                            // verify startTime must be correct format TimeStamp
                           if ( isset( $resultTestThird['errors'][0] ) )
                                $this->unit->run($resultTestThird['errors'][0], "The startDate field must contain a valid date (m-d-Y H:i:s)", "To verify update sweepstake is invalid", "verify startTime must be correct format TimeStamp");

                            // verify startTime must be correct format TimeStamp
                           if ( isset( $resultTestThird['errors'][1] ) )
                                $this->unit->run($resultTestThird['errors'][1], "The endDate field must contain a valid date (m-d-Y H:i:s)", "To verify update sweepstake is invalid", "verify startTime must be correct format TimeStamp");

                        }
                    }
                }
            }

            $dataSeconds['startDate'] = $endTime;
            $dataSeconds['endDate'] = $startTime;
            $testResultFourth = $this->sweepstake->edit( $idExit, $dataSeconds);

            // verify endTime must be greater than startTime
            if (is_array($testResultFourth) && isset($testResultFourth['errors'])) {

                $this->unit->run($testResultFourth['errors'], "End Date ({$startTime}) must greater than Start Date ({$endTime})", "To verify update sweepstake is invalid", "verify endTime must be greater than startTime");
            } 

            // verify update sweepstake is valid
            $dataName = array('name' => "Nhan Doan");

            $testResultFifth = $this->sweepstake->edit($idExit, $dataName);

            if(is_object($testResultFifth) && isset($testResultFifth->id)) {

                // Verify name return must be name input before
                $this->unit->run($testResultFifth->name, $dataName['name'], "To verify update sweepstake is valid", "Verify name return must be name input before");
            } 
            
            $dataDes = array('description' => "Nhan Doan");

            $testResultFifth = $this->sweepstake->edit($idExit, $dataDes);

            if(is_object($testResultFifth) && isset($testResultFifth->id)) {

                // Verify description return must be name input before
                $this->unit->run($testResultFifth->description, $dataDes['description'], "To verify update sweepstake is valid", "Verify description return must be name input before");
            }

            $dataTimeExpected = array('startDate'=>'10-09-2014 12:00:00', 'endDate' => '12-09-2014 12:00:00');
            $testResultTime = $this->sweepstake->edit($idExit, $dataTimeExpected);
            if(is_object($testResultTime) && isset($testResultTime->id)) {

                // Verify startDate return must be name input before
                $this->unit->run($testResultFifth->startDate, $dataTimeExpected['startDate'], "To verify update sweepstake is valid", "Verify startDate return must be startDate input before");

                // Verify endDate return must be name input before
                $this->unit->run($testResultFifth->endDate, $dataTimeExpected['endDate'], "To verify update sweepstake is valid", "Verify endDate return must be endDate input before");
            }
            
        } else {
           echo "<h4 style='color: red;'>Can't verify update a sweepstakes. Please make sure Sweepstakes doesn't empty or exist but all status isDeleted = 1. Try run testing update Sweepstakes before test Get All list sweepstakes.<h4>";  
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

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