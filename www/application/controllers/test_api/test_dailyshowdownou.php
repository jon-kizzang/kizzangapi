<?php

class test_dailyshowdownou extends CI_Controller {
  
    function __construct() {

        parent::__construct();

        // loading model dailyshow under or overs
        $this->load->model('parlayoucategory');

        //loading library unit test
        $this->load->library( 'unit_test' );

        // loading database test
        $this->load->database( 'test', TRUE );

        //To enable strict mode 
        $this->unit->use_strict( TRUE );

        // Disable database debugging so we can test all units without stopping
        // at the first SQL error
        $this->db->db_debug = FALSE;

    }
    //==============================
    // Test dailyshow under or over categories    //
    //============================
    function testCategoryAdd() {

        $data = array(
            'name' => 'Big Game 21' . md5(date('Y-m-d H:i:s').rand(1,100)),
        );

        // To verify add dailyshow under or over Category is invalid
        //========================================= 
        // To verify data is empty
        $dataInvalid = '';
        $testResultFirst = $this->parlayoucategory->add( $dataInvalid );
        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->unit->run( $testResultFirst['message'], 'Please enter the required data', 'To verify add Category is invalid', 'To verify data is empty');
        }

        // To verify data is empty
        $dataInvalid['name'] = '';
        $testResultSecond = $this->parlayoucategory->add( $dataInvalid );

        if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

