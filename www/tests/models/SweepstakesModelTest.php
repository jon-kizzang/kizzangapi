<?php

/**
 * @group Model
 */
class SweepstakesModelTest extends CIUnit_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model('sweepstake');

        $this->sweepstake = $this->CI->sweepstake;
        $this->player = $this->CI->player;
        // disable send SQS when run unit test
        $this->player->executeTesting = TRUE;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    function testAddNewSweepstake() {

        $nameInvalid      = array(NULL, '');
        $desInvalid       = array(NULL, '');
        $startTimeInvalid = array(null, 'abc', '', -123, 0, '00-00-0000 00:00:00', '2014-02-02 10:00:00', '01:02:25 2014/08/02');
        $endTimeInvalid   = array(null, 'abc', '', -123, 0,'00-00-0000 00:00:00', '2014-02-02 10:00:00', '01:02:25 2014/08/02');
        $name             = "Name of sweepstake";
        $des              = "Description of sweepstake";
        $endTime          = '10-09-2014 12:00:00';
        $startTime        = '09-09-2014 10:00:00';

        // verify add new sweepstake is invalid
        foreach ($nameInvalid as $key => $value) {

           if(array_key_exists($key, $desInvalid)) {

                $data = array(
                    'name'            => $nameInvalid[$key],
                    'description'     => $desInvalid[$key],
                    'startDate'       => $startTime,
                    'endDate'         => $endTime,
                    'imageURL'        => 'http://abc.img',
                    'titleImageURL'  => 'abc',
                    'sweepstakeType' => 'closed',
                    'maxWinners'      => 20,
                    'color'           => '#FFFF',
                    'maxEntrants'     => 3,
                    'displayValue'    => '4',
                    'ratioTicket'     => 6,
                    'taxValue'        => 10,
                    );

                $resultTest = $this->sweepstake->add( $data );

                if ( is_array($resultTest) && isset($resultTest['message']) ) {

                    // verify name is invalid
                    if ( isset($resultTest['message'][0]) ){

                        $this->assertContains( $resultTest['message'][0], "The name field is required.", "To verify name is invalid");
                    }

                    // verify description is invalid
                    if ( isset($resultTest['message'][1]) ) {

                        $this->assertContains( $resultTest['message'][1], "The description field is required.", "To verify name is invalid");
                    }
                }
           }
        }

        foreach ( $startTimeInvalid as $key => $value ) {

            if ( array_key_exists( $key, $endTimeInvalid ) ) {
                $dataSeconds      = array(
                    'name'            => $name,
                    'description'     => $des,
                    'startDate'       => $startTimeInvalid[$key],
                    'endDate'         => $endTimeInvalid[$key],
                    'imageURL'        => 'http://abc.img',
                    'titleImageURL'   => 'abc',
                    'sweepstakeType'  => 'closed',
                    'maxWinners'      => 20,
                    'color'           => '#FFFF',
                    'maxEntrants'     => 3,
                    'displayValue'    => '4',
                    'ratioTicket'     => 6,
                    'taxValue'        => 10,
                    );

                $resultTestSecond = $this->sweepstake->add( $dataSeconds );

                if ( is_array($resultTestSecond) && isset($resultTestSecond['message']) ) {

                    if ( empty( $startTimeInvalid[$key] ) && empty( $endTimeInvalid[$key] )) {

                       // verify startTime must be is required
                       if ( isset( $resultTestSecond['message'][0] ) ){

                            $this->assertContains($resultTestSecond['message'][0], "The startDate field is required.", "To verify startTime must be is required");
                       }

                       // verify end time must be is required
                       if ( isset( $resultTestSecond['message'][1] ) ){

                            $this->assertContains($resultTestSecond['message'][1], "The endDate field is required.", "To verify end time must be is required");
                        }

                    } else {

                        // verify startTime must be correct format TimeStamp
                       if ( isset( $resultTestSecond['message'][0] ) ) {

                            $this->assertContains($resultTestSecond['message'][0], "The startDate field must contain a valid date (m-d-Y H:i:s)", "verify startTime must be correct format TimeStamp");
                       }

                        // verify startTime must be correct format TimeStamp
                       if ( isset( $resultTestSecond['message'][1] ) ){

                            $this->assertContains($resultTestSecond['message'][1], "The endDate field must contain a valid date (m-d-Y H:i:s)", "To verify startTime must be correct format TimeStamp");
                       }

                    }
                }
            }
        }

        $dataSeconds['startDate'] = $endTime;
        $dataSeconds['endDate']   = $startTime;
        $testResultThird          = $this->sweepstake->add($dataSeconds);

        // verify endTime must be greater than startTime
        if (is_array($testResultThird) && isset($testResultThird['message'])) {

            $this->assertContains($testResultThird['message'], "End Date must greater than Start Date", "To verify endTime must be greater than startTime");
        }

        // verify add new sweepstake is valid
        $dataValid = array(
            'name'            => $name,
            'description'     => $des,
            'startDate'       => $startTime,
            'endDate'         => $endTime,
            'imageURL'        => 'http://abc.img',
            'titleImageURL'   => 'abc',
            'sweepstakeType'  => 'closed',
            'maxWinners'      => 20,
            'color'           => '#FFFF',
            'maxEntrants'     => 3,
            'displayValue'    => '4',
            'ratioTicket'     => 6,
            'taxValue'        => 10,
            );

        $testResultThird = $this->sweepstake->add($dataValid);

        if ( is_object($testResultThird) && isset($testResultThird->id) ) {

            $dataExpected = $this->sweepstake->get_by(array('id' => $testResultThird->id, 'isDeleted' => 0));

            foreach ((array)$testResultThird as $key => $value) {
                if ( array_key_exists($key, (array)$dataExpected) ) {

                    if ($key != "statusCode") {

                        // verify add new sweepstakes is valid
                        $this->assertEquals($testResultThird->$key, $dataExpected->$key, "To verify add new sweepstake is valid", "verify ". $key . "return must be equal " . $key. " had added on database" );
                    }
                }
            }
        }
    }

    function testGetByIdSweepStake() {

        $dataExit  = $this->sweepstake->order_by('id', 'DESC')->get_by('isDeleted', 0);
        $idNotExit = isset($dataExit->id) ? ((int)$dataExit->id + 1) : 1 ;
        $count     = $this->sweepstake->count_by('isDeleted', 0);

        // verify get by id return invalid
        $idIvalid  = array(NULL, '', 0, 'abc', -123, $idNotExit);

        foreach ($idIvalid as $key => $value) {

            $testResullt = $this->sweepstake->getById($idIvalid[$key]);

            if ( is_array($testResullt) && isset($testResullt['message']) ) {

                // verify id is not exit on database
                if ($key == 5) {

                    $this->assertContains($testResullt['message'], "Sweepstake Not Found", "verify id is not exit on database");

                } else {

                    // verify id is invalid
                    $this->assertContains($testResullt['message'], "Id must is a numeric and greater than zero", "verify id is invalid");
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

                                $this->assertEquals($testResultSecond->$key, $dataExpected->$key, "To Verify " . $key . "return must be equal " . $key . "get on database");
                            }

                        }
                    }
                }
            }
        } else {

            $this->assertTrue( FALSE,"Can't verify get sweepstake by Id is valid. Please make sure Sweepstake doesn't empty or all have deleted. Try run testing add new sweepstake before test Get sweepstake by Id" );
        }
    }

    function testGetAllSweepStakes() {

        $countExpected = $this->sweepstake->count_by( 'isDeleted', 0 );

        if ( (int)$countExpected > 0 ) {

            // Todo with offset and limit with -123
            $offsetInvalid = array(null,'','abc', 0);
            $limitInvalid  = array(null,'','abc', 0);

            foreach ($limitInvalid as $key => $value) {

                if ( array_key_exists($key, $offsetInvalid) ){

                    $resultTest = $this->sweepstake->getAll($offsetInvalid[$key], $limitInvalid[$key]);

                    if ( is_array($resultTest) && isset($resultTest['message']) ) {

                        // To verify get all list return is invalid
                        $this->assertContains($resultTest['message'], "Sweepstakes Not Found", "Verify offset and limit is invalid");
                    }
                }
            }

            // To verify Gel all list sweepstake return is valid follow offset and limit
            $offset = 0;
            $limit  = 4;

            $resultTestSecond = $this->sweepstake->getAll($offset, $limit);

            if (is_array($resultTestSecond) && isset($resultTestSecond['sweepstakes'])) {

                // verify limit return must be equal limit input before
                if ( isset($resultTestSecond['limit']) ) {

                    $this->assertEquals($resultTestSecond['limit'], $limit, "verify limit return must be equal limit input before");
                }

                // verify offset return must be equal offset input before
                if ( isset($resultTestSecond['offset']) ) {

                    $this->assertEquals($resultTestSecond['offset'], $offset, "To verify offset return must be equal offset input before");
                }

                // verify count return must be equal count when get form data

                if( isset($resultTestSecond['count']) ) {

                    $this->assertEquals($resultTestSecond['count'], $countExpected, "To verify count return must be equal count get from data");
                }

                if( $limit < $countExpected)  {

                    // verify count list return must be equal value limit input
                    $this->assertEquals(sizeof($resultTestSecond['sweepstakes']), $limit, "To verify count return must be equal count when get form data");

                } else {

                    // verify count list return must be equal count of get on database
                    $this->assertContains(sizeof($resultTestSecond['sweepstakes']), $countExpected, "verify count list return must be equal count of get on database");
                }

            }

            // To verify Gel all list sweepstake return is valid follow offset and limit
            $offset = 1;
            $limit = 2;
            $resultTestThird = $this->sweepstake->getAll($limit, $offset);
            $dataExpectedOffset = $this->sweepstake->limit(3)->get_many_by('isDeleted', 0);

            if ( is_array($resultTestThird) && isset($resultTestThird['sweepstakes']) ) {

                // verify list return must be follow offset is 2
                if( sizeof($dataExpectedOffset) >= 3 && sizeof($resultTestThird['sweepstakes']) >= 3 ) {

                    $this->assertEquals($dataExpectedOffset[$offset]->id, $resultTestThird['sweepstakes'][0]->id, "verify id return must be equal id when get on database");
                }

                // verify limit return must be equal limit input before
                if ( isset($resultTestThird['limit']) ) {

                    $this->assertEquals($resultTestThird['limit'], $limit, "verify limit return must be equal limit input before");
                }

                // verify offset return must be equal offset input before
                if ( isset($resultTestThird['offset']) ) {

                    $this->assertEquals($resultTestThird['offset'], $offset, "verify offset return must be equal offset input before");
                }

                // verify count return must be equal count when get form data
                if( isset($resultTestThird['count']) ) {

                    $this->assertEquals($resultTestThird['count'], $countExpected, "verify offset return must be equal offset input before");
                }

                if( $limit < $countExpected)  {

                    // verify count list return must be equal value limit input
                    $this->assertEquals(sizeof($resultTestThird['sweepstakes']), $limit, "verify count return must be equal count when get form data");

                } else {

                    // verify count list return must be equal count of get on database
                    $this->assertEquals(sizeof($resultTestThird['sweepstakes']), $countExpected, "verify count list return must be equal count of get on database");
                }
            }

        } else {

            $this->assertTrue( FALSE, "Can't verify Sweepstales is valid. Please make sure Sweepstales doesn't empty. Try run testing add new Sweepstales.");
        }
    }

    function testUpdateSweepstake() {

        $nameInvalid      = array(NULL, '');
        $desInvalid       = array(NULL, '');
        $startTimeInvalid = array(null, 'abc', '', -123, 0, '00-00-0000 00:00:00', '2014-02-02 10:00:00', '01:02:25 2014/08/02');
        $endTimeInvalid   = array(null, 'abc', '', -123, 0,'00-00-0000 00:00:00', '2014-02-02 10:00:00', '01:02:25 2014/08/02');
        $name             = "Name of sweepstake";
        $des              = "Description of sweepstake";
        $endTime          = '10-09-2014 12:00:00';
        $startTime        = '09-09-2014 10:00:00';

        $countExpected = $this->sweepstake->count_by('isDeleted', 0);

        if ($countExpected > 0) {

            $dataExit   = $this->sweepstake->order_by('id', 'DESC')->get_by('isDeleted', 0);
            $idExit     = $dataExit->id;
            $idNotExit  = isset($dataExit->id) ? ((int)$dataExit->id + 1) : 1 ;
            $idInvalid  = array(NULL, '', 0, 'abc', -123, $idNotExit);
            $dataUpdate = array('name'=>$name, 'description' => $des, 'startDate' => $startTime, 'endDate' => $endTime);

            // verify update sweepstake is invalid
            foreach ($idInvalid as $key => $value) {

                $testResult = $this->sweepstake->edit($value, $dataUpdate);

                if( is_array($testResult) && isset($testResult['message'])) {

                    // verify id sweepstake is invalid
                    if( $key == 5 ) {

                        $this->assertContains($testResult['message'], "Sweepstake Not Found" , "verify id sweepstake isn't exist");
                    } else {

                       $this->assertContains($testResult['message'], "Id must is a numeric and greater than zero", "verify id sweepstake is invalid");
                    }
                }
            }

            // verify add new sweepstake is invalid
            foreach ($nameInvalid as $key => $value) {

               if( array_key_exists($key, $desInvalid) ) {

                    $data = array(
                        'name'       => $nameInvalid[$key],
                        'description'=> $desInvalid[$key],
                        'startDate'  => $startTime,
                        'endDate'    => $endTime
                        );

                    $resultTestSecond = $this->sweepstake->edit( $idExit, $data );

                    if ( is_array($resultTestSecond) && isset($resultTestSecond['message']) ) {

                        // verify name is invalid
                        if ( isset($resultTestSecond['message'][0]) ){

                            $this->assertContains( $resultTestSecond['message'][0], "The name field is required.", "Verify name is invalid");
                        }

                        // verify description is invalid
                        if ( isset($resultTestSecond['message'][1]) ){

                            $this->assertContains( $resultTestSecond['message'][1], "The description field is required.", "Verify name is invalid");
                        }
                    }
               }
            }

            foreach ( $startTimeInvalid as $key => $value ) {

                if ( array_key_exists( $key, $endTimeInvalid ) ) {

                    $dataSeconds = array(
                        'name'        => $name,
                        'description' => $des,
                        'startDate'   => $startTimeInvalid[$key],
                        'endDate'     => $endTimeInvalid[$key]
                        );

                    $resultTestThird = $this->sweepstake->edit( $idExit, $dataSeconds );

                    if ( is_array($resultTestThird) && isset($resultTestThird['message']) ) {

                        if ( empty( $startTimeInvalid[$key] ) && empty( $endTimeInvalid[$key] )) {

                           // verify startTime must be is required
                           if ( isset( $resultTestThird['message'][0] ) ){

                                $this->assertContains($resultTestThird['message'][0], "The startDate field is required.", "verify startTime must be is required");
                           }

                           // verify end time must be is required
                           if ( isset( $resultTestThird['message'][1] ) ) {

                                $this->assertContains($resultTestThird['message'][1], "The endDate field is required.", "verify end time must be is required");
                           }

                        } else {

                            // verify startTime must be correct format TimeStamp
                           if ( isset( $resultTestThird['message'][0] ) ) {

                                $this->assertContains($resultTestThird['message'][0], "The startDate field must contain a valid date (m-d-Y H:i:s)", "verify startTime must be correct format TimeStamp");
                           }

                            // verify startTime must be correct format TimeStamp
                           if ( isset( $resultTestThird['message'][1] ) )
                                $this->assertContains($resultTestThird['message'][1], "The endDate field must contain a valid date (m-d-Y H:i:s)", "verify startTime must be correct format TimeStamp");

                        }
                    }
                }
            }

            $dataSeconds['startDate'] = $endTime;
            $dataSeconds['endDate']   = $startTime;
            $testResultFourth         = $this->sweepstake->edit( $idExit, $dataSeconds);

            // verify endTime must be greater than startTime
            if (is_array($testResultFourth) && isset($testResultFourth['message'])) {

                $this->assertContains($testResultFourth['message'], "End Date ({$startTime}) must greater than Start Date ({$endTime})", "verify endTime must be greater than startTime");
            }

            // verify update sweepstake is valid
            $dataName        = array('name' => "Nhan Doan");

            $testResultFifth = $this->sweepstake->edit($idExit, $dataName);

            if(is_object($testResultFifth) && isset($testResultFifth->id)) {

                // Verify name return must be name input before
                $this->assertEquals($testResultFifth->name, $dataName['name'], "Verify name return must be name input before");
            }

            $dataDes         = array('description' => "Nhan Doan");

            $testResultSixth = $this->sweepstake->edit($idExit, $dataDes);

            if( is_object($testResultSixth) && isset($testResultSixth->id) ) {

                // Verify description return must be name input before
                $this->assertEquals($testResultSixth->description, $dataDes['description'], "Verify description return must be name input before");
            }

            $dataTimeExpected = array('startDate'=>'10-09-2014 12:00:00', 'endDate' => '12-09-2014 12:00:00');
            $testResultTime   = $this->sweepstake->edit($idExit, $dataTimeExpected);

            if( is_object($testResultTime) && isset($testResultTime->id) ) {

                // Verify startDate return must be name input before
                $this->assertEquals($testResultTime->startDate, $dataTimeExpected['startDate'], "Verify startDate return must be startDate input before");

                // Verify endDate return must be name input before
                $this->assertEquals($testResultTime->endDate, $dataTimeExpected['endDate'], "Verify endDate return must be endDate input before");
            }

        } else {

            $this->assertTrue( FALSE, "Can't verify update a sweepstakes. Please make sure Sweepstakes doesn't empty or exist but all status isDeleted = 1. Try run testing update Sweepstakes before test Get All list sweepstakes." );
        }
    }

}