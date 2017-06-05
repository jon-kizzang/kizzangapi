<?php

class DailyShowDownOUGameTest extends CIUnit_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model(array('parlayoucard', 'parlayoucategory','parlayouconfig', 'parlayoupick', 'parlayouplayercard', 'parlayouresult', 'parlayouschedule', 'parlayouteam'));

        $this->parlayoucard       = $this->CI->parlayoucard;
        $this->parlayouteam       = $this->CI->parlayouteam;
        $this->parlayouschedule   = $this->CI->parlayouschedule;
        $this->parlayoucategory   = $this->CI->parlayoucategory;
        $this->parlayouconfig     = $this->CI->parlayouconfig;
        $this->parlayoupick       = $this->CI->parlayoupick;
        $this->parlayouresult     = $this->CI->parlayouresult;
        $this->parlayouplayercard = $this->CI->parlayouplayercard;

        $this->parlayouplayercard->executeTesting = TRUE;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    //==============================
    // Test dailyshow under or over categories    //
    //============================
    function testCategoryAdd() {

        $data = array(
            'name' => 'Big Game 21' . md5(date('Y-m-d H:i:s').rand(1,100)),
            'sort' => 2
        );

        // To verify add dailyshow under or over Category is invalid
        //=========================================
        // To verify data is empty
        $dataInvalid     = '';
        $testResultFirst = $this->parlayoucategory->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty');
        }

        // To verify data is empty
        $dataInvalid['name'] = '';
        $testResultSecond    = $this->parlayoucategory->add( $dataInvalid );

        if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

            $this->assertContains( $testResultSecond['message'][0], 'The name field is required.', 'To verify data is empty');
        }

        // To verify add category is exist
        $categories = $this->parlayoucategory->get_by(array('id !=' => 0));

        if ( !empty( $categories ) ) {

            $dataInvalid['name'] = $categories->name;
            $dataInvalid['sort'] = 2;
            $testResultThird     = $this->parlayoucategory->add( $dataInvalid );

            if ( is_array($testResultThird) && isset($testResultThird['message']) ) {

                $this->assertContains( $testResultThird['message'], 'Cannot save a duplicate Parlay Over Under Category with name - ' . $dataInvalid['name'] , 'To verify add category is exist');
            }
        }

        // To verify add dailyshow under or over Category is valid
        //=======================================
        $nameExpected     = $data['name'];
        $testResultFourth = $this->parlayoucategory->add( $data );

        if ( is_object($testResultFourth) ) {

            // To verify name returm must be equal name dailyshow under or over Category input
            $this->assertEquals($testResultFourth->name, $nameExpected, 'To verify name returm must be equal name dailyshow under or over Category input');

        } else {

            $this->assertTrue( FALSE , "Can't verify add category dailyshow under or over is case valid");
        }

    }

    function testCategoryUpdate() {

        $category = $this->parlayoucategory->order_by('id', 'DESC')->get_by(array('id !='=> 0));

        if( !empty($category) ) {

            $id           = $category->id;
            $data['name'] = $category->name . "Update";
            $data['sort'] = $category->sort;

            // To verify update dailyshow under or over Category is invalid
            //=========================================
            // To verify data is empty
            $dataEmpty       = '';
            $testResultFirst = $this->parlayoucategory->edit( $id , $dataEmpty );

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

                // To verify data is empty
                $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty' );
            }

            // To verify id is invalid
            $idInvalid = array('', NULL, 'abc', 0, -1);

            foreach ($idInvalid as $key => $value) {

                $testResultSecond = $this->parlayoucategory->edit( $value, $data );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                   $this->assertContains( $testResultSecond['message'], 'Id must be a numeric and greater than zero', 'To verify id is invalid' );
                }
            }

            // To verify update dailyshow under or over Category is valid
            //=========================================
            $testResultThird = $this->parlayoucategory->edit( $id, $data );

            if ( is_object($testResultThird) ) {

                // To verify name returm must be equal name dailyshow under or over Category input
                $this->assertEquals( $testResultThird->name, substr( $data['name'], 0, 50 ), 'To verify name returm must be equal name dailyshow under or over Category input');

            } else {

                $this->assertTrue( FALSE, "Can't verify update category dailyshow under or over is case valid");
            }

        } else {

            $this->assertTrue( FASLE, "Can't verify update category dailyshow under or over is case valid. Please testing add dailyshow under or over Category before testing update.");
        }

    }

    //=======================
    // Test dailyshow under or over teams   //
    //======================
    function testTeamAdd() {

        $data = array(
            'sportCategoryId' => 1,
            'name'           => "Add Team". md5(date('Y-m-d H:i:s').rand(1,100)) ,
        );

        // To verify add dailyshow under or over Team is invalid
        //=========================================
        // To verify data is empty
        $dataInvalid     = '';
        $testResultFirst = $this->parlayouteam->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty');
        }

        // To verify Team catagoryId is invalid
        $categoryIdInvalid = array('', null, 0, -1);
        foreach ($categoryIdInvalid as $value) {

            $category                    = $data;
            $category['sportCategoryId'] = $value;
            $testResultSecond            = $this->parlayouteam->add( $category );

            if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                if( !empty($value) ) {


                    $this->assertContains( $testResultSecond['message'][0], 'The sportCategoryId field must contain a number greater than 0.', 'To verify catagoryId is invalid' );

                } else {

                    $this->assertContains( $testResultSecond['message'][0], 'The sportCategoryId field is required.', 'To verify catagoryId is invalid' );
                }

            }
        }

        // To verify Team team1 is invalid
        $nameInvalid           = $data;
        $nameInvalid['name']   = '';
        $testResultThird       = $this->parlayouteam->add( $nameInvalid );

        if( is_array($testResultThird) && isset($testResultThird['message'])) {

            $this->assertContains( $testResultThird['message'][0], 'The name field is required.', 'To verify name is invalid' );

        }

        // To verify add dailyshow under or over Team is valid
        //=========================================
        $testResultFourth = $this->parlayouteam->add( $data );

        if ( is_object($testResultFourth) ) {

            // To verify categoryId return must be categoryId from input
            $this->assertEquals( $data['sportCategoryId'], (int)$testResultFourth->sportCategoryId , 'To verify categoryId return must be categoryId from input');

            // To verify name return must be name from input
            $this->assertEquals( substr( $data['name'], 0, 50 ), $testResultFourth->name , 'To verify name return must be name from input');

        } else {

            $this->assertTrue( FALSE, "Can't verify add team dailyshow under or over is case valid") ;
        }

    }

    function testTeamUpdate() {

        $team = $this->parlayouteam->get_by( array('id !=' => 0) );

        if ( !empty( $team ) ) {

            $dataupdate['name'] = substr($team->name . md5(date('Y-m-d H:i:s').rand(1,100)), 0, 50 );

            $categoryId         = $team->sportCategoryId;
            $id                 = $team->id;

            // To verify update dailyshow under or over Team is invalid
            //===========================================================

            // To verify name is invlaid
            $nameInvalid['name']   = '';

            $testResultFirst  = $this->parlayouteam->edit($id, $categoryId, $nameInvalid );

            if( is_array($testResultFirst) && isset($testResultFirst['message'])) {

                $this->assertContains( $testResultFirst['message'][0], 'The name field is required.', 'To verify name is invalid' );

            }

            // To verify id is invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $testResultSecond = $this->parlayouteam->edit( $value, $categoryId, $dataupdate );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    $this->assertContains( $testResultSecond['message'], 'Category Id must be a numeric and greater than zero', 'To verify catagoryId is invalid' );

                }
            }

            // To verify category Id is invalid
            $categoryIdInvalid = array('', null, 0, -1);

            foreach ($categoryIdInvalid as $value) {

                $testResultSecond = $this->parlayouteam->edit( $id, $value, $dataupdate );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    $this->assertContains( $testResultSecond['message'], 'Category Id must be a numeric and greater than zero', 'To verify catagoryId is invalid' );
                }
            }

            // To verify data is empty
            $testResultFourth  = $this->parlayouteam->edit($id, $categoryId, '' );

            if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                $this->assertContains( $testResultFourth['message'], 'Please enter the required data', 'To verify name is invalid' );

            }

            // To verify update dailyshow under or over Team is valid
            //=========================================================
            $testResultFifth = $this->parlayouteam->edit( $id, $categoryId, $dataupdate);

            if ( is_object($testResultFifth) ) {

                // To verify id return must be equal id input
                $this->assertEquals($id, $testResultFifth->id, 'To verify id return must be equal id input');

                // To verify categoryId return must be equal categoryId input
                $this->assertEquals($categoryId, $testResultFifth->sportCategoryId, 'To verify categoryId return must be equal categoryId input');

                // To verify name return must be equal name input
                $this->assertEquals($dataupdate['name'], $testResultFifth->name, 'To verify name return must be equal name input');

            } elseif( $testResultFifth['code'] == 5 ) {

                $this->assertContains( $testResultFifth['message'], "Cannot save a duplicate sport team with name - ". $dataupdate['name']. " sportCategoryId - " . $categoryId );
            }

        } else {

            $this->assertTrue( FALSE, "Can't verify update team dailyshow under or over is case valid. Please testing add dailyshowdown OU team before testing update.") ;
        }
    }

    //========================
    // Test dailyshow under or over schedule //
    //=======================

    function testScheduleAdd() {
        $data = array(
            'sportCategoryId' => 1,
            'team1'           => 2,
            'team2'           => 3,
            'dateTime'        => '12-02-2014 00:00:00'
        );

        // To verify add dailyshow under or over Schedule is invalid
        //=========================================
        // To verify data is empty
        $dataInvalid     = '';
        $testResultFirst = $this->parlayouschedule->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->assertContains( $testResultFirst['message'], 'Please the required enter data', 'To verify data is empty');
        }

        // To verify schedule catagoryId is invalid
        $idInvalid = array('', null, 0, -1);
        foreach ($idInvalid as $value) {

            $categoryIdInvalid                    = $data;
            $categoryIdInvalid['sportCategoryId'] = $value;
            $testResultSecond                     = $this->parlayouschedule->add( $categoryIdInvalid );

            if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                if( !empty($value) ) {


                    $this->assertContains( $testResultSecond['message'][0], 'The sport Category Id field must contain a number greater than 0.', 'To verify catagoryId is invalid' );

                } else {

                    $this->assertContains( $testResultSecond['message'][0], 'The sport Category Id field is required.', 'To verify catagoryId is invalid' );
                }

            }
        }
        // To verify schedule team1 is invalid
        $team1Invalid = array('', null, 0, -1);
        foreach ($team1Invalid as $value) {

            $team1            = $data;
            $team1['team1']   = $value;
            $testResultThird  = $this->parlayouschedule->add( $team1 );

            if( is_array($testResultThird) && isset($testResultThird['message'])) {

                if ( !empty( $value ) ) {

                    $this->assertContains( $testResultThird['message'][0], 'The team1 field must contain a number greater than 0.', 'To verify team1 is invalid' );

                } else {

                    $this->assertContains( $testResultThird['message'][0], 'The team1 field is required.', 'To verify team1 is invalid' );
                }
            }
        }

        // To verify schedulee team2 is invalid
        $team1Invalid = array('', null, 0, -1);
        foreach ($team1Invalid as $value) {

            $team2            = $data;
            $team2['team2']   = $value;
            $testResultFourth = $this->parlayouschedule->add( $team2 );

            if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                if ( !empty( $value ) ) {

                    $this->assertContains( $testResultFourth['message'][0], 'The team2 field must contain a number greater than 0.', 'To verify team2 is invalid' );

                } else {

                    $this->assertContains( $testResultFourth['message'][0], 'The team2 field is required.', 'To verify team2 is invalid' );
                }
            }
        }

        // To verify add dailyshow under or over Schedule is valid
        //=========================================
        $testResultFifth = $this->parlayouschedule->add( $data );

        if ( is_object( $testResultFifth ) ) {

            // To verify sportCategoryId return must be equal sportCategoryId input
            $this->assertEquals($data['sportCategoryId'], (int)$testResultFifth->sportCategoryId, 'To verify sportCategoryId return must be equal sportCategoryId input');

            // To verify team1 return must be equal team1 input
            $this->assertEquals($data['team1'], (int)$testResultFifth->team1, 'To verify team1 return must be equal team1 input');

            // To verify team2 return must be equal team2 input
            $this->assertEquals($data['team2'], (int)$testResultFifth->team2, 'To verify team2 return must be equal team2 input');
        }
        else {

            $this->assertTrue(FALSE , "Can't verify add Schedule dailyshow under or over is case valid.") ;
        }
    }

    function testScheduleUpdate() {

        $schedule = $this->parlayouschedule->get_by( array( 'id !=' => 0 ) );

        if ( !empty( $schedule ) ) {

            $id         = $schedule->id;
            $dataUpdate = array(
                'sportCategoryId' => 1,
                'team1'           => 4,
                'team2'           => 5,
                'dateTime'        => '12-02-2014 00:00:00'
            );

            // To verify update dailyshow under or over Schedule is invalid
            //=========================================

            // To verify id input is invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultFirst = $this->parlayouschedule->edit( $value, $dataUpdate );

                if( is_array($testResultFirst) && isset($testResultFirst['message'])) {

                    $this->assertContains( $testResultFirst['message'], 'Id must is a numeric and greater than zero', 'To verify catagoryId is invalid' );

                }
            }
            // To verify data is empty
            $testResultSecond = $this->parlayouschedule->edit( $id, '' );

            if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                $this->assertContains( $testResultSecond['message'], 'Please the required enter data', 'To verify data is empty');
            }

            // To verify sportCategoryId is invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $categoryIdInvalid                    = $dataUpdate;
                $categoryIdInvalid['sportCategoryId'] = $value;
                $testResultThird                      = $this->parlayouschedule->edit( $id, $categoryIdInvalid );

                if( is_array($testResultThird) && isset($testResultThird['message'])) {

                    if( !empty($value) ) {


                        $this->assertContains( $testResultThird['message'][0], 'The sport Category Id field must contain a number greater than 0.', 'To verify catagoryId is invalid' );

                    } else {

                        $this->assertContains( $testResultThird['message'][0], 'The sport Category Id field is required.', 'To verify catagoryId is invalid' );
                    }

                }
            }

            // To verify schedule team1 is invalid
            $team1Invalid = array('', null, 0, -1);
            foreach ($team1Invalid as $value) {

                $team1            = $dataUpdate;
                $team1['team1']   = $value;
                $testResultFourth = $this->parlayouschedule->edit( $id, $team1 );

                if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                    if ( !empty( $value ) ) {

                        $this->assertContains( $testResultFourth['message'][0], 'The team1 field must contain a number greater than 0.', 'To verify team1 is invalid' );

                    } else {

                        $this->assertContains( $testResultFourth['message'][0], 'The team1 field is required.', 'To verify team1 is invalid' );
                    }
                }
            }
            // To verify schedulee team2 is invalid
            $team1Invalid = array('', null, 0, -1);
            foreach ($team1Invalid as $value) {

                $team2            = $dataUpdate;
                $team2['team2']   = $value;
                $testResultFifth  = $this->parlayouschedule->edit( $id, $team2 );

                if( is_array($testResultFifth) && isset($testResultFifth['message'])) {

                    if ( !empty( $value ) ) {

                        $this->assertContains( $testResultFifth['message'][0], 'The team2 field must contain a number greater than 0.', 'To verify team2 is invalid' );

                    } else {

                        $this->assertContains( $testResultFifth['message'][0], 'The team2 field is required.', 'To verify team2 is invalid' );
                    }
                }
            }

            // To verify update dailyshow under or over Schedule is valid
            //=========================================
            $testResultSixth = $this->parlayouschedule->edit( $id, $dataUpdate);

            if( !empty($testResultSixth) ) {

                // To verify id return must be equal id input
                $this->assertEquals($testResultSixth->id, $id, "To verify id return must be equal id input");

                // To verify categoryId return must be equal categoryId input
                $this->assertEquals((int)$testResultSixth->sportCategoryId, $dataUpdate['sportCategoryId'], "To verify categoryId return must be equal categoryId input");

                // To verify team1 return must be equal team1 input
                $this->assertEquals((int)$testResultSixth->team1, $dataUpdate['team1'], "To verify team1 return must be equal team1 input");

                // To verify team2 return must be equal team2 input
                $this->assertEquals((int)$testResultSixth->team2, $dataUpdate['team2'], "To verify team2 return must be equal team2 input");

            } else {

                $this->assertTrue( FALSE, "Can't verify update Schedule dailyshow under or over in case valid") ;
            }

        }

        else {

           $this->assertTrue( FALSE, "Can't verify update Schedule dailyshow under or over. Schedule is empty.") ;
        }
    }

    function testScheduleGetAllByDate() {

        $schedule = $this->parlayouschedule->order_by('dateTime', 'DESC')->get_by(array('id !=' => 0));

        if( !empty( $schedule ) ) {
            $offset = 0;
            $limit = 10;
            $date = date( 'm-d-Y', strtotime( str_replace( '-', '/', $schedule->dateTime) ) );
            $nextDay = date('m-d-Y', strtotime('+1 day', strtotime(str_replace( '-', '/', $schedule->dateTime))));

            // To verify get all by Date dailyshow under or over Schedule is invalid
            //=========================================
            $testResultFirst = $this->parlayouschedule->getAllByDate( $nextDay, $limit, $offset );

            if( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

                $this->assertContains( $testResultFirst['message'], "Parlay Over Under Schedule Not Found on date ". $nextDay, 'To verify date is invalid');
            }

            // To verify get all by Date dailyshow under or over Schedule is valid
            //===========================================================
            $testResultSecond = $this->parlayouschedule->getAllByDate( $date, $limit, $offset );

            if ( isset($testResultSecond['code']) && $testResultSecond['code'] === 0) {
                $games = $testResultSecond['games'];

                foreach ($games as $key => $game) {

                    $dateTest = date( 'm-d-Y', strtotime( str_replace( '-', '/', $game->dateTime) ) );

                    // To verify dateTime return must be equal time from input
                    $this->assertEquals( $date, $dateTest, 'To verify dateTime return must be equal time from input');
                }

            }

        } else {

            $this->assertTrue( FALSE, "Can't verify get all Schedule dailyshow under or over. Schedule is empty.") ;
        }

    }

    function testScheduleGetAllByCategory() {

        $schedule = $this->parlayouschedule->order_by('sportCategoryId', 'DESC')->get_by(array('id !=' => 0));

        if( !empty( $schedule ) ) {

            $offset     = 0;
            $limit      = 10;
            $categoryId = $schedule->sportCategoryId;
            $nextId     = ($schedule->sportCategoryId + 1);

            // To verify get all by Category dailyshow under or over Schedule is invalid
            //=========================================
            $testResultFirst = $this->parlayouschedule->getAllByCategory( $nextId, $limit, $offset );

            if( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

                $this->assertContains( $testResultFirst['message'], "Parlay Over Under Schedule Not Found with category id ". $nextId, 'To verify categoryId is invalid');
            }

            // To verify sportCategoryId is invalid
            $idInvalid = array('', 'abc', null, 0, -1);

            foreach ($idInvalid as $value) {
                $testResultSecond = $this->parlayouschedule->getAllByCategory( $value, $limit, $offset );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    $this->assertContains( $testResultSecond['message'], 'Category Id must be a numeric and greater than zero', 'To verify catagoryId is invalid' );

                }
            }

            // To verify get all by Category dailyshow under or over Schedule is valid
            //=========================================
            $testResultThird = $this->parlayouschedule->getAllByCategory( $categoryId, $limit, $offset );

            if ( isset($testResultThird['code']) && $testResultThird['code'] === 0) {
                $games = $testResultThird['games'];

                foreach ($games as $key => $game) {

                    // To verify categoryId return must be equal categoryId from input
                    $this->assertEquals( $categoryId, $game->sportCategoryId, 'To verify categoryId return must be equal time from input');
                }

            }

        } else {

            $this->assertTrue( FALSE, "Can't verify get all Schedule dailyshow under or over. Schedule is empty") ;
        }

    }

    //=======================
    // Test dailyshow under or over config  //
    //=======================
    function testConfigAdd() {

        $cardDate      = date('m-d-Y');
        $config        = $this->parlayouconfig->order_by('parlayCardId', 'DESC')->get_by(array('parlayCardId !=' => 0));
        $parlayCardId  = (! empty($config) ? ((int)$config->parlayCardId + 1) : 1 );

        $data          = array(
            'parlayCardId' => $parlayCardId,
            'cardWin'      => 'WIN123',
            'cardDate'     => "$cardDate",
            'serialNumber' => 'TestSerial123'
        );

        // To verify add Config is invalid
        //=========================================
        // To verify data is empty
        $dataInvalid     = '';
        $testResultFirst = $this->parlayouconfig->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty');
        }

        // To verify parlayCardId is invalid
        $idInvalid = array('', null, 0, -1);

        foreach ($idInvalid as $value) {

            $parlayCardIdInvalid                 = $data;
            $parlayCardIdInvalid['parlayCardId'] = $value;
            $testResultSecond                    = $this->parlayouconfig->add( $parlayCardIdInvalid );

            if( is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                if( !empty($value) ) {

                    $this->assertContains( $testResultSecond['message'][0], 'The parlayCardId field must contain a number greater than 0.', 'To verify parlayCardId is invalid' );

                } else {

                    $this->assertContains( $testResultSecond['message'][0], 'The parlayCardId field is required.', 'To verify parlayCardId is invalid' );
                }

            }
        }

        // To verify add Config is valid
        //=========================================
        $testResultThird = $this->parlayouconfig->add( $data );

        if ( is_object($testResultThird) ) {

            // To verify parlayCardId return must be equal parlayCardId input
            $this->assertEquals((int)$testResultThird->parlayCardId, $parlayCardId, 'To verify parlayCardId return must be equal parlayCardId input');

            // To verify cardWin return must be equal cardWin input
            $this->assertEquals($testResultThird->cardWin, $data['cardWin'], 'To verify cardWin return must be equal cardWin input');

            $cardDateTest = date('m-d-Y', strtotime($testResultThird->cardDate));

            // To verify cardDate return must be equal cardDate input
            $this->assertEquals($cardDateTest, $data['cardDate'], 'To verify cardDate return must be equal cardDate input');

        }
        else {

            $this->assertTrue( FALSE, "Can't verify add config dailyshow under or over.") ;
        }
    }

    function testConfigUpdate() {

        $config = $this->parlayouconfig->order_by('cardDate', 'DESC')->get_by(array('id !=' => 0));

        if ( !empty($config)) {

            // To verify Update Config is invalid
            //=========================================
            $id         = $config->id;
            $dataUpdate = array(
                'cardWin' => ( strlen($config->cardWin) < 42 ) ? $config->cardWin . "Updated" : substr( $config->cardWin, 0, 50),
            );

            // To verify id is invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultFirst = $this->parlayouconfig->edit( $value, $dataUpdate );

                if( is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                    $this->assertContains( $testResultFirst['message'], 'The id must be a numeric and greater than zero', 'To verify parlayCardId is invalid' );

                }
            }
            // To verify data is empty
            $dataInvalid = '';
            $testResultSecond = $this->parlayouconfig->edit( $id, $dataInvalid );

            if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                $this->assertContains( $testResultSecond['message'], 'Please enter the required data', 'To verify data is empty');
            }

            // To verify Update Config is valid
            //=========================================
            $testResultThird = $this->parlayouconfig->edit( $id, $dataUpdate);

            if ( is_object($testResultThird) ) {

                // To verify id return must be equal id input
                $this->assertEquals( $testResultThird->id, $id, 'To verify id return must be equal id input');

                // To verify cardWin return must be equal cardWin input
                $this->assertEquals( substr($testResultThird->cardWin, 0, 50 ), substr($dataUpdate['cardWin'], 0, 50 ), 'To verify cardWin return must be equal cardWin input');

            } else {

                $this->assertTrue( FALSE, "Can't verify update config dailyshow under or over in case valid.") ;
            }

        } else {

            $this->assertTrue( FALSE, "Can't verify update config dailyshow under or over") ;
        }

    }

    function testConfigGetById() {

        $config = $this->parlayouconfig->order_by('id', 'DESC')->get_by(array('id !=' => 0));

        if (! empty($config) ) {

            $id = $config->id;

            // To verify GetById Config is invalid
            //=========================================
            // To verify id input is invalid
            $idInvalid = array('', 'abc' ,null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultFirst = $this->parlayouconfig->getById( $value );

                if( is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                    $this->assertContains( $testResultFirst['message'], 'Id must is a numeric and greater than zero', 'To verify id is invalid' );

                }
            }

            // To verify GetById Config is valid
            //=========================================

            $testResultSecond = $this->parlayouconfig->getById( $id );

            if ( is_object($testResultSecond) ) {

                // To verify id return must be equal id input
                $this->assertEquals( $testResultSecond->id, $id, 'To verify id return must be equal id input');

                // To verify parlayCardId return must be equal parlayCardId input
                $this->assertEquals( $testResultSecond->parlayCardId, $config->parlayCardId, 'To verify parlayCardId return must be equal parlayCardId database');

                // To verify cardWin return must be equal cardWin input
                $this->assertEquals( $testResultSecond->cardWin, $config->cardWin, 'To verify cardWin return must be equal cardWin database');

                // To verify cardDate return must be equal cardDate database
                $this->assertEquals( $testResultSecond->cardDate, $config->cardDate, 'To verify cardDate return must be equal cardDate database');

            } else {

                $this->assertTrue( FALSE, "Can't verify update config dailyshow under or over in case valid.");
            }

        }
        else {

            $this->assertTrue( FALSE , "Can't verify get By id config dailyshow under or over. Config empty");

        }

    }

    function testConfigGetAllByDate() {

        $config = $this->parlayouconfig->order_by('cardDate', 'DESC')->get_by(array('id !=' => 0));

        if ( !empty($config)) {

            $date            = date('m-d-Y', strtotime($config->cardDate));
            $range           = 3;
            $datePlus        = date( 'm-d-Y', strtotime( $date . "+$range days" ) );
            $testResultFirst = $this->parlayouconfig->getAllByDate( $date, $range);

            // To verify GetAllByDate Config is valid
            //=========================================

            if ( isset($testResultFirst['code']) && $testResultFirst['code'] === 0) {

                foreach ($testResultFirst['items'] as $key => $value) {

                    $true = (strtotime($date) >= strtotime(date('m-d-Y', strtotime($value->cardDate))));
                    // To verify date must be lesster than or equal date input
                    $this->assertTrue( $true, 'To verify GetAllByDate Config is valid', 'To verify date must be lesster than or equal date input' );
                }
            }

        } else {

            $this->assertTrue( FALSE ,"Can't verify get all config by date dailyshow under or over. Config empty.");

        }

    }

    //=======================
    // Test dailyshow under or over card    //
    //=======================
    function testAddCard() {

        $schedule = $this->parlayouschedule->order_by('dateTime', 'DESC')->get_by(array('dateTime >=' => date('Y-m-d 00:00:00')));

        if ( ! empty($schedule) ) {

            $nextCartId = $this->parlayoucard->getNextCardId();
            $data       = array(
                'parlayCardId'    => $nextCartId->parlayCardId,
                'sportScheduleId' => $schedule->id,
                'sportCategoryId' => 1,
                'dateTime'        => date('m-d-Y H:i:s', strtotime( str_replace( '-', '/', $schedule->dateTime ) )),
                'team1'           => $schedule->team1,
                'team2'           => $schedule->team2,
                'team1Name'       => 'A',
                'team2Name'       => 'B',
                'overUnderScore'  => 1,

            );

            // To verify data is empty
            $dataInvalid     = '';
            $testResultFirst = $this->parlayoucard->add( $dataInvalid );

            if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty');
            }

            // To verify parlayCardId Invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $parlayCardIdInvalid = $data;
                $parlayCardIdInvalid['parlayCardId'] = $value;
                $testResultSecond            = $this->parlayoucard->add( $parlayCardIdInvalid );

                if( is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                    if( !empty($value) ) {

                        $this->assertContains( $testResultSecond['message'][0], 'The parlay Card Id field must contain a number greater than 0.', 'To verify parlayCardId is invalid' );
                    } else {

                        $this->assertContains( $testResultSecond['message'][0], 'The parlay Card Id field is required.', 'To verify parlayCardId is invalid' );
                    }

                }
            }
            // To verify sportScheduleId Invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $sportScheduleIdInvalid = $data;
                $sportScheduleIdInvalid['sportScheduleId'] = $value;
                $testResultThird            = $this->parlayoucard->add( $sportScheduleIdInvalid );
                if( is_array($testResultThird) && isset($testResultThird['message']) ) {

                    if( !empty($value) ) {

                        $this->assertContains( $testResultThird['message'][0], 'The sport Schedule Id field must contain a number greater than 0.', 'To verify sportScheduleId is invalid' );
                    } else {

                        $this->assertContains( $testResultThird['message'][0], 'The sport Schedule Id field is required.', 'To verify sportScheduleId is invalid' );
                    }

                }
            }

            // To verify sportCategoryId Invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $sportCategeryIdInvalid = $data;
                $sportCategeryIdInvalid['sportCategoryId'] = $value;

                $testResultFourth            = $this->parlayoucard->add( $sportCategeryIdInvalid );
                if( is_array($testResultFourth) && isset($testResultFourth['message']) ) {

                    if( !empty($value) ) {

                        $this->assertContains( $testResultFourth['message'][0], 'The sport Category Id field must contain a number greater than 0.', 'To verify sportCategoryId is invalid' );
                    } else {

                        $this->assertContains( $testResultFourth['message'][0], 'The sport Category Id field is required.', 'To verify sportCategoryId is invalid' );
                    }

                }
            }

            // To verify add card is valid
            // ===========================
            $testResult = $this->parlayoucard->add($data);

            if ( is_object($testResult) ) {

                // To verify parlayCardId return must be equal parlayCardInput
                $this->assertEquals((int)$testResult->parlayCardId, (int)$data['parlayCardId'], 'To verify parlayCardId return must be equal parlayCardInput');

                // To verify team1 return must be equal parlayCardInput
                $this->assertEquals((int)$testResult->team1, (int)$data['team1'], 'To verify team1 return must be equal team1 Input');
                // To verify team2 return must be equal parlayCardInput
                $this->assertEquals((int)$testResult->team2, (int)$data['team2'], 'To verify team2 return must be equal team2 Input');

                // To verify sportCategoryId return must be equal parlayCardInput
                $this->assertEquals((int)$testResult->sportCategoryId, (int)$data['sportCategoryId'], 'To verify sportCategoryId return must be equal sportCategoryId Input');

                // To verify sportScheduleId return must be equal parlayCardInput
                $this->assertEquals($testResult->sportScheduleId, $data['sportScheduleId'], 'To verify sportScheduleId return must be equal sportScheduleId Input');

                // To verify dateTime return must be equal parlayCardInput
                $this->assertEquals($testResult->dateTime, $schedule->dateTime, 'To verify dateTime return must be equal dateTime Input');

                // To verify overUnderScore return must be equal parlayCardInput
                $this->assertEquals((int)$testResult->overUnderScore, (int)$data['overUnderScore'], 'To verify overUnderScore return must be equal overUnderScore Input');

                // To verify team1Name return must be equal parlayCardInput
                $this->assertEquals($testResult->team1Name, $data['team1Name'], 'To verify team1Name return must be equal team1Name Input');

                // To verify team2Name return must be equal parlayCardInput
                $this->assertEquals($testResult->team2Name, $data['team2Name'], 'To verify team2Name return must be equal team2Name Input');
            } else {

                $this->assertTrue( FALSE, "Can't verify add card dailyshow under or over in case valid");
            }
        } else {

            $this->assertTrue( FALSE, "Can't verify add card dailyshow under or over. schedule empty.");

        }

    }

    function testUpdateCard() {

        $card = $this->parlayoucard->order_by('parlayCardId', 'DESC')->get_by(array('id !=' => 0));

        if ( !empty($card) ) {

            $id              = $card->id;
            $sportParlayCard = $card->parlayCardId;
            $dataUpdate      = array(
                'parlayCardId'    => ((int)$sportParlayCard + 1),
                'sportScheduleId' => $card->sportScheduleId,
                'sportCategoryId' => $card->sportCategoryId,
                'dateTime'        => date('m-d-Y H:i:s', strtotime( str_replace( '-', '/', $card->dateTime ) )),
                'team1'           => $card->team1,
                'team2'           => $card->team2,
                'team1Name'       => 'A',
                'team2Name'       => 'B',
                'overUnderScore'  => 2,
                );

            // To verify update card is invalid
            // ================================

            // To verify id input is invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultFirst = $this->parlayoucard->edit( $value, $dataUpdate );
                if( is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                    $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify id is invalid' );

                }
            }

            // To verify data is empty
            $dataInvalid = '';
            $testResultSecond = $this->parlayoucard->edit( $id, $dataInvalid );

            if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                $this->assertContains( $testResultSecond['message'], 'Please enter the required data', 'To verify data is empty');
            }

            // To verify update card is valid
            // ================================
            $testResultThird = $this->parlayoucard->edit( $id, $dataUpdate);

            if ( is_object($testResultThird) ) {

                // To verify id return must be equal id input
                $this->assertEquals((int)$testResultThird->id, (int)$id, 'To verify id return must be equal parlayCardInput');

                // To verify parlayCardId return must be equal parlayCardInput
                $this->assertEquals((int)$testResultThird->parlayCardId, (int)$dataUpdate['parlayCardId'], 'To verify parlayCardId return must be equal parlayCardInput');

                // To verify team1 return must be equal parlayCardInput
                $this->assertEquals((int)$testResultThird->team1, (int)$dataUpdate['team1'], 'To verify team1 return must be equal team1 Input');
                // To verify team2 return must be equal parlayCardInput
                $this->assertEquals((int)$testResultThird->team2, (int)$dataUpdate['team2'], 'To verify team2 return must be equal team2 Input');

                // To verify sportCategoryId return must be equal parlayCardInput
                $this->assertEquals((int)$testResultThird->sportCategoryId, (int)$dataUpdate['sportCategoryId'], 'To verify sportCategoryId return must be equal sportCategoryId Input');

                // To verify sportScheduleId return must be equal parlayCardInput
                $this->assertEquals($testResultThird->sportScheduleId, $dataUpdate['sportScheduleId'], 'To verify sportScheduleId return must be equal sportScheduleId Input');

                // To verify dateTime return must be equal parlayCardInput
                $this->assertEquals($testResultThird->dateTime, $card->dateTime, 'To verify dateTime return must be equal dateTime Input');

                // To verify overUnderScore return must be equal parlayCardInput
                $this->assertEquals((int)$testResultThird->overUnderScore, (int)$dataUpdate['overUnderScore'], 'To verify overUnderScore return must be equal overUnderScore Input');

                // To verify team1Name return must be equal parlayCardInput
                $this->assertEquals($testResultThird->team1Name, $dataUpdate['team1Name'], 'To verify team1Name return must be equal team1Name Input');

                // To verify team2Name return must be equal parlayCardInput
                $this->assertEquals($testResultThird->team2Name, $dataUpdate['team2Name'], 'To verify team2Name return must be equal team2Name Input');

            } else {

                $this->assertTrue( FALSE, "Can't verify Update card dailyshow under or over in case valid.");
            }

        } else {

            $this->assertTrue( FALSE, "Can't verify Update card dailyshow under or over. schedule empty.");
        }

    }

    function testGetCardByDate() {

        $card = $this->parlayouconfig->order_by('cardDate', 'DESC')->get_by(array('id !=' => 0));

        if ( !empty($card)) {

            $date            = date('m-d-Y', strtotime($card->cardDate));
            $range           = 3;
            $datePlus        = date( 'm-d-Y', strtotime( $date . "+$range days" ) );
            $testResultFirst = $this->parlayoucard->getAll( $date, $range);

            // To verify GetAllByDate card is valid
            //=========================================
            if ( isset($testResultFirst['code']) && $testResultFirst['code'] === 0) {

                foreach ($testResultFirst['games'] as $key => $value) {

                    $true = (strtotime($date) >= strtotime(date('m-d-Y', strtotime($value->cardDate))));

                    // To verify date must be lesster than or equal date input
                    $this->assertTrue( $true, 'To verify GetAllByDate card is valid', 'To verify date must be lesster than or equal date input' );
                }
            }

        } else {

            $this->assertTrue( FALSE , "Can't verify get all card by date dailyshow under or over. Config empty.");

        }

    }

    //=============================================
    // Test dailyshow under or over results      //
    //=============================================
    function testResultAdd() {

        $result = $this->CI->player->memcacheInstance->flush();

        $query = $this->CI->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $query = $this->CI->db->query('TRUNCATE SportOUGameResults;');
        $query = $this->CI->db->query('SET FOREIGN_KEY_CHECKS=1;');

        // check parlay schedule id
        $parlayOUCard = $this->CI->db->query('SELECT *
            FROM  SportOUParlayCards c
            WHERE
                NOT c.id IS NULL AND
                NOT EXISTS(
                    SELECT NULL
                    FROM SportOUGameResults r
                    WHERE c.sportScheduleId = r.sportScheduleId
                )
            LIMIT 1')->result();

        if( sizeof($parlayOUCard) > 0 ) {

            $data = array(
                'sportScheduleId' => $parlayOUCard[0]->sportScheduleId,
                'parlayCardId'    => $parlayOUCard[0]->parlayCardId,
                'score1'          => 12,
                'score2'          => 20,
            );

            // To verify add result is invalid
            // ===============================
            // To verify parlayCardId Invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $parlayCardIdInvalid                 = $data;
                $parlayCardIdInvalid['parlayCardId'] = $value;
                $testResultSecond                    = $this->parlayouresult->add( $parlayCardIdInvalid );

                if( is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                    if( !empty($value) ) {

                        $this->assertContains( $testResultSecond['message'][0], 'The parlay Card Id field must contain a number greater than 0.', 'To verify parlayCardId is invalid' );
                    } else {

                        $this->assertContains( $testResultSecond['message'][0], 'The parlay Card Id field is required.', 'To verify parlayCardId is invalid' );
                    }

                }
            }
            // To verify sportScheduleId Invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $sportScheduleIdInvalid                    = $data;
                $sportScheduleIdInvalid['sportScheduleId'] = $value;
                $testResultThird                           = $this->parlayouresult->add( $sportScheduleIdInvalid );

                if( is_array($testResultThird) && isset($testResultThird['message']) ) {

                    if( !empty($value) ) {

                        $this->assertContains( $testResultThird['message'][0], 'The sport Schedule Id field must contain a number greater than 0.', 'To verify sportScheduleId is invalid' );
                    } else {

                        $this->assertContains( $testResultThird['message'][0], 'The sport Schedule Id field is required.', 'To verify sportScheduleId is invalid' );
                    }

                }
            }

            // To verify add result is valid
            // ===============================
            $testResult = $this->parlayouresult->add( $data );

            if ( is_object($testResult) ) {

                // To verify parlayCardId return must be equal parlayCardId input
                $this->assertEquals((int)$testResult->parlayCardId, (int)$data['parlayCardId'], 'To verify parlayCardId return must be equal parlayCardId input');

                // To verify sportScheduleId return must be equal sportScheduleId input
                $this->assertEquals((int)$testResult->sportScheduleId, (int)$data['sportScheduleId'], 'To verify sportScheduleId return must be equal sportScheduleId input');

                // To verify score1 return must be equal score1 input
                $this->assertEquals((int)$testResult->score1, (int)$data['score1'], 'To verify score1 return must be equal score1 input');

                // To verify score2 return must be equal score2 input
                $this->assertEquals((int)$testResult->score2, (int)$data['score2'], 'To verify score2 return must be equal score2 input');

                $countScore = $data['score2'] + $data['score1'];
                $score      = (int)$parlayOUCard[0]->overUnderScore;

                if ( $countScore >= $score) {

                    // To verify overUnder return must be equal 2
                    $this->assertEquals((int)$testResult->overUnder, 2, 'To verify overUnder return must be equal 2');

                } else {

                    // To verify overUnder return must be equal 1
                    $this->assertEquals((int)$testResult->overUnder, 1, 'To verify overUnder return must be equal 1');
                }

            }  else {

                $this->assertTrue( FALSE, "Can't verify add result dailyshow under or over in case valid.");
            }

        } else {

            $this->assertTrue( FALSE, "Can't verify add result dailyshow under or over. Schedule is'nt exist.");

        }

    }

    function testResultUpdate() {

        $this->CI->player->memcacheInstance->flush();
        $result = $this->parlayouresult->order_by('sportScheduleId', 'ASC')->get_by( array('sportScheduleId !=' => 0, 'parlayCardId !=' => 0) ); 

        if ( !empty($result) ) {

            $dataUpdate = array(
                'parlayCardId' => ( (int)$result->parlayCardId ),
                'score1'       => 12,
                'score2'       => 20,
            );

            $id = $result->sportScheduleId;

            // To verify update results is invalid
            // ===================================
            
            // To verify id input is invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $testResultFirst = $this->parlayouresult->edit( $value, $dataUpdate );

                if( is_array($testResultFirst) && isset($testResultFirst['message']) ) {
                    
                    $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero' , 'To verify id input is invalid');                    
                }   
            }

            // To verify data is empty
            $dataInvalid      = '';
            $testResultSecond = $this->parlayouresult->edit( $id, $dataInvalid );

            if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                $this->assertContains( $testResultSecond['message'], 'Please enter the required data', 'To verify data is empty');
            }

            // To verify parlayCardId input is invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $parlayCardIdInvalid                 = $dataUpdate;
                $parlayCardIdInvalid['parlayCardId'] = $value;
                $testResultThird                     = $this->parlayouresult->edit( $id, $parlayCardIdInvalid );

                if( is_array($testResultThird) && isset($testResultThird['message']) ) {

                    if( !empty($value) ) {

                        $this->assertContains( $testResultThird['message'][0], 'The parlay Card Id field must contain a number greater than 0.', 'To verify parlayCardId is invalid' );
                    } else {
                        
                        $this->assertContains( $testResultThird['message'][0], 'The parlay Card Id field is required.', 'To verify parlayCardId is invalid' );
                    }
                    
                }   
            }

            // To verify update results is valid
            // ===================================
            

        } else {

            $this->assertTrue( FALSE, "Can't verify update result dailyshow under or over. Result is empty.");

        }
        
    }

}