            $this->unit->run( $testResultSecond['message'][0], 'The name field is required.', 'To verify add Category is invalid', 'To verify data is empty');
        }

        // To verify add category is exist
        $categories = $this->parlayoucategory->get_by(array('id !=' => 0));

        if ( !empty( $categories ) ) {

            $dataInvalid['name'] = $categories->name;
            $testResultThird = $this->parlayoucategory->add( $dataInvalid ); 

            if (is_array($testResultThird) && isset($testResultThird['message']) ) {

                $this->unit->run( $testResultThird['message'], 'Cannot save a duplicate Parlay Over Under Category with name - ' . $dataInvalid['name'] , 'To verify add dailyshow under or over Category is invalid', 'To verify add category is exist');
            }
        }

        // To verify add dailyshow under or over Category is valid
        //======================================= 
        $nameExpected = $data['name'];
        $testResultFourth = $this->parlayoucategory->add( $data );

        if ( is_object($testResultFourth) ) {

            // To verify name returm must be equal name dailyshow under or over Category input
            $this->unit->run($testResultFourth->name, $nameExpected, 'To verify add dailyshow under or over Category is valid', 'To verify name returm must be equal name dailyshow under or over Category input');

        } else {

            echo "<h4 style='color:red;'> Can't verify add category dailyshow under or over is case valid</h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());    
    }

    function testCategoryUpdate() {

        $category = $this->parlayoucategory->order_by('id', 'DESC')->get_by(array('id !='=> 0));

        if( !empty($category) ) {
            $id = $category->id;
            $data['name'] = $category->name . "Update";
            // To verify update dailyshow under or over Category is invalid
            //=========================================
            // To verify data is empty
            $dataEmpty = '';

            $testResultFirst = $this->parlayoucategory->edit( $id , $dataEmpty );

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

                // To verify data is empty
                $this->unit->run( $testResultFirst['message'], 'Please enter the required data', 'To verify update category result is invalid', 'To verify data is empty' );
            }

            // To verify id is invalid
            $idInvalid = array('', NULL, 'abc', 0, -1);
            foreach ($idInvalid as $key => $value) {
                $testResultSecond = $this->parlayoucategory->edit( $value, $data );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                   $this->unit->run( $testResultSecond['message'], 'Id must be a numeric and greater than zero', 'To verify update dailyshow under or over category is invalid', 'To verify id is invalid' );
                }  
            } 

            // To verify update dailyshow under or over Category is valid
            //========================================= 
            $testResultThird = $this->parlayoucategory->edit( $id, $data );

            if ( is_object($testResultThird) ) {

                // To verify name returm must be equal name dailyshow under or over Category input
                $this->unit->run( $testResultThird->name, substr( $data['name'], 0, 50 ), 'To verify add dailyshow under or over Category is valid', 'To verify name returm must be equal name dailyshow under or over Category input');

            } else {

                echo "<h4 style='color:red;'> Can't verify update category dailyshow under or over is case valid</h4>";
            }

        } else {

            echo "<h4 style='color:red;'> Can't verify update category dailyshow under or over is case valid. Please testing add dailyshow under or over Category before testing update.</h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    }

    function testCategoryGetAllByDate() {

        // To verify get all by Date dailyshow under or over Category is invalid
        //========================================= 
        
        // To verify get all by Date dailyshow under or over Category is valid
        //========================================= 
    }

    function testCategoryGetAllByCategory() {

        // To verify get all by Category dailyshow under or over Category is invalid
        //========================================= 
        
        // To verify get all by Category dailyshow under or over Category is valid
        //========================================= 
    }

    function testCategoryDelete() {

        // To verify delete dailyshow under or over Category is invalid
        //========================================= 
        
        // To verify delete dailyshow under or over Category is valid
        //========================================= 
    }

    
    //=======================
    // Test dailyshow under or over teams   //
    //======================
    function testTeamAdd() {
        $data = array(
            'sportCategoryId' => 1,
            'name'           => "Add Team". md5(date('Y-m-d H:i:s').rand(1,100)) ,
        );

        $this->load->model('parlayouteam');
        // To verify add dailyshow under or over Team is invalid
        //========================================= 
        // To verify data is empty
        $dataInvalid = '';
        $testResultFirst = $this->parlayouteam->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->unit->run( $testResultFirst['message'], 'Please enter the required data', 'To verify add Team is invalid', 'To verify data is empty');
        }

        // To verify Team catagoryId is invalid
        $categoryIdInvalid = array('', null, 0, -1);
        foreach ($categoryIdInvalid as $value) {
            $category                    = $data;
            $category['sportCategoryId'] = $value;
            $testResultSecond            = $this->parlayouteam->add( $category );
            if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                if( !empty($value) ) {
                    

                    $this->unit->run( $testResultSecond['message'][0], 'The sportCategoryId field must contain a number greater than 0.', 'To verify add Team is invalid', 'To verify catagoryId is invalid' );
                } else {
                    
                    $this->unit->run( $testResultSecond['message'][0], 'The sportCategoryId field is required.', 'To verify add Team is invalid', 'To verify catagoryId is invalid' );
                }
                
            }   
        }

        // To verify Team team1 is invalid  
        $nameInvalid = $data;
        $nameInvalid['name']   = '';
        $testResultThird  = $this->parlayouteam->add( $nameInvalid );
        if( is_array($testResultThird) && isset($testResultThird['message'])) {

            $this->unit->run( $testResultThird['message'][0], 'The name field is required.', 'To verify add name is invalid', 'To verify name is invalid' );
            
        }

        // To verify add dailyshow under or over Team is valid
        //========================================= 
        $testResultFourth = $this->parlayouteam->add( $data );

        if ( is_object($testResultFourth) ) {

            // To verify categoryId return must be categoryId from input
            $this->unit->run( $data['sportCategoryId'], (int)$testResultFourth->sportCategoryId , 'To verify add dailyshow under or over Team is valid', 'To verify categoryId return must be categoryId from input');

            // To verify name return must be name from input
            $this->unit->run( substr( $data['name'], 0, 50 ), $testResultFourth->name , 'To verify add dailyshow under or over Team is valid', 'To verify name return must be name from input');

        } else {

            echo "<h4 style='color:red;'> Can't verify add team dailyshow under or over is case valid.</h4>";
        }
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());    
    }

    function testTeamUpdate() {
        $this->load->model('parlayouteam');

        $team = $this->parlayouteam->get_by( array('id !=' => 0) );

        if ( !empty( $team ) ) {

            $dataupdate['name'] = substr($team->name . md5(date('Y-m-d H:i:s').rand(1,100)), 0, 50 );

            $categoryId         = $team->sportCategoryId;
            $id                 = $team->sportCategoryId;    

            // To verify update dailyshow under or over Team is invalid
            //===========================================================  
            
            // To verify name is invlaid
            $nameInvalid['name']   = '';
            
            $testResultFirst  = $this->parlayouteam->edit($id, $categoryId, $nameInvalid );
            if( is_array($testResultFirst) && isset($testResultFirst['message'])) {

                $this->unit->run( $testResultFirst['message'][0], 'The name field is required.', 'To verify add name is invalid', 'To verify name is invalid' );
                
            }

            // To verify id is invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {
                
                $testResultSecond = $this->parlayouteam->edit( $value, $categoryId, $dataupdate );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    $this->unit->run( $testResultSecond['message'], 'Id must be a numeric and greater than zero', 'To verify add Team is invalid', 'To verify catagoryId is invalid' );
                    
                }   
            } 

            // To verify category Id is invalid
            $categoryIdInvalid = array('', null, 0, -1);
            foreach ($categoryIdInvalid as $value) {
                $testResultSecond            = $this->parlayouteam->edit( $id, $value, $dataupdate );
                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    $this->unit->run( $testResultSecond['message'], 'Category Id must be a numeric and greater than zero', 'To verify add Team is invalid', 'To verify catagoryId is invalid' );
                }   
            }

            // To verify data is empty
            $testResultFourth  = $this->parlayouteam->edit($id, $categoryId, '' );
            if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                $this->unit->run( $testResultFourth['message'], 'Please enter the required data', 'To verify add name is invalid', 'To verify name is invalid' );
                
            }

            // To verify update dailyshow under or over Team is valid
            //=========================================================  
            $testResultFifth = $this->parlayouteam->edit( $id, $categoryId, $dataupdate);

            if ( is_object($testResultFifth) ) {

                // To verify id return must be equal id input
                $this->unit->run($id, $testResultFifth->id, 'To verify update dailyshow under or over Team is valid', 'To verify id return must be equal id input');

                // To verify categoryId return must be equal categoryId input
                $this->unit->run($categoryId, $testResultFifth->sportCategoryId, 'To verify update dailyshow under or over Team is invalid', 'To verify categoryId return must be equal categoryId input');

                // To verify name return must be equal name input
                $this->unit->run($dataupdate['name'], $testResultFifth->name, 'To verify update dailyshow under or over Team is invalid', 'To verify name return must be equal name input');
            } else {

                echo "<h4 style='color:red;'> Can't verify update team dailyshow under or over is case valid.</h4>";
            }        

        } else {
            
            echo "<h4 style='color:red;'> Can't verify update team dailyshow under or over is case valid. Please testing add dailyshowdown OU team before testing update.</h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());  
    }

    function testTeamGetAllByDate() {

        // To verify get all by Date dailyshow under or over Team is invalid
        //========================================= 
        
        // To verify get all by Date dailyshow under or over Team is valid
        //========================================= 
    }

    function testTeamGetAllByCategory() {

        // To verify get all by Category dailyshow under or over Team is invalid
        //========================================= 
        
        // To verify get all by Category dailyshow under or over Team is valid
        //========================================= 
    }

    function testTeamDelete() {

        // To verify delete dailyshow under or over Team is invalid
        //========================================= 
        
        // To verify delete dailyshow under or over Team is valid
        //========================================= 
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

        $this->load->model('parlayouschedule');
        // To verify add dailyshow under or over Schedule is invalid
        //========================================= 
        // To verify data is empty
        $dataInvalid = '';
        $testResultFirst = $this->parlayouschedule->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->unit->run( $testResultFirst['message'], 'Please the required enter data', 'To verify add schedule is invalid', 'To verify data is empty');
        }

        // To verify schedule catagoryId is invalid
        $idInvalid = array('', null, 0, -1);
        foreach ($idInvalid as $value) {
            $categoryIdInvalid = $data;
            $categoryIdInvalid['sportCategoryId'] = $value;
            $testResultSecond            = $this->parlayouschedule->add( $categoryIdInvalid );
            if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                if( !empty($value) ) {
                    

                    $this->unit->run( $testResultSecond['message'][0], 'The sport Category Id field must contain a number greater than 0.', 'To verify add Schedule is invalid', 'To verify catagoryId is invalid' );
                } else {
                    
                    $this->unit->run( $testResultSecond['message'][0], 'The sport Category Id field is required.', 'To verify add Schedule is invalid', 'To verify catagoryId is invalid' );
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
                    $this->unit->run( $testResultThird['message'][0], 'The team1 field must contain a number greater than 0.', 'To verify add schedule is invalid', 'To verify team1 is invalid' );
                } else {
                    $this->unit->run( $testResultThird['message'][0], 'The team1 field is required.', 'To verify add schedule is invalid', 'To verify team1 is invalid' );
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
                    $this->unit->run( $testResultFourth['message'][0], 'The team2 field must contain a number greater than 0.', 'To verify add schedule is invalid', 'To verify team2 is invalid' );
                } else {
                    $this->unit->run( $testResultFourth['message'][0], 'The team2 field is required.', 'To verify add schedule is invalid', 'To verify team2 is invalid' );
                }
            }   
        }

        // Toverify CategoryId , team1, team2 is not exist


        // To verify add dailyshow under or over Schedule is valid
        //========================================= 
        $testResultFifth = $this->parlayouschedule->add( $data );

        if ( is_object( $testResultFifth ) ) {

            // To verify sportCategoryId return must be equal sportCategoryId input
            $this->unit->run($data['sportCategoryId'], (int)$testResultFifth->sportCategoryId, 'To verify add dailyshow under or over Schedule is valid', 'To verify sportCategoryId return must be equal sportCategoryId input');

            // To verify team1 return must be equal team1 input
            $this->unit->run($data['team1'], (int)$testResultFifth->team1, 'To verify add dailyshow under or over Schedule is valid', 'To verify team1 return must be equal team1 input');

            // To verify team2 return must be equal team2 input
            $this->unit->run($data['team2'], (int)$testResultFifth->team2, 'To verify add dailyshow under or over Schedule is valid', 'To verify team2 return must be equal team2 input');
        } 
        else {

            echo "<h4 style='color:red;'> Can't verify add Schedule dailyshow under or over is case valid.</h4>";
        }
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());    
    }

    function testScheduleUpdate() {

        $this->load->model('parlayouschedule');

        $schedule = $this->parlayouschedule->get_by( array( 'id !=' => 0 ) );

        if ( !empty( $schedule ) ) {
            $id = $schedule->id;
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

                    $this->unit->run( $testResultFirst['message'], 'Id must is a numeric and greater than zero', 'To verify Update Schedule is invalid', 'To verify catagoryId is invalid' );
                    
                }   
            }
            // To verify data is empty
            $testResultSecond = $this->parlayouschedule->edit( $id, '' );

            if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                $this->unit->run( $testResultSecond['message'], 'Please the required enter data', 'To verify update schedule is invalid', 'To verify data is empty');
            }

            // To verify sportCategoryId is invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {
                $categoryIdInvalid = $dataUpdate;
                $categoryIdInvalid['sportCategoryId'] = $value;
                $testResultThird            = $this->parlayouschedule->edit( $id, $categoryIdInvalid );
                if( is_array($testResultThird) && isset($testResultThird['message'])) {

                    if( !empty($value) ) {
                        

                        $this->unit->run( $testResultThird['message'][0], 'The sport Category Id field must contain a number greater than 0.', 'To verify update Schedule is invalid', 'To verify catagoryId is invalid' );
                    } else {
                        
                        $this->unit->run( $testResultThird['message'][0], 'The sport Category Id field is required.', 'To verify update Schedule is invalid', 'To verify catagoryId is invalid' );
                    }
                    
                }   
            }

            // To verify schedule team1 is invalid
            $team1Invalid = array('', null, 0, -1);
            foreach ($team1Invalid as $value) {
                $team1            = $dataUpdate;
                $team1['team1']   = $value;
                $testResultFourth  = $this->parlayouschedule->edit( $id, $team1 );

                if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                    if ( !empty( $value ) ) {
                        $this->unit->run( $testResultFourth['message'][0], 'The team1 field must contain a number greater than 0.', 'To verify update schedule is invalid', 'To verify team1 is invalid' );
                    } else {
                        $this->unit->run( $testResultFourth['message'][0], 'The team1 field is required.', 'To verify update schedule is invalid', 'To verify team1 is invalid' );
                    }
                }   
            }
            // To verify schedulee team2 is invalid
            $team1Invalid = array('', null, 0, -1);
            foreach ($team1Invalid as $value) {
                $team2            = $dataUpdate;
                $team2['team2']   = $value;
                $testResultFifth = $this->parlayouschedule->edit( $id, $team2 );
                if( is_array($testResultFifth) && isset($testResultFifth['message'])) {

                    if ( !empty( $value ) ) {
                        $this->unit->run( $testResultFifth['message'][0], 'The team2 field must contain a number greater than 0.', 'To verify update schedule is invalid', 'To verify team2 is invalid' );
                    } else {
                        $this->unit->run( $testResultFifth['message'][0], 'The team2 field is required.', 'To verify update schedule is invalid', 'To verify team2 is invalid' );
                    }
                }   
            }

            // To verify update dailyshow under or over Schedule is valid
            //========================================= 
            $testResultSixth = $this->parlayouschedule->edit( $id, $dataUpdate);
            
            if( !empty($testResultSixth) ) {
                
                // To verify id return must be equal id input 
                $this->unit->run($testResultSixth->id, $id, "To verify update dailyshow under or over Schedule is valid", "To verify id return must be equal id input");

                // To verify categoryId return must be equal categoryId input 
                $this->unit->run((int)$testResultSixth->sportCategoryId, $dataUpdate['sportCategoryId'], "To verify update dailyshow under or over Schedule is valid", "To verify categoryId return must be equal categoryId input");

                // To verify team1 return must be equal team1 input 
                $this->unit->run((int)$testResultSixth->team1, $dataUpdate['team1'], "To verify update dailyshow under or over Schedule is valid", "To verify team1 return must be equal team1 input");

                // To verify team2 return must be equal team2 input 
                $this->unit->run((int)$testResultSixth->team2, $dataUpdate['team2'], "To verify update dailyshow under or over Schedule is valid", "To verify team2 return must be equal team2 input");

            } else {

                echo "<h4 style='color:red;'> Can't verify update Schedule dailyshow under or over in case valid</h4>";
            }

        } 
        else {

           echo "<h4 style='color:red;'> Can't verify update Schedule dailyshow under or over. Schedule is empty</h4>"; 
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    }

    function testScheduleGetAllByDate() {

        $this->load->model('parlayouschedule');
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

                $this->unit->run( $testResultFirst['message'], "Parlay Over Under Schedule Not Found on date ". $nextDay, 'To verify get all by Date dailyshow under or over Schedule is invalid', 'To verify date is invalid');
            }

            // To verify get all by Date dailyshow under or over Schedule is valid
            //=========================================================== 
            $testResultSecond = $this->parlayouschedule->getAllByDate( $date, $limit, $offset );

            if ( isset($testResultSecond['code']) && $testResultSecond['code'] === 0) {
                $games = $testResultSecond['games'];

                foreach ($games as $key => $game) {
                    
                    $dateTest = date( 'm-d-Y', strtotime( str_replace( '-', '/', $game->dateTime) ) );

                    // To verify dateTime return must be equal time from input
                    $this->unit->run( $date, $dateTest, 'To verify get all by Date dailyshow under or over Schedule is valid', 'To verify dateTime return must be equal time from input');
                }

            }

        } else {

            echo "<h4 style='color:red;'> Can't verify get all Schedule dailyshow under or over. Schedule is empty</h4>"; 
        }       

        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    }

    function testScheduleGetAllByCategory() {
        
        $this->load->model('parlayouschedule');
        $schedule = $this->parlayouschedule->order_by('sportCategoryId', 'DESC')->get_by(array('id !=' => 0));

        if( !empty( $schedule ) ) {
            $offset = 0;
            $limit = 10;        
            $categoryId = $schedule->sportCategoryId;
            $nextId = ($schedule->sportCategoryId + 1); 

            // To verify get all by Category dailyshow under or over Schedule is invalid
            //========================================= 
            $testResultFirst = $this->parlayouschedule->getAllByCategory( $nextId, $limit, $offset );

            if( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

                $this->unit->run( $testResultFirst['message'], "Parlay Over Under Schedule Not Found with category id ". $nextId, 'To verify get all by Date dailyshow under or over Schedule is invalid', 'To verify categoryId is invalid');
            }

            // To verify sportCategoryId is invalid
            $idInvalid = array('', 'abc', null, 0, -1);
            
            foreach ($idInvalid as $value) {
                $testResultSecond = $this->parlayouschedule->getAllByCategory( $value, $limit, $offset );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    $this->unit->run( $testResultSecond['message'], 'Category Id must be a numeric and greater than zero', 'To verify getAllByCategory Schedule is invalid', 'To verify catagoryId is invalid' );
                                        
                }   
            }

            // To verify get all by Category dailyshow under or over Schedule is valid
            //========================================= 
            $testResultThird = $this->parlayouschedule->getAllByCategory( $categoryId, $limit, $offset );

            if ( isset($testResultThird['code']) && $testResultThird['code'] === 0) {
                $games = $testResultThird['games'];

                foreach ($games as $key => $game) {

                    // To verify categoryId return must be equal categoryId from input
                    $this->unit->run( $categoryId, $game->sportCategoryId, 'To verify get all by Date dailyshow under or over Schedule is valid', 'To verify categoryId return must be equal time from input');
                }

            }

        } else {

            echo "<h4 style='color:red;'> Can't verify get all Schedule dailyshow under or over. Schedule is empty</h4>"; 
        }       

        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 

    }

    function testScheduleDelete() {

        // To verify delete dailyshow under or over Schedule is invalid
        //========================================= 
        
        // To verify delete dailyshow under or over Schedule is valid
        //========================================= 
    }

    //=======================
    // Test dailyshow under or over config  // 
    //=======================
    function testConfigAdd() {

        $this->load->model('parlayouconfig');

        $cardDate  = date('m-d-Y');
        $config = $this->parlayouconfig->order_by('parlayCardId', 'DESC')->get_by(array('parlayCardId !=' => 0));
        $parlayCardId = (! empty($config) ? ((int)$config->parlayCardId + 1) : 1 ); 
        $data = array(
            'parlayCardId' => $parlayCardId,
            'cardWin' => 'WIN123',
            'cardDate' => "$cardDate",
        );

        // To verify add Config is invalid
        //========================================= 
        // To verify data is empty
        $dataInvalid = '';
        $testResultFirst = $this->parlayouconfig->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->unit->run( $testResultFirst['message'], 'Please the required enter data', 'To verify add config is invalid', 'To verify data is empty');
        }
        // To verify parlayCardId is invalid
        $idInvalid = array('', null, 0, -1);
        foreach ($idInvalid as $value) {

            $parlayCardIdInvalid = $data;
            $parlayCardIdInvalid['parlayCardId'] = $value;
            $testResultSecond            = $this->parlayouconfig->add( $parlayCardIdInvalid );

            if( is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                if( !empty($value) ) {

                    $this->unit->run( $testResultSecond['message'][0], 'The parlayCardId field must contain a number greater than 0.', 'To verify add config is invalid', 'To verify parlayCardId is invalid' );
                } else {
                    
                    $this->unit->run( $testResultSecond['message'][0], 'The parlayCardId field is required.', 'To verify add config is invalid', 'To verify parlayCardId is invalid' );
                }
                
            }   
        }
        // To verify add Config is valid
        //========================================= 
        $testResultThird = $this->parlayouconfig->add( $data );

        if ( is_object($testResultThird) ) {

            // To verify parlayCardId return must be equal parlayCardId input
            $this->unit->run((int)$testResultThird->parlayCardId, $parlayCardId, 'To verify add Config is valid', 'To verify parlayCardId return must be equal parlayCardId input');

            // To verify cardWin return must be equal cardWin input
            $this->unit->run($testResultThird->cardWin, $data['cardWin'], 'To verify add Config is valid', 'To verify cardWin return must be equal cardWin input');

            $cardDateTest = date('m-d-Y', strtotime($testResultThird->cardDate));
            
            // To verify cardDate return must be equal cardDate input
            $this->unit->run($cardDateTest, $data['cardDate'], 'To verify add Config is valid', 'To verify cardDate return must be equal cardDate input');

        } 
        else {

            echo "<h4 style='color:red;'> Can't verify add config dailyshow under or over.</h4>"; 
        }       

        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());      

    }

    function testConfigUpdate() {

        $this->load->model('parlayouconfig');

        $config = $this->parlayouconfig->order_by('cardDate', 'DESC')->get_by(array('id !=' => 0));
        
        if ( !empty($config)) {

            // To verify Update Config is invalid
            //========================================= 
            $id = $config->id;
            $dataUpdate = array(
                'cardWin' => $config->cardWin . "Updated",
            );

            // To verify id is invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultFirst = $this->parlayouconfig->edit( $value, $dataUpdate );

                if( is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                    $this->unit->run( $testResultFirst['message'], 'The id must be a numeric and greater than zero', 'To verify update config is invalid', 'To verify parlayCardId is invalid' );
                    
                }   
            }
            // To verify data is empty
            $dataInvalid = '';
            $testResultSecond = $this->parlayouconfig->edit( $id, $dataInvalid );

            if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                $this->unit->run( $testResultSecond['message'], 'Please enter the required data', 'To verify update config is invalid', 'To verify data is empty');
            }

            // To verify Update Config is valid
            //=========================================
            
            $testResultThird = $this->parlayouconfig->edit( $id, $dataUpdate);
            
            if ( is_object($testResultThird) ) {

                // To verify id return must be equal id input
                $this->unit->run( $testResultThird->id, $id, 'To verify update config is valid', 'To verify id return must be equal id input');


                // To verify cardWin return must be equal cardWin input
                $this->unit->run( $testResultThird->cardWin, $dataUpdate['cardWin'], 'To verify update config is valId', 'To verify cardWin return must be equal cardWin input');

            } else {

                echo "<h4 style='color:red;'> Can't verify update config dailyshow under or over in case valid.</h4>";
            }
        
        } else {
            
            echo "<h4 style='color:red;'> Can't verify update config dailyshow under or over.</h4>"; 
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    function testConfigGetById() {

        $this->load->model('parlayouconfig');

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

                    $this->unit->run( $testResultFirst['message'], 'Id must is a numeric and greater than zero', 'To verify get By id config is invalid', 'To verify id is invalid' );
                    
                }   
            }

            // To verify GetById Config is valid
            //========================================= 
            
            $testResultSecond = $this->parlayouconfig->getById( $id ); 

            if ( is_object($testResultSecond) ) {

                // To verify id return must be equal id input
                $this->unit->run( $testResultSecond->id, $id, 'To verify update config is valid', 'To verify id return must be equal id input');

                // To verify parlayCardId return must be equal parlayCardId input
                $this->unit->run( $testResultSecond->parlayCardId, $config->parlayCardId, 'To verify update config is valId', 'To verify parlayCardId return must be equal parlayCardId database');

                // To verify cardWin return must be equal cardWin input
                $this->unit->run( $testResultSecond->cardWin, $config->cardWin, 'To verify update config is valId', 'To verify cardWin return must be equal cardWin database');

                // To verify cardDate return must be equal cardDate database
                $this->unit->run( $testResultSecond->cardDate, $config->cardDate, 'To verify update config is valId', 'To verify cardDate return must be equal cardDate database');

            } else {

                echo "<h4 style='color:red;'> Can't verify update config dailyshow under or over in case valid.</h4>";
            }

        } 
        else {

            echo "<h4 style='color:red;'> Can't verify get By id config dailyshow under or over. Config empty.</h4>";

        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    function testConfigGetAllByDate() {
        $this->load->model( 'parlayouconfig' );
        $config = $this->parlayouconfig->order_by('cardDate', 'DESC')->get_by(array('id !=' => 0));

        if ( !empty($config)) {

            $date = date('m-d-Y', strtotime($config->cardDate));

            $range = 3;
            $datePlus = date( 'm-d-Y', strtotime( $date . "+$range days" ) );

            $testResultFirst = $this->parlayouconfig->getAllByDate( $date, $range);

            // To verify GetAllByDate Config is valid
            //========================================= 

            if ( isset($testResultFirst['code']) && $testResultFirst['code'] === 0) {

                foreach ($testResultFirst['items'] as $key => $value) {
                    
                    $true = (strtotime($date) >= strtotime(date('m-d-Y', strtotime($value->cardDate))));
                    // To verify date must be lesster than or equal date input
                    $this->unit->run( $true, 'is_true', 'To verify GetAllByDate Config is valid', 'To verify date must be lesster than or equal date input' );
                }
            }

        } else {

            echo "<h4 style='color:red;'> Can't verify get all config by date dailyshow under or over. Config empty.</h4>";

        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());

    }

    //=======================
    // Test dailyshow under or over card    //
    //=======================
    function testAddCard() {

        $this->load->model('parlayouschedule');
        $this->load->model('parlayoucard');
        $schedule = $this->parlayouschedule->order_by('dateTime', 'DESC')->get_by(array('dateTime >=' => date('Y-m-d 00:00:00')));

        if ( ! empty($schedule) ) {

            $nextCartId = $this->parlayoucard->getNextCardId();
            $data = array(
                'parlayCardId' => $nextCartId->parlayCardId,
                'sportScheduleId' => $schedule->id,
                'sportCategoryId' => 1,
                'dateTime' => date('m-d-Y H:i:s', strtotime( str_replace( '-', '/', $schedule->dateTime ) )),
                'team1' => $schedule->team1,
                'team2' => $schedule->team2,
                'team1Name' => 'A',
                'team2Name' => 'B',
                'overUnderScore' => 1,

            );

            // To verify data is empty
            $dataInvalid = '';
            $testResultFirst = $this->parlayoucard->add( $dataInvalid );

            if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                $this->unit->run( $testResultFirst['message'], 'Please enter the required data', 'To verify add card is invalid', 'To verify data is empty');
            }

            // To verify parlayCardId Invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $parlayCardIdInvalid = $data;
                $parlayCardIdInvalid['parlayCardId'] = $value;
                $testResultSecond            = $this->parlayoucard->add( $parlayCardIdInvalid );

                if( is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                    if( !empty($value) ) {

                        $this->unit->run( $testResultSecond['message'][0], 'The parlay Card Id field must contain a number greater than 0.', 'To verify add card is invalid', 'To verify parlayCardId is invalid' );
                    } else {
                        
                        $this->unit->run( $testResultSecond['message'][0], 'The parlay Card Id field is required.', 'To verify add card is invalid', 'To verify parlayCardId is invalid' );
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

                        $this->unit->run( $testResultThird['message'][0], 'The sport Schedule Id field must contain a number greater than 0.', 'To verify add card is invalid', 'To verify sportScheduleId is invalid' );
                    } else {
                        
                        $this->unit->run( $testResultThird['message'][0], 'The sport Schedule Id field is required.', 'To verify add card is invalid', 'To verify sportScheduleId is invalid' );
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

                        $this->unit->run( $testResultFourth['message'][0], 'The sport Category Id field must contain a number greater than 0.', 'To verify add card is invalid', 'To verify sportCategoryId is invalid' );
                    } else {
                        
                        $this->unit->run( $testResultFourth['message'][0], 'The sport Category Id field is required.', 'To verify add card is invalid', 'To verify sportCategoryId is invalid' );
                    }
                    
                }   
            }

            // To verify add card is valid
            // ===========================
            $testResult = $this->parlayoucard->add($data);

            if ( is_object($testResult) ) {

                // To verify parlayCardId return must be equal parlayCardInput
                $this->unit->run((int)$testResult->parlayCardId, (int)$data['parlayCardId'], 'To verify add card is valid', 'To verify parlayCardId return must be equal parlayCardInput');

                // To verify team1 return must be equal parlayCardInput
                $this->unit->run((int)$testResult->team1, (int)$data['team1'], 'To verify add card is valid', 'To verify team1 return must be equal team1 Input');
                // To verify team2 return must be equal parlayCardInput
                $this->unit->run((int)$testResult->team2, (int)$data['team2'], 'To verify add card is valid', 'To verify team2 return must be equal team2 Input');

                // To verify sportCategoryId return must be equal parlayCardInput
                $this->unit->run((int)$testResult->sportCategoryId, (int)$data['sportCategoryId'], 'To verify add card is valid', 'To verify sportCategoryId return must be equal sportCategoryId Input');

                // To verify sportScheduleId return must be equal parlayCardInput
                $this->unit->run($testResult->sportScheduleId, $data['sportScheduleId'], 'To verify add card is valid', 'To verify sportScheduleId return must be equal sportScheduleId Input');

                // To verify dateTime return must be equal parlayCardInput
                $this->unit->run($testResult->dateTime, $schedule->dateTime, 'To verify add card is valid', 'To verify dateTime return must be equal dateTime Input');

                // To verify overUnderScore return must be equal parlayCardInput
                $this->unit->run((int)$testResult->overUnderScore, (int)$data['overUnderScore'], 'To verify add card is valid', 'To verify overUnderScore return must be equal overUnderScore Input');

                // To verify team1Name return must be equal parlayCardInput
                $this->unit->run($testResult->team1Name, $data['team1Name'], 'To verify add card is valid', 'To verify team1Name return must be equal team1Name Input');

                // To verify team2Name return must be equal parlayCardInput
                $this->unit->run($testResult->team2Name, $data['team2Name'], 'To verify add card is valid', 'To verify team2Name return must be equal team2Name Input');
            } else {

                echo "<h4 style='color:red;'> Can't verify add card dailyshow under or over in case valid.</h4>";
            }
        } else {

            echo "<h4 style='color:red;'> Can't verify add card dailyshow under or over. schedule empty.</h4>";

        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    function testUpdateCard() {
        $this->load->model( 'parlayoucard' );
        $card = $this->parlayoucard->order_by('parlayCardId', 'DESC')->get_by(array('id !=' => 0));

        if ( !empty($card) ) {

            $id = $card->id;
            $sportParlayCard = $card->parlayCardId;
            $dataUpdate = array(
                'parlayCardId' => ((int)$sportParlayCard + 1),
                'sportScheduleId' => $card->sportScheduleId,
                'sportCategoryId' => $card->sportCategoryId,
                'dateTime' => date('m-d-Y H:i:s', strtotime( str_replace( '-', '/', $card->dateTime ) )),
                'team1' => $card->team1,
                'team2' => $card->team2,
                'team1Name' => 'A',
                'team2Name' => 'B',
                'overUnderScore' => 2,
                );
            
            // To verify update card is invalid
            // ================================
            
            // To verify id input is invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultFirst = $this->parlayoucard->edit( $value, $dataUpdate );
                if( is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                    $this->unit->run( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify update card is invalid', 'To verify id is invalid' );
                    
                }   
            }

            // To verify data is empty
            $dataInvalid = '';
            $testResultSecond = $this->parlayoucard->edit( $id, $dataInvalid );

            if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                $this->unit->run( $testResultSecond['message'], 'Please enter the required data', 'To verify update card is invalid', 'To verify data is empty');
            }

            // To verify update card is valid
            // ================================
            $testResultThird = $this->parlayoucard->edit( $id, $dataUpdate);

            if ( is_object($testResultThird) ) {

                // To verify id return must be equal id input
                $this->unit->run((int)$testResultThird->id, (int)$id, 'To verify update card is valid', 'To verify id return must be equal parlayCardInput');

                // To verify parlayCardId return must be equal parlayCardInput
                $this->unit->run((int)$testResultThird->parlayCardId, (int)$dataUpdate['parlayCardId'], 'To verify update card is valid', 'To verify parlayCardId return must be equal parlayCardInput');

                // To verify team1 return must be equal parlayCardInput
                $this->unit->run((int)$testResultThird->team1, (int)$dataUpdate['team1'], 'To verify update card is valid', 'To verify team1 return must be equal team1 Input');
                // To verify team2 return must be equal parlayCardInput
                $this->unit->run((int)$testResultThird->team2, (int)$dataUpdate['team2'], 'To verify update card is valid', 'To verify team2 return must be equal team2 Input');

                // To verify sportCategoryId return must be equal parlayCardInput
                $this->unit->run((int)$testResultThird->sportCategoryId, (int)$dataUpdate['sportCategoryId'], 'To verify update card is valid', 'To verify sportCategoryId return must be equal sportCategoryId Input');

                // To verify sportScheduleId return must be equal parlayCardInput
                $this->unit->run($testResultThird->sportScheduleId, $dataUpdate['sportScheduleId'], 'To verify update card is valid', 'To verify sportScheduleId return must be equal sportScheduleId Input');

                // To verify dateTime return must be equal parlayCardInput
                $this->unit->run($testResultThird->dateTime, $card->dateTime, 'To verify update card is valid', 'To verify dateTime return must be equal dateTime Input');

                // To verify overUnderScore return must be equal parlayCardInput
                $this->unit->run((int)$testResultThird->overUnderScore, (int)$dataUpdate['overUnderScore'], 'To verify update card is valid', 'To verify overUnderScore return must be equal overUnderScore Input');

                // To verify team1Name return must be equal parlayCardInput
                $this->unit->run($testResultThird->team1Name, $dataUpdate['team1Name'], 'To verify update card is valid', 'To verify team1Name return must be equal team1Name Input');

                // To verify team2Name return must be equal parlayCardInput
                $this->unit->run($testResultThird->team2Name, $dataUpdate['team2Name'], 'To verify update card is valid', 'To verify team2Name return must be equal team2Name Input');
            } else {

                echo "<h4 style='color:red;'> Can't verify Update card dailyshow under or over in case valid.</h4>"; 
            }
                        
        } else {

            echo "<h4 style='color:red;'> Can't verify Update card dailyshow under or over. schedule empty.</h4>"; 
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    function testGetCardByDate() {

        $this->load->model('parlayoucard');
        $this->load->model('parlayouconfig');
        $card = $this->parlayouconfig->order_by('cardDate', 'DESC')->get_by(array('id !=' => 0));

        if ( !empty($card)) {

            $date = date('m-d-Y', strtotime($card->cardDate));

            $range = 3;
            $datePlus = date( 'm-d-Y', strtotime( $date . "+$range days" ) );

            $testResultFirst = $this->parlayoucard->getAll( $date, $range);
            // To verify GetAllByDate card is valid
            //========================================= 
            if ( isset($testResultFirst['code']) && $testResultFirst['code'] === 0) {

                foreach ($testResultFirst['games'] as $key => $value) {
                    
                    $true = (strtotime($date) >= strtotime(date('m-d-Y', strtotime($value->cardDate))));
                    // To verify date must be lesster than or equal date input
                    $this->unit->run( $true, 'is_true', 'To verify GetAllByDate card is valid', 'To verify date must be lesster than or equal date input' );
                }
            }

        } else {

            echo "<h4 style='color:red;'> Can't verify get all card by date dailyshow under or over. Config empty.</h4>";

        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());

    }

    //=============================================
    // Test dailyshow under or over results      //
    //=============================================
    function testResultAdd() {

        $this->load->model('parlayouresult');

        // check parlay schedule id
        $parlayOUCard = $this->db->query('SELECT *
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
                'parlayCardId' => $parlayOUCard[0]->parlayCardId,
                'score1' => 12,
                'score2' => 20,
            );

            // To verify add result is invalid
            // ===============================
            // To verify parlayCardId Invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $parlayCardIdInvalid = $data;
                $parlayCardIdInvalid['parlayCardId'] = $value;
                $testResultSecond            = $this->parlayouresult->add( $parlayCardIdInvalid );

                if( is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                    if( !empty($value) ) {

                        $this->unit->run( $testResultSecond['message'][0], 'The parlay Card Id field must contain a number greater than 0.', 'To verify add result is invalid', 'To verify parlayCardId is invalid' );
                    } else {
                        
                        $this->unit->run( $testResultSecond['message'][0], 'The parlay Card Id field is required.', 'To verify add result is invalid', 'To verify parlayCardId is invalid' );
                    }
                    
                }   
            }
            // To verify sportScheduleId Invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $sportScheduleIdInvalid = $data;
                $sportScheduleIdInvalid['sportScheduleId'] = $value;
                $testResultThird            = $this->parlayouresult->add( $sportScheduleIdInvalid );
                if( is_array($testResultThird) && isset($testResultThird['message']) ) {

                    if( !empty($value) ) {

                        $this->unit->run( $testResultThird['message'][0], 'The sport Schedule Id field must contain a number greater than 0.', 'To verify add result is invalid', 'To verify sportScheduleId is invalid' );
                    } else {
                        
                        $this->unit->run( $testResultThird['message'][0], 'The sport Schedule Id field is required.', 'To verify add result is invalid', 'To verify sportScheduleId is invalid' );
                    }
                    
                }   
            }

            // To verify add result is valid
            // ===============================
            $testResult = $this->parlayouresult->add( $data );

            if ( is_object($testResult) ) {

                // To verify parlayCardId return must be equal parlayCardId input
                $this->unit->run((int)$testResult->parlayCardId, (int)$data['parlayCardId'], 'To verify add result is valid', 'To verify parlayCardId return must be equal parlayCardId input');

                // To verify sportScheduleId return must be equal sportScheduleId input
                $this->unit->run((int)$testResult->sportScheduleId, (int)$data['sportScheduleId'], 'To verify add result is valid', 'To verify sportScheduleId return must be equal sportScheduleId input');

                // To verify score1 return must be equal score1 input
                $this->unit->run((int)$testResult->score1, (int)$data['score1'], 'To verify add result is valid', 'To verify score1 return must be equal score1 input');

                // To verify score2 return must be equal score2 input
                $this->unit->run((int)$testResult->score2, (int)$data['score2'], 'To verify add result is valid', 'To verify score2 return must be equal score2 input');

                $countScore = $data['score2'] + $data['score1'];
                $score = (int)$parlayOUCard[0]->overUnderScore;
                if ( $countScore >= $score) {

                    // To verify overUnder return must be equal 2
                    $this->unit->run((int)$testResult->overUnder, 2, 'To verify add result is valid', 'To verify overUnder return must be equal 2');

                } else {

                    // To verify overUnder return must be equal 1
                    $this->unit->run((int)$testResult->overUnder, 1, 'To verify add result is valid', 'To verify overUnder return must be equal 1'); 
                }

            }  else {

                echo "<h4 style='color:red;'> Can't verify add result dailyshow under or over in case valid</h4>";
            }  

        } else {

            echo "<h4 style='color:red;'> Can't verify add result dailyshow under or over. Schedule is'nt exist.</h4>";

        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    function testResultUpdate() {

    }

    function testResultGetAll() {

    }

    /**
     * returnResult 
     * @param  array $results 
     * @return string
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